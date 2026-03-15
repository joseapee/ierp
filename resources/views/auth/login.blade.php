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

            @if(session('error'))
                <div class="alert alert-danger py-2 mb-3">{{ session('error') }}</div>
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

            {{-- Social login divider --}}
            <div class="text-center my-3">
                <span class="text-muted fs-12">or sign in with</span>
            </div>

            {{-- Social login buttons --}}
            <div class="d-flex gap-2">
                <a href="{{ route('social.redirect', 'google') }}"
                   class="btn btn-outline-light border flex-fill d-flex align-items-center justify-content-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48">
                        <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4C12.955 4 4 12.955 4 24s8.955 20 20 20s20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                        <path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                        <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                        <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                    </svg>
                    <span class="fs-13">Google</span>
                </a>
                <a href="{{ route('social.redirect', 'apple') }}"
                   class="btn btn-outline-light border flex-fill d-flex align-items-center justify-content-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.05 20.28c-.98.95-2.05.88-3.08.4-1.09-.5-2.08-.48-3.24 0-1.44.62-2.2.44-3.06-.4C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                    </svg>
                    <span class="fs-13">Apple</span>
                </a>
            </div>

            {{-- Create account link --}}
            <div class="text-center mt-3 fw-medium fs-13">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-primary">Create Account</a>
            </div>

        </div>
    </div>
</x-layouts.auth>
