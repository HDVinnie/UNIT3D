@extends('layout.default')

@section('title')
    <title>Authentications Manager - {{ config('other.title') }}</title>
@endsection

@section('breadcrumb')
    <li>
        <a href="{{ route('users.show', ['username' => $user->username]) }}" itemprop="url" class="l-breadcrumb-item-link">
            <span itemprop="title" class="l-breadcrumb-item-link-title">{{ $user->username }}</span>
        </a>
    </li>
    <li>
        <a href="{{ route('authentications.show', ['username' => $user->username]) }}" itemprop="url" class="l-breadcrumb-item-link">
            <span itemprop="title" class="l-breadcrumb-item-link-title">@lang('user.auth-manager')</span>
        </a>
    </li>
@endsection

@section('content')
    <div class="container">
        <div class="block">
            <h2>
                <a href="{{ route('users.show', ['username' => $user->username]) }}">
                    {{ $user->username }}
                </a>
                @lang('user.auth-manager')
            </h2>
            <hr>
            <div class="row">
                <div class="col-sm-12">
                    <h2>Latest Authentications</h2>
                    <div class="table-responsive">
                        <table class="table table-condensed table-striped table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>System</th>
                                <th>System Ver.</th>
                                <th>Browser</th>
                                <th>Browser Ver.</th>
                                <th>IP Address</th>
                                <th>Type</th>
                                <th>Trusted</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($user->authentications) == 0)
                                <p>The are no authentication entries in the database!</p>
                            @else
                                @foreach ($user->authentications as $authentication)
                                    <tr>
                                        <td>
                                            {{ $authentication->device->platform }}
                                        </td>
                                        <td>
                                            {{ $authentication->device->platform_version }}
                                        </td>
                                        <td>
                                            {{ $authentication->device->browser }}
                                        </td>
                                        <td>
                                            {{ $authentication->device->browser_version }}
                                        </td>
                                        <td>
                                            {{ $authentication->ip_address }}
                                        </td>
                                        <td>
                                            @if ($authentication->device->is_desktop == 0)
                                                <i class="{{ config('other.font-awesome') }} fa-mobile text-blue"
                                                   data-toggle="tooltip"
                                                   title="Mobile Device">
                                                </i>
                                            @else
                                                <i class="{{ config('other.font-awesome') }} fa-desktop text-blue"
                                                   data-toggle="tooltip"
                                                   title="Desktop / Laptop">
                                                </i>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($authentication->device->is_trusted == 0)
                                                <i class="{{ config('other.font-awesome') }} fa-times text-red"></i>
                                            @else
                                                <i class="{{ config('other.font-awesome') }} fa-check text-green"></i>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $authentication->type }}
                                        </td>
                                        <td>
                                            @if ($authentication->device->is_trusted == 0)
                                                <a href="" type="button" class="btn btn-xs btn-success">Trust Device</a>
                                            @else
                                                <a href="" type="button" class="btn btn-xs btn-danger">Untrust Device</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="text-center">

            </div>
        </div>
    </div>
@endsection