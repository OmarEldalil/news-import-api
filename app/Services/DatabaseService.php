<?php

namespace App\Services;

class DatabaseService
{
    const TRANSACTION_TRIAL_COUNT = 2;
    public function __construct()
    {
    }

    public function transaction(callable $function)
    {
        \DB::transaction($function, self::TRANSACTION_TRIAL_COUNT);

    }
}
