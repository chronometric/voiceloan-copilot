## VoiceLoan Copilot (skeleton)

Session-based **auth** (`/login`, `/register`), **borrowers** plus **identity**, **employment**, **assets**, **declarations** tables, and **audit_logs**. Intended as the PHP dashboard for OpenAI Realtime + Twilio middleware.

### Setup

1. `composer install`
2. Copy `.env.example` to `.env`, run `php artisan key:generate`
3. SQLite (default): ensure `database/database.sqlite` exists; `DB_CONNECTION=sqlite` in `.env`
4. `php artisan migrate`

### Phase 1 (dashboard + API)

- **Web:** `/dashboard`, `/borrowers` (list/create/edit with tabs: Main, Identity, Employment, Assets, Declarations, Audit)
- **Audit:** model observers log create/update/delete on borrower-related models to `audit_logs` (user, entity, old/new JSON)
- **API (Sanctum):** `POST /api/login` with `email`, `password` returns `{ "token": "...", "token_type": "Bearer" }`. Send `Authorization: Bearer <token>` and `Accept: application/json`.

| Method | Endpoint |
|--------|----------|
| GET/PATCH/DELETE | `/api/borrowers/{uuid}` |
| GET/PATCH | `/api/borrowers/{uuid}/identity` |
| GET/POST/PATCH/DELETE | `/api/borrowers/{uuid}/employments` … `/employments/{id}` |
| GET/POST/PATCH/DELETE | `/api/borrowers/{uuid}/assets` … `/assets/{id}` |
| GET/PATCH | `/api/borrowers/{uuid}/declaration` |
| GET | `/api/user` |

Validation errors return **422** with Laravel’s standard `{ "message": "...", "errors": { "field": ["..."] } }` shape.

### Phase 2 — Voice middleware (Node)

Set `VOICE_BRIDGE_KEY` in Laravel `.env` (same value as `voice-bridge/.env` `VOICE_BRIDGE_KEY`).

| Header | Value |
|--------|--------|
| `X-Voice-Bridge-Key` | `VOICE_BRIDGE_KEY` |

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/voice/sessions` | Body: `{ "call_sid", "borrower_uuid" }` — bind Twilio CallSid → borrower |
| GET | `/api/voice/sessions/{callSid}/borrower` | Full borrower JSON (for debugging / tools) |
| PATCH | `/api/voice/sessions/{callSid}/borrower` | Partial update; **422** + `errors` for re-asks |
| POST | `/api/voice/tools` | Body: `{ "call_sid", "name", "arguments" }` — tools include `get_borrower`, `get_urla_context`, `patch_borrower`, `send_sms`, `transfer_to_human` |

**Node bridge** (`voice-bridge/`): `npm install`, copy `.env.example` → `.env`, then `npm start`. Listens on `ws://0.0.0.0:8765/ws` by default. Twilio (or a test client) connects with query `?CallSid=...&BorrowerUuid=<uuid>`. The service registers the session in Laravel, opens **OpenAI Realtime** WebSocket, relays audio/text, and runs tools via `/api/voice/tools`. Realtime event names vary by API revision — adjust `voice-bridge/src/openaiRealtime.js` `parseFunctionCallEvent` if needed.

### Phase 3 — Conversational URLA / 1003 logic

- **Field inventory:** `config/urla1003.php` — stages (`intake` → `review`), `required_by_stage` paths (dot notation), `fields` labels, and `prompts` (system + per-section copy).
- **Services:** `App\Services\Urla1003\` — `Urla1003FieldResolver`, `Urla1003SnapshotService` (compact snapshot + missing fields per stage), `Urla1003PromptService` (prompt pack), `UrlaConversationStateService` (state + sync after PATCH/tools).
- **State (DB):** `urla_conversation_states` — one row per borrower (`borrower_id` unique), optional `call_sid`, `current_stage`, `current_section`, `clarification_counts` (JSON), `last_tool_results` (JSON). Sync runs after voice `PATCH /borrower`, and after tools `get_borrower`, `patch_borrower`, `get_urla_context`, `transfer_to_human`.

| Method | Endpoint |
|--------|----------|
| GET | `/api/voice/sessions/{callSid}/urla/context` — prompt pack (system, section_instruction, missing_fields, snapshot_compact, clarification_counts) |
| PATCH | `/api/voice/sessions/{callSid}/urla/state` — body: `current_stage` and/or `current_section` and/or `increment_clarification_for` (field path) |

**Voice tools:** `get_urla_context` (same payload as GET), `get_borrower`, `patch_borrower`, plus Phase 4 tools below. Redis optional; this implementation uses the database only.

### Phase 4 — Twilio + SMS + compliance

- **SMS:** `App\Services\TwilioSmsService` → Twilio REST API (`TWILIO_*` in `.env`). Voice tool `send_sms` with `body`, optional `link_url`, optional `to_e164`. Compliance footer from `config/compliance.php` is appended to every SMS.
- **Audit:** successful sends log `sms_sent`; failures log `sms_failed` on `audit_logs` (`entity_type` `sms.outbound`, `new_values` JSON).
- **Compliance:** `config/compliance.php` — `voice_disclaimer` (injected into URLA prompt pack + line about `transfer_to_human`), `sms_footer`, `transfer_status` (default `escalated`).
- **transfer_to_human:** voice tool sets borrower `status` to `transfer_status` (must be in `config/borrower.statuses`); optional `reason` stored in `last_tool_results` / tool response.

### Phase 5 — Security & compliance hardening

- **Secrets:** API keys and bridge credentials live only in `.env` / deployment secrets (`OPENAI_*`, `TWILIO_*`, `VOICE_BRIDGE_KEY`, `APP_KEY`). The voice bridge authenticates with `X-Voice-Bridge-Key`, **not** Sanctum; rotate the bridge key by updating Laravel + `voice-bridge/.env` and restarting the Node service.
- **Sanctum tokens:** Optional expiry via `SANCTUM_TOKEN_EXPIRATION` (minutes). After compromise or key rotation, revoke all API tokens: `php artisan sanctum:revoke-tokens --all` (clients must log in again).
- **PII:** `borrower_identity.ssn_last4` is stored encrypted at rest (`encrypted` cast). `audit_logs` redact sensitive keys; SMS audit entries mask destination numbers. Voice/URLA JSON masks SSN in `BorrowerResource` for `api/voice/*` and masks `identity.ssn_last4` in URLA compact snapshots sent to the model.
- **TCPA / consent:** `config/compliance.php` documents that consent capture and legal copy are your responsibility; customize `sms_footer`, voice disclaimers, and internal policies with counsel before production outreach.
- **Rate limits:** `config/rate_limits.php` + `AppServiceProvider` — `throttle:login` on `POST /api/login`, `throttle:voice` on `/api/voice/*`, `throttle:api` on Sanctum JSON API. Tune with `RATE_LIMIT_LOGIN_PER_MINUTE`, `RATE_LIMIT_VOICE_PER_MINUTE`, `RATE_LIMIT_API_PER_MINUTE`.

### Phase 6 — Demo & handoff

- **End-to-end demo (local):** Full checklist is in **`docs/DEMO_E2E.md`** — Laravel + voice-bridge + OpenAI Realtime, register a session, simulate or place a call, confirm URLA/borrower updates, then verify the **dashboard** and optional **SMS** follow-up.
- **Loom / portfolio recording:** Use **`docs/LOOM_DEMO_SCRIPT.md`** — narrator script and shot list for a ~3–5 minute walkthrough of the same flow for clients or Upwork portfolio.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
