<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AutoPlanInvestment;

class CreditExpiredAutoInvestments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    /**
     * The name and signature of the console command.
     *
     * You can pass an optional user_id to target a specific user
     */
    protected $signature = 'auto:credit {user_id?}';

    /**
     * The console command description.
     */
    protected $description = 'Credit expired AutoPlanInvestments to user auto wallet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        $query = AutoPlanInvestment::where('expire_at', '<=', now())
                    ->where('credited', false);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $expiredInvestments = $query->get();

        if ($expiredInvestments->isEmpty()) {
            $this->info('No expired auto investments found.');
            return 0;
        }

        foreach ($expiredInvestments as $investment) {
            $user = $investment->user;

            if (!$user || !$user->wallet) {
                $this->warn("User or wallet not found for investment ID {$investment->id}");
                continue;
            }

            // Calculate positions value
            $positionsValue = 0;
            foreach ($investment->positions as $position) {
                if ($position->asset) {
                    $leverageValue = abs((float)($position->leverage ?? 1));
                    $positionsValue += ((($position->quantity * $position->asset->price) - $position->amount) * $leverageValue) + ($position->extra * $leverageValue);
                }
            }

            // Total amount to credit = investment amount + positions value
            $amountToCredit = $investment->amount + $positionsValue;

            // Credit user's auto wallet
            $user->wallet->credit($amountToCredit, 'auto', 'Expired AutoPlanInvestment ID: ' . $investment->id);

            // Mark as credited to prevent double credit
            $investment->credited = true;
            $investment->save();

            $this->info("Credited ${$amountToCredit} to user ID {$user->id} for investment ID {$investment->id}");
        }

        return 0;
    }
}
