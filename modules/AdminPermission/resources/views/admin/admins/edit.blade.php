@extends('admin.layouts.app')

@section('title', __('Edit admin'))

@section('content')
    @include('adminpermission::admin.admins.partials.form', [
        'action' => route('admin.admins.update', $admin),
        'method' => 'PUT',
    ])
@endsection
