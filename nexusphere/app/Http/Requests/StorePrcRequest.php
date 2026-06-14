<?php declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\NgWord;
use Illuminate\Foundation\Http\FormRequest;

class StorePrcRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sentence' => ['required', 'string', 'max:1000', new NgWord],
            'images.*' => ['image', 'max:5120'],
            'videos.*' => ['mimetypes:video/mp4,video/quicktime', 'max:51200'],
        ];
    }

    public function messages(): array
    {
        return [
            'sentence.required' => '投稿内容は必須です',
            'sentence.max' => '投稿内容は1000文字以内です',
            'images.*.image' => '画像ファイルを選択してください',
            'images.*.max' => '画像は5MB以下です',
            'videos.*.mimetypes' => '動画はMP4形式です',
            'videos.*.max' => '動画は50MB以下です',
        ];
    }
}
