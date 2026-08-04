@extends('admin.layouts.app')

@section('title', __('Edit role'))

@section('content')
    @include('adminpermission::admin.roles.partials.form', [
        'action' => route('admin.roles.update', $role),
        'method' => 'PUT',
    ])
@endsection
