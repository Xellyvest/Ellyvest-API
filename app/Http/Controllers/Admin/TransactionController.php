<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationController as Notifications;

class TransactionController extends Controller
{

    public function index(Request $request)
    {
        $query = Transaction::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if($request->type == 'credit') {
            $type = "Deposit";
        } elseif($request->type == 'debit') {
            $type = "Withdrawal";
        } elseif($request->type == 'transfer') {
            $type = "Transfer";
        } else {
            $type = "Transactions";
        }
    
        $users = User::all();
        $transactions = $query->latest()->paginate(20);
    
        return view('admin.transaction', [
            'transactions' => $transactions,
            'title' => $type,
            'users' => $users,
            'selectedUser' => $request->user_id, 
            'selectedType' => $request->type,
        ]);
    }

    public function addTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required'],
            'amount' => ['required'],
            'account' => ['required', 'in:wallet,cash,brokerage,auto,ira'],
            'type' => ['required'],
            'comment' => ['sometimes'],
            'to' => ['sometimes', 'in:wallet,cash,brokerage,auto,ira'],
            'created_at' => ['required'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid input data');
        }

        $user = User::where('id', $request['user_id'])->first();
        
        if(!$user) {
            return back()->with('error', 'User does not exist!');
        }

        if($request->type == 'credit')
        {
            $amount = $request->amount;
            $comment = $request->comment ?? "Credit by Admin";

            $user->wallet->credit($amount, $request->account, $comment);

            $transaction = $user->storeTransaction($amount, $user->wallet->id, Wallet::class, 'credit', 'approved', $comment, 'wallet', null, Carbon::parse($request->created_at)->format('Y-m-d H:i:s'));

            if($request->has('is_email'))
                Notifications::sendApprovedDepositNotification($user, $amount, $request->comment);

            if($transaction)
                return redirect()->back()->with('success', 'Account credited successfully');
            
        } elseif($request->type == 'debit') {

            $amount = $request->amount;
            $comment = $request->comment ?? "Debited by Admin";

            $balance = $user->wallet->getBalance($request->account);
            if($amount > $balance)
                return back()->with('error', 'Insufficient Account balance');

            $user->wallet->debit($amount, $request->account, $comment);

            $transaction = $user->storeTransaction($amount, $user->wallet->id, Wallet::class, 'debit', 'approved', $comment, 'wallet', null, Carbon::parse($request->created_at)->format('Y-m-d H:i:s'));

            if($request->has('is_email'))
                Notifications::sendApprovedWithdrawalNotification($user, $amount, $comment);

            if($transaction)
                return redirect()->back()->with('success', 'Account debited successfully');
        } elseif($request->type == 'transfer') {

            $amount = $request->amount;
            $account = $request->account;
            $to = $request->to;
            $comment = $request->comment ?? 'Transferred from ' . $account . ' to ' . $to . ' by Admin';

            $balance = $user->wallet->getBalance($account);
            if($amount > $balance)
                return back()->with('error', 'Insufficient ' . $account . ' balance');

            if(!$to)
                return back()->with('error', 'Invalid recepient account');

            if($account === $to)
                return back()->with('error', 'Transfer cannot be made to same account');

            $user->wallet->debit($amount, $account, $comment);
            $user->wallet->credit($amount, $to, $comment);

            $transaction = $user->storeTransaction($amount, $user->wallet->id, Wallet::class, 'transfer', 'approved', $comment, $account, $to, Carbon::parse($request->created_at)->format('Y-m-d H:i:s'));

            if($request->has('is_email'))
                Notifications::sendTransferNotification($user, (float) $amount, $account, $to);

            if($transaction)
                return redirect()->back()->with('success', 'Account transfer successful');

        }
        
        return redirect()->back()->with('error', 'Error: Something went worng!! ');
    }

    public function editTransaction(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'user_id' => ['required'],
            'comment' => ['required'],
            'amount' => ['required', 'numeric', 'min:0.01'], // Ensure amount is positive
            'created_at' => ['required', 'date'], // Ensure created_at is a valid date
        ]);

        // If validation fails, return with errors
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid input data');
        }

        $user = User::where('id', $request['user_id'])->first();
        
        if(!$user) {
            return back()->with('error', 'User does not exist!');
        }

        // Find the transaction by ID
        $transaction = Transaction::find($id);

        // If the transaction doesn't exist, return with an error
        if (!$transaction) {
            return back()->with('error', 'Transaction not found!');
        }

        if($transaction->status == 'approved')
        {
            if($transaction->type = 'credit')
            {
                $user->wallet->debit($transaction->amount, 'wallet', 'Admin edit transaction');
                $user->wallet->credit($request->amount, 'wallet', 'Admin edit transaction');
            } elseif ($transaction->type = 'debit')
            {
                $user->wallet->credit($transaction->amount, 'wallet', 'Admin edit transaction');
                $user->wallet->debit($request->amount, 'wallet', 'Admin edit transaction');
            }
        }

        // Update the transaction amount and created_at fields
        $transaction->update([
            'amount' => $request->amount,
            'comment' => $request->comment,
            'created_at' => Carbon::parse($request->created_at)->format('Y-m-d H:i:s'),
        ]);

        // Return with a success message
        return redirect()->back()->with('success', 'Transaction updated successfully');
    }

    public function toggleTransaction(Transaction $transaction, $status)
    {
        $user = $transaction->user;
        $wallet = $user->wallet;
        $amount = $transaction->amount;
    
        if (!$user || !$wallet) {
            return back()->with('error', 'User or Wallet not found!');
        }
    
        // Check if transaction is already cancelled
        if ($transaction->status === 'cancelled') {
            return back()->with('error', 'Transaction has already been cancelled.');
        }

        if($transaction->status === 'approved')
        {
            // Reverse logic
            if ($transaction->type === 'credit') {
                $balance = $wallet->getBalance('wallet');
                if ($amount > $balance) {
                    return back()->with('error', 'Insufficient balance to reverse credit.');
                }
        
                $wallet->debit($amount, 'wallet', 'Reversed credit transaction');
            } elseif ($transaction->type === 'debit') {
                $wallet->credit($amount, 'wallet', 'Reversed debit transaction');
            }
        }

        // Mark as cancelled (instead of deleting)
        $transaction->update([
            'status' => $status,
        ]);
    
        return redirect()->back()->with('success', 'Transaction successfully reversed.');
    }

    public function markProgressTransaction(Transaction $transaction)
    {
        // Check if transaction is already cancelled
        if ($transaction->type !== 'debit' || $transaction->status !== 'pending') {
            return back()->with('error', 'Transaction has to be withdrawal and pending.');
        }

        $transaction->update([
            'status' => 'in_progress',
        ]);
    
        return redirect()->back()->with('success', 'Transaction successfully updated.');
    }

    public function destroyTransaction(Transaction $transaction)
    {   
        $amount = $transaction->amount;

        $user = User::where('id', $transaction['user_id'])->first();
        
        if(!$user) {
            return back()->with('error', 'User does not exist!');
        }

        if($transaction->status == 'approved'){
            if($transaction->type == 'credit') {

                $balance = $user->wallet->getBalance('wallet');
                if($amount > $balance)
                    return back()->with('error', 'Transaction cannot be deleted, due to unavailable funds');
    
                $user->wallet->debit($amount, 'wallet', 'Revesed transaction');
    
                $balance = $transaction->balance - $amount;
    
            } elseif($transaction->type == 'debit') {
    
                $user->wallet->credit($amount, 'wallet', 'Revesed transaction');
    
            }
        }

        $transaction->delete();

        return redirect()->back()->with('success', 'Transaction deleted successfully.');
    }

    public function deposit(Request $request, Transaction $transaction)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approved,decline',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid input data');
        }

        $user = $transaction->user;

        // APPROVE DEPOSIT 
        if($request->action == 'approved') {
            
            $user->wallet->credit($transaction->amount, 'wallet', 'Admin approved deposit');

            $transaction->update(['status' => 'approved']);

            Notifications::sendApprovedDepositNotification($user, $transaction->amount);
            
        } elseif($request->action == 'decline') {

            $transaction->update(['status' => 'declined',]);

            Notifications::sendDeclinedDepositNotification($user, $transaction->amount, $transaction->comment);

        } else {
            return back()->with('error', 'Error process transaction, try again');
        }

        return back()->with('success', 'Transaction updates successfully');
    }

    public function withdraw(Request $request, Transaction $transaction)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approved,decline',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid input data');
        }

        $user = $transaction->user;

        if($request->action == 'approved') {
            
            $user->wallet->debit($transaction->amount, 'wallet', 'Admin approved withdrawal');

            $transaction->update(['status' => 'approved']);

            Notifications::sendApprovedWithdrawalNotification($user, $transaction->amount, $transaction->comment);
            
        } elseif($request->action == 'decline') {

            $transaction->update(['status' => 'declined',]);

            Notifications::sendDeclinedWithdrawalNotification($user, $transaction->amount, $transaction->comment);

        } else {
            return back()->with('error', 'Error process transaction, try again');
        }

        return back()->with('success', 'Transaction updates successfully');
    }

    // Decline a transaction
    public function decline(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->status = 'declined';
        $transaction->save();

        return redirect()->back()->with('success', 'Transaction declined successfully.');
    }


}
