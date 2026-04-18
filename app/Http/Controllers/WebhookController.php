<?php

namespace App\Http\Controllers;

use App\Enums\Bank;
use App\Enums\WebhookStatus;
use App\Jobs\ProcessWebhookJob;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;

class WebhookController extends Controller
{
    public function __invoke(Request $request, string $bank): Response
    {
        $request->validate([
            'bank' => [new Enum(Bank::class)],
        ]);

        $log = WebhookLog::create([
            'bank'        => Bank::from($bank),
            'raw_payload' => $request->getContent(),
            'status'      => WebhookStatus::Pending,
        ]);

        ProcessWebhookJob::dispatch($log);

        return response()->noContent();
    }
}
