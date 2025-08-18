<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool|\Illuminate\Auth\Access\Response
     */
    public function authorize(): bool|\Illuminate\Auth\Access\Response
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */

    public function rules(): array
    {
        $rules = [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['nullable', Rule::in(['wallet', 'save', 'trade'])],
            'type' => ['required', Rule::in(['credit', 'debit', 'transfer'])],
            'comment' => ['nullable', 'string', 'max:255'],
            'to' => ['sometimes', Rule::in(['wallet', 'cash', 'brokerage', 'auto', 'ira'])],
            'from' => ['sometimes', Rule::in(['wallet', 'cash', 'brokerage', 'auto', 'ira'])],
            'payment_method_id' => ['nullable', 'uuid', 'exists:payment_methods,id']
        ];
    
        // Add proof validation for bank deposits
        if ($this->type === 'credit' && 
            (str_contains(strtolower($this->comment), 'bank') || 
             str_contains(strtolower($this->comment), 'deposit'))) {
            $rules['proof'] = ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'];
        }
    
        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'method' => $this->method ?? 'wallet',
            'transactable_type' => match ($this->method ?? 'wallet') {
                'wallet' => \App\Models\Wallet::class,
                'save' => \App\Models\Savings::class,
                'trade' => \App\Models\Trade::class,
            },
        ]);
    }
}
