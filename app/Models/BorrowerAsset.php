<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowerAsset extends Model
{
    protected $table = 'borrower_assets';

    protected $fillable = [
        'borrower_id',
        'asset_type',
        'description',
        'value_cents',
    ];

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }
}
