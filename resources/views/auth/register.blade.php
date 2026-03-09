<x-layouts.auth>
    <div class="card custom-card border-0 my-4">
        <div class="card-body p-5">

            <div class="mb-4">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('vyzor/images/brand-logos/toggle-logo.png') }}" alt="iERP" class="desktop-dark" style="height:40px;">
                </a>
            </div>

            <div class="mb-4">
                <h4 class="mb-1 fw-semibold">Create an account</h4>
                <p class="mb-0 text-muted fw-normal">Fill in the details below to register.</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="row gy-3">

                    {{-- Name --}}
                    <div class="col-12">
                        <label for="name" class="form-label text-default">Full Name</label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Enter your full name"
                               required
                               autofocus
                               autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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
                               autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="col-12">
                        <label for="password" class="form-label text-default">Password</label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Create a password"
                               required
                               autocomplete="new-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="col-12">
                        <label for="password_confirmation" class="form-label text-default">Confirm Password</label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               class="form-control"
                               placeholder="Repeat your password"
                               required
                               autocomplete="new-password">
                    </div>

                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-wave">Register</button>
                </div>

                <div class="text-center mt-3 fw-medium fs-13">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-primary">Sign In</a>
                </div>

            </form>

        </div>
    </div>
</x-layouts.auth>
