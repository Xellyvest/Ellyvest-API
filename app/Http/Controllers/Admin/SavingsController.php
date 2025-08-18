<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Account;
use App\Models\Country;
use App\Models\Savings;
use App\Models\Position;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SavingsLedger;
use App\Models\SavingsAccount;
use Illuminate\Support\Carbon;
use PhpParser\Node\Stmt\Return_;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\NotificationController as Notifications;

class SavingsController extends Controller
{
    public function index()
    {
        $savings = Savings::latest()->paginate(20);
        $accounts = SavingsAccount::all();
        $users = User::all();

        return view('admin.savings', [
            'savings' => $savings,
            'users' => $users,
            'accounts' => $accounts,
        ]);
    }

    public function accounts()
    {
        $accounts = SavingsAccount::paginate(20);
        $countries = Country::all();

        return view('admin.accounts', [
            'accounts' => $accounts,
            'countries' => $countries,
        ]);
    }

    public function fetchTransactions()
    {
        $transactions = SavingsLedger::latest()->paginate(20);

        return view('admin.savings-profit', [
            'transactions' => $transactions,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => ['required'],
            'user_id' => ['required'],
            'created_at' => ['required'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid input data');
        }

        $user = User::where('id', $request['user_id'])->first();
        
        if(!$user) {
            return back()->with('error', 'User does not exist!');
        }
        
        // Prevent duplicate savings account type for the user
        if ($user->savings()->where('savings_account_id', $request->account_id)->exists()) {
            return redirect()->back()->with('error', 'User already have this savings account.');
        }

        $accounts = Savings::create([
            'user_id' => $request['user_id'],
            'savings_account_id' => $request['account_id'],
            'balance' => 0,
            'old_balance' => 0,
            'comment' => 'Admin created account',
            'created_at' => Carbon::parse($request['created_at'])->format('Y-m-d H:i:s'),
        ]);

        if($accounts)
            return redirect()->back()->with('success', 'Account created successfully.');

        return redirect()->back()->with('error', 'Error Storing Account');
    }

    public function storeAccounts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'title' => ['required'],
            'note' => ['required'],
            'countries_id' => ['required', 'array'], // Ensure it is an array
            'countries_id.*' => ['exists:countries,id'], // Validate each country ID
            'min_contribution' => ['required'],
            'max_contribution' => ['required'],
            'min_cashout' => ['required'],
            'max_cashout' => ['required'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid input data');
        }

        $accounts = SavingsAccount::create([
            'name' => $request['name'],
            'slug' => Str::slug($request['name']),
            'title' => $request['title'],
            'note' => $request['note'],
            'country_id' => json_encode($request->countries_id),
            'status' => 'active',
            'min_contribution' => $request['min_contribution'],
            'max_contribution' => $request['max_contribution'],
            'min_cashout' => $request['min_cashout'],
            'max_cashout' => $request['max_cashout'],
        ]);

        if($accounts)
            return redirect()->back()->with('success', 'Account created successfully.');

        return redirect()->back()->with('error', 'Error Storing Account');
    }

    public function updateAccounts(Request $request, SavingsAccount $savingsAccount)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'title' => ['required'],
            'note' => ['required'],
            'countries_id' => ['required', 'array'],
            'min_contribution' => ['required'],
            'max_contribution' => ['required'],
            'min_cashout' => ['required'],
            'max_cashout' => ['required'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid input data');
        }

        $savingsAccount->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'title' => $request->title,
            'note' => $request->note,
            'min_contribution' => $request->min_contribution,
            'max_contribution' => $request->max_contribution,
            'min_cashout' => $request->min_cashout,
            'max_cashout' => $request->max_cashout,
            'country_id' => json_encode($request->countries_id), // Store as JSON array
        ]);

        return redirect()->back()->with('success', 'Account updated successfully.');
    }

    public function destroyAccount(SavingsAccount $savingsAccount)
    {   
        // Check if there are any related Savings records
        if ($savingsAccount->savings()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete account: It is linked to existing savings.');
        }
    
        // Proceed with deletion if no related savings exist
        $savingsAccount->delete();

        return redirect()->back()->with('success', 'Account deleted successfully.');
    }

    public function transactions(User $user, Savings $savings)
    {
        $transactions = SavingsLedger::where('user_id', $user->id)
            ->where('savings_id', $savings->id)
            ->latest()
            ->paginate(20);

        return view('admin.savings-transactions', [
            'transactions' => $transactions,
            'user' => $user,
            'savings' => $savings,
        ]);
    }

    public function contribute(Request $request, User $user, Savings $savings)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
            'type' => 'required|in:credit,debit,profit',
            'created_at' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid input data');
        }

        $amount = $request->amount;
        $type = $request->type;
        $created_at  = Carbon::parse($request->created_at)->format('Y-m-d H:i:s');

        switch ($type) {
            case 'credit':
                $balance = $user->wallet->getBalance('wallet');
                if ($balance < $amount) {
                    return back()->with('error', 'Insufficient funds in users wallet balance.');
                }

                $user->wallet->debit($amount, 'wallet', 'Savings Contribution to ' . $savings->savingsAccount->name . ' account.');

                SavingsLedger::record($user, 'credit', $savings->id, $amount, 'contribution', 'approved', 'Admin Contribution', $created_at);

                // Update balances
                $savings->update([
                    'old_balance' => $savings->balance,
                    'balance' => $savings->balance + $amount
                ]);

                if($request->has('is_email'))
                    Notifications::sendSavingsCreditNotification($user, $savings->savingsAccount, $amount, $savings->balance);

                break;
            case 'debit':
                $balance = $savings->balance;
                if ($balance < $amount) {
                    return back()->with('error', "Insufficient funds in your " . $savings->savingsAccount->name . " account.");
                }

                $user->wallet->credit($amount, 'wallet', 'Savings Cashout from ' . $savings->savingsAccount->name . ' account.');

                SavingsLedger::record($user, 'debit', $savings->id, $amount, 'contribution', 'approved', 'Admin Contribution', $created_at);

                // Update balances
                $savings->update([
                    'old_balance' => $savings->balance,
                    'balance' => $savings->balance - $amount
                ]);

                if($request->has('is_email'))
                    Notifications::sendSavingsDebitNotification($user, $savings->savingsAccount, $amount, $savings->balance);

                break;
            case 'profit':
                SavingsLedger::record($user, 'credit', $savings->id, $amount, 'profit', 'approved', 'Admin Contribution', $created_at);

                // Update balances
                $savings->update([
                    'old_balance' => $savings->balance,
                    'balance' => $savings->balance + $amount
                ]);

                if($request->has('is_email'))
                    Notifications::sendSavingsCreditNotification($user, $savings->savingsAccount, $amount, $savings->balance);

                break;
            default:
                return back()->with('error', 'Wrong method');
        }

        

        if($savings)
            return back()->with('success', 'Contribution added successfully');

        return back()->with('error', 'Error making contributions');
    }

    public function approveDebit(SavingsLedger $savingsLedger)
    { 
        // Only process debit transactions that are pending
        if ($savingsLedger->type !== 'debit' || $savingsLedger->status !== 'pending') {
            return back()->with('error', 'Only pending debit transactions can be approved');
        }

        DB::transaction(function () use ($savingsLedger) {
            // Get the related savings account
            $savings = Savings::findOrFail($savingsLedger->savings_id);
            $user = User::findOrFail($savingsLedger->user_id);

            // Verify the savings has sufficient balance (double check)
            if ($savings->balance < $savingsLedger->amount) {
                throw new \Exception("Insufficient funds in savings account");
            }

            // Update savings balance
            $savings->update([
                'old_balance' => $savings->balance,
                'balance' => $savings->balance - $savingsLedger->amount
            ]);

            // Credit user's wallet
            $user->wallet->credit(
                $savingsLedger->amount, 
                'wallet', 
                'Approved withdrawal from ' . $savings->savingsAccount->name . ' savings'
            );

            // Update the ledger status
            $savingsLedger->update([
                'status' => 'approved',
                'balance' => $savings->balance,
                'old_balance' => $savings->old_balance
            ]);

            // Send notifications
            Notifications::sendApprovedSavingsDebitNotification(
                $user, 
                $savings->savingsAccount, 
                $savingsLedger->amount, 
                $savings->balance
            );
        });

        return back()->with('success', 'Debit transaction approved successfully');
    }

    public function declineDebit(SavingsLedger $savingsLedger)
    {
        // Only process debit transactions that are pending
        if ($savingsLedger->type !== 'debit' || $savingsLedger->status !== 'pending') {
            return back()->with('error', 'Only pending debit transactions can be declined');
        }

        DB::transaction(function () use ($savingsLedger) {
            // Update the ledger status to declined
            $savingsLedger->update([
                'status' => 'declined'
            ]);

            // Get user and savings account for notification
            $user = User::findOrFail($savingsLedger->user_id);
            $savings = Savings::findOrFail($savingsLedger->savings_id);

            // Send notification
            Notifications::sendDeclinedSavingsDebitNotification(
                $user, 
                $savings->savingsAccount, 
                $savingsLedger->amount,
                $savings->balance
            );
        });

        return back()->with('success', 'Debit transaction declined successfully');
    }

    public function lockAccount(Request $request, Savings $saving)
    {
        $request->validate([
            'locked_account_message' => 'required|string|max:255'
        ]);

        $saving->update([
            'status' => 'locked',
            'locked_account_message' => $request->locked_account_message
        ]);

        return back()->with('success', 'Account locked successfully');
    }

    public function unlockAccount(Savings $saving)
    {
        $saving->update([
            'status' => 'active',
        ]);

        return back()->with('success', 'Account unlocked successfully');
    }

    public function lockTrading(Request $request, Savings $saving)
    {
        $request->validate([
            'locked_trading_message' => 'required|string|max:255'
        ]);

        $saving->update([
            'trading' => 'locked',
            'locked_trading_message' => $request->locked_trading_message
        ]);

        return back()->with('success', 'Trading locked successfully');
    }

    public function unlockTrading(Savings $saving)
    {
        $saving->update([
            'trading' => 'active',
        ]);

        return back()->with('success', 'Trading unlocked successfully');
    }

    public function fetchUserSavings(User $user)
    {
        $savings = $user->savings()
            ->with('savingsAccount')
            ->get()
            ->map(function ($saving) {
                $totalInvested = Position::where('savings_id', $saving->id)
                    ->where('user_id', $saving->user_id)
                    ->sum('amount');

                $balance = $saving->balance - $totalInvested;

                return [
                    'id' => $saving->id,
                    'balance' => $balance,
                    'savings_account' => [
                        'name' => $saving->savingsAccount->name,
                    ]
                ];
            });

        return response()->json($savings);
    }


    public function destroy(SavingsLedger $savingsLedger)
    {   
        $amount = $savingsLedger->amount;

        $savings = Savings::findOrFail($savingsLedger['savings_id']);
        $user = User::findOrFail($savingsLedger->user_id);

        if($savingsLedger->status == 'approved')
        {
            $user->wallet->debit(
                $savingsLedger->amount, 
                'wallet', 
                'Delete action from ' . $savings->savingsAccount->name . ' savings'
            );

            if($savingsLedger->type == 'credit') {
                $balance = $savings->balance - $amount;
            } else {
                $balance = $savings->balance + $amount;
            }
    
            $savings->update([
                'old_balance' => $savings->balance,
                'balance' => $balance,
            ]);
        }

        $savingsLedger->delete();

        return redirect()->back()->with('success', 'Transaction deleted successfully.');
    }

}
