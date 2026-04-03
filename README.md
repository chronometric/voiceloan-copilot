# VoiceLoan Copilot

A **Laravel** application that serves as the system of record for mortgage borrower data: a staff **dashboard**, a **JSON API** (Sanctum), and **voice** integrations via a **Node** bridge to OpenAI Realtime, with optional **Twilio SMS**, conversational **URLA / 1003** helpers, audit logging, and security-focused defaults.

---

## Contents

- [Stack](#stack)
- [Requirements](#requirements)
- [Quick start](#quick-start)
- [Configuration](#configuration)
- [Testing](#testing)
- [API overview](#api-overview)
- [Voice bridge](#voice-bridge)
- [Feature map (phases)](#feature-map-phases)
- [Documentation](#documentation)
- [Security & compliance](#security--compliance)
- [License](#license)

---

## Stack

| Layer | Technology |
|--------|------------|
| Application | PHP 8.2+, Laravel 11 |
| Dashboard & API | Blade, Sanctum, SQLite (default) or your database |
| Voice relay | Node 20+, `ws`, OpenAI Realtime API |
| Outbound SMS | Twilio REST (`TWILIO_*` in `.env`) |

---

## Requirements

- **PHP** 8.2 or newer with extensions required by Laravel  
- **Composer** 2.x  
- **Node.js** 20+ (for `voice-bridge/`)  
- **SQLite** (default) or MySQL/PostgreSQL if you change `DB_*` in `.env`

---

## Quick start

1. Install PHP dependencies: `composer install`  
2. Copy environment file: `cp .env.example .env` (Windows: `copy .env.example .env`)  
3. Generate app key: `php artisan key:generate`  
4. For SQLite (default): create `database/database.sqlite` if missing; ensure `DB_CONNECTION=sqlite` in `.env`  
5. Run migrations: `php artisan migrate`  
6. Start the app: `php artisan serve`  
7. **Voice bridge** (optional): `cd voice-bridge && npm install && cp .env.example .env`, set `OPENAI_API_KEY`, `VOICE_BRIDGE_KEY` (match Laravel), `LARAVEL_URL`, then `npm start`

Register a user at `/register`, then use the dashboard at `/dashboard` and borrowers at `/borrowers`.

---

## Configuration

All secrets belong in **`.env`** (never commit real keys). Highlights:

| Area | Variables (see `.env.example`) |
|------|--------------------------------|
| App | `APP_KEY`, `APP_URL` |
| Voice bridge | `VOICE_BRIDGE_KEY`, `VOICE_SESSION_TTL_HOURS` |
| OpenAI (Node) | `OPENAI_API_KEY`, `OPENAI_REALTIME_MODEL` in `voice-bridge/.env` |
| Twilio | `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_FROM_NUMBER` |
| Compliance | `COMPLIANCE_SMS_FOOTER`, `COMPLIANCE_TRANSFER_STATUS`, optional `COMPLIANCE_VOICE_DISCLAIMER` |
| API tokens | Optional `SANCTUM_TOKEN_EXPIRATION` (minutes) |
| Rate limits | `RATE_LIMIT_LOGIN_PER_MINUTE`, `RATE_LIMIT_VOICE_PER_MINUTE`, `RATE_LIMIT_API_PER_MINUTE` |

The voice bridge authenticates to Laravel with the header **`X-Voice-Bridge-Key`**. It does **not** use Sanctum; dashboard JSON API uses **Bearer** tokens from `POST /api/login`.

---

## Testing

```bash
composer test
```

PHPUnit uses **SQLite in-memory** (`phpunit.xml`). The suite expects `VOICE_BRIDGE_KEY=test-voice-bridge-key` in the PHPUnit environment (already set there). Run `composer install` before testing.

---

## API overview

Base path: **`/api`**. Send `Accept: application/json`. Authenticated routes require `Authorization: Bearer <token>` from `POST /api/login` with `email` and `password`.

| Method | Endpoint |
|--------|----------|
| POST | `/api/login` — returns `token` and `token_type` |
| GET | `/api/user` |
| GET / PATCH / DELETE | `/api/borrowers/{uuid}` |
| GET / PATCH | `/api/borrowers/{uuid}/identity` |
| GET / POST / PATCH / DELETE | `/api/borrowers/{uuid}/employments` … `/employments/{id}` |
| GET / POST / PATCH / DELETE | `/api/borrowers/{uuid}/assets` … `/assets/{id}` |
| GET / PATCH | `/api/borrowers/{uuid}/declaration` |

Validation errors respond with **422** and Laravel’s standard `message` / `errors` payload.

---

## Voice bridge

Set **`VOICE_BRIDGE_KEY`** in Laravel `.env` and the same value in **`voice-bridge/.env`**.

| Header | Value |
|--------|--------|
| `X-Voice-Bridge-Key` | Same as `VOICE_BRIDGE_KEY` |

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/voice/sessions` | Body: `call_sid`, `borrower_uuid` — bind session to borrower |
| GET | `/api/voice/sessions/{callSid}/borrower` | Borrower JSON (tools / debugging) |
| PATCH | `/api/voice/sessions/{callSid}/borrower` | Partial update; **422** with `errors` for re-prompts |
| GET | `/api/voice/sessions/{callSid}/urla/context` | URLA prompt pack |
| PATCH | `/api/voice/sessions/{callSid}/urla/state` | Stage / section / clarification counters |
| POST | `/api/voice/tools` | Tool execution: `call_sid`, `name`, `arguments` |

**Tools** include: `get_borrower`, `get_urla_context`, `patch_borrower`, `send_sms`, `transfer_to_human`.

The Node service (`voice-bridge/`) listens on **`ws://0.0.0.0:8765/ws`** by default. Connect with query parameters `CallSid` and `BorrowerUuid`. It registers the session in Laravel, opens **OpenAI Realtime**, relays audio/text, and calls Laravel for tools. If Realtime event shapes change between API revisions, adjust `voice-bridge/src/openaiRealtime.js` (e.g. `parseFunctionCallEvent`).

---

## Feature map (phases)

### Phase 1 — Dashboard and API

- Web UI: `/dashboard`, `/borrowers` with tabs (Main, Identity, Employment, Assets, Declarations, Audit).  
- **Audit:** Observers write create/update/delete events to `audit_logs`.  
- **API:** Sanctum token from `/api/login` for JSON access to borrowers and related resources.

### Phase 2 — Voice middleware

- Node bridge + Laravel `/api/voice/*` routes secured by `X-Voice-Bridge-Key`.  
- Session binding and tool bridge as described above.

### Phase 3 — Conversational URLA / 1003

- **`config/urla1003.php`:** stages, required fields, labels, prompts.  
- **Services:** `App\Services\Urla1003\` — field resolution, snapshots, prompt packs, conversation state.  
- **Table:** `urla_conversation_states` (per borrower; sync after PATCH and relevant tools).  
- State uses the database only (no Redis required in this implementation).

### Phase 4 — Twilio and SMS

- **`App\Services\TwilioSmsService`** — outbound SMS; compliance footer from `config/compliance.php`.  
- **`send_sms` tool:** body, optional `link_url`, optional `to_e164`.  
- Audit actions `sms_sent` / `sms_failed`; **`transfer_to_human`** sets borrower status per `compliance.transfer_status` (must match `config/borrower.statuses`).

### Phase 5 — Security and compliance hardening

- Secrets only via environment; rotate **`VOICE_BRIDGE_KEY`** in Laravel and the bridge, then restart Node.  
- Optional Sanctum token expiry; **`php artisan sanctum:revoke-tokens --all`** to invalidate API tokens.  
- **PII:** `ssn_last4` encrypted at rest; audit redaction; masked values in voice/URLA surfaces where configured.  
- **TCPA / consent:** Configure copy with counsel; see `config/compliance.php`.  
- **Rate limits:** `config/rate_limits.php` — login, voice, and Sanctum API routes throttled (tunable via env).

### Phase 6 — Demo and handoff

- **End-to-end demo:** `docs/DEMO_E2E.md`  
- **Loom / portfolio script:** `docs/LOOM_DEMO_SCRIPT.md`

---

## Documentation

| Document | Description |
|----------|-------------|
| `docs/DEMO_E2E.md` | Local end-to-end demo and client handoff checklist |
| `docs/LOOM_DEMO_SCRIPT.md` | Narrator script and shot list for a short screen recording |

---

## Security & compliance

- Do not commit `.env` or live API keys.  
- Review SMS and voice compliance (TCPA, consent, opt-out) with legal counsel before production.  
- For vulnerabilities **in this application’s code**, report through the repository maintainer’s preferred channel. For issues in the **Laravel framework**, follow [Laravel’s security policy](https://laravel.com/docs/contributions#security-vulnerabilities).

---

## License

This project is open-sourced under the [MIT License](https://opensource.org/licenses/MIT).

The [Laravel framework](https://laravel.com) is also MIT-licensed. See the [Laravel documentation](https://laravel.com/docs) for framework usage, contributions, and upstream security reporting.
