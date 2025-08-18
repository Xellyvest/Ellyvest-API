<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Wallet;
use App\Enums\ApiErrorCode;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use App\Services\User\TransactionService;
use App\Services\User\UserSettingsService;
use Illuminate\Notifications\Notification;
use Symfony\Component\HttpFoundation\Response;
use App\Spatie\QueryBuilder\IncludeSelectFields;
use App\Http\Requests\User\StoreTransactionRequest;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use App\DataTransferObjects\Models\TransactionModelData;
use App\Http\Controllers\NotificationController as Notifications;
use App\Http\Requests\User\ToggleTransactionsRequest;

class TransactionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param \App\Models\Transaction $transaction
     */
    public function __construct(public Transaction $transaction)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $transactions = QueryBuilder::for(
            $this->transaction->query()->where('user_id', $request->user()->id)
        )
            ->allowedFields($this->transaction->getQuerySelectables()) // Get the selectable fields dynamically
            ->allowedFilters([
                'status',
                'type', // Filter by transaction type (credit, debit, transfer)
                AllowedFilter::scope('creation_date'), // Custom filter scope for creation date
                AllowedFilter::exact('amount'), // Exact match filter for amount
                AllowedFilter::exact('transactable_id'), // Exact match filter for transactable ID
                AllowedFilter::exact('transactable_type'), // Filter by the type of transactable model (e.g., Wallet, Savings, Trade)
                AllowedFilter::scope('comment'), // Scope for filtering by comment (if applicable)
            ])
            ->allowedIncludes([
                AllowedInclude::custom('user', new IncludeSelectFields([
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                ])),
                AllowedInclude::custom('transactable', new IncludeSelectFields([
                    'id',
                    'type', // You can select more details depending on the model (e.g., Wallet, Trade, Savings)
                    'amount', // Amount associated with transactable
                    'status', // You can display status of the related model
                ])),
            ])
            ->defaultSort('-created_at') // Default sort by created_at descending
            ->allowedSorts([
                'status', // Sort by transaction status
                'type', // Sort by transaction type (credit, debit, transfer)
                'amount', // Sort by transaction amount
                'created_at', // Sort by creation date
                'updated_at', // Sort by updated date
            ])
            ->paginate((int) $request->per_page) // Paginate the results
            ->withQueryString(); // Retain query string for pagination links

        return ResponseBuilder::asSuccess()
            ->withMessage('Transactions fetched successfully')
            ->withData([
                'transactions' => $transactions,
            ])
            ->build();
    }

    /**
     * Create a new transaction.
     *
     * @param \App\Http\Requests\User\StoreTransactionRequest $request
     * @param \App\Services\TransactionService $transactionService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(
        StoreTransactionRequest $request,
        TransactionService $transactionService
    ): Response {
        $user = $request->user();
        $wallet = $user->wallet;
        $balance = $wallet->getBalance('wallet');
        $amount = (float) $request->amount;
        $type = $request->type;
        $comment = $request->comment;
        $proof = $request->file('proof');

        // Get the payment method if provided
        $paymentMethod = null;
        if ($request->has('payment_method_id')) {
            $paymentMethod = PaymentMethod::where('user_id', $user->id)
                ->find($request->payment_method_id);
                
            if (!$paymentMethod) {
                return ResponseBuilder::asError(404)
                    ->withMessage('Payment method not found.')
                    ->build();
            }
        }
    
        $settings = app(UserSettingsService::class);
    
        // Validate balance for debit
        if ($type === 'debit' && $amount > $balance) {
            return ResponseBuilder::asError(ApiErrorCode::INSUFFICIENT_FUNDS->value)
                ->withMessage('Insufficient wallet balance.')
                ->build();
        }

        // Validate Locked Cash
        if($type === 'debit' && $user->settings->locked_cash === true)
        {
            return ResponseBuilder::asError(ApiErrorCode::INSUFFICIENT_FUNDS->value)
                    ->withMessage($user->settings->locked_cash_message)
                    ->build();
        }

        // Validate Locked Cash
        if($type === 'credit' && $user->settings->locked_bank_deposit == true && $request->comment == 'cash deposit via (bank deposit)')
        {
            return ResponseBuilder::asError(ApiErrorCode::INSUFFICIENT_FUNDS->value)
                    ->withMessage($user->settings->locked_bank_deposit_message)
                    ->build();
        }
    
        // Validate min/max deposit
        if ($type === 'credit') {
            $isBankDeposit = str_contains(strtolower($comment), 'bank');
            
            if ($isBankDeposit) {
                $min = $settings->getValue($user, 'min_cash_bank_deposit');
                $max = $settings->getValue($user, 'max_cash_bank_deposit');
            } else {
                // Default to crypto for any non-bank comment
                $min = $settings->getValue($user, 'min_cash_crypto_deposit');
                $max = $settings->getValue($user, 'max_cash_crypto_deposit');
            }

            if ($amount < $min) {
                return ResponseBuilder::asError(ApiErrorCode::INSUFFICIENT_FUNDS->value)
                    ->withMessage("Minimum deposit amount is $min.")
                    ->build();
            }

            if ($amount > $max) {
                return ResponseBuilder::asError(ApiErrorCode::INSUFFICIENT_FUNDS->value)
                    ->withMessage("Maximum deposit amount is $max.")
                    ->build();
            }
        }

        // Validate min/max withdrawal
        if ($type === 'debit') {
            $isBankWithdrawal = str_contains(strtolower($comment), 'bank') || 
                                ($paymentMethod && $paymentMethod->type === 'bank');
            
            if ($isBankWithdrawal) {
                $min = $settings->getValue($user, 'min_cash_bank_withdrawal');
                $max = $settings->getValue($user, 'max_cash_bank_withdrawal');
            } else {
                // Default to crypto for any non-bank comment or payment method
                $min = $settings->getValue($user, 'min_cash_crypto_withdrawal');
                $max = $settings->getValue($user, 'max_cash_crypto_withdrawal');
            }

            if ($amount < $min) {
                return ResponseBuilder::asError(ApiErrorCode::INSUFFICIENT_FUNDS->value)
                    ->withMessage("Minimum withdrawal amount is $min.")
                    ->build();
            }

            if ($amount > $max) {
                return ResponseBuilder::asError(ApiErrorCode::INSUFFICIENT_FUNDS->value)
                    ->withMessage("Maximum withdrawal amount is $max.")
                    ->build();
            }
        }

        $paymentMethodData = null;
        if ($paymentMethod) {
            $paymentMethodData = $paymentMethod->type === 'bank'
                ? [
                    'id' => $paymentMethod->id,
                    'type' => $paymentMethod->type,
                    'label' => $paymentMethod->label,
                    'currency' => $paymentMethod->currency,
                    'account_name' => $paymentMethod->account_name,
                    'account_number' => $paymentMethod->account_number,
                    'bank_name' => $paymentMethod->bank_name,
                    'routing_number' => $paymentMethod->routing_number,
                    'bank_reference' => $paymentMethod->bank_reference,
                    'bank_address' => $paymentMethod->bank_address,
                ]
                : [
                    'id' => $paymentMethod->id,
                    'type' => $paymentMethod->type,
                    'label' => $paymentMethod->label,
                    'currency' => $paymentMethod->currency,
                    'wallet_address' => $paymentMethod->wallet_address,
                ];
        }
    
        // Proceed with transaction
        $transaction = $transactionService->create(
            (new TransactionModelData())
                ->setUserId($user->id)
                ->setAmount($amount)
                ->setTransactableId($wallet->id)
                ->setTransactableType(Wallet::class)
                ->setType($type)
                ->setStatus('pending')
                ->setSwapFrom('wallet')
                ->setSwapTo(null)
                ->setComment($request->comment)
                ->setPaymentMethod($paymentMethodData)
                ->setProof($proof), 
            $user
        );
    
        $admin = Admin::where('email', config('app.admin_mail'))->first();
    
        // Notifications
        if ($type === 'credit') {
            Notifications::sendDepositNotification($user, $amount, $request->comment);
            Notifications::sendAdminNewDepositNotification($admin, $user, $amount, $request->comment);
        } elseif ($type === 'debit') {
            Notifications::sendWithdrawalNotification($user, $amount, $request->comment);
            Notifications::sendAdminNewWithdrawalNotification($admin, $user, $amount, $request->comment);
        }
    
        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Transaction created successfully')
            ->withData(['transaction' => $transaction])
            ->build();
    }
    

    /**
     * Create a new transaction.
     *
     * @param \App\Http\Requests\User\StoreTransactionRequest $request
     * @param \App\Services\TransactionService $transactionService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function transfer(
        StoreTransactionRequest $request,
        TransactionService $transactionService
    ): Response { 

        $user = $request->user();

        if($user->settings->locked_cash === true)
        {
            return ResponseBuilder::asError(ApiErrorCode::INSUFFICIENT_FUNDS->value)
                    ->withMessage($user->settings->locked_cash_message)
                    ->build();
        }

        $transaction = $transactionService->swap(
            (new TransactionModelData())
                ->setUserId($request->user()->id)
                ->setAmount((float) $request->amount)
                ->setTransactableId($request->user()->wallet->id)
                ->setTransactableType($request->transactable_type)
                ->setType($request->type)
                ->setStatus("approved")
                ->setSwapFrom($request->from)
                ->setSwapTo($request->to)
                ->setComment($request->comment),
            $request->user()
        );
        $admin = Admin::where('email', config('app.admin_mail'))->first();

        Notifications::sendTransferNotification($request->user(), (float) $request->amount, $request->from, $request->to);
        Notifications::sendAdminNewTransferNotification($admin, $request->user(), (float) $request->amount, $request->from, $request->to);

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Transafer created successfully')
            ->withData([
                'transaction' => $transaction,
            ])
            ->build();
    }


    /**
     * Cancel a pending transaction.
     *
     * @param \App\Http\Requests\User\StoreTransactionRequest $request
     * @param \App\Services\TransactionService $transactionService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cancel(
        ToggleTransactionsRequest $request,
        Transaction $transaction,
        TransactionService $transactionService
    ): Response {
        $user = $request->user();
    
        // Ensure the transaction belongs to the authenticated user
        if ($transaction->user_id !== $user->id) {
            return ResponseBuilder::asError(500)
                ->withHttpCode(Response::HTTP_FORBIDDEN)
                ->withMessage('Unauthorized access to this transaction')
                ->build();
        }
    
        // Ensure the transaction is still pending
        if ($transaction->status !== "pending") {
            return ResponseBuilder::asError(500)
                ->withHttpCode(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->withMessage("You can only cancel a pending transaction")
                ->build();
        }
    
        $transaction = $transactionService->cancel($transaction, $user);
    
        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_OK)
            ->withMessage('Transaction cancelled successfully')
            ->withData([
                'transaction' => $transaction,
            ])
            ->build();
    }
}
