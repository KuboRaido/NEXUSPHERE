<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NgWord implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        // 入力値の正規化
        // 'RNASKVC':
        // R: 全角英字 -> 半角
        // N: 全角数字 -> 半角
        // A: 半角英数字 -> 半角 (RとNに含まれるが念のため)
        // S: 全角スペース -> 半角スペース
        // K: 半角カタカナ -> 全角カタカナ
        // V: 濁点付きの文字を1文字に合成
        // C: 全角ひらがな -> 全角カタカナ (「ばか」と「バカ」を同一視するため)
        $normalized_value = mb_convert_kana($value, 'RNASKVC');

        // ホワイトリスト処理
        $whitelists = config('ng_words.whitelist', []);
        
        // チェック対象は正規化済みの文字列
        $clean_value = $normalized_value; 

        foreach ($whitelists as $white_word) {
            if ($white_word === '') continue;
            
            // ホワイトリストも入力値と同じ基準で正規化してマッチさせる
            $normalized_white_word = mb_convert_kana($white_word, 'RNASKVC');
            
            // 大文字小文字を区別せず置換 (str_ireplace)
            $clean_value = str_ireplace($normalized_white_word, '@@@', $clean_value);
        }

        // 部分一致チェック 
        $partial_words = config('ng_words.partial_match', []);

        foreach($partial_words as $word){
            if($word === '') continue;
            
            // NGワードも入力値と同じ基準で正規化
            $normalized_word = mb_convert_kana($word, 'RNASKVC');

            // 正規化済みの $clean_value に対してチェック
            if(mb_stripos($clean_value, $normalized_word) !== false){
                $fail('入力内容に不適切な表現が含まれています。');
                return;
            }
        }

        // 完全一致・単語境界チェック 
        $exact_words = config('ng_words.exact_match', []);
        
        if (!empty($exact_words)) {
            $quoted_words = [];
            foreach ($exact_words as $w) {
                if ($w === '') continue;
                
                // NGワードを正規化（例: 全角ＳＭ → 半角SM）
                $norm_w = mb_convert_kana($w, 'RNASKVC');
                
                // 正規表現用にエスケープして配列に追加
                $quoted_words[] = preg_quote($norm_w, '/');
            }

            if (!empty($quoted_words)) {
                // u修飾子: UTF-8として扱う
                // i修飾子: 大文字小文字を区別しない
                // \b: 単語の境界（日本語間では効かないが英単語には有効）
                $pattern = '/\b(' . implode('|', $quoted_words) . ')\b/iu';
                
                // 正規化 & 無害化済みの $clean_value でチェック
                if (preg_match($pattern, $clean_value)) {
                    $fail('入力内容に不適切な表現が含まれています。');
                    return;
                }
            }
        }
    }
}