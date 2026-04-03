<?php

namespace App\Observers;

use App\Models\BorrowerEmployment;
use App\Services\AuditLogger;

class BorrowerEmploymentObserver
{
    public function created(BorrowerEmployment $borrowerEmployment): void
    {
        AuditLogger::logCreated($borrowerEmployment);
    }

    public function updated(BorrowerEmployment $borrowerEmployment): void
    {
        AuditLogger::logUpdated($borrowerEmployment);
    }

    public function deleted(BorrowerEmployment $borrowerEmployment): void
    {
        AuditLogger::logDeleted($borrowerEmployment);
    }
}
