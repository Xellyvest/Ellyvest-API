<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'country_id' => 'sometimes|uuid|exists:countries,id',
            'state_id' => 'sometimes|uuid|exists:states,id',
            'city_id' => 'sometimes|uuid|exists:cities,id',
            'city' => 'sometimes|string|max:255',
            'currency_id' => 'sometimes|uuid|exists:currencies,id',
            'first_name' => 'sometimes|string|max:191',
            'last_name' => 'sometimes|string|max:191',
            'username' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('users', 'username'),
            ],
            'phone' => 'sometimes|string|max:15',
            'address' => 'sometimes|string|max:255',
            'zipcode' => 'sometimes|string|max:20',
            'ssn' => 'sometimes|string|max:20',
            'dob' => 'nullable|date',
            'nationality' => 'sometimes|string|max:191',
        ];
    }
}
