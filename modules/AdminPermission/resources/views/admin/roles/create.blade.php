@extends('admin.layouts.app')

@section('title', __('Add role'))

@section('content')
    @include('adminpermission::admin.roles.partials.form', [
        'action' => route('admin.roles.store'),
        'method' => null,
    ])
@endsection
