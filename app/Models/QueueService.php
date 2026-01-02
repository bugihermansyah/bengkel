<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class QueueService extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'queue_date',
        'queue_number',
        'queue_code',
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

    protected static function booted()
    {
        static::creating(function ($model) {

            DB::transaction(function () use ($model) {

                $today = Carbon::today()->toDateString();

                $lastNumber = self::whereDate('queue_date', $today)
                    ->lockForUpdate()
                    ->max('queue_number');

                $model->queue_date = $today;
                $model->queue_number = ($lastNumber ?? 0) + 1;
                $model->queue_code = 'A' . str_pad(
                    $model->queue_number,
                    3,
                    '0',
                    STR_PAD_LEFT
                );
            });

        });
    }
}
