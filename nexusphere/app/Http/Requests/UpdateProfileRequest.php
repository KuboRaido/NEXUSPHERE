<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name'       => 'required|string|max:255',
            'subject'    => 'nullable|string|max:255',
            'job'        => 'required|string|max:2',
            'grade'      => 'nullable|string|max:2',
            'major'      => 'nullable|string|max:255',
            'icon'       => 'nullable|image|max:2048',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name'       => '名前',
            'subject'    => '学部',
            'job'        => '区分',
            'grade'      => '学年',
            'major'      => '学科',
            'icon'       => 'アイコン',
        ];
    }
}
