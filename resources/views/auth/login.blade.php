@extends('layouts.app')

@section('title', 'Customer Login | Grafika South')

@section('content')
    @include('auth.portal', [
        'mode'         => 'login',
        'showSwitcher' => !request()->routeIs('admin.login'),
    ])
@endsection
