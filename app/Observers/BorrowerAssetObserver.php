<?php

namespace App\Observers;

use App\Models\BorrowerAsset;
use App\Services\AuditLogger;

class BorrowerAssetObserver
{
    public function created(BorrowerAsset $borrowerAsset): void
    {
        AuditLogger::logCreated($borrowerAsset);
    }

    public function updated(BorrowerAsset $borrowerAsset): void
    {
        AuditLogger::logUpdated($borrowerAsset);
    }

    public function deleted(BorrowerAsset $borrowerAsset): void
    {
        AuditLogger::logDeleted($borrowerAsset);
    }
}
