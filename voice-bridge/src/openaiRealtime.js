import WebSocket from 'ws';

/**
 * Connect to OpenAI Realtime WebSocket.
 */
export function connectOpenAIRealtime({ apiKey, model }) {
  const url = `wss://api.openai.com/v1/realtime?model=${encodeURIComponent(model)}`;
  return new WebSocket(url, {
    headers: {
      Authorization: `Bearer ${apiKey}`,
      'OpenAI-Beta': 'realtime=v1',
    },
  });
}

/**
 * Initial session with instructions + tools (function calling for Laravel bridge).
 */
export function buildSessionUpdate(callSid, borrowerUuid) {
  return {
    type: 'session.update',
    session: {
      modalities: ['text', 'audio'],
      instructions: [
        'You are a professional mortgage loan assistant.',
        'Use tools to read and update borrower data in the CRM. Never invent borrower data.',
        `This call is bound to call_sid=${callSid} and borrower_uuid=${borrowerUuid}.`,
        'When you need CRM data, call get_borrower. For URLA/1003 conversational context (missing fields, compact snapshot, prompts), call get_urla_context.',
        'To update top-level borrower fields, call patch_borrower with only the fields the user confirmed.',
      ].join('\n'),
      tools: [
        {
          type: 'function',
          name: 'get_borrower',
          description: 'Load the full borrower record (profile + sections) from the CRM.',
          parameters: { type: 'object', properties: {}, additionalProperties: false },
        },
        {
          type: 'function',
          name: 'get_urla_context',
          description:
            'Load URLA/1003 prompt pack: system + section instructions, missing required fields, compact snapshot, clarification counts.',
          parameters: { type: 'object', properties: {}, additionalProperties: false },
        },
        {
          type: 'function',
          name: 'patch_borrower',
          description: 'Update borrower main fields (display_name, email, phone, status).',
          parameters: {
            type: 'object',
            properties: {
              display_name: { type: 'string' },
              email: { type: 'string' },
              phone: { type: 'string' },
              status: { type: 'string' },
            },
            additionalProperties: false,
          },
        },
      ],
      tool_choice: 'auto',
    },
  };
}

/**
 * Parse function-call completion events (handles common Realtime shapes).
 */
export function parseFunctionCallEvent(msg) {
  if (!msg || typeof msg !== 'object') return null;

  if (msg.type === 'response.function_call_arguments.done') {
    return {
      callId: msg.call_id,
      name: msg.name,
      arguments: safeJsonParse(msg.arguments),
    };
  }

  if (msg.type === 'response.output_item.done' && msg.item?.type === 'function_call') {
    return {
      callId: msg.item.call_id,
      name: msg.item.name,
      arguments: safeJsonParse(msg.item.arguments),
    };
  }

  if (msg.type === 'conversation.item.completed' && msg.item?.type === 'function_call') {
    return {
      callId: msg.item.call_id,
      name: msg.item.name,
      arguments: safeJsonParse(msg.item.arguments),
    };
  }

  return null;
}

function safeJsonParse(s) {
  if (s == null || s === '') return {};
  if (typeof s === 'object') return s;
  try {
    return JSON.parse(s);
  } catch {
    return {};
  }
}

/**
 * Send function output back into the Realtime session.
 */
export function buildFunctionOutputItem(callId, outputObj) {
  return {
    type: 'conversation.item.create',
    item: {
      type: 'function_call_output',
      call_id: callId,
      output: JSON.stringify(outputObj),
    },
  };
}

export function buildResponseCreate() {
  return { type: 'response.create' };
}
