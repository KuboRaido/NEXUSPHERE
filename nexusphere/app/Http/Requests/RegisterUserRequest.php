<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
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
            'mail'     => ['required', 'email', 'unique:users,mail', 'regex:/@(edu.sba|edu.ssm|sba|ssm)\.ac\.jp$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'name'     => ['required', 'string', 'max:255'],
            'job'      => ['required', 'string', 'max:2'],
            'grade'    => ['required_if:job,学生|date', 'integer', 'min:1', 'max:4'],
            'subject'  => ['required_if:job,学生|date', 'string', 'max:255'],
            'major'    => ['required_if:job,学生|date', 'string', 'max:255'],
            'icon'     => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'mail.required'      => 'メールアドレスは必ず入力してください',
            'mail.unique'        => 'そのメールアドレスは既に登録されています',
            'icon.max'           => '画像が大きすぎます.5MB以下にしてください',
            'password.confirmed' => 'パスワードが再入力したものと合っていません。',
        ];
    }
}
