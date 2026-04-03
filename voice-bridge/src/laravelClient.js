/**
 * Server-to-server calls to Laravel /api/voice/* with X-Voice-Bridge-Key.
 */

const DEFAULT_FETCH_MS = 30000;

function fetchWithTimeout(url, options = {}, timeoutMs = DEFAULT_FETCH_MS) {
  const controller = new AbortController();
  const id = setTimeout(() => controller.abort(), timeoutMs);
  return fetch(url, { ...options, signal: controller.signal }).finally(() => clearTimeout(id));
}

export async function registerSession(laravelUrl, bridgeKey, callSid, borrowerUuid) {
  const res = await fetchWithTimeout(`${laravelUrl.replace(/\/$/, '')}/api/voice/sessions`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-Voice-Bridge-Key': bridgeKey,
    },
    body: JSON.stringify({ call_sid: callSid, borrower_uuid: borrowerUuid }),
  });
  if (!res.ok) {
    const text = await res.text();
    throw new Error(`registerSession failed ${res.status}: ${text}`);
  }
  return res.json();
}

export async function executeVoiceTool(laravelUrl, bridgeKey, callSid, name, args) {
  const res = await fetchWithTimeout(`${laravelUrl.replace(/\/$/, '')}/api/voice/tools`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-Voice-Bridge-Key': bridgeKey,
    },
    body: JSON.stringify({
      call_sid: callSid,
      name,
      arguments: args && typeof args === 'object' ? args : {},
    }),
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const err = new Error(data.message || `tool failed ${res.status}`);
    err.status = res.status;
    err.body = data;
    throw err;
  }
  return data;
}
