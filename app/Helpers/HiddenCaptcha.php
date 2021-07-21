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

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class HiddenCaptcha
{
    /**
     * Set the hidden captcha tags to put in your form.
     *
     * @param string $mustBeEmptyField
     *
     * @return string
     */
    public static function render(string $mustBeEmptyField = '_username'): string
    {
        $ts = \time();
        $random = Str::random(16);

        // Generate the token
        $token = [
            'timestamp'         => $ts,
            'session_id'        => \session()->getId(),
            'ip'                => \request()->ip(),
            'user_agent'        => \request()->header('User-Agent'),
            'random_field_name' => $random,
            'must_be_empty'     => $mustBeEmptyField,
        ];

        // Encrypt the token
        $token = Crypt::encrypt(\serialize($token));

        return (string) \view('partials.captcha', ['mustBeEmptyField' => $mustBeEmptyField, 'ts' => $ts, 'random' => $random, 'token' => $token]);
    }

    /**
     * Check the hidden captcha values.
     */
    public static function check(Validator $validator, int $minLimit = 0, int $maxLimit = 1_200): bool
    {
        $formData = $validator->getData();

        // Check post values
        if (! isset($formData['_captcha']) || ! ($token = self::getToken($formData['_captcha']))) {
            return false;
        }

        // Hidden "must be empty" field check
        if (! \array_key_exists($token['must_be_empty'], $formData) || ! empty($formData[$token['must_be_empty']])) {
            return false;
        }

        // Check time limits
        $now = \time();
        if ($now - $token['timestamp'] < $minLimit || $now - $token['timestamp'] > $maxLimit) {
            return false;
        }

        // Check the random posted field
        if (empty($formData[$token['random_field_name']])) {
            return false;
        }

        // Check if the random field value is similar to the token value
        $randomField = $formData[$token['random_field_name']];

        return \ctype_digit($randomField) && $token['timestamp'] == $randomField;
    }

    /**
     * Get and check the token values.
     */
    private static function getToken(string $captcha): string | bool | array
    {
        // Get the token values
        try {
            $token = Crypt::decrypt($captcha);
        } catch (\Exception) {
            return false;
        }

        $token = @\unserialize($token);

        // Token is null or unserializable
        if (! $token || ! \is_array($token) || empty($token)) {
            return false;
        }

        // Check token values
        if (empty($token['session_id']) ||
            empty($token['ip']) ||
            empty($token['user_agent']) ||
            $token['session_id'] !== \session()->getId() ||
            $token['ip'] !== \request()->ip() ||
            $token['user_agent'] !== \request()->header('User-Agent')
        ) {
            return false;
        }

        return $token;
    }
}
