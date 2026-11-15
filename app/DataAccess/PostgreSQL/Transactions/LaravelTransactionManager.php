<?php

namespace App\DataAccess\PostgreSQL\Transactions;

use App\Business\Shared\Contracts\TransactionManager;
use Illuminate\Support\Facades\DB;

final class LaravelTransactionManager implements TransactionManager
{
    public function run(callable $operation): mixed
    {
        // Laravel passes its Connection to transaction callbacks. Keep that
        // framework detail out of the business callback contract.
        $callback = static fn (): mixed => $operation();

        return DB::connection()->transaction($callback);
    }
}
