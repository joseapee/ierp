<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="ltr"
      data-nav-layout="vertical"
      data-vertical-style="overlay"
      data-theme-mode="light"
      data-header-styles="light"
      data-menu-styles="light"
      data-toggled="close">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'iERP') }} — Setup</title>

    {{-- Bootstrap CSS --}}
    <link id="style" href="{{ asset('vyzor/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Vyzor styles --}}
    <link href="{{ asset('vyzor/css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('vyzor/css/icons.css') }}" rel="stylesheet">

    @livewireStyles
    @stack('styles')
</head>

<body class="authentication-background">

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-xxl-6 col-xl-7 col-lg-8 col-md-10 col-12">

                {{ $slot }}

            </div>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="{{ asset('vyzor/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
