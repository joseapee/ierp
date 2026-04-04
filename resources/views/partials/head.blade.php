<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name', 'iERP') }}@hasSection('title') — @yield('title')@endif</title>

{{-- Vyzor theme config must load before CSS --}}
<script src="{{ asset('vyzor/js/main.js') }}" data-navigate-once></script>

{{-- Restore dark mode from localStorage on every navigation to prevent flash --}}
<script>
    (function () {
        if (localStorage.getItem('vyzordarktheme')) {
            var h = document.documentElement;
            h.setAttribute('data-theme-mode', 'dark');
            h.setAttribute('data-header-styles', localStorage.getItem('vyzorHeader') || 'transparent');
            h.setAttribute('data-menu-styles', localStorage.getItem('vyzorMenu') || 'transparent');
        }
    })();
</script>

{{-- Bootstrap CSS --}}
<link id="style" href="{{ asset('vyzor/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

{{-- Vyzor styles --}}
<link href="{{ asset('vyzor/css/styles.css') }}" rel="stylesheet">
<link href="{{ asset('vyzor/css/icons.css') }}" rel="stylesheet">

{{-- Node Waves CSS --}}
<link href="{{ asset('vyzor/libs/node-waves/waves.min.css') }}" rel="stylesheet">

{{-- Simplebar CSS --}}
<link href="{{ asset('vyzor/libs/simplebar/simplebar.min.css') }}" rel="stylesheet">

{{-- Choices CSS --}}
<link href="{{ asset('vyzor/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">

{{-- Toastify CSS --}}
<link href="{{ asset('vyzor/libs/toastify-js/src/toastify.css') }}" rel="stylesheet">

{{-- Header search dropdown styles --}}
<style>
    [data-theme-mode="dark"] .header-search-dropdown {
        background-color: var(--custom-white) !important;
        border-color: var(--default-border) !important;
    }
    [data-theme-mode="dark"] .header-search-dropdown .text-dark {
        color: #fff !important;
    }
    .header-search-dropdown .search-result-item:hover {
        background-color: rgba(var(--primary-rgb), 0.1);
    }
    [x-cloak] { display: none !important; }
</style>

@livewireStyles

@stack('styles')
