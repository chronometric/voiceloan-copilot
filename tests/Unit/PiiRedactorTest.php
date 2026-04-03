<?php

namespace Tests\Unit;

use App\Services\PiiRedactor;
use Tests\TestCase;

class PiiRedactorTest extends TestCase
{
    public function test_redact_audit_masks_configured_keys(): void
    {
        $out = PiiRedactor::redactAudit([
            'ssn_last4' => '1234',
            'first_name' => 'Pat',
        ]);

        $this->assertSame('[redacted]', $out['ssn_last4']);
        $this->assertSame('Pat', $out['first_name']);
    }

    public function test_redact_audit_accepts_null(): void
    {
        $this->assertNull(PiiRedactor::redactAudit(null));
    }

    public function test_mask_phone_keeps_last_two_digits(): void
    {
        $this->assertSame('***00', PiiRedactor::maskPhone('+1555123400'));
    }

    public function test_mask_urla_snapshot_redacts_ssn_path(): void
    {
        $this->assertSame(
            '[redacted]',
            PiiRedactor::maskUrlaSnapshot('identity.ssn_last4', '1234'),
        );
        $this->assertSame('Jane', PiiRedactor::maskUrlaSnapshot('identity.first_name', 'Jane'));
    }
}
