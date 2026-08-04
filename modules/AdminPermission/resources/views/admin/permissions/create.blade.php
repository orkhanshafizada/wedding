@extends('admin.layouts.app')

@section('title', __('Add permission'))

@section('content')
    @include('adminpermission::admin.permissions.partials.form', [
        'action' => route('admin.permissions.store'),
        'method' => null,
    ])
@endsection
