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
    <title>{{ config('app.name', 'iERP') }}@hasSection('title') — @yield('title')@endif</title>

    {{-- Bootstrap CSS --}}
    <link id="style" href="{{ asset('vyzor/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Vyzor styles --}}
    <link href="{{ asset('vyzor/css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('vyzor/css/icons.css') }}" rel="stylesheet">

    @livewireStyles
    @stack('styles')
</head>

<body class="authentication-background">

    <div class="authentication-basic-background">
        <img src="{{ asset('vyzor/images/media/backgrounds/9.png') }}" alt="">
    </div>

    <div class="container">
        <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
            <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-6 col-sm-8 col-12">

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
