<x-layouts.auth>
    <div class="card custom-card border-0 my-4">
        <div class="card-body p-5">

            <div class="mb-4">
                <a href="{{ route('login') }}">
                    <img src="{{ asset('vyzor/images/brand-logos/toggle-logo.png') }}" alt="iERP" class="desktop-dark" style="height:40px;">
                </a>
            </div>

            <div class="mb-4">
                <h4 class="mb-1 fw-semibold">Forgot Password?</h4>
                <p class="mb-0 text-muted fw-normal">Enter your email and we'll send you a reset link.</p>
            </div>

            @if(session('status'))
                <div class="alert alert-success py-2 mb-3">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="row gy-3">
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
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-wave">Email Password Reset Link</button>
                </div>

                <div class="text-center mt-3 fw-medium fs-13">
                    <a href="{{ route('login') }}" class="text-primary">Back to Sign In</a>
                </div>

            </form>

        </div>
    </div>
</x-layouts.auth>
