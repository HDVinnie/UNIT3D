@extends('layout.default')

@section('title')
    <title>{{ $user->username }} - @lang('common.members') - {{ config('other.title') }}</title>
@endsection

@section('meta')
    <meta name="description"
          content="@lang('user.profile-desc', ['user' => $user->username, 'title' => config('other.title')])">
@endsection

@section('breadcrumb')
    <li>
        <a href="{{ route('users.show', [ 'username' => $user->username]) }}" itemprop="url"
           class="l-breadcrumb-item-link">
            <span itemprop="title" class="l-breadcrumb-item-link-title">{{ $user->username }}</span>
        </a>
    </li>
@endsection

@section('content')
    <div class="container">
        @if (!auth()->user()->isAllowed($user))
            <div class="container pl-0 text-center">
                <div class="jumbotron shadowed">
                    <div class="container">
                        <h1 class="mt-5 text-center">
                            <i class="{{ config('other.font-awesome') }} fa-times text-danger"></i>@lang('user.private-profile')
                        </h1>
                        <div class="separator"></div>
                        <p class="text-center">@lang('user.not-authorized')</p>
                    </div>
                </div>
            </div>
        @else
            <div class="block">
                @if (auth()->user()->id == $user->id || auth()->user()->group->is_modo)
                    @include('user.buttons.profile')
                @else
                    @include('user.buttons.public')
                @endif
                <div class="header gradient blue">
                    <div class="inner_content">
                        <div class="content">
                            <div class="col-md-2">
                                @if ($user->image != null)
                                    <img src="{{ url('files/img/' . $user->image) }}" alt="{{ $user->username }}"
                                         class="img-circle">
                                @else
                                    <img src="{{ url('img/profile.png') }}" alt="{{ $user->username }}"
                                         class="img-circle">
                                @endif
                            </div>
                            <div class="col-lg-10">
                                <h2>{{ $user->username }}
                                    @if ($user->isOnline())
                                        <i class="{{ config('other.font-awesome') }} fa-circle text-green" data-toggle="tooltip" title=""
                                           data-original-title="@lang('user.online')"></i>
                                    @else
                                        <i class="{{ config('other.font-awesome') }} fa-circle text-red" data-toggle="tooltip" title=""
                                           data-original-title="@lang('user.offline')"></i>
                                    @endif
                                    <a href="{{ route('create', ['receiver_id' => $user->id, 'username' => $user->username]) }}">
                                        <i class="{{ config('other.font-awesome') }} fa-envelope text-info"></i>
                                    </a>
                                    <a href="#modal_user_gift" data-toggle="modal"
                                            data-target="#modal_user_gift"><i
                                                class="{{ config('other.font-awesome') }} fa-gift text-info"></i></a>
                                    @if ($user->getWarning() > 0)
                                        <i class="{{ config('other.font-awesome') }} fa-exclamation-circle text-orange" aria-hidden="true"
                                           data-toggle="tooltip" title="" data-original-title="@lang('user.active-warning')">
                                        </i>
                                    @endif
                                    @if ($user->notes->count() > 0 && auth()->user()->group->is_modo)
                                        <a href="{{ route('user_setting', ['username' => $user->username]) }}"
                                           class="edit">
                                        <i class="{{ config('other.font-awesome') }} fa-comment fa-beat text-danger" aria-hidden="true" data-toggle="tooltip"
                                            title="" data-original-title="@lang('user.staff-noted')">
                                        </i>
                                        </a>
                                    @endif
                                </h2>
                                <h4>@lang('common.group'): <span class="badge-user text-bold"
                                                                       style="color:{{ $user->group->color }}; background-image:{{ $user->group->effect }};"><i
                                                class="{{ $user->group->icon }}" data-toggle="tooltip" title=""
                                                data-original-title="{{ $user->group->name }}"></i> {{ $user->group->name }}</span>
                                </h4>
                                <h4>@lang('user.registration-date') {{ $user->created_at === null ? "N/A" : date('M d Y', $user->created_at->getTimestamp()) }}</h4>
        @if (auth()->user()->id != $user->id)
                                <span style="float:right;">
        @if (auth()->user()->group->is_modo)
                                        <button class="btn btn-xs btn-warning" data-toggle="modal"
                                                data-target="#modal_user_note"><span
                                                    class="{{ config('other.font-awesome') }} fa-sticky-note"></span> @lang('user.note') </button>
                                        @if ($user->group->id == 5)
                                            <button class="btn btn-xs btn-warning" data-toggle="modal"
                                                    data-target="#modal_user_unban"><span
                                                        class="{{ config('other.font-awesome') }} fa-undo"></span> @lang('user.unban') </button>
                                        @else
                                            <button class="btn btn-xs btn-danger" data-toggle="modal"
                                                    data-target="#modal_user_ban"><span
                                                        class="{{ config('other.font-awesome') }} fa-ban"></span> @lang('user.ban')</button>
                                        @endif
                                        <a href="{{ route('user_setting', ['username' => $user->username]) }}"
                                           class="btn btn-xs btn-warning"><span
                                                    class="{{ config('other.font-awesome') }} fa-pencil"></span> @lang('user.edit') </a>
                                        <button class="btn btn-xs btn-danger" data-toggle="modal"
                                                data-target="#modal_user_delete"><span
                                                    class="{{ config('other.font-awesome') }} fa-trash"></span> @lang('user.delete') </button>
                                    @endif
                                        </span>
                                    @endif

                            </div>
                        </div>
                    </div>
                </div>


                        @if (auth()->user()->isAllowed($user,'profile','show_profile_torrent_count'))
                            <div class="button-holder some-padding">
                                <div class="button-left-small">

                                </div>
                                <div class="button-right-large">
                            <span class="badge-user badge-float p-10"><i
                                        class="{{ config('other.font-awesome') }} fa-upload"></i> @lang('user.total-uploads')
                                : <span class="text-green text-bold">{{ $user->torrents->count() }}</span></span>
                                    <span class="badge-user badge-float p-10"><i
                                                class="{{ config('other.font-awesome') }} fa-download"></i> @lang('user.total-downloads')
                                        : <span class="text-red text-bold">{{ $history->where('actual_downloaded', '>', 0)->count() }}</span></span>
                                    <span class="badge-user badge-float p-10"><i
                                                class="{{ config('other.font-awesome') }} fa-cloud-upload"></i> @lang('user.total-seeding')
                                        : <span class="text-green text-bold">{{ $user->getSeeding() }}</span></span>
                                    <span class="badge-user badge-float p-10"><i
                                                class="{{ config('other.font-awesome') }} fa-cloud-download"></i> @lang('user.total-leeching')
                                        : <span class="text-red text-bold">{{ $user->getLeeching() }}</span></span>
                                </div>
                            </div>
                        @endif

                    <hr class="no-space">


                    <h3><i class="{{ config('other.font-awesome') }} fa-unlock"></i> @lang('user.public-info')</h3>
                <div class="table-responsive">
                    <table class="table table-condensed table-striped table-bordered">
                        <tbody>
                        <tr>
                            <td colspan="2" class="text-bold">
                                <div class="button-holder">
                                    <div class="button-left-small">@lang('user.user') @lang('user.information'):</div>
                                    <div class="button-right-large">

                                    </div>
                                </div>
                            </td>
                        </tr>
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_title'))
                        <tr>
                            <td>@lang('user.title')</td>
                            <td>
                                <span class="badge-extra">{{ $user->title }}</span>
                            </td>
                        </tr>
                        @endif
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_about'))
                        <tr>
                            <td>@lang('user.about')</td>
                            <td>
                                <span class="badge-extra">@emojione($user->getAboutHtml())</span>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="2" class="text-bold">
                                <div class="button-holder">
                                    <div class="button-left-small">@lang('torrent.torrent') @lang('torrent.statistics'):</div>
                                    <div class="button-right-large">

                                    </div>
                                </div>
                            </td>
                        </tr>
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_torrent_ratio'))
            <tr>
                <td class="col-md-2">@lang('common.download')</td>
                <td>
                    <span class="badge-extra text-red" data-toggle="tooltip" title=""
                          data-original-title="@lang('user.download-recorded')">{{ $user->getDownloaded() }}</span> = 
                    <span class="badge-extra text-blue" data-toggle="tooltip" title=""
                          data-original-title="@lang('user.download-true')">{{ App\Helpers\StringHelper::formatBytes($realdownload , 2) }}</span> - 
                    <span class="badge-extra text-orange" data-toggle="tooltip" title=""
                          data-original-title="@lang('user.download-bon')">{{ App\Helpers\StringHelper::formatBytes($bondownload , 2) }}</span></td>
            </tr>
            <tr>
                <td>@lang('common.upload')</td>
                <td>
                    <span class="badge-extra text-green" data-toggle="tooltip" title=""
                          data-original-title="@lang('user.upload-recorded')">{{ $user->getUploaded() }}</span> = 
                    <span class="badge-extra text-blue" data-toggle="tooltip" title=""
                          data-original-title="@lang('user.upload-true')">{{ App\Helpers\StringHelper::formatBytes($realupload , 2) }}</span> + 
                    <span class="badge-extra text-orange" data-toggle="tooltip" title=""
                          data-original-title="@lang('user.upload-bon')">{{ App\Helpers\StringHelper::formatBytes($bonupload , 2) }}</span></td>
            </tr>
            <tr>
                <td>@lang('common.ratio')</td>
                <td><span class="badge-user group-member">{{ $user->getRatioString() }}</span></td>
            </tr>
            <tr>
                <td>@lang('common.buffer')</td>
                <td>
                    <span class="badge-user group-member">{{ $user->untilRatio(config('other.ratio')) }}</span>
                </td>
            </tr>
            @endif
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_torrent_seed'))
            <tr>
                <td>@lang('user.total-seedtime')</td>
                <td>
                    <span class="badge-user group-member">{{ App\Helpers\StringHelper::timeElapsed($history->sum('seedtime')) }} ( @lang('user.all-torrents') )</span>
                </td>
            </tr>
            <tr>
                <td>@lang('user.avg-seedtime')</td>
                <td>
                    <span class="badge-user group-member">{{ App\Helpers\StringHelper::timeElapsed(round($history->sum('seedtime') / max(1, $history->count()))) }} ( @lang('user.per-torrent') )</span>
                </td>
            </tr>
            <tr>
                <td>@lang('user.seeding-size')</td>
                <td>
                    <span class="badge-user group-member">{{ App\Helpers\StringHelper::formatBytes($user->getTotalSeedSize() , 2) }}</span>
                </td>
            </tr>
            @endif
                        <tr>
                            <td colspan="2" class="text-bold">
                                <div class="button-holder">
                                    <div class="button-left-small">@lang('user.user') @lang('user.statistics'):</div>
                                    <div class="button-right-large">

                                    </div>
                                </div>
                            </td>
                        </tr>
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_bon_extra'))
                    <tr>
                        <td>@lang('user.bon')</td>
                        <td>
                            <ul class="list-inline mb-0">
                                <li>
          <span class="badge-extra"><strong>@lang('bon.bon'):</strong>
            <span class="text-green text-bold">{{ $user->getSeedbonus() }}</span>
          </span>
                                </li>
                                <li>
          <span class="badge-extra"><strong>@lang('user.tips-received'):</strong>
            <span class="text-pink text-bold">{{ number_format($user->bonReceived()->where('name', '=', 'tip')->sum('cost'), 2) }} @lang('bon.bon')</span>
          </span>
                                </li>
                                <li>
          <span class="badge-extra"><strong>@lang('user.tips-given'):</strong>
            <span class="text-pink text-bold">{{ number_format($user->bonGiven()->where('name', '=', 'tip')->sum('cost'), 2) }} @lang('bon.bon')</span>
          </span>
                                </li>
                                <li>
          <span class="badge-extra"><strong>@lang('user.gift-received'):</strong>
            <span class="text-pink text-bold">{{ number_format($user->bonReceived()->where('name', '=', 'gift')->sum('cost'), 2) }} @lang('bon.bon')</span>
          </span>
                                </li>
                                <li>
          <span class="badge-extra"><strong>@lang('user.gift-given'):</strong>
            <span class="text-pink text-bold">{{ number_format($user->bonGiven()->where('name', '=', 'gift')->sum('cost'), 2) }} @lang('bon.bon')</span>
          </span>
                                </li>
                                <li>
          <span class="badge-extra"><strong>@lang('user.bounty-received'):</strong>
            <span class="text-pink text-bold">{{ number_format($user->bonReceived()->where('name', '=', 'request')->sum('cost'), 2) }} @lang('bon.bon')</span>
          </span>
                                </li>
                                <li>
          <span class="badge-extra"><strong>@lang('user.bounty-given'):</strong>
            <span class="text-pink text-bold">{{ number_format($user->bonGiven()->where('name', '=', 'request')->sum('cost'), 2) }} @lang('bon.bon')</span>
          </span>
                                </li>
                            </ul>
                        </td>
                    </tr>
                    @endif
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_torrent_extra'))
                        <tr>
                        <td>@lang('user.torrents')</td>
                        <td>
                            <ul class="list-inline mb-0">
                                <li>
          <span class="badge-extra"><strong>@lang('common.fl_tokens'):</strong>
            <span class="text-green text-bold">{{ $user->fl_tokens }}</span>
          </span>
                                </li>
                                <li>
          <span class="badge-extra"><strong>@lang('user.thanks-received'):</strong>
            <span class="text-pink text-bold">{{ $user->thanksReceived()->count() }}</span>
          </span>
                                </li>
                                <li>
          <span class="badge-extra"><strong>@lang('user.thanks-given'):</strong>
            <span class="text-pink text-bold"> {{ $user->thanksGiven()->count() }}</span>
          </span>
                                </li>
                            </ul>
                        </td>
                        </tr>
                        @endif
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_comment_extra'))
                        <tr>
                            <td>@lang('user.comments')</td>
                            <td>
                                <ul class="list-inline mb-0">
                                <li>
          <span class="badge-extra"><strong>@lang('user.article-comments'):</strong>
            <span class="text-green text-bold">{{ $user->comments()->where('article_id', '>', 0)->count() }}</span>
          </span>
                                </li>
                                <li>
          <span class="badge-extra"><strong>@lang('user.torrent-comments'):</strong>
            <span class="text-green text-bold">{{ $user->comments()->where('torrent_id', '>', 0)->count() }}</span>
          </span>
                                </li>
                                <li>
          <span class="badge-extra"><strong>@lang('user.request-comments'):</strong>
            <span class="text-green text-bold">{{ $user->comments()->where('requests_id', '>', 0)->count() }}</span>
          </span>
                                </li>
                                </ul>
                            </td>
                        </tr>
                        @endif
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_forum_extra'))
                        <tr>
                            <td>@lang('user.forums')</td>
                            <td>
                                <ul class="list-inline mb-0">
                                    <li>
          <span class="badge-extra"><strong>@lang('user.topics-started'):</strong>
            <span class="text-green text-bold">{{ $user->topics->count() }}</span>
          </span>
                                </li>
                                <li>
          <span class="badge-extra"><strong>@lang('user.posts-posted'):</strong>
            <span class="text-green text-bold">{{ $user->posts->count() }}</span>
          </span>
                                </li>
                            </ul>
                        </td>
                    </tr>
                        @endif
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_request_extra'))
                            <tr>
                                <td>@lang('user.requests')</td>
                                <td>
                                    <ul class="list-inline mb-0">
                                        <li>
          <span class="badge-extra"><strong>@lang('user.requested'):</strong>
            <span class="text-pink text-bold">{{ $requested }}</span>
          </span>
                                        </li>
                                        <li>
          <span class="badge-extra"><strong>@lang('user.filled-request'):</strong>
            <span class="text-green text-bold">{{ $filled }}</span>
          </span>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        @endif
            <tr>
                <td colspan="2" class="text-bold">@lang('common.warnings'):</td>
            </tr>
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_warning'))
                        <tr>
                <td colspan="2" class="text-right">

                    <div class="progress">
                        <div class="progress-bar progress-bar-danger progress-bar-striped active" role="progressbar"
                             style="width:0%; border-bottom-color: #8c0408;">
                        </div>
                        @php $percent = 100 / config('hitrun.max_warnings'); @endphp
                        @foreach ($warnings as $warning)
                            <div class="progress-bar progress-bar-danger progress-bar-striped active" role="progressbar"
                                 style="width: {{ $percent }}%; border-bottom-color: #8c0408;">
                                {{ strtoupper(trans('user.warning')) }}
                            </div>
                        @endforeach
                    </div>
                    <div class="some-padding">
                    <span class="badge-user text-red text-bold">

                        <strong>@lang('user.active-warnings')
                            : {{ $warnings->count() }} / {!! config('hitrun.max_warnings') !!}</strong></span>
          <span class="badge-user"><strong>@lang('user.hit-n-runs-count'):</strong>
            <span class="{{ $user->hitandruns > 0 ? 'text-red' : 'text-green' }} text-bold">{{ $user->hitandruns }}</span>
          </span>
                    @if (auth()->user()->group->is_modo)
                        <a href="{{ route('warnings.show', ['username' => $user->username]) }}"><span
                                    class="badge-user text-bold"><strong>@lang('user.warning-log')</strong></span></a>
                        <a href="{{ route('banlog', ['username' => $user->username]) }}"><span
                                    class="badge-user text-bold"><strong>@lang('user.ban-log')</strong></span></a>
                    @endif
                    </div>
                </td>
            </tr>
                            @endif
            </tbody>
            </table>


                    @if(auth()->user()->id == $user->id)
                        @include('user.buttons.user')
                    @elseif(auth()->user()->group && auth()->user()->group->is_modo == 1)
                        @include('user.buttons.staff')
                    @endif


                </div>
    </div>

            <div class="well">
                <div class="row">
                    <div class="col-md-12 profile-footer">
                        @lang('user.badges')
                        <i class="{{ config('other.font-awesome') }} fa-badge text-success"></i>
                        <span>:</span>
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_badge'))
                            @if ($user->getSeeding() >= 150)
                                <span class="badge-user" style="background-color:#3fb618; color:rgb(255,255,255);" data-toggle="tooltip"
                                      title="" data-original-title="@lang('user.certified-seeder-desc')"><i
                                            class="{{ config('other.font-awesome') }} fa-upload"></i> @lang('user.certified-seeder')!</span>
                            @endif
                            @if ($history->where('actual_downloaded', '>', 0)->count() >= 100)
                                <span class="badge-user" style="background-color:#ff0039; color:rgb(255,255,255);" data-toggle="tooltip"
                                      title="" data-original-title="@lang('user.certified-downloader-desc')"><i
                                            class="{{ config('other.font-awesome') }} fa-download"></i> @lang('user.certified-downloader')!</span>
                            @endif
                            @if ($user->getSeedbonus() >= 50000)
                                <span class="badge-user" style="background-color:#9400d3; color:rgb(255,255,255);" data-toggle="tooltip"
                                      title="" data-original-title="@lang('user.certified-banker-desc')"><i
                                            class="{{ config('other.font-awesome') }} fa-coins"></i> @lang('user.certified-banker')!</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="well">
                <div class="row">
                    <div class="col-md-12 profile-footer">
                        @lang('user.recent-achievements')
                        <i class="{{ config('other.font-awesome') }} fa-trophy text-success"></i>
                        <span>:</span>
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_achievement'))
                            @php
                                $x=1;
                            @endphp
                            @foreach ($user->unlockedAchievements() as $a)
                                @php
                                    if($x > 25) { continue; }
                                @endphp
                                <img src="/img/badges/{{ $a->details->name }}.png" data-toggle="tooltip" title=""
                                     height="50px" data-original-title="{{ $a->details->name }}" alt="{{ $a->details->name }}">
                                @php
                                    $x++;
                                @endphp
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="well">
                <div class="row">
                    <div class="col-md-12 profile-footer followers">
                        @lang('user.recent-followers')
                        <i class="{{ config('other.font-awesome') }} fa-users text-success"></i>
                        <span>:</span>
                        @if (auth()->user()->isAllowed($user,'profile','show_profile_follower'))
                            @foreach ($followers as $f)
                                @if ($f->user->image != null)
                                    <a href="{{ route('users.show', ['username' => $f->user->username]) }}">
                                        <img src="{{ url('files/img/' . $f->user->image) }}" data-toggle="tooltip"
                                             title="{{ $f->user->username }}" height="50px"
                                             data-original-title="{{ $f->user->username }}"
                                             alt="{{ $f->user->username }}">
                                    </a>
                                @else
                                    <a href="{{ route('users.show', ['username' => $f->user->username]) }}">
                                        <img src="{{ url('img/profile.png') }}" data-toggle="tooltip"
                                             title="{{ $f->user->username }}" height="50px"
                                             data-original-title="{{ $f->user->username }}"
                                             alt="{{ $f->user->username }}">
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
    @if (auth()->user()->id == $user->id || auth()->user()->group->is_modo)
        <div class="block">
            <h3><i class="{{ config('other.font-awesome') }} fa-lock"></i> @lang('user.private-info')</h3>
            <div class="table-responsive">
                <table class="table table-condensed table-striped table-bordered">
                <tbody>
                <tr>
                    <td colspan="2" class="text-bold">
                        <div class="button-holder">
                            <div class="button-left-small">@lang('user.id-permissions'):</div>
                            <div class="button-right-large">

                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>@lang('user.invited-by')</td>
                    <td>
                    @if ($invitedBy)
                        <a href="{{ route('users.show', ['username' => $invitedBy->sender->username]) }}">
                            <span class="text-bold" style="color:{{ $invitedBy->sender->group->color }}; ">
                                <i class="{{ $invitedBy->sender->group->icon }}"></i> {{ $invitedBy->sender->username }}
                            </span>
                        </a>
                    @else
                        <span class="text-bold">@lang('user.open-registration')</span>
                    @endif
                    </td>
                </tr>
                <tr>
                    <td class="col-md-2"> @lang('user.passkey')</td>
                    <td>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-xxs btn-info collapsed" data-toggle="collapse"
                                    data-target="#pid_block"
                                    aria-expanded="false">@lang('user.show-passkey')</button>
                        </div>
                        <div class="col-md-8">
                            <div id="pid_block" class="collapse" aria-expanded="false" style="height: 0;">
                                <span class="text-monospace">{{ $user->passkey }}</span>
                                <br>
                            </div>
                            <span class="small text-red">@lang('user.passkey-warning')</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td> @lang('user.user-id')</td>
                    <td>{{ $user->id }}</td>
                </tr>
                <tr>
                    <td> @lang('common.email')</td>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <td> @lang('user.last-login')</td>
                    <td>@if ($user->last_login != null){{ $user->last_login->toDayDateTimeString() }}
                        ({{ $user->last_login->diffForHumans() }})@else N/A @endif</td>
                </tr>
                <tr>
                    <td> @lang('user.can-upload')</td>
                    @if ($user->can_upload == 1)
                        <td><i class="{{ config('other.font-awesome') }} fa-check text-green"></i></td>
                    @else
                        <td><i class="{{ config('other.font-awesome') }} fa-times text-red"></i></td>
                    @endif
                </tr>
                <tr>
                    <td> @lang('user.can-download')</td>
                    @if ($user->can_download == 1)
                        <td><i class="{{ config('other.font-awesome') }} fa-check text-green"></i></td>
                    @else
                        <td><i class="{{ config('other.font-awesome') }} fa-times text-red"></i></td>
                    @endif
                </tr>
                <tr>
                    <td> @lang('user.can-comment')</td>
                    @if ($user->can_comment == 1)
                        <td><i class="{{ config('other.font-awesome') }} fa-check text-green"></i></td>
                    @else
                        <td><i class="{{ config('other.font-awesome') }} fa-times text-red"></i></td>
                    @endif
                </tr>
                <tr>
                    <td> @lang('user.can-request')</td>
                    @if ($user->can_request == 1)
                        <td><i class="{{ config('other.font-awesome') }} fa-check text-green"></i></td>
                    @else
                        <td><i class="{{ config('other.font-awesome') }} fa-times text-red"></i></td>
                    @endif
                </tr>
                <tr>
                    <td> @lang('user.can-chat')</td>
                    @if ($user->can_chat == 1)
                        <td><i class="{{ config('other.font-awesome') }} fa-check text-green"></i></td>
                    @else
                        <td><i class="{{ config('other.font-awesome') }} fa-times text-red"></i></td>
                    @endif
                </tr>
                <tr>
                    <td> @lang('user.can-invite')</td>
                    @if ($user->can_invite == 1)
                        <td><i class="{{ config('other.font-awesome') }} fa-check text-green"></i></td>
                    @else
                        <td><i class="{{ config('other.font-awesome') }} fa-times text-red"></i></td>
                    @endif
                </tr>
                <tr>
                    <td> @lang('user.invites')</td>
                    @if ($user->invites > 0)
                        <td><span class="text-success text-bold"> {{ $user->invites }}</span>
                        </td>
                    @else
                        <td><span class="text-danger text-bold"> {{ $user->invites }}</span>
                        </td>
                    @endif
                </tr>
                <tr>
                    <td> @lang('user.last_auth_ip')</td>
                    <td>
                        <span class="text-success text-bold"> {{ $user->authentications->last()->ip_address }}
                            @if (auth()->user()->id == $user->id || auth()->user()->group->is_modo)
                                <a href="{{ route('authentications.show', ['username' => $user->username]) }}">
                                    <span class="badge-user text-bold"><strong>@lang('user.auth-log')</strong></span>
                                </a>
                            @endif
                        </span>

                    </td>
                </tr>
                </tbody>
            </table>
            </div>
            <br>
        </div>

                <div class="block">
                    <h3><i class="{{ config('other.font-awesome') }} fa-bell"></i> @lang('user.important-info')</h3>
                    <div class="table-responsive">
                        <table class="table table-condensed table-striped table-bordered">
                            <thead>
                            <tr>
                                <th colspan="4" class="text-bold">
                                    <div class="button-holder">
                                        <div class="button-left-small">Hit & Runs</div>
                                        <div class="button-right-large">

                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </thead>

                            <thead>
                            <th>@lang('torrent.torrent')</th>
                            <th>@lang('user.warned-on')</th>
                            <th>@lang('user.expires-on')</th>
                            <th>@lang('user.active')</th>
                            </thead>

                            <tbody>
                            @foreach ($hitrun as $hr)
                                <tr>
                                    <td>
                                        <a class="text-bold" href="{{ route('torrent', ['id' => $hr->torrenttitle->id]) }}">
                                            {{ $hr->torrenttitle->name }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $hr->created_at }}
                                    </td>
                                    <td>
                                        {{ $hr->expires_on }}
                                    </td>
                                    <td>
                                        @if ($hr->active == 1)
                                            <span class='label label-success'>@lang('common.yes')</span>
                                        @else
                                            <span class='label label-danger'>@lang('user.expired')</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $hitrun->links() }}
                    </div>
                </div>
        @endif
        @endif
        </div>
        @include('user.user_modals', ['user' => $user])
@endsection
