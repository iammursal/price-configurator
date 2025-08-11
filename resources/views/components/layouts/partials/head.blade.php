{{-- Meta --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="">

{{-- Favicon --}}
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

{{-- Fonts --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
	href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap"
	rel="stylesheet">

{{-- CSS --}}
@vite('resources/css/app.css')
@livewireStyles
