<?php

namespace App\Jobs;

use App\Models\WebhookLog;
use App\Services\WebhookIngestionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class ProcessWebhookJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public readonly WebhookLog $log) {}

    public function handle(WebhookIngestionService $service): void
    {
        $service->ingest($this->log);
    }

    public function failed(Throwable $exception): void
    {
        $this->log->markAsFailed();
    }
}
