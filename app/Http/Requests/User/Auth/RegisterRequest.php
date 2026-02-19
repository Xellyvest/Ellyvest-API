<?php

namespace App\Http\Requests\User\Auth;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    private string|null $countryCode;

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
        return [
            // Required Fields
            'first_name' => 'required|string|max:191',
            'last_name'  => 'required|string|max:191',
            'username'   => ['required', 'string', 'max:20', Rule::unique('users', 'username')],
            'email'      => [
                'required',
                Rule::when(app()->environment('production'), 'email:rfc,dns', 'email'),
                Rule::unique('users', 'email'),
            ],
            'password'   => ['required', 'confirmed'],

            // Optional Fields (Changed to nullable/sometimes)
            'country_id'  => 'nullable|uuid|exists:countries,id',
            'state_id'    => 'nullable|uuid|exists:states,id',
            'currency_id' => 'nullable|uuid|exists:currencies,id',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
            'city'        => 'nullable|string|max:255',
            'zipcode'     => 'nullable|string|max:20',
            'ssn'         => 'nullable|string|max:20',
            'dob'         => 'nullable|date',
            'nationality' => 'nullable|string|max:191',
            'experience'  => 'nullable|string|max:191',
            'employed'    => 'nullable|string|max:191',
            'id_number'   => 'nullable|string|max:191',
            'front_id'    => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
            'back_id'     => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ];
    }
}
