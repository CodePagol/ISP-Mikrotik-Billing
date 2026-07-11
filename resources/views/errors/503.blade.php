@extends('errors.layout')
@section('code', '503')
@section('title', 'Under Maintenance')
@section('icon', '🔧')
@section('heading', 'We\'ll Be Back Soon')
@section('message')
    @if(!empty($exception) && $exception->getMessage())
        {{ $exception->getMessage() }}
    @else
        We are currently performing scheduled maintenance. Please check back shortly.
    @endif
@endsection
