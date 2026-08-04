@extends('admin.layouts.app')

@section('content')
    @include('gallery::admin.items.form', [
        'isEdit' => true,
        'formAction' => route('admin.gallery.items.update', [$menu, $album, $item]),
        'formMethod' => 'PUT',
        'pageTitle' => __('Edit item'),
        'submitLabel' => __('Update item'),
    ])
@endsection
