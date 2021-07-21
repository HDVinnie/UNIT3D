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

class StringHelper
{
    public const KIB = 1_024;

    public const MIB = 1_024 * 1_024;

    public const GIB = 1_024 * 1_024 * 1_024;

    public const TIB = 1_024 * 1_024 * 1_024 * 1_024;

    public const PIB = 1_024 * 1_024 * 1_024 * 1_024 * 1_024;

    /**
     * @var string
     */
    private const CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';

    /**
     * @var string[]
     */
    private const ENDS = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];

    public static function generateRandomString($length = 20): string
    {
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= self::CHARACTERS[\rand(0, \strlen(self::CHARACTERS) - 1)];
        }

        return $string;
    }

    public static function formatBytes($bytes = 0, $precision = 2): string
    {
        $minus = false;
        if ($bytes < 0) {
            $minus = true;
            $bytes *= -1;
        }
        $suffix = 'B';
        $value = $bytes;
        if ($bytes >= self::PIB) {
            $suffix = 'PiB';
            $value = $bytes / self::PIB;
        } elseif ($bytes >= self::TIB) {
            $suffix = 'TiB';
            $value = $bytes / self::TIB;
        } elseif ($bytes >= self::GIB) {
            $suffix = 'GiB';
            $value = $bytes / self::GIB;
        } elseif ($bytes >= self::MIB) {
            $suffix = 'MiB';
            $value = $bytes / self::MIB;
        } elseif ($bytes >= self::KIB) {
            $suffix = 'KiB';
            $value = $bytes / self::KIB;
        }

        $result = \round($value, $precision);
        if ($minus) {
            $result *= -1;
        }

        return $result.' '.$suffix;
    }

    /**
     * @method timeRemaining
     *
     * @param time $seconds in bigInt
     *
     * @return string
     */
    public static function timeRemaining(time $seconds): string
    {
        $minutes = 0;
        $hours = 0;
        $days = 0;
        $weeks = 0;
        $months = 0;
        $years = 0;

        $seconds = \config('hitrun.seedtime') - $seconds;

        if ($seconds == 0) {
            return 'N/A';
        }
        while ($seconds >= 31_536_000) {
            $years++;
            $seconds -= 31_536_000;
        }
        while ($seconds >= 2_592_000) {
            $months++;
            $seconds -= 2_592_000;
        }
        while ($seconds >= 604_800) {
            $weeks++;
            $seconds -= 604_800;
        }
        while ($seconds >= 86_400) {
            $days++;
            $seconds -= 86_400;
        }
        while ($seconds >= 3_600) {
            $hours++;
            $seconds -= 3_600;
        }
        while ($seconds >= 60) {
            $minutes++;
            $seconds -= 60;
        }
        $years = ($years === 0) ? '' : $years.\trans('common.abbrev-years');
        $months = ($months === 0) ? '' : $months.\trans('common.abbrev-months');
        $weeks = ($weeks === 0) ? '' : $weeks.\trans('common.abbrev-weeks');
        $days = ($days === 0) ? '' : $days.\trans('common.abbrev-days');
        $hours = ($hours === 0) ? '' : $hours.\trans('common.abbrev-hours');
        $minutes = ($minutes === 0) ? '' : $minutes.\trans('common.abbrev-minutes');
        $seconds = ($seconds == 0) ? '' : $seconds.\trans('common.abbrev-seconds');

        return $years.$months.$weeks.$days.$hours.$minutes.$seconds;
    }

    /**
     * @method timeElapsed
     *
     * @param time $seconds in bigInt
     *
     * @return string
     */
    public static function timeElapsed(time $seconds): string
    {
        $minutes = 0;
        $hours = 0;
        $days = 0;
        $weeks = 0;
        $months = 0;
        $years = 0;

        if ($seconds == 0) {
            return 'N/A';
        }
        while ($seconds >= 31_536_000) {
            $years++;
            $seconds -= 31_536_000;
        }
        while ($seconds >= 2_592_000) {
            $months++;
            $seconds -= 2_592_000;
        }
        while ($seconds >= 604_800) {
            $weeks++;
            $seconds -= 604_800;
        }
        while ($seconds >= 86_400) {
            $days++;
            $seconds -= 86_400;
        }
        while ($seconds >= 3_600) {
            $hours++;
            $seconds -= 3_600;
        }
        while ($seconds >= 60) {
            $minutes++;
            $seconds -= 60;
        }
        $years = ($years === 0) ? '' : $years.\trans('common.abbrev-years');
        $months = ($months === 0) ? '' : $months.\trans('common.abbrev-months');
        $weeks = ($weeks === 0) ? '' : $weeks.\trans('common.abbrev-weeks');
        $days = ($days === 0) ? '' : $days.\trans('common.abbrev-days');
        $hours = ($hours === 0) ? '' : $hours.\trans('common.abbrev-hours');
        $minutes = ($minutes === 0) ? '' : $minutes.\trans('common.abbrev-minutes');
        $seconds = ($seconds == 0) ? '' : $seconds.\trans('common.abbrev-seconds');

        return $years.$months.$weeks.$days.$hours.$minutes.$seconds;
    }

    public static function ordinal($number): string
    {
        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return $number.'th';
        }

        return $number.self::ENDS[$number % 10];
    }
}
