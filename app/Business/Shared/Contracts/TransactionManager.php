<?php

namespace App\Business\Shared\Contracts;

interface TransactionManager
{
    /**
     * Run an operation within one database transaction.
     *
     * Data access operations called by the callback must use the same
     * configured connection so row locks remain inside this transaction.
     */
    public function run(callable $operation): mixed;
}
