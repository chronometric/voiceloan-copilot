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
| POST | `/api/voice/tools` | Body: `{ "call_sid", "name": "get_borrower" \| "get_urla_context" \| "patch_borrower", "arguments": {} }` — unified tool bridge |

**Node bridge** (`voice-bridge/`): `npm install`, copy `.env.example` → `.env`, then `npm start`. Listens on `ws://0.0.0.0:8765/ws` by default. Twilio (or a test client) connects with query `?CallSid=...&BorrowerUuid=<uuid>`. The service registers the session in Laravel, opens **OpenAI Realtime** WebSocket, relays audio/text, and runs tools via `/api/voice/tools`. Realtime event names vary by API revision — adjust `voice-bridge/src/openaiRealtime.js` `parseFunctionCallEvent` if needed.

### Phase 3 — Conversational URLA / 1003 logic

- **Field inventory:** `config/urla1003.php` — stages (`intake` → `review`), `required_by_stage` paths (dot notation), `fields` labels, and `prompts` (system + per-section copy).
- **Services:** `App\Services\Urla1003\` — `Urla1003FieldResolver`, `Urla1003SnapshotService` (compact snapshot + missing fields per stage), `Urla1003PromptService` (prompt pack), `UrlaConversationStateService` (state + sync after PATCH/tools).
- **State (DB):** `urla_conversation_states` — one row per borrower (`borrower_id` unique), optional `call_sid`, `current_stage`, `current_section`, `clarification_counts` (JSON), `last_tool_results` (JSON). Sync runs after voice `PATCH /borrower`, and after tools `get_borrower`, `patch_borrower`, `get_urla_context`.

| Method | Endpoint |
|--------|----------|
| GET | `/api/voice/sessions/{callSid}/urla/context` — prompt pack (system, section_instruction, missing_fields, snapshot_compact, clarification_counts) |
| PATCH | `/api/voice/sessions/{callSid}/urla/state` — body: `current_stage` and/or `current_section` and/or `increment_clarification_for` (field path) |

**Voice tools:** `get_urla_context` (same payload as GET), plus `get_borrower` / `patch_borrower`. Redis optional; this implementation uses the database only.

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
