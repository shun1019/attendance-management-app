<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeName = $this->route()->getName();

        $rules = [
            'zipcode' => ['required', 'regex:/^\d{3}-\d{4}$/'],
            'address' => ['required', 'string'],
            'building' => ['required', 'string',],
        ];

        if ($routeName === 'profile.update') {
            $rules['username'] = ['required', 'string'];
        } elseif ($routeName === 'purchase.address.update') {
            unset($rules['username']);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'username.required' => 'お名前を入力してください。',
            'zipcode.required' => '郵便番号を入力してください。',
            'zipcode.regex' => '郵便番号は「XXX-XXXX」の形式で入力してください。',
            'address.required' => '住所を入力してください。',
            'building.required' => '建物名を入力してください。',
        ];
    }
}
