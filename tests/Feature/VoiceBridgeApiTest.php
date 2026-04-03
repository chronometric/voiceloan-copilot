<?php

namespace Tests\Feature;

use App\Models\Borrower;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoiceBridgeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_missing_bridge_key_header(): void
    {
        $this->postJson('/api/voice/sessions', [
            'call_sid' => 'CAtest123',
            'borrower_uuid' => '00000000-0000-4000-8000-000000000001',
        ])->assertStatus(401);
    }

    public function test_rejects_invalid_bridge_key(): void
    {
        $this->postJson('/api/voice/sessions', [
            'call_sid' => 'CAtest123',
            'borrower_uuid' => '00000000-0000-4000-8000-000000000001',
        ], [
            'X-Voice-Bridge-Key' => 'wrong-key',
        ])->assertStatus(403);
    }

    public function test_registers_session_when_borrower_exists(): void
    {
        $user = User::factory()->create();
        $borrower = Borrower::query()->create([
            'created_by_user_id' => $user->id,
            'status' => 'draft',
            'display_name' => 'Test',
            'email' => 'a@example.com',
            'phone' => '+15555550100',
        ]);

        $this->postJson('/api/voice/sessions', [
            'call_sid' => 'CAtest456',
            'borrower_uuid' => $borrower->uuid,
        ], [
            'X-Voice-Bridge-Key' => 'test-voice-bridge-key',
        ])->assertOk()->assertJson(['ok' => true]);
    }

    public function test_returns_503_when_bridge_key_not_configured(): void
    {
        config(['voice.bridge_key' => '']);

        $this->postJson('/api/voice/sessions', [
            'call_sid' => 'CAx',
            'borrower_uuid' => '00000000-0000-4000-8000-000000000001',
        ], [
            'X-Voice-Bridge-Key' => 'ignored',
        ])->assertStatus(503);
    }
}
