<?php

namespace App\Observers;

use App\Models\BorrowerIdentity;
use App\Services\AuditLogger;

class BorrowerIdentityObserver
{
    public function created(BorrowerIdentity $borrowerIdentity): void
    {
        AuditLogger::logCreated($borrowerIdentity);
    }

    public function updated(BorrowerIdentity $borrowerIdentity): void
    {
        AuditLogger::logUpdated($borrowerIdentity);
    }

    public function deleted(BorrowerIdentity $borrowerIdentity): void
    {
        AuditLogger::logDeleted($borrowerIdentity);
    }
}
