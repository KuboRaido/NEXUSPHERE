<?php declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NgWord implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        $normalizedValue = mb_convert_kana($value, 'RNASKVC');

        $whitelists = config('ng_words.whitelist', []);
        $cleanValue = $normalizedValue;

        foreach ($whitelists as $whiteWord) {
            if ($whiteWord === '') {
                continue;
            }

            $normalizedWhiteWord = mb_convert_kana($whiteWord, 'RNASKVC');
            $cleanValue = str_ireplace($normalizedWhiteWord, '@@@', $cleanValue);
        }

        $partialWords = config('ng_words.partial_match', []);

        foreach ($partialWords as $word) {
            if ($word === '') {
                continue;
            }

            $normalizedWord = mb_convert_kana($word, 'RNASKVC');

            if (mb_stripos($cleanValue, $normalizedWord) !== false) {
                $fail('入力内容に不適切な表現が含まれています。');
                return;
            }
        }

        $exactWords = config('ng_words.exact_match', []);

        if (!empty($exactWords)) {
            $quotedWords = [];

            foreach ($exactWords as $w) {
                if ($w === '') {
                    continue;
                }

                $normW = mb_convert_kana($w, 'RNASKVC');
                $quotedWords[] = preg_quote($normW, '/');
            }

            if (!empty($quotedWords)) {
                $pattern = '/\b(' . implode('|', $quotedWords) . ')\b/iu';

                if (preg_match($pattern, $cleanValue)) {
                    $fail('入力内容に不適切な表現が含まれています。');
                    return;
                }
            }
        }
    }
}