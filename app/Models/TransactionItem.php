<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'name',
        'qty',
        'price',
        'subtotal',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
