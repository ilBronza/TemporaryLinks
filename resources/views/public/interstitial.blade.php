@extends('temporarylinks::public.layout')

@section('content')

	<h1>{{ $link->name }}</h1>

	<p>{{ trans('temporarylinks::messages.interstitialText') }}</p>

	<form method="POST" action="{{ route('temporarylinks.public.proceed', ['token' => $token]) }}">
		@csrf

		<button class="tl-button" type="submit">{{ trans('temporarylinks::messages.proceed') }}</button>
	</form>

@endsection
