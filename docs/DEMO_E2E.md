# End-to-end demo (Phase 6)

Goal: **call (or simulated session) → voice collects/updates data → dashboard shows changes → optional SMS.**

## Prerequisites

| Component | Notes |
|-----------|--------|
| Laravel | `php artisan serve` (or your host), `.env` with `APP_KEY`, DB migrated |
| Voice bridge | `cd voice-bridge && npm install && npm start` — default `ws://127.0.0.1:8765/ws` |
| Secrets | `OPENAI_API_KEY` in `voice-bridge/.env`; `VOICE_BRIDGE_KEY` matches Laravel |
| Twilio (optional) | `TWILIO_*` in Laravel for real SMS; for voice media, point Twilio Media Streams (or your relay) at the bridge WebSocket with `CallSid` + `BorrowerUuid` query params |
| Borrower | At least one borrower with a **real mobile** if testing SMS; note **UUID** for the bridge URL |

## Sequence

1. **Dashboard — seed the story**  
   Log in → **Borrowers** → open or create a borrower. Set **display name**, **email**, **phone** (E.164 or 10-digit US). Save. Open **Identity** / **Main** tabs if you want visible fields for the demo.

2. **Start the voice session**  
   When the bridge accepts a connection it calls `POST /api/voice/sessions` with `call_sid` and `borrower_uuid`. In production Twilio supplies `CallSid`; locally use a synthetic id (e.g. `CAtest001`) and the borrower’s UUID from the URL or edit screen.

3. **Connect audio**  
   Connect your Twilio stream or **test client** to:  
   `ws://<host>:8765/ws?CallSid=<callSid>&BorrowerUuid=<borrower-uuid>`  
   (Use `wss://` and public host/ngrok if Twilio is in the cloud.)

4. **Run the conversation**  
   Ask the assistant to confirm or update fields (e.g. name, email, phone, URLA section). Tools (`get_borrower`, `get_urla_context`, `patch_borrower`, etc.) run through Laravel; **422** responses drive re-prompts in voice.

5. **Verify dashboard**  
   Refresh the borrower **Main** / **Identity** / **Audit** tabs — updates and **audit_logs** should reflect voice-driven changes and tool activity.

6. **Optional SMS follow-up**  
   With Twilio configured, invoke the **`send_sms`** tool (or exercise it from your integration test) with a short body and optional `link_url` (doc or magic link). Check the borrower’s phone and **Audit** for `sms_sent` / compliance footer.

## Handoff checklist (for clients)

- [ ] `.env` production values: `APP_URL`, `VOICE_BRIDGE_KEY`, `OPENAI_*`, `TWILIO_*`, DB  
- [ ] Voice bridge deployed with same `VOICE_BRIDGE_KEY`; TLS for `wss://`  
- [ ] Rate limits and TCPA/consent copy reviewed (`config/compliance.php`, README Phase 5)  
- [ ] Twilio numbers and consent flow aligned with counsel  
- [ ] Backup / retention policy for `audit_logs` and borrower PII  
