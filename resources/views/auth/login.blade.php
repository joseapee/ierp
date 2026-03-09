<x-layouts.auth>
    <div class="card custom-card border-0 my-4">
        <div class="card-body p-5">

            <div class="mb-4">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('vyzor/images/brand-logos/toggle-logo.png') }}" alt="iERP" class="desktop-dark" style="height:40px;">
                </a>
            </div>

            <div class="mb-4">
                <h4 class="mb-1 fw-semibold">Hi, Welcome back!</h4>
                <p class="mb-0 text-muted fw-normal">Please enter your credentials to sign in.</p>
            </div>

            {{-- Session status (e.g. password-reset success) --}}
            @if(session('status'))
                <div class="alert alert-success py-2 mb-3">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="row gy-3">

                    {{-- Email --}}
                    <div class="col-12">
                        <label for="email" class="form-label text-default">Email</label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="Enter your email"
                               required
                               autofocus
                               autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="col-12 mb-2">
                        <label for="password" class="form-label text-default d-block">Password</label>
                        <div class="position-relative">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Enter your password"
                                   required
                                   autocomplete="current-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-2 d-flex align-items-center justify-content-between">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                                <label class="form-check-label" for="remember_me">Remember me</label>
                            </div>
                            @if(Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="link-danger fw-medium fs-12">
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                    </div>

                </div>

                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-primary btn-wave">Sign In</button>
                </div>

            </form>

        </div>
    </div>
</x-layouts.auth>
