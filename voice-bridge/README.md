# voiceloan-voice-bridge

Node WebSocket service: **Twilio Conversation Relay** (or any client) ↔ **OpenAI Realtime API** ↔ **Laravel** `/api/voice/*`.

## Setup

1. In Laravel `.env`: `VOICE_BRIDGE_KEY=<long random secret>` and `php artisan migrate`.
2. Copy `.env.example` to `.env` here; set `OPENAI_API_KEY`, `VOICE_BRIDGE_KEY` (same as Laravel), `LARAVEL_URL`.
3. `npm install` && `npm start`.

## Connection URL

```
wss://<your-host>:8765/ws?CallSid=<CA...>&BorrowerUuid=<borrower uuid>
```

`BorrowerUuid` must exist in `borrowers.uuid`. The bridge registers `POST /api/voice/sessions` then connects upstream to OpenAI Realtime.

## Twilio

Point **Conversation Relay** (or your Voice webhook) at this WebSocket URL. If Twilio sends `CallSid` only in the first JSON frame, ensure `BorrowerUuid` is still available (query string, Twilio `<Parameter>`, or your IVR).

## Audio codecs

Twilio media is often **mu-law**; OpenAI Realtime expects **PCM16** in `input_audio_buffer.append`. Production deployments should transcode (e.g. ffmpeg or a DSP step). This repo forwards base64 payloads as a starting point.

## OpenAI Realtime events

Function-call event shapes change between API snapshots. If tools never fire, update `src/openaiRealtime.js` → `parseFunctionCallEvent` to match your model’s events.
