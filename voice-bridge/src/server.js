import 'dotenv/config';
import http from 'http';
import WebSocket, { WebSocketServer } from 'ws';
import { URL } from 'url';
import {
  connectOpenAIRealtime,
  buildSessionUpdate,
  parseFunctionCallEvent,
  buildFunctionOutputItem,
  buildResponseCreate,
} from './openaiRealtime.js';
import { twilioToOpenAI, extractCallSidFromTwilioMessage } from './twilioRelay.js';
import { registerSession, executeVoiceTool } from './laravelClient.js';

const {
  OPENAI_API_KEY,
  OPENAI_REALTIME_MODEL = 'gpt-4o-realtime-preview-2024-12-17',
  LARAVEL_URL = 'http://127.0.0.1:8000',
  VOICE_BRIDGE_KEY,
  BRIDGE_HOST = '0.0.0.0',
  BRIDGE_PORT = '8765',
  BRIDGE_PATH = '/ws',
} = process.env;

if (!OPENAI_API_KEY) {
  console.error('Missing OPENAI_API_KEY');
  process.exit(1);
}
if (!VOICE_BRIDGE_KEY) {
  console.error('Missing VOICE_BRIDGE_KEY (must match Laravel VOICE_BRIDGE_KEY)');
  process.exit(1);
}

const server = http.createServer((_req, res) => {
  res.writeHead(200, { 'Content-Type': 'text/plain' });
  res.end('voiceloan-voice-bridge\n');
});

const wss = new WebSocketServer({ noServer: true });

server.on('upgrade', (request, socket, head) => {
  const path = new URL(request.url || '/', `http://${request.headers.host}`).pathname;
  if (path !== BRIDGE_PATH) {
    socket.destroy();
    return;
  }
  wss.handleUpgrade(request, socket, head, (ws) => {
    wss.emit('connection', ws, request);
  });
});

wss.on('connection', async (clientWs, req) => {
  const url = new URL(req.url || '/', `http://${req.headers.host}`);
  let callSid = url.searchParams.get('CallSid') || url.searchParams.get('call_sid') || '';
  let borrowerUuid = url.searchParams.get('BorrowerUuid') || url.searchParams.get('borrower_uuid') || '';

  let openaiWs = null;
  let pendingTwilioBuffer = [];

  const flushTwilioBuffer = () => {
    while (pendingTwilioBuffer.length && openaiWs && openaiWs.readyState === WebSocket.OPEN) {
      const raw = pendingTwilioBuffer.shift();
      try {
        const msg = JSON.parse(raw);
        const sid = extractCallSidFromTwilioMessage(msg);
        if (sid && !callSid) callSid = sid;
        const mapped = twilioToOpenAI(msg);
        if (mapped) {
          for (const ev of mapped) openaiWs.send(JSON.stringify(ev));
        } else if (msg.type && String(msg.type).includes('realtime')) {
          openaiWs.send(raw);
        }
      } catch {
        /* ignore parse errors for non-JSON frames */
      }
    }
  };

  try {
    if (!callSid) {
      callSid = `CA${Date.now().toString(36)}`;
      console.warn('No CallSid in query; using synthetic', callSid);
    }
    if (!borrowerUuid) {
      clientWs.close(4000, 'Missing BorrowerUuid query param');
      return;
    }

    await registerSession(LARAVEL_URL, VOICE_BRIDGE_KEY, callSid, borrowerUuid);
    console.log('Session bound', { callSid, borrowerUuid });

    openaiWs = connectOpenAIRealtime({
      apiKey: OPENAI_API_KEY,
      model: OPENAI_REALTIME_MODEL,
    });

    openaiWs.on('open', () => {
      openaiWs.send(JSON.stringify(buildSessionUpdate(callSid, borrowerUuid)));
      flushTwilioBuffer();
    });

    openaiWs.on('message', async (data) => {
      const text = data.toString();
      let msg;
      try {
        msg = JSON.parse(text);
      } catch {
        clientWs.send(text);
        return;
      }

      const fn = parseFunctionCallEvent(msg);
      if (fn && fn.name && openaiWs.readyState === WebSocket.OPEN) {
        try {
          const result = await executeVoiceTool(
            LARAVEL_URL,
            VOICE_BRIDGE_KEY,
            callSid,
            fn.name,
            fn.arguments,
          );
          openaiWs.send(JSON.stringify(buildFunctionOutputItem(fn.callId, result)));
          openaiWs.send(JSON.stringify(buildResponseCreate()));
        } catch (err) {
          const payload = {
            ok: false,
            error: err.message,
            body: err.body,
          };
          if (fn.callId) {
            openaiWs.send(JSON.stringify(buildFunctionOutputItem(fn.callId, payload)));
            openaiWs.send(JSON.stringify(buildResponseCreate()));
          }
        }
        return;
      }

      if (clientWs.readyState === WebSocket.OPEN) {
        clientWs.send(text);
      }
    });

    openaiWs.on('error', (e) => console.error('OpenAI WS error', e));
    openaiWs.on('close', () => clientWs.close());

    clientWs.on('message', (data) => {
      const raw = data.toString();
      if (!openaiWs || openaiWs.readyState !== WebSocket.OPEN) {
        pendingTwilioBuffer.push(raw);
        return;
      }
      try {
        const msg = JSON.parse(raw);
        const sid = extractCallSidFromTwilioMessage(msg);
        if (sid && !callSid) callSid = sid;
        const mapped = twilioToOpenAI(msg);
        if (mapped) {
          for (const ev of mapped) openaiWs.send(JSON.stringify(ev));
        } else {
          openaiWs.send(raw);
        }
      } catch {
        openaiWs.send(raw);
      }
    });

    clientWs.on('close', () => {
      if (openaiWs && openaiWs.readyState === WebSocket.OPEN) openaiWs.close();
    });
    clientWs.on('error', (e) => console.error('Client WS error', e));
  } catch (e) {
    console.error('Connection setup failed', e);
    clientWs.close(1011, String(e.message));
  }
});

server.listen(Number(BRIDGE_PORT), BRIDGE_HOST, () => {
  console.log(
    `Voice bridge listening ws://${BRIDGE_HOST}:${BRIDGE_PORT}${BRIDGE_PATH} (Twilio / test client)`,
  );
});
