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

class MediaInfo
{
    private const REGEX_SECTION = "/^(?:(?:general|video|audio|text|menu)(?:\s\#\d+?)*)$/i";

    /**
     * @var string[]
     */
    private const REPLACE = [
        ' '        => '',
        'channels' => 'ch',
        'channel'  => 'ch',
        '1ch'      => '1.0ch',
        '7ch'      => '6.1ch',
        '6ch'      => '5.1ch',
        '2ch'      => '2.0ch',
    ];

    /**
     * @var int[]
     */
    private const FACTORS = ['b' => 0, 'kb' => 1, 'mb' => 2, 'gb' => 3, 'tb' => 4, 'pb' => 5, 'eb' => 6, 'zb' => 7, 'yb' => 8];

    public function parse($string): array
    {
        $string = \trim($string);
        $lines = \preg_split("/\r\n|\n|\r/", $string);

        $output = [];
        foreach ($lines as $line) {
            $line = \trim($line); // removed strtolower, unnecessary with the i-switch in the regexp (caseless) and adds problems with values; added it in the required places instead.
            if (\preg_match(self::REGEX_SECTION, $line)) {
                $section = $line;
                $output[$section] = [];
            }
            if (isset($section)) {
                $output[$section][] = $line;
            }
        }

        if (\count($output) > 0) {
            $output = $this->parseSections($output);
        }

        return $this->formatOutput($output);
    }

    private function parseSections(array $sections): array
    {
        $output = [];
        foreach ($sections as $key => $section) {
            $keySection = \strtolower(\explode(' ', $key)[0]);
            if (! empty($section)) {
                if ($keySection === 'general') {
                    $output[$keySection] = $this->parseProperty($section, $keySection);
                } else {
                    $output[$keySection][] = $this->parseProperty($section, $keySection);
                }
            }
        }

        return $output;
    }

    private function parseProperty($sections, $section): array
    {
        $output = [];
        foreach ($sections as $info) {
            $property = null;
            $value = null;
            $info = \explode(':', $info, 2);
            if (\count($info) >= 2) {
                $property = \strtolower(\trim($info[0]));
                $value = \trim($info[1]);
            }
            if ($property && $value) {
                switch (\strtolower($section)) {
                    case 'general':
                        switch ($property) {
                            case 'complete name':
                            case 'completename':
                                $output['file_name'] = self::stripPath($value);

                                break;
                            case 'format':
                                $output['format'] = $value;

                                break;
                            case 'duration':
                                $output['duration'] = $value;

                                break;
                            case 'file size':
                            case 'filesize':
                                $output['file_size'] = $this->parseFileSize($value);

                                break;
                            case 'overall bit rate':
                            case 'overallbitrate':
                                $output['bit_rate'] = $this->parseBitRate($value);

                                break;
                        }

                        break;

                    case 'video':
                        switch ($property) {
                            case 'format':
                                $output['format'] = $value;

                                break;
                            case 'format version':
                            case 'format_version':
                                $output['format_version'] = $value;

                                break;
                            case 'codec id':
                            case 'codecid':
                                $output['codec'] = $value;

                                break;
                            case 'width':
                                $output['width'] = $this->parseWidthHeight($value);

                                break;
                            case 'height':
                                $output['height'] = $this->parseWidthHeight($value);

                                break;
                            case 'stream size':
                            case 'stream_size':
                                $output['stream_size'] = $this->parseFileSize($value);

                                break;
                            case 'writing library':
                            case 'encoded_library':
                                $output['writing_library'] = $value;

                                break;
                            case 'frame rate mode':
                            case 'framerate_mode':
                                $output['framerate_mode'] = $value;

                                break;
                            case 'frame rate':
                            case 'framerate':
                                // if variable this becomes Original frame rate
                                $output['frame_rate'] = $value;

                                break;
                            case 'display aspect ratio':
                            case 'displayaspectratio':
                                $output['aspect_ratio'] = \str_replace('/', ':', $value); // mediainfo sometimes uses / instead of :
                                break;
                            case 'bit rate':
                            case 'bitrate':
                                $output['bit_rate'] = $this->parseBitRate($value);

                                break;
                            case 'bit rate mode':
                            case 'bitrate_mode':
                                $output['bit_rate_mode'] = $value;

                                break;
                            case 'nominal bit rate':
                            case 'bitrate_nominal':
                                $output['bit_rate_nominal'] = $this->parseBitRate($value);

                                break;
                            case 'bits/(pixel*frame)':
                            case 'bits-(pixel*frame)':
                                $output['bit_pixel_frame'] = $value;

                                break;
                            case 'bit depth':
                            case 'bitdepth':
                                $output['bit_depth'] = $value;

                                break;
                            case 'encoding settings':
                                $output['encoding_settings'] = $value;

                                break;
                            case 'language':
                                $output['language'] = $value;

                                break;
                            case 'format profile':
                                $output['format_profile'] = $value;

                                break;
                            case 'color primaries':
                                $output['color_primaries'] = $value;

                                break;
                            case 'title':
                                $output['title'] = $value;

                                break;
                            case 'scan type':
                                $output['scan_type'] = $value;

                                break;
                            case 'transfer characteristics':
                                $output['transfer_characteristics'] = $value;

                                break;
                            case 'hdr format':
                                $output['hdr_format'] = $value;

                                break;
                        }

                        break;

                    case 'audio':
                        switch ($property) {
                            case 'codec id':
                            case 'codecid':
                                $output['codec'] = $value;

                                break;
                            case 'format':
                                $output['format'] = $value;

                                break;
                            case 'bit rate':
                            case 'bitrate':
                                $output['bit_rate'] = $this->parseBitRate($value);

                                break;
                            case 'channel(s)':
                                $output['channels'] = $this->parseAudioChannels($value);

                                break;
                            case 'title':
                                $output['title'] = $value;

                                break;
                            case 'language':
                                $output['language'] = $value;

                                break;
                            case 'format profile':
                            case 'format_profile':
                                $output['format_profile'] = $value;

                                break;
                            case 'stream size':
                            case 'stream_size':
                                $output['stream_size'] = $this->parseFileSize($value);

                                break;
                        }

                        break;

                    case 'text':
                        switch ($property) {
                            case 'codec id':
                            case 'codecid':
                                $output['codec'] = $value;

                                break;
                            case 'format':
                                $output['format'] = $value;

                                break;
                            case 'title':
                                $output['title'] = $value;

                                break;
                            case 'language':
                                $output['language'] = $value;

                                break;
                            case 'default':
                                $output['default'] = $value;

                                break;
                            case 'forced':
                                $output['forced'] = $value;

                                break;
                        }

                        break;
                }
            }
        }

        return $output;
    }

    public static function stripPath($string)
    {
        $string = \str_replace('\\', '/', $string);
        $pathParts = \pathinfo($string);

        return $pathParts['basename'];
    }

    private function parseFileSize($string): float
    {
        $number = (float) $string;
        \preg_match('#[KMGTPEZ]#i', $string, $size);
        if (! empty($size[0])) {
            $number = $this->computerSize($number, $size[0].'b');
        }

        return $number;
    }

    private function parseBitRate($string): array | string
    {
        return \str_replace([' ', 'kbps'], ['', ' kbps'], \strtolower($string));
    }

    private function parseWidthHeight($string): array | string
    {
        return \str_replace(['pixels', ' '], null, \strtolower($string));
    }

    private function parseAudioChannels($string): array | string
    {
        return \str_ireplace(\array_keys(self::REPLACE), self::REPLACE, $string);
    }

    private function formatOutput($data): array
    {
        $output = [];
        $output['general'] = empty($data['general']) ? null : $data['general'];
        $output['video'] = empty($data['video']) ? null : $data['video'];
        $output['audio'] = empty($data['audio']) ? null : $data['audio'];
        $output['text'] = empty($data['text']) ? null : $data['text'];

        return $output;
    }

    private function computerSize($number, $size): float
    {
        $bytes = (float) $number;
        $size = \strtolower($size);

        if (isset(self::FACTORS[$size])) {
            return (float) \number_format($bytes * (1_024 ** self::FACTORS[$size]), 2, '.', '');
        }

        return $bytes;
    }
}
