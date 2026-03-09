<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="ltr"
      data-nav-layout="vertical"
      data-theme-mode="light"
      data-header-styles="light"
      data-menu-styles="light"
      data-toggled="close"
      data-vertical-style="overlay">
<head>
    @include('partials.head')
</head>

<body>
    <div class="progress-top-bar"></div>

    <div class="page">

        {{-- Authenticated header --}}
        @include('partials.header')

        {{-- Sidebar navigation --}}
        @include('partials.sidebar')

        {{-- Main content --}}
        <div class="main-content app-content">
            <div class="container-fluid">

                {{ $slot }}

            </div>
        </div>

        {{-- Footer --}}
        <footer class="footer mt-auto py-3 text-center">
            <div class="container">
                <span class="text-muted fs-12">
                    Copyright &copy; {{ date('Y') }}
                    <span class="fw-medium text-dark">iERP</span>.
                    All rights reserved.
                </span>
            </div>
        </footer>

    </div>

    @include('partials.scripts')
    <x-toast-container />
</body>
</html>
