<?php

namespace App\Http\Requests;

use App\Rules\NgWord;
use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
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
            'group_name' => ['required','string','max:255',new NgWord],
            'user_ids'   => 'required|array',
            'user_ids.*' => 'integer|exists:users,user_id',
            'icon'       => ['nullable','image','max:2048'],
        ];
    }
}
