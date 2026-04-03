# Loom demo script (portfolio / clients)

**Length target:** ~3–5 minutes · **Tone:** clear, confident, not rushed

Use **one screen** for the dashboard and optionally a **second window** for terminal (bridge logs) or Twilio debugger—only if it helps clarity.

---

## 0. Intro (20–30 s)

**Say:**

> “This is VoiceLoan Copilot—a small Laravel dashboard plus a Node voice bridge that talks to OpenAI Realtime. I’ll show a borrower record, simulate or place a voice session, update data through the assistant, refresh the dashboard to prove it persisted, then optionally send an SMS follow-up.”

**Do:** Show the repo or product name in the browser tab; stay on **login** or **dashboard**.

---

## 1. Dashboard — borrower setup (45–60 s)

**Say:**

> “I’m logged into the app. Here’s the borrower list—I’ll open a borrower we’ll use for the call. I’ve set name, email, and phone so the voice layer can read and patch CRM fields.”

**Do:** Navigate **Borrowers** → open one borrower → briefly show **Main** (and **Identity** if relevant). Point at **phone** and **UUID** (or mention UUID is used to bind the call).

---

## 2. Voice path — session + bridge (45–60 s)

**Say:**

> “The voice service registers the Twilio call ID with this borrower’s UUID in Laravel, then opens a WebSocket to OpenAI Realtime. Tools like get borrower, patch borrower, and URLA context all go through the PHP API—so one source of truth.”

**Do:** Optionally show `voice-bridge` terminal (“listening”) or a diagram slide; if live, show connection with `CallSid` and `BorrowerUuid` in the query string.

---

## 3. Live or narrated conversation (60–90 s)

**Say:**

> “On the call, the assistant confirms or updates fields—for example display name or a URLA section. If something’s invalid, the API returns validation errors and the model can re-ask.”

**Do:** Short clip of speaking **or** narrate over a pre-recorded call: one successful **patch** and one **read-back** from the assistant.

---

## 4. Prove it in the UI (30–45 s)

**Say:**

> “Back in the dashboard—refresh—and the same fields we set on the call are here. Audit shows what changed.”

**Do:** Hard refresh borrower page → show updated fields → open **Audit** tab for one relevant line.

---

## 5. Optional SMS (30–45 s)

**Say:**

> “For follow-up—doc links or reminders—the assistant can trigger SMS through Twilio. Compliance footer is appended server-side, and sends are logged in audit.”

**Do:** Show **Audit** entry for `sms_sent` or a phone screenshot (blur PII if needed).

---

## 6. Close (15–20 s)

**Say:**

> “So: voice capture and URLA logic sit on OpenAI Realtime; persistence, compliance hooks, and the loan-officer dashboard are Laravel. Happy to walk through deployment or security next.”

**Do:** Stop recording; end screen optional.

---

## Shot list (editor)

| # | Visual | Audio hook |
|---|--------|------------|
| 1 | Login / dashboard | What the product is |
| 2 | Borrower detail + phone | Data we’ll use on the call |
| 3 | Bridge or WS URL (optional) | How the call binds to CRM |
| 4 | Voice snippet (or VO) | Tools updating CRM |
| 5 | Refresh + Audit | Proof + traceability |
| 6 | SMS audit or phone (optional) | Outbound follow-up |

---

## Tips

- **Blur** phone numbers and tokens in post.  
- **Mute** tab noise; use a clean mic.  
- If the call fails live, **skip** to the dashboard proof and say “in a full run, the same tools fire.”
