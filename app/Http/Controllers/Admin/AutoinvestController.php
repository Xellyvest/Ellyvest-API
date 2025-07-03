<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Asset;
use App\Models\Trade;
use App\Models\AutoPlan;
use App\Models\Position;
use App\Helpers\FileHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;
use App\Models\AutoPlanInvestment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Exceptions\ExpectationFailedException;
use App\Http\Requests\Admin\StoreAutoPlanRequest;
use App\Http\Requests\Admin\UpdateAutoPlanRequest;

class AutoinvestController extends Controller
{
    public function index()
    {
        $plans = AutoPlan::all();

        return view('admin.auto-plan', [
            'plans' => $plans,
        ]);
    }

    public function store(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name'        => ['required', 'string', 'unique:auto_plans,name'],
            'min_invest'  => ['required', 'numeric', 'min:0'],
            'max_invest'  => ['required', 'numeric', 'min:0'],
            'win_rate'    => ['required', 'numeric', 'min:0'],
            'duration'    => ['required', 'string'],
            'milestone'   => ['required', 'string'],
            'aum'         => ['required', 'string'],
            'type'         => ['required', 'string', 'in:conservative,moderate,aggressive'],
            'day_returns'     => ['required', 'string'],
            'expected_returns'     => ['required', 'string'],
            'status' => ['nullable', 'in:active,locked'],
            'img'         => ['sometimes', 'image'], // 'image' ensures it's a valid image file
        ]);
    
        // Handle validation failure
        if ($validator->fails()) {
            $firstError = $validator->errors()->first(); // get the first error message
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $firstError); // pass the first error to the session
        }
    
        // Get validated data
        $data = $validator->validated();
    
        // Handle file upload
        if ($request->hasFile('img')) {
            $file = $request->file('img');
            $path = $this->uploadFile($file, 'auto_plans');
            $data['img'] = $path;
        }

        $data['status'] = $request->has('status') ? 'active' : 'locked';
    
        // Create the plan
        $plan = AutoPlan::create($data);
    
        if ($plan) {
            return redirect()->back()->with('success', 'Plan stored successfully');
        }
    
        return redirect()->back()->with('error', 'Something went wrong. Please try again.');
    }

    public function show(AutoPlan $auto_plan)
    {
        return $auto_plan;
    }

    public function update(Request $request, AutoPlan $autoPlan)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name'        => ['required', 'string'],
            'min_invest'  => ['required', 'numeric', 'min:0'],
            'max_invest'  => ['required', 'numeric', 'min:0'],
            'win_rate'    => ['required', 'numeric', 'min:0'],
            'duration'    => ['required', 'string'],
            'milestone'   => ['required', 'string'],
            'aum'         => ['required', 'string'],
            'type'         => ['required', 'string', 'in:conservative,moderate,aggressive'],
            'day_returns'     => ['required', 'string'],
            'expected_returns'     => ['required', 'string'],
            'status' => ['nullable', 'in:active,locked'],
            'img'         => ['sometimes', 'image'],
        ]);
    
        // Handle validation failure
        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $firstError);
        }
    
        // Get validated data
        $data = $validator->validated();
    
        // Handle file upload if a new image is provided
        if ($request->hasFile('img')) {
            $file = $request->file('img');
            $path = $this->uploadFile($file, 'auto_plans');
            $data['img'] = $path;
    
            // delete old image from storage if needed
            Storage::delete($autoPlan->img); 
        }

        $data['status'] = $request->has('status') ? 'active' : 'locked';
    
        // Update the plan
        $autoPlan->update($data);
    
        return redirect()->back()->with('success', 'Plan updated successfully');
    }  

    public function updateInvestment(Request $request, AutoPlanInvestment $investment)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            // 'user_id' => ['required', 'exists:users,id'],
            // 'auto_plan_id' => ['required', 'exists:auto_plans,id'],
            // 'amount' => ['required', 'numeric', 'min:0'],
            'start_at' => ['nullable', 'date'],
            'expire_at' => ['nullable', 'date', 'after_or_equal:start_at'],
        ]);

        // Handle validation failure
        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $firstError);
        }

        // Get validated data
        $data = $validator->validated();

        // Format dates if provided
        if ($request->has('start_at')) {
            $data['start_at'] = Carbon::parse($data['start_at']);
        }
        
        if ($request->has('expire_at')) {
            $data['expire_at'] = Carbon::parse($data['expire_at']);
        }

        // Update the investment
        $investment->update($data);

        return redirect()->back()->with('success', 'Investment updated successfully');
    }

    public function destroy(AutoPlan $autoPlan)
    {
        Storage::delete($autoPlan->img); 

        $autoPlan->delete();

        return redirect()->back()->with('success', 'Plan deleted successfully');
    }

    private function uploadFile(UploadedFile $file, string $directory): string
    {
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');
    
        throw_if($path === false, \Symfony\Component\HttpFoundation\Exception\BadRequestException::class, 'Image could not be uploaded.');
    
        return $path;
    }

    public function investment()
    {
        $investments = AutoPlanInvestment::all();
        $users = User::all();
        $autoPlans = AutoPlan::all();

        return view('admin.auto-investments', [
            'investments' => $investments,
            'users' => $users,
            'autoPlans' => $autoPlans,
        ]);
    }

    public function getUserPlans(User $user)
    {
        $plans = $user->autoPlanInvestment()
            ->with('plan')
            ->where(function ($query) {
                $query->whereNull('expire_at')
                      ->orWhere('expire_at', '>', now());
            })
            ->get()
            ->map(function ($investment) {
                $totalInvested = Position::where('auto_plan_investment_id', $investment->id)
                    ->where('user_id', $investment->user->id)
                    ->sum('amount');

                    // dd($totalInvested);
    
                $balance = $investment->amount - $totalInvested;
    
                return [
                    'id' => $investment->id,
                    'balance' => $balance,
                    'amount' => $investment->amount,
                    'expire_at' => optional($investment->expire_at)->format('Y-m-d'),
                    'plan' => [
                        'name' => $investment->plan->name,
                    ]
                ];
            });
    
        return response()->json($plans);
    }
    
    public function closeAutoInvestment($autoPlanInvestmentId)
    {
        return DB::transaction(function () use ($autoPlanInvestmentId) {
            $investment = AutoPlanInvestment::with('user')
                ->lockForUpdate()
                ->findOrFail($autoPlanInvestmentId);

            $user = $investment->user;

            $totalInvested = Position::where('auto_plan_investment_id', $investment->id)
                ->where('user_id', $user->id)
                ->sum('amount');

            $balance = $investment->amount - $totalInvested;

            $positions = Position::where('auto_plan_investment_id', $investment->id)
                ->where('user_id', $user->id)
                ->get();

            foreach ($positions as $position) {
                $quantity = $position->quantity;
                try {
                    $this->closePosition($user, $position, $quantity);
                } catch (\Exception $e) {
                    dd($e);
                }
            }

            $investment->delete();

            if ($balance > 0) {
                $user->wallet->credit(
                    $balance,
                    'auto',
                    'Auto plan investment closing balance'
                );
            }

            return redirect()->back()->with('success', 'Plan deleted successfully');
        });
    }

    private function closePosition(User $user, Position $position, float $quantityToClose)
    {
        return DB::transaction(function () use ($user, $position, $quantityToClose) {
            $asset = Asset::find($position->asset_id);
            if (!$asset) {
                throw new \Exception('Asset not found');
            }

            if ($position->user_id != $user->id) {
                throw new \Exception('This position does not belong to the user');
            }

            if ($position->quantity <= 0 || $quantityToClose > $position->quantity) {
                throw new \Exception('Invalid quantity to close');
            }

            $position->lockForUpdate();
            $wallet = $position->account ?? 'wallet';
            $leverage = abs($position->leverage ?? 1);

            $closedQuantity = $quantityToClose;
            $closedValue = $asset->price * $closedQuantity;
            $openingValue = $position->price * $closedQuantity;
            $pl = ($closedValue - $openingValue + $position->extra) * $leverage;
            $plPercentage = ($pl / $openingValue) * 100;

            $comment = "Closed position on {$asset->name}";
            $user->wallet->credit($openingValue, $wallet, $comment);

            if ($pl != 0) {
                $transactionType = $pl > 0 ? 'credit' : 'debit';
                $user->wallet->{$transactionType}(abs($pl), $wallet, $comment);
            }

            Trade::create([
                'user_id' => $user->id,
                'asset_id' => $position->asset_id,
                'asset_type' => $asset->type,
                'type' => 'sell',
                'price' => $asset->price,
                'quantity' => $closedQuantity,
                'account' => $position->account,
                'amount' => $openingValue + $pl,
                'status' => 'open',
                'entry' => $position->entry,
                'exit' => $position->exit,
                'leverage' => $position->leverage,
                'interval' => $position->interval,
                'tp' => $position->tp,
                'sl' => $position->sl,
                'extra' => 0,
                'pl' => $pl,
                'pl_percentage' => $plPercentage,
            ]);

                $remainingPositions = Position::where('user_id', $user->id)
                    ->where('asset_id', $position->asset_id)
                    ->where('quantity', '>', $closedQuantity)
                    ->exists();

                $openBuyTrades = Trade::where('user_id', $user->id)
                    ->where('asset_id', $position->asset_id)
                    ->where('type', 'buy')
                    ->where('status', 'open')
                    ->get();

                foreach ($openBuyTrades as $trade) {
                    $trade->update([
                        'pl' => $pl,
                        'pl_percentage' => $plPercentage
                    ]);
                }

                if (!$remainingPositions) {
                    Trade::where('user_id', $user->id)
                        ->where('asset_id', $position->asset_id)
                        ->update(['status' => 'close']);
                }

                $position->delete();
        });
    }

    public function startInvestment(Request $request)
    {
        $request->validate([
            'auto_plan_id' => 'required|exists:auto_plans,id',
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
        ]);
    
        $plan = AutoPlan::findOrFail($request->auto_plan_id);
        $user = User::with('wallet')->findOrFail($request->user_id);
    
        // Validate investment amount
        if ($request->amount < $plan->min_invest) {
            return back()->with('error', "Minimum investment amount is {$plan->min_invest}");
        }
    
        if ($request->amount > $plan->max_invest) {
            return back()->with('error', "Maximum investment amount is {$plan->max_invest}");
        }
    
        // Check balance
        $balance = $user->wallet->getBalance('auto');
        if ($balance < $request->amount) {
            return back()->with('error', 'Insufficient balance in auto investing wallet');
        }
    
        // Process investment
        $user->wallet->debit($request->amount, 'auto', 'Auto plan investment');
    
        AutoPlanInvestment::create([
            'user_id' => $user->id,
            'auto_plan_id' => $plan->id,
            'amount' => $request->amount,
            'start_at' => now(),
            'expire_at' => $this->calculateEndDate(now(), $plan->duration, $plan->milestone),
        ]);

        return redirect()->back()->with('success', 'Investement stored successfully');
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
