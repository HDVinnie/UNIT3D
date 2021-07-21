<?php

declare(strict_types=1);
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Helpers;

/**
 * Class LanguageCensor.
 *
 * A class that can redact/replace words.
 */
class LanguageCensor
{
    /**
     * @var string
     */
    private const SPECIAL_CHARS = "<>\n [].;,";

    protected static function isSpecial($c): bool
    {
        return \str_contains(self::SPECIAL_CHARS, $c);
    }

    protected static function matchWordIndexes($string, $word): array
    {
        $result = [];
        $length = \strlen($word);
        $stringLength = \strlen($string);
        $pos = \stripos($string, $word, 0);
        while ($pos !== false) {
            $prev = ($pos === 0) ? ' ' : $string[$pos - 1];
            $last = ($pos + $length) < $stringLength ? $string[$pos + $length] : ' ';
            if (self::isSpecial($prev) && self::isSpecial($last)) {
                $result[] = $pos;
            }
            $pos = \stripos($string, $word, $pos + $length);
        }

        return $result;
    }

    /**
     * Censor a text.
     *
     * @param $source
     *
     * @return mixed
     */
    public static function censor($source): mixed
    {
        foreach (\config('censor.redact', []) as $word) {
            $result = '';
            $length = \strlen($source);
            $wordLength = \strlen($word);
            \assert($wordLength > 0);
            $indexes = self::matchWordIndexes($source, $word);
            $ignore = 0;
            for ($i = 0; $i < $length; $i++) {
                if ((\is_countable($indexes) ? \count($indexes) : 0) > 0 && $indexes[0] == $i) {
                    $match = \substr($source, $indexes[0], $wordLength);
                    $result .= \sprintf("<span class='censor'>%s</span>", $match);
                    $ignore = $wordLength - 1;
                } elseif ($ignore > 0) {
                    $ignore--;
                } else {
                    $result .= $source[$i];
                }
            }
            $source = $result;
        }

        $replaceDict = \config('censor.replace', []);
        foreach ($replaceDict as $word => $replacement) {
            $source = \str_ireplace($word, $replacement, $source);
        }

        return $source;
    }
}
