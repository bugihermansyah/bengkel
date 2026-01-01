<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueService extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'number',
        'vehicle_id',
        'mechanic_id',
        'complaint',
        'status',
        'notes',
        'process_at'
    ];

    protected $casts = [
        'cancel_at' => 'datetime',
        'process_at' => 'datetime',
        'finish_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
