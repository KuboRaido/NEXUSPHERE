<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class SendDmRequest extends FormRequest
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
     * 送信先は circle_id → group_id → to の優先順で決まる。
     * dm.js は1対1でも circle_id=0 を送ってくるため、0 は「指定なし」として扱う
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'text'    => ['nullable','string','max:5000'],
            'files.*' => ['nullable','file','max:51200','mimetypes:image/*,video/*'],
        ];

        if ($this->integer('circle_id')) {
            return $rules + [
                'circle_id' => ['required', 'integer', 'exists:circles,circle_id'],
            ];
        }

        if ($this->integer('group_id')) {
            return $rules + [
                'group_id' => ['required', 'integer', 'exists:groups,group_id'],
            ];
        }

        $userPk = (new User)->getKeyName();

        return $rules + [
            'to' => ['required', 'integer', "exists:users,{$userPk}"],
        ];
    }
}
