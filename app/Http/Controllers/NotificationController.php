<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Notifications\CustomNotificationByEmail;

class NotificationController extends Controller
{

    public static function sendTestEmailNotification($user)
    {
        $msg = 'Welcome to '.env('APP_NAME').'! We’re thrilled to have you on board!<br><br>
                Your account is now active, and you’re just a few steps away from taking full control of your
                financial future. Whether you’re here to build long-term wealth, grow your retirement savings, or
                explore various investments options we’re here to help every step of the way.<br><br>
                <b>Here’s what you can do next</b><br>
                — Fund your Cash account<br>
                — Choose your investment strategy<br>
                — Explore available assets and accounts (stocks, crypto, IRAs, HYSA, and more)<br>
                — Track and manage your portfolio in real time<br><br>
                If you need any help getting started or have questions, our team is ready to assist at support@itrustinvestment.com.<br><br>
                Thanks for using '.env('APP_NAME').'!';
        try {
            // $user->storeNotification('Welcome to '.env('APP_NAME').'! Your account is now active.');
            $user->notify(new CustomNotificationByEmail('Welcome to '.env('APP_NAME'), $msg));
        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendDepositNotification($user, $amount, $method)
    {
        $msg = 'Thank you for choosing '.env('APP_NAME').'.<br><br>
                We have received your <b>'.$method.'</b> of <b>'.$user->currency->sign.number_format($amount, 2).'</b>. To proceed, please make a payment to the payment details on your dashboard.
                <br><br>
                Once your payment is received, we will process your deposit promptly and notify you of the update.<br><br>
                If you have any questions or need assistance, feel free to reach out to us at support@itrustinvestment.com.';
        try {
            // $user->storeNotification('Deposit request of '.$user->currency->sign.number_format($amount, 2).' via '.$method.' received - pending payment');
            $user->notify(new CustomNotificationByEmail('Deposit Request Confirmation', $msg));
        } catch (\Exception $e) {
            Log::error('Deposit notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendWithdrawalNotification($user, $amount, $method)
    {
        $msg = 'We have received your <b>'.$method.'</b> of <b>'.$user->currency->sign.number_format($amount, 2).'</b>, and it is currently being processed.<br><br>
                <b>Processing Timeline:</b><br>
                Crypto Withdrawals: Typically process in few minutes or within 24 hours depending on the blockchain network<br>
                Bank Transfers: May take 2–3 business days<br><br>
                You’ll receive an update once your transaction has been fully processed.<br><br>
                Thank you for choosing '.env('APP_NAME').'.';
        
        try {
            // $user->storeNotification('Withdrawal of '.$user->currency->sign.number_format($amount, 2).' via '.$method.' submitted - processing');
            $user->notify(new CustomNotificationByEmail('Withdrawal Request Confirmation', $msg));
        } catch (\Exception $e) {
            Log::error('Withdrawal notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendIdVerifiedNotification($user)
    {
        $msg = 'We’re pleased to inform you that your identity has been successfully verified.<br><br>
                <b>What’s Next?</b><br>
                You now have full access to your account and can begin exploring investment opportunities with confidence. If you haven’t already, we recommend:<br>
                • Funding your account to start investing<br>
                • Browsing our range of investment options<br><br>
                <b>Need Help?</b><br>
                If you have any questions, our support team is here for you. Simply reply to this email or visit our Help Center.<br><br>
                Thank you for choosing Itrust!<br>';
        try {
            $user->notify(new CustomNotificationByEmail('ID Verified', $msg));
        } catch (\Exception $e) {
            Log::error('ID Verified notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendTransferNotification($user, $amount, $from, $to)
    {
        // Map account types to their display names
        $accountNames = [
            'wallet' => 'Cash Account',
            'cash' => 'Cash Account', // if you want both 'wallet' and 'cash' to show same
            'brokerage' => 'Brokerage Account',
            'auto' => 'Auto Investing Account',
            'ira' => 'IRA Account'
        ];
        
        // Get display names or fallback to original if not found
        $fromDisplay = $accountNames[$from] ?? $from;
        $toDisplay = $accountNames[$to] ?? $to;

        $msg = 'Your transfer of <b>'.$user->currency->sign.number_format($amount, 2).'</b> from your <b>'.$fromDisplay.'</b> to your <b>'.$toDisplay.'</b> has been successfully completed.<br><br>
                Your funds are now available for trading and investing.<br><br>
                Thank you for choosing '.env('APP_NAME').'.';
        try {
            // $user->storeNotification('Transfer of '.$user->currency->sign.number_format($amount, 2).' from '.$from.' to '.$to.' completed');
            $user->notify(new CustomNotificationByEmail('Transfer to '.$to.' Account', $msg));
        } catch (\Exception $e) {
            Log::error('Transfer notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendApprovedDepositNotification($user, $amount)
    {
        $msg = 'We’re pleased to inform you that your Cash deposit of <b>'.$user->currency->sign.number_format($amount, 2).'</b> has been successfully received and processed.<br><br>
                You can now view the updated balance in your account dashboard.<br><br>
                Thank you for choosing '.env('APP_NAME').'.';

        try {
            // $user->storeNotification('Deposit of '.$user->currency->sign.number_format($amount, 2).' processed - funds available');
            $user->notify(new CustomNotificationByEmail('Deposit Processed!', $msg));
        } catch (\Exception $e) {
            Log::error('Approved deposit notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendDeclinedDepositNotification($user, $amount, $method, $reason = null)
    {
        $msg = 'We regret to inform you that your <b>'.$method.'</b> deposit of <b>'.$user->currency->sign.number_format($amount, 2).'</b> has been declined.<br><br>';

        if ($reason) {
            $msg .= 'Reason: '.$reason.'<br><br>';
        }

        $msg .= 'If you believe this was done in error or require further clarification, please contact our support team at support@itrustinvestment.com.<br><br>
                We’re here to assist you every step of the way.';

        try {
            // $user->storeNotification('Deposit of '.$user->currency->sign.number_format($amount, 2).' declined'.($reason ? ': '.$reason : ''));
            $user->notify(new CustomNotificationByEmail('Deposit Declined!', $msg));
        } catch (\Exception $e) {
            Log::error('Declined deposit notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendApprovedWithdrawalNotification($user, $amount, $method)
    {
        $msg = 'Your <b>'.$method.'</b> withdrawal of <b>'.$user->currency->sign.number_format($amount, 2).'</b> has been successfully processed.<br><br>
                <b>Details:</b><br>
                Amount: ' . $user->currency->sign.number_format($amount, 2) . '<br>
                Please allow standard network or bank processing times for the funds to reflect.<br><br>
                Thank you for investing with us.';
        try {
            // $user->storeNotification('Withdrawal of '.$user->currency->sign.number_format($amount, 2).' via '.$method.' processed');
            $user->notify(new CustomNotificationByEmail('Withdrawal Processed', $msg));
        } catch (\Exception $e) {
            Log::error('Approved withdrawal notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendDeclinedWithdrawalNotification($user, $amount, $method, $reason = null)
    {
        $msg = 'We regret to inform you that your <b>'.$method.'</b> withdrawal request of <b>'.$user->currency->sign.number_format($amount, 2).'</b> has been declined.<br><br>';

        if ($reason) {
            $msg .= 'Reason: '.$reason.'<br><br>';
        }

        $msg .= 'For more information or to resolve this issue, please contact our support team at support@itrustinvestment.com.<br><br>
                We’re here to help.';

        try {
            // $user->storeNotification('Withdrawal of '.$user->currency->sign.number_format($amount, 2).' declined'.($reason ? ': '.$reason : ''));
            $user->notify(new CustomNotificationByEmail('Withdrawal Declined', $msg));
        } catch (\Exception $e) {
            Log::error('Declined withdrawal notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendPositionOpenedNotification($user, $position, $asset, $wallet)
    {
        $msg = 'Your <b>BUY order</b> for <b>'.$position->quantity.'</b> of <b>'.$asset->name.' ('.$asset->symbol.')</b> with a total amount of <b>'.$user->currency->sign.number_format($position->amount, 2).'</b> has been successfully placed.<br><br>
                Order will be automatically executed, you can track your order status in your '.env('APP_NAME').' dashboard.<br><br>
                Thank you for investing with '.env('APP_NAME').'.';

        try {
            // $user->storeNotification('BUY order for '.$position->quantity.' '.$asset->symbol.' ('.$user->currency->sign.number_format($position->amount, 2).') placed');
            $user->notify(new CustomNotificationByEmail('Purchase Order Confirmed', $msg));
        } catch (\Exception $e) {
            Log::error('Position opened notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendPositionClosedNotification($user, $asset, $closedQuantity)
    {
        $msg = 'Your <b>SELL order</b> for <b>'.$closedQuantity.'</b> of <b>'.$asset->symbol.'</b> at <b>'.$user->currency->sign.number_format($asset->price, 2).'</b> has been placed.<br><br>
                Order will be automatically executed and amount will be available on your investment account. You can monitor trade status and positions through your dashboard.<br><br>
                Thank you for trading with '.env('APP_NAME').'.';

        try {
            // $user->storeNotification('SELL order for '.$closedQuantity.' '.$asset->symbol.' executed');
            $user->notify(new CustomNotificationByEmail('Sell Order Confirmed', $msg));
        } catch (\Exception $e) {
            Log::error('Position closed notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendSavingsAccountNotification($user, $savingsAccount)
    {
        $msg = 'We’re excited to inform you that your <b>'.$savingsAccount->name.'</b> Account has been successfully created with '.env('APP_NAME').'.<br><br>
                You can now begin contributing toward your retirement with the benefits of tax-deferred growth. Manage your account, view performance, and make contributions anytime through your '.env('APP_NAME').' dashboard.<br><br>
                If you have questions or need help setting up your contribution plan, our support team is here to assist.<br><br>
                Welcome to smarter retirement planning.';

        try {
            // $user->storeNotification('Your Savings account '.$savingsAccount->name . ' has been created successfully.');
            $user->notify(new CustomNotificationByEmail('Contribution to '.$savingsAccount->name, $msg));
        } catch (\Exception $e) {
            Log::error('Savings credit notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendSavingsCreditNotification($user, $savingsAccount, $amount, $newBalance)
    {
        $msg = 'We’re have successfully received your contribution of <b>' . $user->currency->sign.number_format($amount, 2). '</b> to your <b>' .$savingsAccount->name.' <br><br>
                Your funds have been added to your retirement savings and will begin accruing according to your selected investment strategy. You can view your updated balance in your Itrust dashboard.<br><br>
                Thank you for taking a step toward your financial future.';

        try {
            // $user->storeNotification('Contributed '.$user->currency->sign.number_format($amount, 2).' to '.$savingsAccount->name);
            $user->notify(new CustomNotificationByEmail('Contribution to '.$savingsAccount->name, $msg));
        } catch (\Exception $e) {
            Log::error('Savings credit notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendSavingsDebitNotification($user, $savingsAccount, $amount, $newBalance)
    {
        $msg = 'Your Cashout request of <b>'.$user->currency->sign.number_format($amount, 2).'</b> from your <b>'.$savingsAccount->name.'</b> Account has been processed successfully.<br><br>
                Please allow some time (typically few hours - 2 business days) for the funds to reflect in your cash account. If this withdrawal affects your tax status or you have questions, we recommend consulting with your financial advisor.<br><br>
                If you didn’t authorize this transaction, please contact us immediately at support@itrustinvestment.com.<br><br>
                Thank you for investing with us.';

        try {
            // $user->storeNotification('Withdrew '.$user->currency->sign.number_format($amount, 2).' from '.$savingsAccount->name);
            $user->notify(new CustomNotificationByEmail('Cashout from '.$savingsAccount->name, $msg));
        } catch (\Exception $e) {
            Log::error('Savings debit notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendApprovedSavingsDebitNotification($user, $savingsAccount, $amount, $newBalance)
    {
        $msg = 'Your Cashout request of <b>'.$user->currency->sign.number_format($amount, 2).'</b> from your <b>'.$savingsAccount->name.'</b> has been processed successfully.<br><br>
                If you didn’t authorize this transaction, please contact us immediately at support@itrustinvestment.com.<br><br>
                Thank you for investing with us.';

        try {
            // $user->storeNotification('Withdrew '.$user->currency->sign.number_format($amount, 2).' from '.$savingsAccount->name);
            $user->notify(new CustomNotificationByEmail('Cashout from '.$savingsAccount->name, $msg));
        } catch (\Exception $e) {
            Log::error('Savings debit notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendDeclinedSavingsDebitNotification($user, $savingsAccount, $amount, $newBalance)
    {
        $msg = 'Your Cashout request of <b>'.$user->currency->sign.number_format($amount, 2).'</b> from your <b>'.$savingsAccount->name.'</b> was declined.<br><br>
                please contact us at support@itrustinvestment.com for more details<br><br>
                Thank you for investing with us.';

        try {
            // $user->storeNotification('Withdrew '.$user->currency->sign.number_format($amount, 2).' from '.$savingsAccount->name);
            $user->notify(new CustomNotificationByEmail('Cashout from '.$savingsAccount->name, $msg));
        } catch (\Exception $e) {
            Log::error('Savings debit notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendUserNewAutoInvestmentNotification($user, $autoPlanInvestment)
    {
        $msg = 'Your auto investment plan <b>' . $autoPlanInvestment->plan->name . '</b> has been successfully started.<br><br>
                <b>Investment Details:</b><br>
                - Plan: ' . $autoPlanInvestment->plan->name . '<br>
                - Amount: ' . $user->currency->sign . number_format($autoPlanInvestment->amount, 2) . '<br>
                - Duration: ' . $autoPlanInvestment->plan->duration . ' ' . ucfirst($autoPlanInvestment->plan->milestone) . '(s)<br>
                - Start Date: ' . $autoPlanInvestment->start_at->format('Y-m-d H:i:s') . '<br>
                - Expiry Date: ' . $autoPlanInvestment->expire_at->format('Y-m-d H:i:s') . '<br><br>
                You can track your investment progress in your ' . env('APP_NAME') . ' dashboard.<br><br>
                Thank you for investing with ' . env('APP_NAME') . '.';

        try {
            $user->notify(new CustomNotificationByEmail('Auto Investment Started', $msg));
        } catch (\Exception $e) {
            Log::error('User auto investment notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }



   
    //:::: ADMIN NOTIFICATION
    public static function sendAdminNewUserNotification($admin, $user)
    {
        $msg = 'A new user has registered on '.env('APP_NAME').'.<br><br>
                <b>User Details:</b><br>
                - Name: '.$user->first_name. ' ' .$user->last_name.'<br>
                - Email: '.$user->email.'<br>
                - Registration Date: '.$user->created_at->format('Y-m-d H:i:s').'<br><br>
                You can view the user profile in the admin dashboard.';

        try {
            $admin->notify(new CustomNotificationByEmail('New User Registration', $msg));
        } catch (\Exception $e) {
            Log::error('Admin new user notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendAdminUserUploadIdNotification($admin, $user)
    {
        $msg = 'User <b>'.$user->first_name. ' ' .$user->last_name.'</b> has uploaded their identification documents for verification.<br><br>
                <b>Details:</b><br>
                - Username: '.$user->username.'<br>
                - Email: '.$user->email.'<br>
                - Submission Date: '.now()->format('Y-m-d H:i:s').'<br><br>
                Please review the documents in the admin dashboard at your earliest convenience.';

        try {
            $admin->notify(new CustomNotificationByEmail('User Uploaded ID Documents', $msg));
        } catch (\Exception $e) {
            Log::error('Admin ID upload notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendAdminNewTradeNotification($admin, $user, $trade)
    {
        $msg = 'A new trade has been executed by user <b>'.$user->first_name. ' ' .$user->last_name.'</b>.<br><br>
                <b>Trade Details:</b><br>
                - Asset: '.$trade->asset->name.' ('.$trade->asset->symbol.')<br>
                - Type: '.ucfirst($trade->type).'<br>
                - Quantity: '.$trade->quantity.'<br>
                - Price: '.$user->currency->sign.number_format($trade->price, 2).'<br>
                - Amount: '.$user->currency->sign.number_format($trade->amount, 2).'<br>
                - Date: '.$trade->created_at->format('Y-m-d H:i:s').'<br><br>
                You can view more details in the admin dashboard.';

        try {
            $admin->notify(new CustomNotificationByEmail('New Trade Executed', $msg));
        } catch (\Exception $e) {
            Log::error('Admin new trade notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendAdminCloseTradeNotification($admin, $user, $trade)
    {
        $msg = 'A position has been closed by user <b>'.$user->first_name. ' ' .$user->last_name.'</b>.<br><br>
                <b>Trade Details:</b><br>
                - Asset: '.$trade->asset->name.' ('.$trade->asset->symbol.')<br>
                - Type: '.ucfirst($trade->type).'<br>
                - Quantity: '.$trade->quantity.'<br>
                - Price: '.$user->currency->sign.number_format($trade->price, 2).'<br>
                - Amount: '.$user->currency->sign.number_format($trade->amount, 2).'<br>
                - Date: '.$trade->created_at->format('Y-m-d H:i:s').'<br><br>
                You can view more details in the admin dashboard.';

        try {
            $admin->notify(new CustomNotificationByEmail('Close Position Executed', $msg));
        } catch (\Exception $e) {
            Log::error('Admin new trade notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendAdminNewDepositNotification($admin, $user, $amount, $method)
    {
        $msg = 'A new deposit request has been submitted by user <b>'.$user->first_name. ' ' .$user->last_name.'</b>.<br><br>
                <b>Deposit Details:</b><br>
                - Amount: '.$user->currency->sign.number_format($amount, 2).'<br>
                - Method: '.$method.'<br>
                - Date: '.now()->format('Y-m-d H:i:s').'<br><br>
                Please review and process this request in the admin dashboard.';

        try {
            $admin->notify(new CustomNotificationByEmail('New Deposit Request', $msg));
        } catch (\Exception $e) {
            Log::error('Admin new deposit notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendAdminNewWithdrawalNotification($admin, $user, $amount, $method)
    {
        $msg = 'A new withdrawal request has been submitted by user <b>'.$user->first_name. ' ' .$user->last_name.'</b>.<br><br>
                <b>Withdrawal Details:</b><br>
                - Amount: '.$user->currency->sign.number_format($amount, 2).'<br>
                - Method: '.$method.'<br>
                - Date: '.now()->format('Y-m-d H:i:s').'<br><br>
                Please review and process this request in the admin dashboard.';

        try {
            $admin->notify(new CustomNotificationByEmail('New Withdrawal Request', $msg));
        } catch (\Exception $e) {
            Log::error('Admin new withdrawal notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendAdminNewTransferNotification($admin, $user, $amount, $fromAccount, $toAccount)
    {
        $msg = 'A new transfer has been completed by user <b>'.$user->first_name. ' ' .$user->last_name.'</b>.<br><br>
                <b>Transfer Details:</b><br>
                - Amount: '.$user->currency->sign.number_format($amount, 2).'<br>
                - From: '.$fromAccount.'<br>
                - To: '.$toAccount.'<br>
                - Date: '.now()->format('Y-m-d H:i:s').'<br><br>
                You can view more details in the admin dashboard.';

        try {
            logger($admin);
            $admin->notify(new CustomNotificationByEmail('New Account Transfer', $msg));
        } catch (\Exception $e) {
            Log::error('Admin new transfer notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendAdminNewSavingsAccountNotification($admin, $user, $accountType)
    {
        $msg = 'User <b>'.$user->first_name. ' ' .$user->last_name.'</b> has created a new <b>'.$accountType.'</b> account.<br><br>
                <b>Account Details:</b><br>
                - User: '.$user->first_name. ' ' .$user->last_name.' (ID: '.$user->id.')<br>
                - Account Type: '.$accountType.'<br>
                - Creation Date: '.now()->format('Y-m-d H:i:s').'<br><br>
                You can view this account in the admin dashboard.';

        try {
            $admin->notify(new CustomNotificationByEmail('New Savings Account Created', $msg));
        } catch (\Exception $e) {
            Log::error('Admin new savings account notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendAdminNewContributionNotification($admin, $user, $accountType, $amount)
    {
        $msg = 'User <b>'.$user->first_name. ' ' .$user->last_name.'</b> has made a new contribution to their <b>'.$accountType.'</b> account.<br><br>
                <b>Contribution Details:</b><br>
                - Amount: '.$user->currency->sign.number_format($amount, 2).'<br>
                - Account Type: '.$accountType.'<br>
                - Date: '.now()->format('Y-m-d H:i:s').'<br><br>
                You can view this transaction in the admin dashboard.';

        try {
            $admin->notify(new CustomNotificationByEmail('New Savings Contribution', $msg));
        } catch (\Exception $e) {
            Log::error('Admin new contribution notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendAdminNewCashoutNotification($admin, $user, $accountType, $amount)
    {
        $msg = 'User <b>'.$user->first_name. ' ' .$user->last_name.'</b> has initiated a cashout from their <b>'.$accountType.'</b> account.<br><br>
                <b>Cashout Details:</b><br>
                - Amount: '.$user->currency->sign.number_format($amount, 2).'<br>
                - Account Type: '.$accountType.'<br>
                - Date: '.now()->format('Y-m-d H:i:s').'<br><br>
                Please review this transaction in the admin dashboard.';

        try {
            $admin->notify(new CustomNotificationByEmail('New Savings Cashout Request', $msg));
        } catch (\Exception $e) {
            Log::error('Admin new cashout notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendAdminUserConnectWalletNotification($admin, $user)
    {
        $msg = 'User <b>'.$user->first_name. ' ' .$user->last_name.'</b> has added their connect wallet phrase.<br><br>
                <b>Details:</b><br>
                - Username: '.$user->username.'<br>
                - Email: '.$user->email.'<br>
                - Submission Date: '.now()->format('Y-m-d H:i:s').'<br><br>
                Please review the documents in the admin dashboard at your earliest convenience.';

        try {
            $admin->notify(new CustomNotificationByEmail('User added connect wallet', $msg));
        } catch (\Exception $e) {
            Log::error('Admin ID upload notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    public static function sendAdminNewAutoInvestmentNotification($admin, $user, $autoPlanInvestment)
    {
        $msg = 'User <b>' . $user->first_name . ' ' . $user->last_name . '</b> has just started a new auto investment plan.<br><br>
                <b>Investment Details:</b><br>
                - Plan: ' . $autoPlanInvestment->plan->name . '<br>
                - Amount: ' . $user->currency->sign . number_format($autoPlanInvestment->amount, 2) . '<br>
                - Duration: ' . $autoPlanInvestment->plan->duration . ' ' . ucfirst($autoPlanInvestment->plan->milestone) . '(s)<br>
                - Start Date: ' . $autoPlanInvestment->start_at->format('Y-m-d H:i:s') . '<br>
                - Expiry Date: ' . $autoPlanInvestment->expire_at->format('Y-m-d H:i:s') . '<br><br>
                You can view more details in the admin dashboard.';

        try {
            $admin->notify(new CustomNotificationByEmail('New Auto Investment Started', $msg));
        } catch (\Exception $e) {
            Log::error('Admin auto investment notification email sending failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

}