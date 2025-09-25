<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatusKerjaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'loker_id' => 'required|exists:lokers,id'
        ];
    }
}
