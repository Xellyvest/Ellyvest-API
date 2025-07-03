<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{
    use UUID;

    protected $fillable = [
        'user_id',
        'min_cash_bank_deposit',
        'min_cash_crypto_deposit',
        'max_cash_bank_deposit',
        'max_cash_crypto_deposit',
        'min_cash_bank_withdrawal',
        'min_cash_crypto_withdrawal',
        'max_cash_bank_withdrawal',
        'max_cash_crypto_withdrawal',
        'locked_cash',
        'locked_cash_message',
        'locked_bank_deposit',
        'locked_bank_deposit_message',
        'connected_wallet_at',
        'connected_wallet',
        'is_connect_activated',
        'drip',
        'trade',

        'beneficiary_first_name',
        'beneficiary_last_name',
        'beneficiary_nationality',
        'beneficiary_dob',
        'beneficiary_email',
        'beneficiary_phone',
        'beneficiary_address',
        'beneficiary_country',
        'beneficiary_state',
        'beneficiary_city',
        'beneficiary_zipcode',
    ];
    
    protected $casts = [
        'locked_cash' => 'boolean',
        'locked_bank_deposit' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
