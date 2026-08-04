@extends('admin.layouts.app')

@section('title', __('Add admin'))

@section('content')
    @include('adminpermission::admin.admins.partials.form', [
        'action' => route('admin.admins.store'),
        'method' => null,
    ])
@endsection
