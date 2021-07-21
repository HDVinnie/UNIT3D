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

class BBCodeConverter
{
    /**
     * BBCodeConverter Constructor.
     *
     * @param $text
     */
    public function __construct(public $text)
    {
    }

    /**
     * @brief Replaces BBCode size.
     */
    protected function replaceSize(): void
    {
        $this->text = \preg_replace_callback('#\[size=([\W\w\s]*?)]([\W\w\s]*?)\[/size]#iu',

            fn ($matches) => '<span style="font-size: '.\trim($matches[1], '').';">'.\trim($matches[1], '').'</span>',

            $this->text
        );
    }

    /**
     * @brief Replaces BBCode center.
     */
    protected function replaceCenter(): void
    {
        $this->text = \preg_replace_callback('#\[center]([\W\w\s]*?)\[/center]#iu',

            fn ($matches) => '<span class="text-center">'.\trim($matches[1], ' ').'</span>',

            $this->text
        );
    }

    /**
     * @brief Replaces BBCode bold.
     */
    protected function replaceBold(): void
    {
        $this->text = \preg_replace_callback('#\[b]([\W\w\s]*?)\[/b]#iu',

            fn ($matches) => '**'.\trim($matches[1], ' ').'**',

            $this->text
        );
    }

    /**
     * @brief Replaces BBCode italic.
     */
    protected function replaceItalic(): void
    {
        $this->text = \preg_replace_callback('#\[i]([\W\w\s]*?)\[/i]#iu',

            fn ($matches) => '*'.\trim($matches[1], ' ').'*',

            $this->text
        );
    }

    /**
     * @brief Replaces BBCode underline. Hoedown support underline.
     */
    protected function replaceUnderline(): void
    {
        $this->text = \preg_replace_callback('#\[u]([\W\w\s]*?)\[/u]#iu',

            fn ($matches) => '_'.\trim($matches[1], ' ').'_',

            $this->text
        );
    }

    /**
     * @brief Replaces BBCode strikethrough.
     */
    protected function replaceStrikethrough(): void
    {
        $this->text = \preg_replace_callback('#\[s]([\W\w\s]*?)\[/s]#iu',

            fn ($matches) => '~~'.\trim($matches[1], ' ').'~~',

            $this->text
        );
    }

    /**
     * @brief Replaces BBCode lists.
     */
    protected function replaceLists(): void
    {
        $this->text = \preg_replace_callback('#\[list(?P<type>=1)?](?P<items>[\W\w\s]*?)\[/list]#iu',

            function ($matches) {
                $buffer = '';

                $list = \preg_replace('#\s*$|^\s*#mu', '', $matches['items']);
                if (\is_null($list)) {
                    throw new \RuntimeException('Text has malformed BBCode lists');
                }
                $items = \preg_split('#\[\*]#u', $list);

                $counter = \is_countable($items) ? \count($items) : 0;

                if (isset($matches['type']) && $matches['type'] === '=1') { // ordered list
                    // We start from 1 to discard the first string, in fact, it's empty.
                    for ($i = 1; $i < $counter; $i++) {
                        if (! empty($items[$i])) {
                            $buffer .= ($i).'. '.\trim($items[$i]).PHP_EOL;
                        }
                    }
                } else { // unordered list
                    // We start from 1 to discard the first string, in fact, it's empty.
                    for ($i = 1; $i < $counter; $i++) {
                        if (! empty($items[$i])) {
                            $buffer .= '- '.\trim($items[$i]).PHP_EOL;
                        }
                    }
                }

                // We need a like break above the list and another one below.
                if (! empty($buffer)) {
                    $buffer = PHP_EOL.$buffer.PHP_EOL;
                }

                return $buffer;
            },

            $this->text
        );
    }

    /**
     * @brief Replaces BBCode tables.
     */
    protected function replaceTables(): void
    {
        $replaceRow = function ($matches) {
            $columns = $matches['columns'];
            $columns = \trim($columns);

            $cells = \preg_replace_callback('#\[td?](?P<cells>[\W\w\s]*?)\[/td]#iu', fn ($matches) => $matches['cells'].' | ', $columns);

            if ($cells !== '') {
                $cells = '| '.$cells;
            }

            return \trim($cells);
        };

        $this->text = \preg_replace_callback('#\[table?](?P<rows>[\W\w\s]*?)\[/table]#iu', function ($tableMatches) use ($replaceRow) {
            \preg_match_all('#\[th?](?P<columns>[\W\w\s]*?)\[/th]#iu', $tableMatches['rows'], $headerMatches, PREG_SET_ORDER);
            $headers = [];
            if (\count($headerMatches) !== 0) {
                $headers = \array_map($replaceRow, $headerMatches);
            }

            \preg_match_all('#\[tr?](?P<columns>[\W\w\s]*?)\[/tr]#iu', $tableMatches['rows'], $contentMatches, PREG_SET_ORDER);
            $rows = [];
            if (\count($contentMatches) !== 0) {
                $rows = \array_map($replaceRow, $contentMatches);
            }

            $headerSeparator = '';
            if (\count($rows) > 0) {
                $columnCount = \substr_count($rows[0], '|');

                if (\count($headers) === 0) {
                    $headers[] = \implode(' ', \array_fill(0, $columnCount, '|'));
                }

                $headerSeparator = \implode(' --- ', \array_fill(0, $columnCount, '|'));
            } else {
                return $tableMatches['rows'];
            }

            $headers[] = $headerSeparator;

            return \implode("\n", \array_merge($headers, $rows))."\n";
        }, $this->text);
    }

    /**
     * @brief Replaces BBCode urls.
     */
    protected function replaceUrls(): void
    {
        $this->text = \preg_replace_callback('#\[url\s*=\s*("[^"]*"|\A[^\']*\Z|[^\'">\]\s]+)\s*[^]\s]*]([\W\w\s]*?)\[/url]#iu',

            function ($matches) {
                if (isset($matches[1], $matches[2])) {
                    return '['.$matches[2].']('.$matches[1].')';
                }

                throw new \RuntimeException('Text has malformed BBCode urls');
            },

            $this->text
        );
    }

    /**
     * @brief Replaces BBCode images.
     */
    protected function replaceImage(): void
    {
        $this->text = \preg_replace_callback('#\[img]([\W\w\s]*?)\[/img]#iu',

            fn ($matches) => PHP_EOL.'![]'.'('.$matches[1].')'.PHP_EOL,

            $this->text
        );
    }

    /**
     * @brief Replaces BBCode images.
     */
    protected function replaceImages(): void
    {
        $this->text = \preg_replace_callback('#\[img\s*=\s*("[^"]*"|\A[^\']*\Z|[^\'">\]\s]+)\s*[^]\s]*\[/img]#iu',

                fn ($matches) => PHP_EOL.'!['.$matches[2].']'.'('.$matches[1].')'.PHP_EOL,

            $this->text
        );
    }

    /**
     * @brief Replaces BBCode quotes.
     */
    protected function replaceQuotes(): void
    {
        // Removes the inner quotes, leaving just one level.
        $this->text = \preg_replace('#\G(?<!^)(?>(\[quote\b[^]]*](?>[^[]++|\[(?!/?quote)|(?1))*\[/quote])|(?<!\[)(?>[^[]++|\[(?!/?quote))+\K)|\[quote\b[^]]*]\K#', '', $this->text);

        // Replaces all the remaining quotes with '> ' characters.
        $this->text = \preg_replace_callback('#\[quote\b[^]]*]((?>[^[]++|\[(?!/?quote))*)\[/quote]#i',

            function ($matches) {
                $quote = \preg_replace('#^\s*#mu', '', \trim($matches[1]));

                return '> '.$quote.PHP_EOL.PHP_EOL;
            },

            $this->text
        );
    }

    /**
     * @brief Replaces BBCode snippets.
     */
    protected function replaceSnippets(): void
    {
        $this->text = \preg_replace_callback('#\[code\s*=?(?P<language>\w*)](?P<snippet>[\W\w\s]*?)\[/code]#iu',

            function ($matches) {
                if (isset($matches['snippet'])) {
                    $language = \strtolower($matches['language']);

                    if ($language === 'html4strict' || $language === 'div') {
                        $language = 'html';
                    } elseif ($language === 'shell' || $language === 'dos' || $language === 'batch') {
                        $language = 'sh';
                    } elseif ($language === 'xul' || $language === 'wpf') {
                        $language = 'xml';
                    } elseif ($language === 'asm') {
                        $language = 'nasm';
                    } elseif ($language === 'vb' || $language === 'visualbasic' || $language === 'vba') {
                        $language = 'vb.net';
                    } elseif ($language === 'asp') {
                        $language = 'aspx-vb';
                    } elseif ($language === 'xaml') {
                        $language = 'xml';
                    } elseif ($language === 'cplusplus') {
                        $language = 'cpp';
                    } elseif ($language === 'txt' || $language === 'gettext') {
                        $language = 'text';
                    } elseif ($language === 'basic') {
                        $language = 'cbmbas';
                    } elseif ($language === 'lisp') {
                        $language = 'clojure';
                    } elseif ($language === 'aspnet') {
                        $language = 'aspx-vb';
                    }

                    return PHP_EOL.'```'.$language.PHP_EOL.\trim($matches['snippet']).PHP_EOL.'```'.PHP_EOL;
                }

                throw new \RuntimeException('Text has malformed BBCode snippet.');
            },

            $this->text
        );
    }

    /**
     * @brief Replace BBCode spoiler.
     */
    protected function replaceSpoilers(): void
    {
        $this->text = \preg_replace_callback('#\[spoiler](.*?)\[/spoiler]#ius',

            fn ($matches) => '<p><details class="label label-primary"><summary>Spoiler</summary><pre><code>'.\trim($matches[1]).'</code></pre></details></p>',

            $this->text
        );
    }

    /**
     * @brief Replace BBCode named spoiler.
     */
    protected function replaceNamedSpoilers(): void
    {
        $this->text = \preg_replace_callback('#\[spoiler=(.*?)](.*?)\[/spoiler]#ius',

            fn ($matches) => '<p><details class="label label-primary"><summary>'.\trim($matches[1]).'</summary><pre><code>'.\trim($matches[2]).'</code></pre></details></p>',

            $this->text
        );
    }

    /**
     * @brief Replace BBCode color.
     */
    protected function replaceColor(): void
    {
        $this->text = \preg_replace_callback('#\[color=([\W\w\s]*?)]([\W\w\s]*?)\[/color]#iu',

            fn ($matches) => '<span style="color: '.\trim($matches[1], '').';">'.\trim($matches[2], '').'</span>',

            $this->text
        );
    }

    /**
     * @brief Replace BBCode Video.
     */
    protected function replaceVideo(): void
    {
        $this->text = \preg_replace_callback('#\[video=[^]]*.([\W\w\s][^\[]*)\[/video]#iu',

            fn ($matches) => '<iframe src="https://www.youtube-nocookie.com/embed/'.\trim($matches[1], '').'?rel=0" width="640" height="480" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>',

            $this->text
        );
    }

    /**
     * @brief Replace BBCode Youtube.
     */
    protected function replaceYoutube(): void
    {
        $this->text = \preg_replace_callback('#\[youtube]([\W\w\s]*?)\[/youtube]#iu',

            fn ($matches) => '<iframe src="https://www.youtube-nocookie.com/embed/'.\trim($matches[1], '').'?rel=0" width="640" height="480" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>',

            $this->text
        );
    }

    /**
     * @brief Replace BBCode Alert.
     */
    protected function replaceAlert(): void
    {
        $this->text = \preg_replace_callback('#\[alert]([\W\w\s]*?)\[/alert]#iu',

            fn ($matches) => '<div class="bbcode-alert">'.\trim($matches[1], '').'</div>',

            $this->text
        );
    }

    /**
     * @brief Replace BBCode Note.
     */
    protected function replaceNote(): void
    {
        $this->text = \preg_replace_callback('#\[note]([\W\w\s]*?)\[/note]#iu',

            fn ($matches) => '<div class="bbcode-note">'.\trim($matches[1], '').'</div>',

            $this->text
        );
    }

    /**
     * @brief Converts the provided BBCode text to an equivalent Markdown text.
     */
    public function toMarkdown()
    {
        $this->replaceCenter();
        $this->replaceSize();
        $this->replaceBold();
        $this->replaceItalic();
        $this->replaceUnderline();
        $this->replaceStrikethrough();
        $this->replaceLists();
        $this->replaceTables();
        $this->replaceUrls();
        $this->replaceImage();
        $this->replaceImages();
        $this->replaceQuotes();
        $this->replaceSnippets();
        $this->replaceSpoilers();
        $this->replaceNamedSpoilers();
        $this->replaceColor();
        $this->replaceVideo();
        $this->replaceYoutube();
        $this->replaceAlert();
        $this->replaceNote();

        return $this->text;
    }
}
