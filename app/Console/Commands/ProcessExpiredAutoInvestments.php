<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AutoPlanInvestment;
use Illuminate\Support\Facades\DB;

class ProcessExpiredAutoInvestments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto-investments:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Credit auto wallet for expired auto investments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing expired auto investments...');

        AutoPlanInvestment::with('user.wallet')
            ->where('expire_at', '<=', now())
            ->where('start_at', '=>', '2025-12-23 09:00:00')
            ->where('credited', false)
            ->chunkById(100, function ($investments) {

                foreach ($investments as $investment) {
                    DB::transaction(function () use ($investment) {

                        $user = $investment->user;
                        $wallet = $user->wallet;

                        if (!$wallet) {
                            return;
                        }

                        // Credit auto wallet
                        $wallet->credit(
                            $investment->amount,
                            'auto',
                            'Auto plan investment matured'
                        );

                        // Mark as credited
                        $investment->update([
                            'credited' => true,
                        ]);

                        $this->info("Processed {$investment->amount} USD from {$user->email} 🤑");

                    });
                }

            });

        $this->info('Expired auto investments processed successfully.');
        return Command::SUCCESS;
    }
}
