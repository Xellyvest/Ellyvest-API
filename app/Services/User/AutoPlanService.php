<?php

namespace App\Services\User;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\AutoPlan;
use App\Models\AutoPlanInvestment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NotificationController;

class AutoPlanService
{
    public function startInvestment(User $user, array $data)
    {
        DB::transaction(function () use ($user, $data) {
            $plan = AutoPlan::findOrFail($data['auto_plan_id']);
            $data['wallet'] = 'auto';

            $this->validateInvestmentAmount($plan, $data['amount']);
            
            $balance = $user->wallet->getBalance($data['wallet']);

            if ($balance < $data['amount']) {
                throw new \Exception('Insufficient balance in auto investing wallet');
            }

            $startDate = now();
            $endDate = $this->calculateEndDate($startDate, $plan->duration, $plan->milestone);

            $auto = AutoPlanInvestment::create([
                'user_id' => $user->id,
                'auto_plan_id' => $plan->id,
                'amount' => $data['amount'],
                'start_at' => $startDate,
                'expire_at' => $endDate,
            ]);

            $user->wallet->debit($data['amount'], $data['wallet'], 'Auto plan investment');

            NotificationController::sendUserNewAutoInvestmentNotification($user, $auto);
            $admin = Admin::where('email', config('app.admin_mail'))->first();
            NotificationController::sendAdminNewAutoInvestmentNotification($admin, $user, $auto);

            return $auto;
        });
    }

    protected function validateInvestmentAmount(AutoPlan $plan, float $amount): void
    {
        if ($amount < $plan->min_invest) {
            throw new \Exception("Minimum investment amount for this plan is {$plan->min_invest}");
        }

        if ($amount > $plan->max_invest) {
            throw new \Exception("Maximum investment amount for this plan is {$plan->max_invest}");
        }
    }

    protected function calculateEndDate(Carbon $startDate, string $duration, string $milestone): Carbon
    {
        $milestoneNumber = (int) $milestone;
        
        return match ($duration) {
            'day' => $startDate->copy()->addDays($milestoneNumber),
            'month' => $startDate->copy()->addMonths($milestoneNumber),
            'year' => $startDate->copy()->addYears($milestoneNumber),
            default => $startDate->copy()->addDays($milestoneNumber),
        };
    }
}
