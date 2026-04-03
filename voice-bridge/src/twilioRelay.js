/**
 * Twilio Conversation Relay ↔ OpenAI Realtime message helpers.
 * @see https://www.twilio.com/docs/voice/conversationrelay/websocket-messages
 *
 * Twilio sends JSON frames; media uses base64 payloads. OpenAI expects
 * input_audio_buffer.append with base64 PCM16 (per OpenAI Realtime docs).
 */

export function extractCallSidFromTwilioMessage(msg) {
  if (!msg || typeof msg !== 'object') return null;
  if (msg.callSid) return msg.callSid;
  if (msg.call_sid) return msg.call_sid;
  if (msg.start && msg.start.callSid) return msg.start.callSid;
  if (msg.setup && msg.setup.callSid) return msg.setup.callSid;
  return null;
}

/**
 * Map Twilio inbound event to OpenAI Realtime events (subset).
 * Returns array of OpenAI JSON lines to send, or null to forward raw (unknown).
 */
export function twilioToOpenAI(msg) {
  if (!msg || typeof msg !== 'object') return null;

  // Media: forward as input_audio_buffer.append (Twilio payload is often mu-law; production may need transcoding)
  if (msg.event === 'media' && msg.media?.payload) {
    return [
      {
        type: 'input_audio_buffer.append',
        audio: msg.media.payload,
      },
    ];
  }

  if (msg.event === 'start' || msg.event === 'connected') {
    return null;
  }

  return null;
}

/**
 * OpenAI outbound audio → Twilio media format (if your Twilio stream expects this shape).
 */
export function openAIToTwilioMedia(base64Pcm) {
  return {
    event: 'media',
    media: {
      payload: base64Pcm,
    },
  };
}
