<?php

namespace App\Services;

use App\Enums\Bank;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\WebhookLog;
use App\Services\Parsers\Contracts\BankParserInterface;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WebhookIngestionService
{
    public function __construct(private readonly Container $container) {}

    public function ingest(WebhookLog $log): void
    {
        $parser = $this->resolveParser($log->bank);

        $lines = $this->extractLines($log->raw_payload);

        $transactions = array_map(
            fn(string $line) => $parser->parse($line),
            $lines
        );

        DB::transaction(function () use ($transactions, $log) {
            foreach ($transactions as $data) {
                $client = Client::where('account_number', $log->bank)->first()
                    ?? Client::first();

                Transaction::upsert(
                    [[
                        'client_id'     => $client->id,
                        'reference'     => $data['reference'],
                        'amount'        => $data['amount'],
                        'bank'          => $log->bank,
                        'transacted_at' => $data['transacted_at'],
                        'metadata'      => json_encode($data['metadata']),
                    ]],
                    uniqueBy: ['reference'],
                    update: [],
                );
            }

            $log->markAsProcessed();
        });
    }

    private function resolveParser(Bank $bank): BankParserInterface
    {
        $parsers = $this->container->tagged(BankParserInterface::class);

        foreach ($parsers as $parser) {
            if ($parser->supports($bank)) {
                return $parser;
            }
        }

        throw new RuntimeException("No parser found for bank: {$bank->value}");
    }

    private function extractLines(string $payload): array
    {
        return array_filter(
            array_map('trim', explode("\n", $payload)),
            fn(string $line) => $line !== '',
        );
    }
}
