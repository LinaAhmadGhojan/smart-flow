<?php

namespace App\Support;

class ProductDescription
{
    /** Strip "أبرز المميزات" / "Key Features" sections from product copy for quotes & invoices. */
    public static function withoutFeaturesSection(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        foreach (['أبرز المميزات', 'Key Features'] as $marker) {
            $pos = mb_stripos($text, $marker);
            if ($pos === false) {
                continue;
            }

            $text = rtrim(mb_substr($text, 0, $pos));
            $text = preg_replace('/[.\s:]+$/u', '', $text) ?? $text;

            return trim($text);
        }

        return $text;
    }
}
