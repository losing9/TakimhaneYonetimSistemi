<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;

class CheckOverdueLoans extends Command
{
    protected $signature   = 'loans:check-overdue';
    protected $description = 'Gecikmiş zimmetleri tespit et ve durumlarını "overdue" olarak güncelle';

    public function handle(): int
    {
        $updated = Loan::query()
            ->where('status', 'active')
            ->where('planned_return_at', '<', now())
            ->whereNull('returned_at')
            ->update(['status' => 'overdue']);

        $this->info("[{$updated}] zimmet 'Gecikmede' durumuna alındı.");

        return self::SUCCESS;
    }
}
