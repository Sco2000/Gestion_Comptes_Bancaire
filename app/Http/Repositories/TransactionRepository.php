<?php

namespace App\Http\Repositories;

use App\Models\Transaction;

class TransactionRepository
{
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    // TODO: Implement methods as needed
}