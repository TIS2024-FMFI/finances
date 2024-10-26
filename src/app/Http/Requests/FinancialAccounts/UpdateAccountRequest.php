<?php

namespace App\Http\Requests\FinancialAccounts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A request to update an existing financial account.
 *
 * Fields: title, sap_id.
 */
class UpdateAccountRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $account = $this->route('account');

        return [
            'title' => ['required', 'max:255'],
            'sap_id' => [
                'required',
                'max:255',
                'regex:/^[A-Z0-9]+([\-\/][A-Z0-9]+)*$/',
                //Rule::unique('accounts')
                //    ->where('user_id', $this->user()->id)
                //    ->ignore($account)
            ]
        ];
    }
}
