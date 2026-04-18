<?php

namespace App\Services\Parsers;

use App\Enums\Bank;
use App\Services\Parsers\Contracts\BankParserInterface;
use Carbon\Carbon;
use InvalidArgumentException;

class PayTechBankParser implements BankParserInterface
{
    public function supports(Bank $bank): bool
    {
        return $bank === Bank::PayTech;
    }

    public function parse(string $line): array
    {
        $segments = explode('#', $line);

        if (count($segments) !== 3) {
            throw new InvalidArgumentException("Invalid PayTech transaction line: {$line}");
        }

        [$dateAmount, $reference, $keyValueString] = $segments;

        $date = substr($dateAmount, 0, 8);
        $amount = substr($dateAmount, 8);

        return [
            'reference'     => trim($reference),
            'amount'        => $this->parseAmount($amount),
            'transacted_at' => Carbon::createFromFormat('Ymd', trim($date))->startOfDay(),
            'metadata'      => $this->parseKeyValues($keyValueString),
        ];
    }

    private function parseAmount(string $amount): float
    {
        return (float) str_replace(',', '.', trim($amount));
    }

    private function parseKeyValues(string $keyValueString): array
    {
        $parts = explode('/', trim($keyValueString));
        $result = [];

        foreach (array_chunk($parts, 2) as $pair) {
            if (count($pair) === 2) {
                $result[trim($pair[0])] = trim($pair[1]);
            }
        }

        return $result;
    }
}
