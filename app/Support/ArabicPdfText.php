<?php

namespace App\Support;

/**
 * Shape Arabic text for Dompdf (no HarfBuzz). Uses presentation forms when possible.
 */
class ArabicPdfText
{
    /** @var array<string, string[]> letter => [isolated, final, initial, medial] */
    private static array $forms = [
        'ا' => ['ا', 'ﺎ', 'ا', 'ﺎ'], 'أ' => ['أ', 'ﺄ', 'أ', 'ﺄ'], 'إ' => ['إ', 'ﺈ', 'إ', 'ﺈ'],
        'آ' => ['آ', 'ﺂ', 'آ', 'ﺂ'], 'ب' => ['ب', 'ﺐ', 'ﺑ', 'ﺒ'], 'ت' => ['ت', 'ﺖ', 'ﺗ', 'ﺘ'],
        'ث' => ['ث', 'ﺚ', 'ﺛ', 'ﺜ'], 'ج' => ['ج', 'ﺞ', 'ﺟ', 'ﺠ'], 'ح' => ['ح', 'ﺢ', 'ﺣ', 'ﺤ'],
        'خ' => ['خ', 'ﺦ', 'ﺧ', 'ﺨ'], 'د' => ['د', 'ﺪ', 'د', 'ﺪ'], 'ذ' => ['ذ', 'ﺬ', 'ذ', 'ﺬ'],
        'ر' => ['ر', 'ﺮ', 'ر', 'ﺮ'], 'ز' => ['ز', 'ﺰ', 'ز', 'ﺰ'], 'س' => ['س', 'ﺲ', 'ﺳ', 'ﺴ'],
        'ش' => ['ش', 'ﺶ', 'ﺷ', 'ﺸ'], 'ص' => ['ص', 'ﺺ', 'ﺻ', 'ﺼ'], 'ض' => ['ض', 'ﺾ', 'ﺿ', 'ﻀ'],
        'ط' => ['ط', 'ﻂ', 'ﻃ', 'ﻄ'], 'ظ' => ['ظ', 'ﻆ', 'ﻇ', 'ﻈ'], 'ع' => ['ع', 'ﻊ', 'ﻋ', 'ﻌ'],
        'غ' => ['غ', 'ﻎ', 'ﻏ', 'ﻐ'], 'ف' => ['ف', 'ﻒ', 'ﻓ', 'ﻔ'], 'ق' => ['ق', 'ﻖ', 'ﻗ', 'ﻘ'],
        'ك' => ['ك', 'ﻚ', 'ﻛ', 'ﻜ'], 'ل' => ['ل', 'ﻞ', 'ﻟ', 'ﻠ'], 'م' => ['م', 'ﻢ', 'ﻣ', 'ﻤ'],
        'ن' => ['ن', 'ﻦ', 'ﻧ', 'ﻨ'], 'ه' => ['ه', 'ﻪ', 'ﻫ', 'ﻬ'], 'و' => ['و', 'ﻮ', 'و', 'ﻮ'],
        'ي' => ['ي', 'ﻲ', 'ﻳ', 'ﻴ'], 'ى' => ['ى', 'ﻰ', 'ى', 'ﻰ'], 'ة' => ['ة', 'ﺔ', 'ة', 'ﺔ'],
        'ئ' => ['ئ', 'ﺊ', 'ﺋ', 'ﺌ'], 'ؤ' => ['ؤ', 'ﺆ', 'ؤ', 'ﺆ'], 'ء' => ['ء', 'ء', 'ء', 'ء'],
        'لا' => ['لا', 'ﻼ', 'لا', 'ﻼ'],
    ];

    private static array $nonConnectors = [
        'ا', 'أ', 'إ', 'آ', 'د', 'ذ', 'ر', 'ز', 'و', 'ؤ', 'ء', 'ة', 'ى',
    ];

    public static function shape(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        if (class_exists(\ArPHP\I18N\Arabic::class)) {
            try {
                return (new \ArPHP\I18N\Arabic())->utf8Glyphs($text, 80, false);
            } catch (\Throwable $e) {
                // continue with local shaper
            }
        }

        if (!preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return $text;
        }

        // Process line by line
        $lines = explode("\n", $text);
        $shaped = array_map([self::class, 'shapeLine'], $lines);

        return implode("\n", $shaped);
    }

    private static function shapeLine(string $line): string
    {
        // Split into Arabic vs non-Arabic segments
        $segments = preg_split(
            '/([\x{0600}-\x{06FF}\x{0750}-\x{077F}\s]+)/u',
            $line,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        if ($segments === false) {
            return $line;
        }

        $out = '';
        foreach ($segments as $seg) {
            if ($seg === '') {
                continue;
            }
            if (preg_match('/[\x{0600}-\x{06FF}]/u', $seg)) {
                $out .= self::shapeArabicSegment($seg);
            } else {
                $out .= $seg;
            }
        }

        return $out;
    }

    private static function shapeArabicSegment(string $text): string
    {
        // Normalize lam-alef combinations first for later joining
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $n = count($chars);
        $result = [];

        for ($i = 0; $i < $n; $i++) {
            $ch = $chars[$i];

            // Lam + Alef → ligature
            if ($ch === 'ل' && isset($chars[$i + 1]) && in_array($chars[$i + 1], ['ا', 'أ', 'إ', 'آ'], true)) {
                $prevConnects = self::prevConnects($chars, $i);
                $form = $prevConnects ? self::$forms['لا'][1] : self::$forms['لا'][0];
                $result[] = $form;
                $i++;
                continue;
            }

            if (!isset(self::$forms[$ch])) {
                $result[] = $ch;
                continue;
            }

            $prevConnects = self::prevConnects($chars, $i);
            $nextConnects = self::nextConnects($chars, $i);

            if ($prevConnects && $nextConnects) {
                $form = self::$forms[$ch][3]; // medial
            } elseif ($prevConnects) {
                $form = self::$forms[$ch][1]; // final
            } elseif ($nextConnects) {
                $form = self::$forms[$ch][2]; // initial
            } else {
                $form = self::$forms[$ch][0]; // isolated
            }

            $result[] = $form;
        }

        // Dompdf draws LTR; reverse the shaped Arabic segment
        return implode('', array_reverse($result));
    }

    private static function prevConnects(array $chars, int $i): bool
    {
        for ($j = $i - 1; $j >= 0; $j--) {
            $p = $chars[$j];
            if (preg_match('/\s/u', $p)) {
                return false;
            }
            if (!isset(self::$forms[$p]) && $p !== 'ل') {
                return false;
            }
            return !in_array($p, self::$nonConnectors, true);
        }
        return false;
    }

    private static function nextConnects(array $chars, int $i): bool
    {
        $n = count($chars);
        for ($j = $i + 1; $j < $n; $j++) {
            $nx = $chars[$j];
            if (preg_match('/\s/u', $nx)) {
                return false;
            }
            if (!isset(self::$forms[$nx])) {
                return false;
            }
            // Current letter must be able to connect forward
            $cur = $chars[$i];
            return !in_array($cur, self::$nonConnectors, true);
        }
        return false;
    }
}
