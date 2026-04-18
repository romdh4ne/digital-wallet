<?php

namespace App\Services\Parsers\Contracts;

use App\Enums\Bank;

interface BankParserInterface
{
    public function parse(string $line): array;

    public function supports(Bank $bank): bool;
}
