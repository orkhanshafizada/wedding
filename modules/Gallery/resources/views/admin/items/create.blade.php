@extends('admin.layouts.app')

@section('content')
    @include('gallery::admin.items.form', [
        'isEdit' => false,
        'formAction' => route('admin.gallery.items.store', [$menu, $album]),
        'formMethod' => 'POST',
        'pageTitle' => __('Create item'),
        'submitLabel' => __('Create item'),
    ])
@endsection
