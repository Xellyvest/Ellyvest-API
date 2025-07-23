<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Position;
use App\Models\SavingsLedger;
use Illuminate\Console\Command;
use App\Models\ProfitLossHistory;
use App\Models\AutoPlanInvestment;


class RecordProfitLossHistory extends Command
{
    protected $signature = 'record:profit-loss-history';
    protected $description = 'Record cumulative profit/loss for all users hourly';

    public function handle()
    {
        // Get all users (use cursor for memory efficiency with large user bases)
        User::cursor()->each(function ($user) {
            // Calculate values for each account type
            $values = $this->calculateUserValues($user);
            
            // Store the record
            ProfitLossHistory::create([
                'user_id' => $user->id,
                'wallet_value' => $values['wallet'],
                'brokerage_value' => $values['brokerage'],
                'auto_value' => $values['auto'],
                'savings_value' => $values['savings'],
                'total_value' => $values['total'],
                'recorded_at' => now(),
            ]);

        });

        $this->info('Successfully recorded profit/loss history for all users.');
    }

    public function calculateUserValues($user)
    {
        // Calculate wallet value
        $walletValue = $user->wallet ? $user->wallet->getBalance('wallet') : 0;

        // Calculate brokerage value
        $brokerageValue = $this->calculateBrokerageValue($user);

        // Calculate auto value
        $autoValue = $this->calculateAutoValue($user);

        // Calculate savings value
        $savingsValue = $this->calculateSavingsValue($user);

        // Calculate total value
        $totalValue = $brokerageValue + $autoValue;

        return [
            'wallet' => $walletValue,
            'brokerage' => $brokerageValue,
            'auto' => $autoValue,
            'savings' => $savingsValue,
            'total' => $totalValue,
        ];
    }

    protected function calculateBrokerageValue($user)
    {
        $balance = $user->wallet ? $user->wallet->getBalance('brokerage') : 0;
        
        $positionsValue = Position::where('user_id', $user->id)
            ->where('account', 'brokerage')
            ->where('status', 'open')
            ->with('asset')
            ->get()
            ->sum(function ($position) {
                if ($position->asset) {
                    return (($position->quantity * $position->asset->price) + $position->extra) - $position->amount;
                }
                return 0;
            });

        return $positionsValue;
    }

    protected function calculateAutoValue($user)
    {
        $balance = $user->wallet ? $user->wallet->getBalance('auto') : 0;
        
        $investment = AutoPlanInvestment::where('user_id', $user->id)
            ->where('expire_at', '>', now())
            ->sum('amount');
        
        $positionsValue = AutoPlanInvestment::where('user_id', $user->id)
            ->where('expire_at', '>', now())
            ->with('positions.asset')
            ->get()
            ->sum(function ($autoInvestment) {
                return $autoInvestment->positions->sum(function ($position) {
                    if ($position->asset) {
                        return (($position->quantity * $position->asset->price) + $position->extra) - $position->amount;
                    }
                    return 0;
                });
            });

        return $positionsValue;
    }

    protected function calculateSavingsValue($user)
    {
        $savingsQuery = SavingsLedger::where('user_id', $user->id);
        $creditSavings = (clone $savingsQuery)->where('type', 'credit')->sum('amount');
        $debitSavings = (clone $savingsQuery)->where('type', 'debit')->sum('amount');
        
        return $creditSavings - $debitSavings;
    }
}
