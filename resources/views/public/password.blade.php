@extends('temporarylinks::public.layout')

@section('content')

	<h1>{{ $link->name }}</h1>

	<p>{{ trans('temporarylinks::messages.passwordRequired') }}</p>

	<form method="POST" action="{{ route('temporarylinks.public.password', ['token' => $token]) }}">
		@csrf

		<input class="tl-input" type="password" name="password" required autofocus>

		@error('password')
			<p class="tl-error">{{ $message }}</p>
		@enderror

		<button class="tl-button" type="submit">{{ trans('temporarylinks::messages.confirm') }}</button>
	</form>

@endsection
