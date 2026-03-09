<?php

namespace App\Support;

class TextHelper
{
    public static function linkify(string $text): string
    {
        $escaped = e($text);

        return preg_replace_callback('~(https?://[^\s<]+)~iu', function ($m) {
            $url = $m[1];
            if (!filter_var($url, FILTER_VALIDATE_URL)) return $url;

            $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
            if (!in_array($scheme, ['http', 'https'], true)) return $url;

            $label = mb_strimwidth($url, 0, 60, '…', 'UTF-8');

            return '<a href="' . e($url) . '" target="_blank" rel="noopener noreferrer nofollow ugc">'
                . e($label) . '</a>';
        }, $escaped) ?? $escaped;
    }
}