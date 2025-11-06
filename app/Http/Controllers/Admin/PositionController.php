<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Asset;
use App\Models\Trade;
use App\Models\Savings;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Models\AutoPlanInvestment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\NotificationController as Notifications;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::query()->latest();
        
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('account') && $request->account) {
            $query->where('account', $request->account);
        }
        
        $trades = $query->paginate(100);
        $users = User::all();
        $assets = Asset::all();
        
        return view('admin.position', [
            'trades' => $trades,
            'users' => $users,
            'assets' => $assets,
            'selectedUser' => $request->user_id,
            'selectedAccount' => $request->account,
        ]);
    }

    public function fetch(Request $request)
    {
        $query = Trade::query()->latest();
        
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('account') && $request->account) {
            $query->where('account', $request->account);
        }
        
        $trades = $query->paginate(100);
        $users = User::all();
        $assets = Asset::all();
        
        return view('admin.position-history', [
            'trades' => $trades,
            'users' => $users,
            'assets' => $assets,
            'selectedUser' => $request->user_id,
            'selectedAccount' => $request->account,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asset_id' => ['required'],
            'user_id' => ['required'],
            'account' => ['required', 'in:wallet,brokerage,auto,savings'],
            'quantity' => ['required', 'numeric', 'min:0.00000001'],
            'dividends' => ['required', 'numeric', 'min:0.00', 'max:100'],
            'amount' => ['sometimes', 'numeric', 'min:0.1'],
            'entry' => ['sometimes'],
            'exit' => ['sometimes'],
            'tp' => ['sometimes'],
            'sl' => ['sometimes'],
            'leverage' => ['sometimes'],
            'extra' => ['required'],
            'created_at' => ['required', 'date'],
            'auto_plan_investment_id' => ['required_if:account,auto', 'exists:auto_plan_investments,id'],
            'savings_account_id' => ['required_if:account,savings', 'exists:savings,id'],
            'notify' => ['sometimes', 'boolean'],
        ]);

        // Handle validation failure
        if ($validator->fails()) {
            $firstError = $validator->errors()->first(); // get the first error message
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', $firstError); // pass the first error to the session
        }

        $user = User::findOrFail($request['user_id']);
        $asset = Asset::findOrFail($request['asset_id']);
        $wallet = $request->account;
        $newAmount = round($asset->price * $request->quantity, 2);

        switch ($wallet) {
            // case 'auto':
            //     $autoPlanInvestment = AutoPlanInvestment::with('plan')
            //         ->where('id', $request->auto_plan_investment_id)
            //         ->where('user_id', $user->id)
            //         ->firstOrFail();
    
            //     if ($autoPlanInvestment->expire_at?->isPast()) {
            //         return back()->with('error', 'This auto investment plan has expired');
            //     }
    
            //     $totalInvested = Position::where('auto_plan_investment_id', $autoPlanInvestment->id)
            //         ->sum('amount');
    
            //     if ($newAmount > ($autoPlanInvestment->amount - $totalInvested)) {
            //         return back()->with('error', "Insufficient balance in the selected auto investment");
            //     }
            //     break;

            case 'auto':
                $autoPlanInvestment = AutoPlanInvestment::with('plan')
                    ->where('id', $request->auto_plan_investment_id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                if ($autoPlanInvestment->expire_at?->isPast()) {
                    return back()->with('error', 'This auto investment plan has expired');
                }

                // Get positions related to this auto plan
                $positions = Position::where('auto_plan_investment_id', $autoPlanInvestment->id)
                    ->with('asset')
                    ->get();

                // Total invested
                $totalInvested = $positions->sum('amount');

                // Sum of extra
                $totalExtra = $positions->sum('extra');

                // Profit/Loss calculation
                $totalPL = $positions->sum(function ($position) {
                    $currentValue = $position->asset->price * $position->quantity;
                    $investedValue = $position->price * $position->quantity;
                    return $currentValue - $investedValue;
                });

                // Calculate effective balance including PL and extra
                $effectiveBalance = ($autoPlanInvestment->amount - $totalInvested) + $totalPL + $totalExtra;

                if ($newAmount > $effectiveBalance) {
                    return back()->with('error', "Insufficient balance in the selected auto investment");
                }
                break;

    
            case 'savings':
                $savingsAccount = Savings::where('id', $request->savings_account_id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                $totalSavings = Position::where('savings_id', $request->savings_account_id)
                    ->sum('amount');

                $balance = $savingsAccount->balance - $totalSavings;
    
                if ($newAmount > $balance) {
                    return back()->with('error', "Insufficient balance in the selected savings account");
                }
                break;
    
            default:
                if ($user->wallet->getBalance($wallet) < $newAmount) {
                    return back()->with('error', 'Insufficient balance');
                }
        }

        DB::beginTransaction();
        try {
            $positionData = [
                'user_id' => $user->id,
                'asset_id' => $asset->id,
                'asset_type' => $asset->type,
                'account' => $wallet,
                'price' => $asset->price,
                'quantity' => $request->quantity,
                'amount' => $newAmount,
                'status' => 'open',
                'entry' => $request['entry'],
                'tp' => $request['tp'],
                'sl' => $request['sl'],
                'leverage' => $request['leverage'],
                'dividends' => $request['dividends'],
                'extra' => $request['extra'],
                'created_at' => $request['created_at'],
                'auto_plan_investment_id' => $wallet == 'auto' ? $request->auto_plan_investment_id : null,
                'savings_id' => $wallet == 'savings' ? $request->savings_account_id : null,
            ];

            // $existingPosition = Position::where('user_id', $user->id)
            //     ->where('asset_id', $asset->id)
            //     ->where('status', 'open')
            //     // ->when($wallet === 'auto', fn($q) => $q->where('auto_plan_investment_id', $request->auto_plan_investment_id))
            //     ->lockForUpdate()
            //     ->first();

            $existingPosition = Position::where('user_id', $user->id)
                ->where('asset_id', $asset->id)
                ->where('status', 'open')
                ->when($wallet === 'auto', fn($q) => $q->where('auto_plan_investment_id', $request->auto_plan_investment_id))
                ->when($wallet === 'savings', fn($q) => $q->where('savings_id', $request->savings_account_id))
                ->lockForUpdate()
                ->first();

            if ($existingPosition) {
                $existingPosition->increment('quantity', $request->quantity);
                $existingPosition->increment('amount', $newAmount);
                $position = $existingPosition;
            } else {
                $position = Position::create($positionData);
            }

            Trade::create([
                'user_id' => $user->id,
                'position_id' => $position->id,
                'asset_id' => $asset->id,
                'asset_type' => $asset->type,
                'account' => $wallet,
                'type' => 'buy',
                'price' => $asset->price,
                'quantity' => $request->quantity,
                'amount' => $newAmount,
                'status' => 'open',
                'pl' => $request['extra'],
                'pl_percentage' => ($request['extra'] / $newAmount) * 100,
                'auto_plan_investment_id' => $wallet == 'auto' ? $request->auto_plan_investment_id : null,
                'savings_id' => $wallet == 'savings' ? $request->savings_account_id : null,
            ]);

            // Only debit wallet for non-auto accounts
            if ($wallet !== 'auto' && $wallet !== 'savings') {
                $user->wallet->debit($newAmount, $wallet, 'Position opened');
            }

            DB::commit();

            if ($request->boolean('notify')) {
                Notifications::sendPositionOpenedNotification($user, $position, $position->asset, $request->account);
            }

            return back()->with('success', $existingPosition 
                ? "Added {$request['quantity']} units to {$asset->symbol} position" 
                : 'Position created successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing trade: ' . $e->getMessage());
        }
    }

    public function close(Request $request)
    {
        return DB::transaction(function () use ($request) {
            // Validate user, asset, and position
            $user = User::find($request->user_id);
            if (!$user) {
                return back()->with('error', 'User not found');
            }

            $asset = Asset::find($request->asset_id);
            if (!$asset) {
                return back()->with('error', 'Asset not found');
            }

            $position = Position::find($request->position_id);
            if (!$position) {
                return back()->with('error', 'Position not found');
            }

            // Validate position belongs to user and has quantity
            if ($position->user_id != $user->id) {
                return back()->with('error', 'This position does not belong to you');
            }

            if ($position->quantity <= 0) {
                return back()->with('error', 'Position quantity is invalid');
            }

            if ($request->quantity > $position->quantity) {
                return back()->with('error', 'Cannot close more than available position');
            }

            // Lock position and load related data
            $position->lockForUpdate();
            $asset = Asset::findOrFail($position->asset_id);
            $wallet = $position->account ?? 'wallet';
            $leverage = abs($position->leverage ?? 1);

            // Calculate P/L values
            $closedQuantity = $request['quantity'];
            $closedValue = $asset->price * $closedQuantity;
            $openingValue = $position->price * $closedQuantity;
            $pl = ($closedValue - $openingValue + $position->extra) * $leverage;
            $plPercentage = ($pl / $openingValue) * 100;

            // Handle wallet transactions
            $comment = "Closed position on {$asset->name}";
            if($wallet !== 'auto')
                $user->wallet->credit($openingValue, $wallet, $comment);
            
            // if ($pl != 0) {
            //     $transactionType = $pl > 0 ? 'credit' : 'debit';
            //     $user->wallet->{$transactionType}(abs($pl), $wallet, $comment);
            // }

            if ($pl != 0) {
                $transactionType = $pl > 0 ? 'credit' : 'debit';
                if ($wallet == 'auto') {
                    $autoPlan = $position->autoInvest;
                    if ($autoPlan) {
                        $newAmount = $autoPlan->amount + $pl;
                        $autoPlan->update(['amount' => $newAmount]);
                    }
                } else {
                    $user->wallet->{$transactionType}(abs($pl), $wallet, $comment);
                }
            }

            // Record the trade
            $tradeData = [
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
                'auto_plan_investment_id' => $wallet == 'auto' ? $position->auto_plan_investment_id : null,
            ];

            Trade::create($tradeData);

            // Handle full or partial position closure
            if ($position->quantity === $closedQuantity) {
                // Check if there are any remaining positions for the same asset and user
                $remainingPositions = Position::where('user_id', $user->id)
                    ->where('asset_id', $position->asset_id)
                    ->where('quantity', '>', $request['quantity'])
                    ->exists();

                // Check if extra value changed and update trades accordingly
                $openBuyTrades = Trade::where('user_id', $user->id)
                    ->where('asset_id', $position->asset_id)
                    ->where('type', 'buy')
                    ->where('status', 'open')
                    ->get();

                if ($openBuyTrades->count() > 0) {
                    foreach ($openBuyTrades as $trade) {
                        $trade->update([
                            'pl' => $pl,
                            'pl_percentage' => $plPercentage
                        ]);
                    }
                }

                // If no remaining positions, update all related trades to "closed"
                if (!$remainingPositions) {
                    Trade::where('user_id', $user->id)
                        ->where('asset_id', $position->asset_id)
                        ->update(['status' => 'close']);
                }
                
                $position->delete();
                return back()->with('success', 'Position fully closed');
            } else {
                // Update position for partial closure
                $remainingQuantity = $position->quantity - $closedQuantity;
                $closedFraction = $closedQuantity / $position->quantity;
                $remainingExtra = $position->extra * (1 - $closedFraction);
                
                $position->update([
                    'quantity' => $remainingQuantity,
                    'amount' => $position->price * $remainingQuantity,
                    'extra' => $remainingExtra
                ]);
                
                return back()->with('success', 'Position partially closed');
            }
        });
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'asset_id' => ['sometimes', 'required'],
            'user_id' => ['sometimes', 'required'],
            'account' => ['sometimes', 'required', 'in:wallet,brokerage,auto'],
            'quantity' => ['sometimes', 'required', 'numeric', 'min:0.000001'],
            'amount' => ['sometimes', 'numeric', 'min:0.1'],
            'entry' => ['sometimes'],
            'exit' => ['sometimes'],
            'tp' => ['sometimes'],
            'sl' => ['sometimes'],
            'leverage' => ['sometimes'],
            'dividends' => ['sometimes', 'numeric', 'min:0.00', 'max:100'],
            'interval' => ['sometimes'],
            'extra' => ['sometimes', 'required'],
            'created_at' => ['sometimes', 'required', 'date'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid input data');
        }

        // Find the position to update
        $position = Position::find($id);
        if (!$position) {
            return back()->with('error', 'Position not found!');
        }

        // Find the user
        $user = User::find($request->input('user_id', $position->user_id));
        if (!$user) {
            return back()->with('error', 'User does not exist!');
        }

        // Find the asset
        $asset = Asset::find($request->input('asset_id', $position->asset_id));
        if (!$asset) {
            return back()->with('error', 'Asset not found!');
        }

        // Check if the account balance is sufficient for the updated position
        $wallet = $request->input('account', $position->account);
        $balance = $user->wallet->getBalance($wallet);

        $newAmount = $position->price * $request->input('quantity', $position->quantity);

        // if ($balance < $newAmount) {
        //     return back()->with('error', 'Insufficient balance.');
        // }

        // Store the original extra value before update
        $originalExtra = $position->extra;
        $leverage = $position->leverage;
        $newExtra = $request->input('extra', $position->extra);
        $newLeverage = $request->input('leverage', $position->leverage);

        // Update the position
        $position->update([
            'asset_id'   => $request->input('asset_id', $position->asset_id),
            'user_id'    => $request->input('user_id', $position->user_id),
            'account'    => $wallet,
            // 'price'      => $asset->price,  //Dont update the price, it will affect the calculation
            'quantity'   => $request->input('quantity', $position->quantity),
            'amount'     => $request->input('amount', $position->amount), // $newAmount,
            'status'     => $request->input('status', $position->status),
            'entry'      => $request->input('entry', $position->entry),
            'exit'      => $request->input('exit', $position->exit),
            'interval'      => $request->input('interval', $position->interval),
            'tp'         => $request->input('tp', $position->tp),
            'sl'         => $request->input('sl', $position->sl),
            'leverage'   => $request->input('leverage', $position->leverage),
            'dividends'   => $request->input('dividends', $position->dividends),
            'extra'      => $request->input('extra', $position->extra),
            'created_at' => $request->input('created_at', $position->created_at),
        ]);

        // Check if extra value changed and update trades accordingly
        if ($originalExtra != $newExtra) {
            $openBuyTrades = Trade::where('user_id', $user->id)
                ->where('asset_id', $position->asset_id)
                ->where('type', 'buy')
                ->where('status', 'open')
                ->get();

            if ($openBuyTrades->count() > 0) {
                $extraPerTrade = $newExtra / $openBuyTrades->count();

                foreach ($openBuyTrades as $trade) {
                    // Calculate PL percentage: (PL / original amount) * 100
                    $plPercentage = ($trade->amount > 0) ? ($extraPerTrade / $trade->amount) * 100 : 0;

                    $trade->update([
                        'leverage' => $request->input('leverage', $position->leverage),
                        'pl' => $extraPerTrade,
                        'pl_percentage' => $plPercentage
                    ]);
                }
            }
        }

        // Check if leverage value changed and update trades accordingly
        if ($leverage != $newLeverage) {
            $openBuyTrades = Trade::where('user_id', $user->id)
                ->where('asset_id', $position->asset_id)
                ->where('type', 'buy')
                ->where('status', 'open')
                ->get();

            if ($openBuyTrades->count() > 0) {
                foreach ($openBuyTrades as $trade) {
                    $trade->update([
                        'leverage' => $newLeverage,
                    ]);
                }
            }
        }

        return back()->with('success', 'Position updated successfully');
    }
}
