<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'kasir'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customer = $this->route('customer');
        $id = is_object($customer) ? $customer->id : $customer;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('customers', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:150'], 'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:2000'], 'email' => ['nullable', 'email', 'max:150'],
            'registered_at' => ['required', 'date'],
        ];
    }
}
