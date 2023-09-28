<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */

    public function rules()
    {
        return [
            // main fields
            'name' => 'required|string|max:255',
            // 'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'required|string|max:255',
            // 'address' => 'required|string|max:255',
            
            'role' => 'required|string|in:customer,storeAdmin,deliveryBoy,admin',
            //extra fields depends on roles
            //? store admin
            'facebook' => 'required_if:role,storeAdmin|string|max:255',
            'twitter' => 'required_if:role,storeAdmin|string|max:255',
            'instagram' => 'required_if:role,storeAdmin|string|max:255',
            //? delivery boy
            'start_point_address' => 'required_if:role,delivery_boy|nullable|string|max:255',
            'region' => 'required_if:role,delivery_boy|nullable|string|max:255',
            //? admin
            'facebook' => 'required_if:role,admin|string|max:255',
            'twitter' => 'required_if:role,admin|string|max:255',
            'instagram' => 'required_if:role,admin|string|max:255',
            
        ];
    }
    public function messages()
    {
        return [
            'role.in' => 'The user type must be either "admin" or "customer" or "storeAdmin" or "delivery_boy" .',
        ];
    }
}
