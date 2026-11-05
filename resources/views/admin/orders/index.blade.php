@extends('layouts.admin')

@section('title', 'Order Management - Staff Admin')

@section('content')

{{-- SHARED ORDER TABLE COMPONENT --}}
@include('components.orders-table', [
    'orders'       => $orders,
    'isSuperAdmin' => false,
    'indexRoute'   => 'admin.orders.index',
])

@endsection
