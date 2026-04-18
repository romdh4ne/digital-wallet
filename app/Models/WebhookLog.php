<?php

namespace App\Models;

use App\Enums\WebhookStatus;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'bank',
        'raw_payload',
        'status',
    ];

    protected $casts = [
        'status' => WebhookStatus::class,
    ];

   public function markAsProcessed(): bool
    {
        return $this->update(['status' => WebhookStatus::Processed]);
    }

    public function markAsFailed(): bool
    {
        return $this->update(['status' => WebhookStatus::Failed]);
    }
}