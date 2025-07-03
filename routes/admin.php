<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TradeController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\AutoinvestController;
use App\Http\Controllers\Admin\SavingsController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\PaymentMethodController;


Route::get('/login', function () {
    return redirect('admin.login');
})->name('login');

Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected admin routes
Route::group(['middleware' => ['active_admin']], function (){
    Route::get('/alt/login', [UserController::class, 'showLogin'])->name('altLogin');
    Route::post('/alt/login', [UserController::class, 'login'])->name('altLogin');  

    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/update/{user}', [UserController::class, 'update'])->name('users.update');
    Route::put('/users/toggle/{user}', [UserController::class, 'toggle'])->name('users.toggle');
    Route::post('/users/bank/{user}', [UserController::class, 'bank'])->name('users.bank');
    Route::put('/users/kyc/{user}', [UserController::class, 'kyc'])->name('users.kyc');
    Route::put('/users/kyc/{user}/cancel', [UserController::class, 'cancelKYC'])->name('users.cancelkyc');
    Route::delete('/users/delete/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/connect/{user}', [UserController::class, 'toggleWalletConnect'])->name('users.connect');

    Route::post('/user/credit/{user}', [UserController::class, 'credit'])->name('user.credit');
    Route::post('/user/debit/{user}', [UserController::class, 'debit'])->name('user.debit');

    Route::post('/user/settings/{user}', [UserController::class, 'settings'])->name('user.settings');

    Route::get('/user/payment-method', [PaymentMethodController::class, 'index'])->name('user.payment');
    Route::post('/user/payment-method/{user}', [PaymentMethodController::class, 'store'])->name('user.payment.store');
    Route::put('/user/payment-method/{payment}', [PaymentMethodController::class, 'update'])->name('user.payment.update');
    Route::delete('/user/payment-method/{payment}', [PaymentMethodController::class, 'destroy'])->name('user.payment.delete');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::post('/transactions/store', [TransactionController::class, 'addTransaction'])->name('transactions.store');
    Route::post('/transactions/deposit/{transaction}', [TransactionController::class, 'deposit'])->name('transactions.deposit');
    Route::post('/transactions/withdraw/{transaction}', [TransactionController::class, 'withdraw'])->name('transactions.withdraw');
    Route::post('/transactions/{transactions}/decline', [TransactionController::class, 'decline'])->name('transactions.decline');
    Route::put('/transactions/{transaction}/edit', [TransactionController::class, 'editTransaction'])->name('transactions.edit');
    Route::post('/transactions/{transaction}/status/{status}', [TransactionController::class, 'toggleTransaction'])->name('transactions.toggle');
    Route::post('/transactions/{transaction}/toggle', [TransactionController::class, 'markProgressTransaction'])->name('transactions.markProgress');
    Route::delete('/transactions/{transaction}/destroy', [TransactionController::class, 'destroyTransaction'])->name('transactions.destroy');

    Route::get('/trades', [UserController::class, 'trades'])->name('trades');
    Route::post('/trades/user/create', [TradeController::class, 'store'])->name('trade.create');
    Route::put('/trades/user/update/{trade}', [TradeController::class, 'update'])->name('trade.update');
    Route::put('/trades/user/toggle/{trade}', [TradeController::class, 'toggle'])->name('trade.toggle');
    Route::delete('/trades/destroy/{trade}', [TradeController::class, 'destroy'])->name('trade.destroy');

    Route::get('/savings', [SavingsController::class, 'index'])->name('account.savings');
    Route::get('/savings-accounts', [SavingsController::class, 'accounts'])->name('accounts.savings');
    Route::post('/savings-account/store', [SavingsController::class, 'storeAccounts'])->name('account.savings.store');
    Route::put('/savings-account/update/{savingsAccount}', [SavingsController::class, 'updateAccounts'])->name('account.savings.update');
    Route::put('/savings/approve/{savingsLedger}', [SavingsController::class, 'approveDebit'])->name('account.savings.approve');
    Route::put('/savings/decline/{savingsLedger}', [SavingsController::class, 'declineDebit'])->name('account.savings.decline');
    Route::post('/savings/lock/{saving}', [SavingsController::class, 'lockAccount'])->name('account.savings.lock');
    Route::post('/savings/unlock/{saving}', [SavingsController::class, 'unlockAccount'])->name('account.savings.unlock');
    Route::post('/savings/trade/lock/{saving}', [SavingsController::class, 'lockTrading'])->name('account.savings.trade.lock');
    Route::post('/savings/trade/unlock/{saving}', [SavingsController::class, 'unlockTrading'])->name('account.savings.trade.unlock');
    Route::delete('/savings-account/destroy/{savingsAccount}', [SavingsController::class, 'destroyAccount'])->name('account.savings.destroy');
    Route::get('/savings/{user}/transactions/{savings}', [SavingsController::class, 'transactions'])->name('accounts.transactions');
    Route::post('/savings/{user}/contribue/{savings}', [SavingsController::class, 'contribute'])->name('accounts.contribute');
    Route::delete('/savings/destroy/{savingsLedger}', [SavingsController::class, 'destroy'])->name('savings.destroy');
    Route::post('/savings/user/account', [SavingsController::class, 'store'])->name('account.store');
    Route::get('/savings/transactions', [SavingsController::class, 'fetchTransactions'])->name('account.fetch.transactions');

    Route::get('/articles/all', [ArticleController::class, 'index'])->name('article.all');
    Route::post('/articles/store', [ArticleController::class, 'store'])->name('article.store');
    Route::put('/articles/edit/{article}', [ArticleController::class, 'update'])->name('article.edit');
    Route::put('/articles/toggle/{article}', [ArticleController::class, 'toggle'])->name('article.toggle');
    Route::delete('/articles/destroy/{article}', [ArticleController::class, 'destroy'])->name('article.destroy');

    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/config-clear', [SettingController::class, 'clearCache'])->name('clear.config');
    Route::get('/refresh-asset', [SettingController::class, 'refreshPrice'])->name('refresh.asset');

    Route::get('/positions', [PositionController::class, 'index'])->name('positions');
    Route::get('/positions/history', [PositionController::class, 'fetch'])->name('positions.history');
    Route::post('/positions/user/create', [PositionController::class, 'store'])->name('position.create');
    Route::post('/positions/user/close', [PositionController::class, 'close'])->name('position.close');
    Route::put('/positions/user/update/{id}', [PositionController::class, 'update'])->name('position.update');
    Route::put('/trades/update/{trade}', [TradeController::class, 'updateHistory'])->name('trade.date.update');
    Route::delete('/trades/delete/{trade}', [TradeController::class, 'destroyHistory'])->name('trade.date.delete');

    Route::get('/auto-invest/plans', [AutoinvestController::class, 'index'])->name('auto.plans');
    Route::post('/auto-invest/plans', [AutoinvestController::class, 'store'])->name('auto.plans.store');
    Route::put('/auto-invest/{autoPlan}', [AutoinvestController::class, 'update'])->name('auto.plans.update');
    Route::delete('/auto-invest/{autoPlan}', [AutoinvestController::class, 'destroy'])->name('auto.plans.delete');
    Route::get('/auto-invest/investments', [AutoinvestController::class, 'investment'])->name('auto.investments');
    Route::get('/users/{user}/auto-plans', [AutoinvestController::class, 'getUserPlans'])->name('auto.investment.users');
    Route::delete('/auto-invest/close/{autoPlanInvestmentId}', [AutoinvestController::class, 'closeAutoInvestment'])->name('auto.investment.close');
    Route::post('/auto-invest/invest', [AutoinvestController::class, 'startInvestment'])->name('auto.plans.invest');
    Route::put('/auto-invest/investment/{investment}', [AutoinvestController::class, 'updateInvestment'])->name('auto-investments.update');

});