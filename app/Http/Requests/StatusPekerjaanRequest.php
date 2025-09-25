<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatusPekerjaanRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'loker_id' => 'required|exists:lokers,id',
            'tanggal_mulai' => 'required|date',
        ];
    }
}
