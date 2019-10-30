@extends('layout.default')

@section('title')
    <title>Activity Log - @lang('staff.staff-dashboard') - {{ config('other.title') }}</title>
@endsection

@section('meta')
    <meta name="description" content="Activity Log - @lang('staff.staff-dashboard')">
@endsection

@section('breadcrumb')
    <li>
        <a href="{{ route('staff.dashboard.index') }}" itemprop="url" class="l-breadcrumb-item-link">
            <span itemprop="title" class="l-breadcrumb-item-link-title">@lang('staff.staff-dashboard')</span>
        </a>
    </li>
    <li class="active">
        <a href="{{ route('staff.audits.index') }}" itemprop="url" class="l-breadcrumb-item-link">
            <span itemprop="title" class="l-breadcrumb-item-link-title">Activity Log</span>
        </a>
    </li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="block">
            <h2>Activity Log</h2>
            <hr>
            <p class="text-red"><strong><i class="{{ config('other.font-awesome') }} fa-list"></i> Activity Log</strong></p>
            <div class="table-responsive">
                <table class="table table-condensed table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>@lang('common.no')</th>
                        <th>Subject</th>
                        <th>URL</th>
                        <th>Method</th>
                        <th>IP</th>
                        <th>@lang('common.user') Agent</th>
                        <th>Username</th>
                        <th>Created On</th>
                        <th>@lang('common.action')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if ($activities->count())
                        @foreach ($activities as $key => $activity)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>{{ $activity->subject }}</td>
                                <td class="text-success">{{ $activity->url }}</td>
                                <td><label class="label label-info">{{ $activity->method }}</label></td>
                                <td class="text-danger">{{ $activity->ip }}</td>
                                <td class="text-warning">{{ $activity->agent }}</td>
                                <td>{{ $activity->user->username }}</td>
                                <td>
                                    {{ $activity->created_at->toDayDateTimeString() }}
                                    ({{ $activity->created_at->diffForHumans() }})
                                </td>
                                <td>
                                    <a href="{{ route('staff.audits.destroy', ['id' => $activity->id]) }}"
                                       class="btn btn-xs btn-danger">
                                        <i class="{{ config('other.font-awesome') }} fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
                <div class="text-center">
                    {{ $activities->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
