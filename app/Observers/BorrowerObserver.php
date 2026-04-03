<?php

namespace App\Observers;

use App\Models\Borrower;
use App\Services\AuditLogger;

class BorrowerObserver
{
    public function created(Borrower $borrower): void
    {
        AuditLogger::logCreated($borrower);
    }

    public function updated(Borrower $borrower): void
    {
        AuditLogger::logUpdated($borrower);
    }

    public function deleted(Borrower $borrower): void
    {
        AuditLogger::logDeleted($borrower);
    }
}
