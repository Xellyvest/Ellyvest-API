<?php

namespace App\Services\User;

use App\Models\Asset;
use App\Models\Trade;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\NotificationController as Notifications;
use App\Models\User;

class TradeService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function create(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $asset = Asset::findOrFail($data['asset_id']);
            $balance = $user->wallet->getBalance($data['wallet']);
            $amount = ($asset->price * $data['quantity']);

            if ($balance < $amount) {
                abort(403, 'Insufficient balance in your wallet account.');
            }

            $trade = Trade::create([
                'user_id'     => $user->id,
                'asset_id'    => $data['asset_id'],
                'asset_type'  => $data['asset_type'],
                'type'        => $data['type'],
                'price'       => $asset->price,
                'quantity'    => $data['quantity'],
                'amount'      => $amount,
                'status'      => $data['status'] ?? 'open',
                'entry'       => $data['entry'] ?? null,
                'exit'        => $data['exit'] ?? null,
                'leverage'    => $data['leverage'] ?? null,
                'interval'    => $data['interval'] ?? null,
                'tp'          => $data['tp'] ?? null,
                'sl'          => $data['sl'] ?? null,
                'extra'       => 0,
            ]);

            $user->wallet->debit($amount, $data['wallet'], 'Trade create');
            $user->storeTransaction($amount, $trade->id, 'App/Models/Trade', 'debit', 'approved', 'Order created on ' . $asset->symbol . ' of ' . $data['quantity'] . ' units', null, null, now());

            return $trade;
        });
    }

    public function update(Trade $trade, array $data)
    {
        return DB::transaction(function () use ($trade, $data) {
            $trade->update($data);
            return $trade;
        });
    }

    public function toggleStatus(Trade $trade, $user, $request)
    {
        // Ensure the trade belongs to the authenticated user
        if ($user->id !== $trade->user_id) {
            abort(403, 'Unauthorized: This trade does not belong to you.');
        }

        // Prevent reopening a closed trade
        if ($trade->status === 'close') {
            abort(400, 'You cannot toggle a closed trade.');
        }

        if ($trade->status === $request->status) {
            abort(400, 'Trade is already ' . $trade->status);
        }

        if ($request->status === 'close') {
            $asset = Asset::findOrFail($trade['asset_id']);
            $amount = ($asset->price * $trade['quantity']);

            if($amount > 0) {
                $user->wallet->credit($amount, 'wallet', 'Trade close');
                $user->storeTransaction($amount, $trade->id, 'App/Models/Trade', 'credit', 'approved', 'Open Order', null, null, now());
            }
        }

        // Update the trade status
        $trade->update(['status' => $request->status]);

        return $trade;
    }

    // public function createPosition(array $data, $user)
    // {
    //     return DB::transaction(function () use ($data, $user) {
    //         $wallet = $data['wallet'];

    //         $asset = Asset::findOrFail($data['asset_id']);
    //         $balance = $user->wallet->getBalance($wallet);
    //         $newAmount = $asset->price * $data['quantity'];

    //         // Check if user already has an open position for this asset
    //         $existingPosition = Position::where('user_id', $user->id)
    //             ->where('asset_id', $data['asset_id'])
    //             ->where('status', 'open')
    //             ->lockForUpdate() // Prevents race conditions
    //             ->first();

    //         if ($existingPosition) {
    //             // If position exists, update quantity and amount
    //             $newQuantity = $existingPosition->quantity + $data['quantity'];
    //             $updatedAmount = $existingPosition->amount + $newAmount;

    //             if ($balance < $newAmount) {
    //                 abort(403, 'Insufficient balance to add more to this position.');
    //             }

    //             if ($newAmount < 1) {
    //                 abort(403, 'Cannot open position, amount is less than 1.');
    //             }

    //             $existingPosition->update([
    //                 'quantity' => $newQuantity,
    //                 'amount' => $updatedAmount,
    //             ]);

    //              // Store trades as position transaction history
    //             Trade::create([
    //                 'user_id'     => $user->id,
    //                 'asset_id'    => $data['asset_id'],
    //                 'asset_type'  => $asset->type,
    //                 'type'        => 'buy',
    //                 'account'        => $data['wallet'],
    //                 'price'       => $asset->price,
    //                 'quantity'    => $data['quantity'],
    //                 'amount'      => $newAmount,
    //                 'status'      => 'open',
    //                 'entry'       => $data['entry'] ?? null,
    //                 'exit'        => $data['exit'] ?? null,
    //                 'leverage'    => $data['leverage'] ?? 1,
    //                 'interval'    => $data['interval'] ?? null,
    //                 'tp'          => $data['tp'] ?? null,
    //                 'sl'          => $data['sl'] ?? null,
    //                 'extra'       => 0,
    //             ]);

    //             $user->wallet->debit($newAmount, $wallet, 'Added to an existing position');
    //             // $user->storeTransaction($newAmount, $existingPosition->id, Position::class, 'debit', 'approved', "Added {$data['quantity']} units to {$asset->symbol} position", null, null, now());

    //             return $existingPosition;
    //         } else {
    //             // Create a new position if none exists
    //             if ($balance < $newAmount) {
    //                 abort(403, 'Insufficient balance to open a new position.');
    //             }

    //             $trade = Position::create([
    //                 'user_id'    => $user->id,
    //                 'asset_id'   => $data['asset_id'],
    //                 'asset_type' => $asset->type,
    //                 'account'        => $data['wallet'],
    //                 'price'      => $asset->price,
    //                 'quantity'   => $data['quantity'],
    //                 'amount'     => $newAmount,
    //                 'status'     => $data['status'] ?? 'open',
    //                 'entry'      => $data['entry'] ?? null,
    //                 'exit'       => $data['exit'] ?? null,
    //                 'leverage'   => $data['leverage'] ?? null,
    //                 'interval'   => $data['interval'] ?? null,
    //                 'tp'         => $data['tp'] ?? null,
    //                 'sl'         => $data['sl'] ?? null,
    //                 'extra'      => 0,
    //             ]);

    //             // Store trades as position transaction history
    //             Trade::create([
    //                 'user_id'     => $user->id,
    //                 'asset_id'    => $data['asset_id'],
    //                 'asset_type'  => $asset->type,
    //                 'type'        => 'buy',
    //                 'account'        => $data['wallet'],
    //                 'price'       => $asset->price,
    //                 'quantity'    => $data['quantity'],
    //                 'amount'      => $newAmount,
    //                 'status'      => 'open',
    //                 'entry'       => $data['entry'] ?? null,
    //                 'exit'        => $data['exit'] ?? null,
    //                 'leverage'    => $data['leverage'] ?? null,
    //                 'interval'    => $data['interval'] ?? null,
    //                 'tp'          => $data['tp'] ?? null,
    //                 'sl'          => $data['sl'] ?? null,
    //                 'extra'       => 0,
    //             ]);

    //             $user->wallet->debit($newAmount, $wallet, 'Opened a new position');
    //             // $user->storeTransaction($newAmount, $trade->id, Position::class, 'debit', 'approved', "Opened a new position on {$asset->symbol} with {$data['quantity']} units", null, null, now());

    //             return $trade;
    //         }
    //     });
    // }

    public function createPosition(array $data, User $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $asset = Asset::findOrFail($data['asset_id']);
            $newAmount = $asset->price * $data['quantity'];
            $accountType = $data['wallet']; // 'wallet' or 'savings'
            $savingsId = $data['savings_id'] ?? null;

            // Validate based on account type
            if ($accountType !== 'savings') {
                $balance = $user->wallet->getBalance($data['wallet']);

                if ($balance < $newAmount) {
                    abort(403, 'Insufficient wallet balance to open position.');
                }
            } elseif ($accountType === 'savings' && $savingsId) {
                $savings = $user->savings()->findOrFail($savingsId);
                $totalInvested = Position::where('savings_id', $savings->id)
                    ->where('user_id', $user->id)
                    ->sum('amount');
                
                $availableBalance = $savings->balance - $totalInvested;
                if ($availableBalance < $newAmount) {
                    abort(403, 'Insufficient savings balance to open position.');
                }
            }

            // Check for existing position (same asset and same account type)
            $existingPosition = Position::where('user_id', $user->id)
                ->where('asset_id', $data['asset_id'])
                ->where('account', $accountType)
                ->when($accountType === 'savings', fn($q) => $q->where('savings_id', $savingsId))
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if ($existingPosition) {
                // Update existing position
                $existingPosition->update([
                    'quantity' => $existingPosition->quantity + $data['quantity'],
                    'amount' => $existingPosition->amount + $newAmount,
                ]);
                $position = $existingPosition;
            } else {
                // Create new position
                $position = Position::create([
                    'user_id' => $user->id,
                    'asset_id' => $data['asset_id'],
                    'asset_type' => $asset->type,
                    'account' => $accountType,
                    'savings_id' => $accountType === 'savings' ? $savingsId : null,
                    'price' => $asset->price,
                    'quantity' => $data['quantity'],
                    'amount' => $newAmount,
                    'status' => 'open',
                    'entry' => $data['entry'] ?? null,
                    'exit' => $data['exit'] ?? null,
                    'leverage' => $data['leverage'] ?? null,
                    'interval' => $data['interval'] ?? null,
                    'tp' => $data['tp'] ?? null,
                    'sl' => $data['sl'] ?? null,
                ]);
            }

            // Create trade record
            Trade::create([
                'user_id' => $user->id,
                'position_id' => $position->id,
                'asset_id' => $data['asset_id'],
                'asset_type' => $asset->type,
                'type' => 'buy',
                'account' => $accountType,
                'savings_id' => $accountType === 'savings' ? $savingsId : null,
                'price' => $asset->price,
                'quantity' => $data['quantity'],
                'amount' => $newAmount,
                'status' => 'open',
                'entry' => $data['entry'] ?? null,
                'exit' => $data['exit'] ?? null,
                'leverage' => $data['leverage'] ?? null,
                'interval' => $data['interval'] ?? null,
                'tp' => $data['tp'] ?? null,
                'sl' => $data['sl'] ?? null,
            ]);

            // Debit only for wallet positions
            if ($accountType !== 'savings') {
                $user->wallet->debit($newAmount, $data['wallet'], 'Position opened');
            }

            return $position;
        });
    }

    public function closePosition(Position $position, $user, $request)
    {
        return DB::transaction(function () use ($position, $user, $request) {
            // Validate position ownership and status
            if ($user->id !== $position->user_id) {
                abort(403, 'Unauthorized: This trade does not belong to you.');
            }

            if ($position->status === 'locked') {
                abort(400, 'You cannot close this locked position.');
            }

            if ($position->quantity <= 0) {
                abort(400, 'Empty position: Contact admin for more information.');
            }

            if ($request['quantity'] > $position->quantity) {
                abort(400, 'Invalid quantity: You cannot close more than your available position.');
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
            
            if ($pl != 0) {
                $transactionType = $pl > 0 ? 'credit' : 'debit';
                $user->wallet->{$transactionType}(abs($pl), $wallet, $comment);
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
                return 'Order closed successfully';
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
                
                return $position;
            }
        });
    }

}
