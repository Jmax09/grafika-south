@extends('layouts.admin')

@section('title', 'Order Management - Super Admin')

@section('content')

{{-- SHARED ORDER TABLE COMPONENT --}}
@include('components.orders-table', [
    'orders'       => $orders,
    'isSuperAdmin' => true,
    'indexRoute'   => 'super-admin.orders.index',
])

@endsection
