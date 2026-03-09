<x-layouts.auth>
    <div class="card custom-card border-0 my-4">
        <div class="card-body p-5">

            <div class="mb-4">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('vyzor/images/brand-logos/toggle-logo.png') }}" alt="iERP" class="desktop-dark" style="height:40px;">
                </a>
            </div>

            <div class="mb-4">
                <h4 class="mb-1 fw-semibold">Confirm Password</h4>
                <p class="mb-0 text-muted fw-normal">This is a secure area. Please confirm your password before continuing.</p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="row gy-3">
                    <div class="col-12">
                        <label for="password" class="form-label text-default">Password</label>
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
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-wave">Confirm</button>
                </div>

            </form>

        </div>
    </div>
</x-layouts.auth>
