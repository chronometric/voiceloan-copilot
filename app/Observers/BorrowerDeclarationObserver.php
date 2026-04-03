<?php

namespace App\Observers;

use App\Models\BorrowerDeclaration;
use App\Services\AuditLogger;

class BorrowerDeclarationObserver
{
    public function created(BorrowerDeclaration $borrowerDeclaration): void
    {
        AuditLogger::logCreated($borrowerDeclaration);
    }

    public function updated(BorrowerDeclaration $borrowerDeclaration): void
    {
        AuditLogger::logUpdated($borrowerDeclaration);
    }

    public function deleted(BorrowerDeclaration $borrowerDeclaration): void
    {
        AuditLogger::logDeleted($borrowerDeclaration);
    }
}
