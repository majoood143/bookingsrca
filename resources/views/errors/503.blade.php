@extends('errors.layout')

@section('code', '503')
@section('title', __('errors.503.title'))
@section('heading', __('errors.503.heading'))
@section('description', __('errors.503.description'))

@section('actions')
    <a href="javascript:window.location.reload()" class="btn btn-primary">{{ __('errors.refresh') }}</a>
@endsection
