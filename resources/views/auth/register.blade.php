@extends('layouts.app')

@section('title', 'Customer Registration | Grafika South')

@section('content')
    @include('auth.portal', [
        'mode'         => 'register',
        'showSwitcher' => true,
    ])
@endsection
