<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DatabaseService
{
    const int TRANSACTION_TRIAL_COUNT = 2;
    public function __construct()
    {
    }

    public function transaction(callable $function): void
    {
        DB::transaction($function, self::TRANSACTION_TRIAL_COUNT);

    }
}
