@extends('temporarylinks::public.layout')

@section('content')

	<h1>{{ trans('temporarylinks::messages.errorTitle') }}</h1>

	<p class="tl-error">{{ $message }}</p>

@endsection
