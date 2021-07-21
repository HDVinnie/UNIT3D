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

namespace App\Console\Commands;

use App\Models\Torrent;
use App\Models\User;
use App\Services\Tmdb\Client\Movie;
use App\Services\Tmdb\TMDBScraper;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DemoSeed extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'demo:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds Fake Data For Demonstration Or Testing Purposes';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->alert('Demo Seeder v2.0 (Author: Poppabear)');
        $this->warn('*** This process could take a few minutes ***');
        $this->warn('Press CTRL + C to abort');

        \sleep(5);

        $abort = false;

        foreach ($this->ids() as $key => $id) {
            // Users
            $this->info('Creating User Account');

            $uid = User::factory()->create([
                'chatroom_id'    => 1,
                'group_id'       => \rand(1, 20),
                'chat_status_id' => 1,
                'image'          => null,
                'custom_css'     => null,
                'locale'         => 'en',
            ])->id;

            // random boolean
            if ([false, true][\rand(0, 1)]) {
                $movie = $this->search($id);

                // Torrents
                $this->info('Creating Movie Torrents for Account ID #'.$uid);

                try {
                    $year = 2021;

                    if (\array_key_exists('release_date', $movie)) {
                        $year = (int) \substr($movie['release_date'], 0, 4);
                    }

                    Torrent::factory()->create([
                        'user_id'       => $uid,
                        'tmdb'          => $id,
                        'name'          => $movie['title'].' ('.$year.')',
                        'slug'          => Str::slug($movie['title']),
                        'description'   => $movie['overview'],
                        'category_id'   => 1,
                        'type_id'       => \rand(1, 6),
                        'resolution_id' => \rand(1, 10),
                        'featured'      => false,
                        'sticky'        => 0,
                        'release_year'  => $year,
                        'mediainfo'     => '
Complete name                            : Double.Impact.1991.1080p.BluRay.DD+5.1.x264-LoRD.mkv
Format                                   : Matroska
Format version                           : Version 4
File size                                : 14.1 GiB
Duration                                 : 1 h 49 min
Overall bit rate                         : 18.5 Mb/s
Encoded date                             : UTC 2020-02-24 10:07:15
Writing application                      : mkvmerge v43.0.0 (\'The Quartermaster\') 64-bit
Writing library                          : libebml v1.3.10 + libmatroska v1.5.2

Video
ID                                       : 1
Format                                   : AVC
Format/Info                              : Advanced Video Codec
Format profile                           : High@L4.1
Format settings                          : CABAC / 4 Ref Frames
Format settings, CABAC                   : Yes
Format settings, Reference frames        : 4 frames
Codec ID                                 : V_MPEG4/ISO/AVC
Duration                                 : 1 h 49 min
Bit rate                                 : 17.5 Mb/s
Width                                    : 1 920 pixels
Height                                   : 1 040 pixels
Display aspect ratio                     : 1.85:1
Frame rate mode                          : Constant
Frame rate                               : 23.976 (24000/1001) FPS
Color space                              : YUV
Chroma subsampling                       : 4:2:0
Bit depth                                : 8 bits
Scan type                                : Progressive
Bits/(Pixel*Frame)                       : 0.366
Stream size                              : 13.4 GiB (94%)
Title                                    : LoRD
Writing library                          : x264 core 159 r2991+49 ~ LoRD
Encoding settings                        : cabac=1 / ref=4 / deblock=1:-3:-3 / analyse=0x3:0x133 / me=umh / subme=10 / psy=1 / fade_compensate=0.00 / psy_rd=1.10:0.00 / mixed_ref=1 / me_range=32 / chroma_me=1 / trellis=2 / 8x8dct=1 / cqm=0 / deadzone=21,11 / fast_pskip=0 / chroma_qp_offset=-2 / threads=12 / lookahead_threads=2 / sliced_threads=0 / nr=0 / decimate=0 / interlaced=0 / bluray_compat=0 / constrained_intra=0 / fgo=0 / bframes=9 / b_pyramid=2 / b_adapt=2 / b_bias=0 / direct=3 / weightb=1 / open_gop=0 / weightp=2 / keyint=250 / keyint_min=23 / scenecut=40 / intra_refresh=0 / rc_lookahead=60 / rc=2pass / mbtree=0 / bitrate=17500 / ratetol=1.0 / qcomp=0.70 / qpmin=0:0:0 / qpmax=69:69:69 / qpstep=4 / cplxblur=20.0 / qblur=0.5 / vbv_maxrate=62500 / vbv_bufsize=78125 / nal_hrd=none / filler=0 / ip_ratio=1.25 / pb_ratio=1.20 / aq=3:0.55 / aq-sensitivity=10.00 / aq-factor=1.00:1.00:1.00 / aq2=0 / aq3=0
Language                                 : English
Default                                  : Yes
Forced                                   : No

Audio
ID                                       : 2
Format                                   : E-AC-3
Format/Info                              : Enhanced AC-3
Commercial name                          : Dolby Digital Plus
Codec ID                                 : A_EAC3
Duration                                 : 1 h 49 min
Bit rate mode                            : Constant
Bit rate                                 : 1 023 kb/s
Channel(s)                               : 6 channels
Channel layout                           : L R C LFE Ls Rs
Sampling rate                            : 48.0 kHz
Frame rate                               : 93.750 FPS (512 SPF)
Compression mode                         : Lossy
Stream size                              : 803 MiB (6%)
Title                                    : DD+5.1
Language                                 : English
Service kind                             : Complete Main
Default                                  : Yes
Forced                                   : No

Text #1
ID                                       : 3
Format                                   : UTF-8
Codec ID                                 : S_TEXT/UTF8
Codec ID/Info                            : UTF-8 Plain Text
Duration                                 : 1 h 41 min
Bit rate                                 : 26 b/s
Count of elements                        : 669
Stream size                              : 19.9 KiB (0%)
Language                                 : English
Default                                  : No
Forced                                   : No

Text #2
ID                                       : 4
Format                                   : UTF-8
Codec ID                                 : S_TEXT/UTF8
Codec ID/Info                            : UTF-8 Plain Text
Duration                                 : 1 h 48 min
Bit rate                                 : 30 b/s
Count of elements                        : 872
Stream size                              : 24.1 KiB (0%)
Title                                    : SDH
Language                                 : English
Default                                  : No
Forced                                   : No

Text #3
ID                                       : 5
Format                                   : UTF-8
Codec ID                                 : S_TEXT/UTF8
Codec ID/Info                            : UTF-8 Plain Text
Duration                                 : 1 h 41 min
Bit rate                                 : 26 b/s
Count of elements                        : 659
Stream size                              : 19.9 KiB (0%)
Language                                 : French
Default                                  : No
Forced                                   : No

Text #4
ID                                       : 6
Format                                   : UTF-8
Codec ID                                 : S_TEXT/UTF8
Codec ID/Info                            : UTF-8 Plain Text
Duration                                 : 1 h 41 min
Bit rate                                 : 24 b/s
Count of elements                        : 668
Stream size                              : 18.3 KiB (0%)
Title                                    : european
Language                                 : Portuguese
Default                                  : No
Forced                                   : No

Text #5
ID                                       : 7
Format                                   : UTF-8
Codec ID                                 : S_TEXT/UTF8
Codec ID/Info                            : UTF-8 Plain Text
Duration                                 : 1 h 42 min
Bit rate                                 : 26 b/s
Count of elements                        : 765
Stream size                              : 19.5 KiB (0%)
Title                                    : latin
Language                                 : Spanish
Default                                  : No
Forced                                   : No

Menu
00:00:00.000                             : en:Logo/Title/Intro
00:03:24.037                             : en:"My Babies!"
00:09:45.918                             : en:Life In L.A.
00:13:08.621                             : en:"He\'s Your Brother!"
00:21:01.510                             : en:Fencing Benzes
00:29:05.118                             : en:Mistaken Identity
00:37:34.627                             : en:Fishing Expedition
00:42:22.039                             : en:The Drop
00:50:05.711                             : en:Bad Cognac
00:58:56.032                             : en:On the Run
01:07:33.549                             : en:Sibling Rivalry
01:17:59.174                             : en:Hotel Invasion
01:23:51.443                             : en:A Captive Audience
01:28:49.532                             : en:One-On-One Combat
01:34:15.858                             : en:Who Gets Away?
01:44:15.249                             : en:End Credits
                        ',
                        'created_at' => \now(),
                        'bumped_at'  => \now(),
                        'updated_at' => \now(),
                    ]);
                } catch (Exception $exception) {
                    $abort = true;

                    $this->warn($exception);

                    break;
                }
            }

            if ($abort) {
                break;
            }
        }

        if ($abort) {
            $this->error('Aborted ...');
            $this->alert('Demo data was only PARTIALLY seeded! This is likely due to an API Request timeout.');
            $this->alert('Ensure TMDB api key is set and run "php artisan config:clear"');
        } else {
            $this->alert('Demo data has been successfully seeded!');
        }
    }

    private function search($id)
    {
        // we delay between api calls to reduce throttling
        \sleep(2);
        $tmdbScraper = new TMDBScraper();
        $tmdbScraper->movie($id);

        return (new Movie($id))->getData();
    }

    private function ids(): array
    {
        return [
            '602315',
            '572488',
            '572488',
            '635780',
            '660521',
            '442065',
            '442065',
            '442065',
            '442065',
            '666397',
            '666397',
            '653567',
            '586592',
            '653601',
            '494972',
            '666750',
            '668343',
            '647063',
            '666750',
            '666750',
            '620924',
            '565426',
            '671474',
            '534039',
            '658760',
            '605874',
            '663255',
            '236426',
            '622420',
            '662821',
            '674464',
            '550678',
            '550678',
            '594139',
            '586592',
            '618355',
            '550440',
            '674942',
            '457335',
            '457335',
            '342470',
            '550738',
            '550738',
            '550738',
            '501495',
            '654857',
            '586592',
            '618355',
            '502425',
            '586461',
            '526007',
            '618355',
            '618355',
            '465086',
            '597233',
            '465086',
            '581600',
            '581600',
            '465086',
            '382748',
            '618355',
            '465086',
            '465086',
            '465086',
            '646453',
            '581600',
            '382748',
            '665251',
            '570670',
            '514847',
            '627725',
            '627725',
            '665251',
            '570670',
            '508439',
            '508439',
            '627725',
            '495764',
            '508439',
            '508439',
            '639137',
            '508439',
            '448119',
            '454626',
            '338762',
            '495764',
            '338762',
            '508439',
            '495764',
            '437193',
            '338762',
            '495764',
            '338762',
            '465086',
            '338762',
            '556678',
            '529485',
            '448119',
            '448119',
            '674944',
            '448119',
            '448119',
            '481848',
            '481848',
            '560391',
            '685814',
            '655431',
            '448119',
            '448119',
            '448119',
            '38700',
            '502425',
            '495764',
            '454626',
            '38700',
            '454626',
            '38700',
            '38700',
            '566927',
            '664413',
            '443791',
            '443791',
            '443791',
            '443791',
            '504582',
            '665135',
            '448119',
            '688252',
            '553433',
            '687933',
            '689249',
            '689643',
            '502425',
            '502425',
            '443791',
            '502104',
            '570670',
            '555974',
            '668627',
            '617786',
            '682598',
            '502104',
            '595671',
            '502425',
            '446893',
            '502425',
            '492611',
            '542224',
            '492611',
            '688819',
            '526019',
            '522627',
            '582306',
            '592834',
            '592834',
            '592834',
            '492611',
            '38700',
            '526019',
            '526019',
            '651070',
            '454626',
            '38700',
            '624808',
            '38700',
            '522627',
            '522627',
            '646541',
            '38700',
            '522627',
            '38700',
            '522627',
            '673595',
            '664767',
            '592834',
            '526019',
            '492611',
            '492611',
            '492611',
            '528761',
            '522627',
            '448119',
            '675431',
            '539537',
            '466622',
            '38700',
            '627725',
            '38700',
            '526019',
            '492611',
            '489280',
            '541922',
            '653744',
            '549877',
            '664767',
            '466622',
            '457335',
            '689588',
            '443791',
            '694040',
            '664767',
            '466622',
            '466622',
            '466622',
            '539537',
            '457335',
            '457335',
            '457335',
            '667574',
            '664767',
            '531299',
            '466622',
            '466622',
            '526052',
            '675431',
            '663657',
            '620924',
            '457335',
            '522627',
            '664767',
            '545609',
            '560044',
            '542224',
            '585244',
            '664416',
            '679796',
            '338762',
            '560204',
            '542224',
            '514847',
            '585244',
            '338762',
            '585244',
            '585244',
            '542224',
            '542224',
            '338762',
            '338762',
            '664767',
            '338762',
            '338762',
            '542224',
            '560204',
            '585244',
            '560204',
            '560204',
            '474764',
            '690369',
            '560204',
            '627463',
            '664767',
            '338762',
            '338762',
            '617784',
            '689723',
            '503917',
            '495764',
            '609670',
            '454626',
            '454626',
            '481848',
            '674942',
            '589049',
            '454626',
            '454626',
            '664416',
            '503917',
            '539537',
            '495764',
            '495764',
            '539537',
            '539537',
            '495764',
            '539537',
            '474764',
            '399363',
            '474764',
            '481848',
            '481848',
            '481848',
            '474764',
            '501395',
            '690882',
            '527382',
            '578908',
            '639514',
            '589049',
            '589049',
            '495764',
            '684139',
            '576568',
            '664561',
            '618344',
            '673168',
            '545609',
            '520765',
            '574489',
            '501395',
            '664850',
            '639514',
            '495764',
            '589049',
            '481848',
            '495764',
            '567970',
            '675253',
            '466622',
            '495764',
            '539537',
            '481848',
            '474764',
            '589049',
            '518978',
            '655713',
            '518978',
            '699198',
            '665135',
            '572299',
            '556678',
            '454626',
            '556678',
            '556678',
            '556678',
            '555974',
            '555974',
            '555974',
            '508439',
            '560391',
            '429422',
            '529485',
            '529485',
            '582596',
            '658751',
            '609919',
            '525798',
            '597219',
            '597231',
            '505225',
            '505225',
            '449756',
            '598215',
            '676156',
            '605804',
            '689489',
            '401313',
            '508439',
            '508439',
            '466622',
            '399363',
            '508439',
            '673175',
            '649392',
            '653575',
            '385103',
            '385103',
            '570670',
            '704264',
            '570670',
            '508439',
            '618344',
            '548473',
            '570670',
            '570670',
            '618344',
            '560391',
            '338762',
            '449756',
            '449756',
            '570670',
            '560391',
            '529485',
            '529485',
            '696575',
            '449756',
            '672699',
            '560391',
            '696111',
            '706240',
            '706240',
            '529485',
            '567608',
            '495764',
            '38700',
            '449756',
            '706874',
            '684420',
            '576156',
            '481848',
            '618344',
            '685309',
            '526007',
            '526007',
            '566038',
            '429422',
            '429422',
            '429422',
            '429422',
            '708336',
            '677640',
            '602147',
            '597219',
            '513268',
            '599396',
            '699654',
            '698410',
            '706249',
            '570670',
            '690856',
            '709061',
            '385103',
            '520946',
            '620883',
            '565743',
            '660609',
            '710099',
            '606679',
            '647785',
            '565743',
            '647785',
            '606679',
            '707159',
            '514847',
            '514847',
            '526007',
            '710573',
            '710573',
            '710588',
            '710588',
            '514847',
            '514847',
            '514847',
            '572443',
            '514847',
            '635780',
            '711705',
            '689643',
            '339095',
            '513584',
            '513268',
            '598215',
            '690369',
            '598215',
            '598215',
            '513584',
            '513584',
            '690369',
            '677638',
            '339095',
            '418533',
            '702936',
            '418533',
            '702936',
            '702936',
            '585244',
            '579583',
            '703134',
            '475430',
            '475430',
            '418533',
            '418533',
            '581859',
            '525428',
            '579583',
            '714842',
            '566927',
            '581859',
            '522627',
            '623926',
            '522627',
            '623926',
            '664302',
            '566927',
            '566927',
            '667520',
            '619592',
            '514593',
            '619592',
            '590009',
            '619592',
            '451184',
            '619592',
            '590854',
            '555974',
            '618344',
            '633774',
            '555457',
            '555457',
            '555457',
            '566927',
            '703754',
            '546724',
            '555974',
            '555974',
            '595148',
            '535987',
            '446893',
            '508439',
            '695576',
            '446893',
            '595148',
        ];
    }
}
