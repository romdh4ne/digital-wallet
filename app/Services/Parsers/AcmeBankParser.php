<?php

namespace App\Services\Parsers;

use App\Enums\Bank;
use App\Services\Parsers\Contracts\BankParserInterface;
use Carbon\Carbon;
use InvalidArgumentException;

class AcmeBankParser implements BankParserInterface
{
    public function supports(Bank $bank): bool
    {
        return $bank === Bank::Acme;
    }

    public function parse(string $line): array
    {
        $segments = explode('/', $line);

        if (count($segments) !== 3) {
            throw new InvalidArgumentException("Invalid Acme transaction line: {$line}");
        }

        [$amount, $reference, $date] = $segments;

        return [
            'reference'     => trim($reference),
            'amount'        => $this->parseAmount($amount),
            'transacted_at' => Carbon::createFromFormat('Ymd', trim($date))->startOfDay(),
            'metadata'      => [],
        ];
    }

    private function parseAmount(string $amount): float
    {
        return (float) str_replace(',', '.', trim($amount));
    }
}
