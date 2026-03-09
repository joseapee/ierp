<x-layouts.auth>
    <div class="card custom-card border-0 my-4">
        <div class="card-body p-5">

            <div class="mb-4">
                <a href="{{ route('login') }}">
                    <img src="{{ asset('vyzor/images/brand-logos/toggle-logo.png') }}" alt="iERP" class="desktop-dark" style="height:40px;">
                </a>
            </div>

            <div class="mb-4">
                <h4 class="mb-1 fw-semibold">Reset Password</h4>
                <p class="mb-0 text-muted fw-normal">Choose a new password for your account.</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="row gy-3">

                    <div class="col-12">
                        <label for="email" class="form-label text-default">Email</label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email', $request->email) }}"
                               class="form-control @error('email') is-invalid @enderror"
                               required
                               autofocus
                               autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="password" class="form-label text-default">New Password</label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Enter new password"
                               required
                               autocomplete="new-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="password_confirmation" class="form-label text-default">Confirm Password</label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               class="form-control"
                               placeholder="Repeat new password"
                               required
                               autocomplete="new-password">
                    </div>

                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-wave">Reset Password</button>
                </div>

            </form>

        </div>
    </div>
</x-layouts.auth>
