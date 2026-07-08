<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title>{{ config('app.name') }}</title>
	<style>
		body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f4f6; margin: 0; display: flex; min-height: 100vh; align-items: center; justify-content: center; }
		.tl-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.08); padding: 2.5rem; max-width: 420px; width: 100%; margin: 1rem; text-align: center; }
		.tl-card h1 { font-size: 1.25rem; margin: 0 0 1rem; }
		.tl-card p { color: #555; line-height: 1.5; }
		.tl-error { color: #c0392b; }
		.tl-input { width: 100%; box-sizing: border-box; padding: .6rem .8rem; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem; margin-bottom: 1rem; }
		.tl-button { display: inline-block; background: #1e87f0; color: #fff; border: none; border-radius: 6px; padding: .7rem 2rem; font-size: 1rem; cursor: pointer; }
		.tl-button:hover { background: #1670cc; }
	</style>
</head>
<body>
	<div class="tl-card">
		@yield('content')
	</div>
</body>
</html>
