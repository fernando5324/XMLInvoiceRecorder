<?php

namespace App\Http\Requests\Vouchers;

use Illuminate\Foundation\Http\FormRequest;

class DeleteVouchersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'string']
        ];
    }
}
