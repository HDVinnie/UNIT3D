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

use VStelmakh\UrlHighlight\Highlighter\HtmlHighlighter;
use VStelmakh\UrlHighlight\UrlHighlight;
use VStelmakh\UrlHighlight\Validator\Validator;

class Linkify
{
    /**
     * @return string
     *
     * @var string
     */
    public function linky($text): string
    {
        $validator = new Validator(
            false, // bool - if should use top level domain to match urls without scheme
            [],    // string[] - array of blacklisted schemes
            [],    // string[] - array of whitelisted schemes
            true   // bool - if should match emails (if match by TLD set to "false" - will match only "mailto" urls)
        );

        $highlighter = new HtmlHighlighter(
            'http', // string - scheme to use for urls matched by top level domain
            ['rel' => 'noopener noreferrer'], // string[] - key/value map of tag attributes
            '',     // string - content to add before highlight: {here}<a...
            ''      // string - content to add after highlight: ...</a>{here}
        );

        $urlHighlight = new UrlHighlight($validator, $highlighter);

        return $urlHighlight->highlightUrls($text);
    }
}
