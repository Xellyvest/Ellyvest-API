<?php

namespace App\Services\User;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Trade;
use App\Models\Ledger;
use App\Models\Savings;
use App\Models\Position;
use Carbon\CarbonPeriod;
use App\Models\Dividends;
use App\Models\Transaction;
use App\Models\SavingsLedger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get analytics data for the user.
     */

    public function getUserAnalytics($user, string $timeframe = 'all'): array
    {
        // Allowed timeframes
        $timeFilters = [
            '1d' => now()->subHours(24),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '1yr' => now()->subYear(),
            'all' => null, // No filter for "all"
        ];

        // Validate timeframe
        if (!array_key_exists($timeframe, $timeFilters)) {
            throw new \InvalidArgumentException('Invalid timeframe. Allowed values: 1d, 7d, 30d, 1yr, all.');
        }

        // Base queries
        $transactionQuery = Transaction::where('status', 'approved')
            ->where('user_id', $user->id)
            ->where('transactable_id', $user->wallet->id);

        $savingsQuery = SavingsLedger::where('user_id', $user->id);
        $tradesQuery = Trade::where('user_id', $user->id);
        $positionQuery = Position::where('user_id', $user->id);

        // Apply timeframe filter to ledger query if needed
        $ledgerQuery = Transaction::where('status', 'approved')
            ->where('user_id', $user->id);
        
        if ($timeFilters[$timeframe]) {
            $ledgerQuery->where('created_at', '>=', $timeFilters[$timeframe]);
        }

        // Calculate raw values first (without formatting)
        $totalDeposited = (clone $transactionQuery)->where('type', 'credit')->sum('amount');
        $totalWithdrawn = (clone $transactionQuery)->where('type', 'debit')->sum('amount');

        // Savings calculations
        $creditSavings = (clone $savingsQuery)->where('type', 'credit')->where('method', 'contribution')->sum('amount');
        $debitSavings = (clone $savingsQuery)->where('type', 'debit')->where('method', 'contribution')->sum('amount');
        $rawTotalSavings = $creditSavings - $debitSavings;

        $creditTotalSavings = (clone $savingsQuery)->where('type', 'credit')->where('method', 'profit')->sum('amount');
        $debitTotalSavings = (clone $savingsQuery)->where('type', 'debit')->where('method', 'profit')->sum('amount');
        $rawTotalReturn = $creditTotalSavings - $debitTotalSavings;

        $savingsLast24h = (clone $savingsQuery)
            ->where('created_at', '>=', now()->subHours(24))
            ->sum('amount');

        // Get all positions with their assets
        $positions = (clone $positionQuery)
            ->with('asset')
            ->get();

        $positions24Hours = (clone $positionQuery)
            ->with('asset')
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        // Trades calculations
        $rawTotalBuy = (clone $tradesQuery)
            ->where('type', 'buy')
            ->where('status', 'open')
            ->sum('amount');

        $rawTotalPl = (clone $tradesQuery)
            ->where('type', 'sell')
            ->sum('pl');

        $totalExtra = $positions
            ->sum('extra');

        // Calculate total extra from all positions
        $totalExtra = $positions
            ->where('created_at', '>=', now()->subHours(24))
            ->sum('extra');

        // Calculate sum of all positions
        $rawTotalInvestment = $positions->sum(function($trade) {
            $currentValue = $trade->quantity * $trade->asset->price;
            return $currentValue;
        }) + $positions->sum('extra');

        // Calculate the original investment amount (without extra)
        $originalInvestment = $positions->sum('amount');

        // Calculate the percentage rate
        $percentageRate = 0;
        if ($originalInvestment > 0) {
            $percentageRate = (($rawTotalInvestment - $originalInvestment) / $originalInvestment) * 100;
        }

        // Calculate 24-hour P&L: (current value - invested amount) + extra from positions
        $rawTotalInvestment24hr = $positions24Hours->sum(function($trade) {
            $currentValue = $trade->quantity * $trade->asset->price;
            $leverageValue = abs((float)($trade->leverage ?? 1));
            return (($currentValue - $trade->amount) * $leverageValue) + ($trade->extra * $leverageValue);
        });

        // $timeframe = '1d'; // default to 1 day
        $chartData = $this->getNetworthChartData($user, $timeframe);

        return [
            'total_deposited' => number_format($totalDeposited, 2),
            'total_withdrawn' => number_format($totalWithdrawn, 2),
            'total_savings' => number_format($rawTotalSavings, 2),
            'total_savings_return' => number_format($rawTotalReturn, 2),
            'total_savings_24hr' => number_format($savingsLast24h, 2),
            'total_investment' => number_format($rawTotalInvestment, 2),
            'total_investment_percentage' => number_format($percentageRate, 2),
            'total_investment_24hr' => number_format($rawTotalInvestment24hr, 2),
            'chart_data' => $chartData,
        ];
    }

    /**
     * Get networth chart data for a user based on timeframe
     * 
     * @param \App\Models\User $user
     * @param string $timeframe (1d, 7d, 30d, 1yr, all)
     * @return array
     */
    public function getNetworthChartData(User $user, string $timeframe): array
    {
        $now = now();
        $chartData = [];
        
        switch ($timeframe) {
            case '1d':
                // Last 24 hours, hourly data
                for ($i = 23; $i >= 0; $i--) {
                    $time = $now->copy()->subHours($i)->startOfHour();
                    $endTime = $time->copy()->endOfHour();
                    
                    $networth = $this->calculateNetworthAtTime($user, $endTime);
                    
                    $chartData[$time->format('Y-m-d H:i:s')] = $networth;
                }
                break;
                
            case '7d':
                // Last 7 days, daily data
                for ($i = 6; $i >= 0; $i--) {
                    $time = $now->copy()->subDays($i)->startOfDay();
                    $endTime = $time->copy()->endOfDay();
                    
                    $networth = $this->calculateNetworthAtTime($user, $endTime);
                    
                    $chartData[$time->format('Y-m-d 00:00:00')] = $networth;
                }
                break;
                
            case '30d':
                // Last 30 days, daily data
                for ($i = 29; $i >= 0; $i--) {
                    $time = $now->copy()->subDays($i)->startOfDay();
                    $endTime = $time->copy()->endOfDay();
                    
                    $networth = $this->calculateNetworthAtTime($user, $endTime);
                    
                    $chartData[$time->format('Y-m-d 00:00:00')] = $networth;
                }
                break;
                
            case '1yr':
                // Last 12 months, monthly data
                for ($i = 11; $i >= 0; $i--) {
                    $time = $now->copy()->subMonths($i)->startOfMonth();
                    $endTime = $time->copy()->endOfMonth();
                    
                    $networth = $this->calculateNetworthAtTime($user, $endTime);
                    
                    $chartData[$time->format('Y-m-01 00:00:00')] = $networth;
                }
                break;
                
            case 'all':
                // All available data (from first transaction to now)
                $firstTransactionDate = $this->getFirstTransactionDate($user);
                
                if ($firstTransactionDate) {
                    $currentDate = $firstTransactionDate->copy()->startOfDay();
                    
                    while ($currentDate <= $now) {
                        $endTime = $currentDate->copy()->endOfDay();
                        $networth = $this->calculateNetworthAtTime($user, $endTime);
                        
                        $chartData[$currentDate->format('Y-m-d 00:00:00')] = $networth;
                        
                        $currentDate->addDay();
                    }
                }
                break;
        }
        
        return $chartData;
    }

    /**
     * Calculate networth at a specific point in time
     * 
     * @param \App\Models\User $user
     * @param \Carbon\Carbon $time
     * @return float
     */
    protected function calculateNetworthAtTime(User $user, Carbon $time): float
    {
        // 1. Calculate cash balance (wallet balance at that time)
        // $cashBalance = Transaction::where('user_id', $user->id)
        //     ->where('status', 'approved')
        //     ->where('created_at', '<=', $time)
        //     ->where('type', 'credit')
        //     ->sum('amount');

        // 1. Calculate cash balance (wallet balance at that time)
        $creditBalance = Transaction::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('type', 'credit')
            ->where('created_at', '<=', $time)
            ->sum('amount');

        $debitBalance = Transaction::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('type', 'debit')
            ->where('created_at', '<=', $time)
            ->sum('amount');

        $cashBalance = $creditBalance - $debitBalance;
        
        // 2. Calculate total investment (positions value at that time)
        $totalInvestment = Position::where('user_id', $user->id)
            ->where('status', 'open')
            ->where('created_at', '<=', $time)
            // ->with(['asset' => function($query) use ($time) {
            //     $query->select('id', 'price')
            //         ->where('updated_at', '<=', $time)
            //         ->orderBy('updated_at', 'desc')
            //         ->limit(1);
            // }])
            ->get()
            ->sum(function($position) {
                return ($position->quantity * ($position->asset->price ?? 0)) + $position->extra - $position->amount;
            });

        // 3. Calculate total investment (positions value at that time)
        $totalTradeHistoryInvestment = Trade::where('user_id', $user->id)
            ->where('type', 'sell')
            ->where('created_at', '<=', $time)
            ->sum('pl');

        $creditSavings = SavingsLedger::where('user_id', $user->id)
            ->where('type', 'credit')
            ->where('method', 'profit')
            ->where('created_at', '<=', $time)
            ->sum('amount');
        
        $debitSavings = SavingsLedger::where('user_id', $user->id)
            ->where('type', 'debit')
            ->where('method', 'profit')
            ->where('created_at', '<=', $time)
            ->sum('amount');
        
        $totalSavings = $creditSavings - $debitSavings;
        
        return $cashBalance + $totalInvestment + $totalTradeHistoryInvestment + $totalSavings;
    }

    /**
     * Get the date of the first transaction for a user
     * 
     * @param \App\Models\User $user
     * @return \Carbon\Carbon|null
     */
    protected function getFirstTransactionDate(User $user): ?Carbon
    {
        $firstTransaction = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->first();
        
        return $firstTransaction ? $firstTransaction->created_at : null;
    }

    public function getDividendChartData(User $user, string $timeframe): array
    {
        $now = Carbon::now();
        $format = 'Y-m-d H:i:s';
        
        // Determine date range and grouping based on timeframe
        switch ($timeframe) {
            case 'week':
                $startDate = $now->copy()->subDays(7);
                $groupBy = 'date';
                $dbFormat = '%Y-%m-%d';
                break;
                
            case 'month':
                $startDate = $now->copy()->subDays(31);
                $groupBy = 'date';
                $dbFormat = '%Y-%m-%d';
                break;
                
            case 'year':
                $startDate = $now->copy()->subMonths(12);
                $groupBy = 'month';
                $dbFormat = '%Y-%m';
                break;
                
            case 'all':
                $startDate = null; // No date limit
                $groupBy = 'month';
                $dbFormat = '%Y-%m';
                break;
                
            default:
                throw new \InvalidArgumentException('Invalid timeframe specified');
        }

        // Query dividends with optional date filtering
        $query = Dividends::where('user_id', $user->id)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dbFormat}') as date_group"),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('date_group')
            ->orderBy('date_group');

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        $dividends = $query->get()->keyBy('date_group');

        // Generate complete date range with zeros for missing dates
        return $this->generateCompleteDateRange(
            $startDate ?? $user->created_at,
            $now,
            $groupBy,
            $dividends
        );
    }

    protected function generateCompleteDateRange(
        Carbon $startDate,
        Carbon $endDate,
        string $groupBy,
        Collection $dividends
    ): array {
        $result = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $key = $current->format($groupBy === 'month' ? 'Y-m' : 'Y-m-d');
            
            // For hourly data if needed later
            if ($groupBy === 'hour') {
                $key = $current->format('Y-m-d H:00:00');
            }
            
            $result[$key] = $dividends->has($key) 
                ? (float) $dividends[$key]->total_amount 
                : 0;
                
            // Move to next period
            $groupBy === 'month' 
                ? $current->addMonth() 
                : $current->addDay();
        }
        
        return $result;
    }

}
