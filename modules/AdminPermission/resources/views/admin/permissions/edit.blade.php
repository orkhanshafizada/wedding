@extends('admin.layouts.app')

@section('title', __('Edit permission'))

@section('content')
    @include('adminpermission::admin.permissions.partials.form', [
        'action' => route('admin.permissions.update', $permission),
        'method' => 'PUT',
    ])
@endsection
