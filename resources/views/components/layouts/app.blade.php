<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $lang ?? app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="robots" content="noindex, follow" />

	<title>
		@if(isset($title))
			{{ $title . ' | ' }}
		@endif
		{{ config('app.name') }}
	</title>

	<x-layouts.partials.head />

	@stack('head')
</head>

<body class="font-sans antialiased {dark:bg-black dark:text-white/50}">

	@persist('header')
		@if(isset($header))
            {{ $header }}
        @else
			<x-layouts.partials.header />
		@endif
	@endpersist

	{{ $slot }}

	@persist('footer')
		@if(isset($footer))
            {{ $footer }}
        @else
			<x-layouts.partials.footer />
		@endif
	@endpersist

	@stack('initialScripts')
	<x-layouts.partials.scripts />
	@stack('finalScripts')

</body>

</html>
