<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NgWord;

class StoreCircleRequest extends FormRequest
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
            'name'        => ['required','string','max:255','unique:circles,circle_name',new NgWord],
            'sentence'    => ['required','string','max:255',new NgWord],
            'image'       => ['required','image','max:5120'],
            'category'    => ['nullable','string', new NgWord],
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
            'image.required' => '画像を設定してください',
            'name.unique'    => 'そのサークル名はすでに使用されております',
        ];
    }
}
