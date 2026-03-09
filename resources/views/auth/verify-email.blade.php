<x-layouts.auth>
    <div class="card custom-card border-0 my-4">
        <div class="card-body p-5">

            <div class="mb-4">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('vyzor/images/brand-logos/toggle-logo.png') }}" alt="iERP" class="desktop-dark" style="height:40px;">
                </a>
            </div>

            <div class="mb-4">
                <h4 class="mb-1 fw-semibold">Verify Your Email</h4>
                <p class="mb-0 text-muted fw-normal">Thanks for signing up! Please verify your email address by clicking the link we just sent you.</p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success py-2 mb-3">
                    A new verification link has been sent to the email address you provided during registration.
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-between mt-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-wave">Resend Verification Email</button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-wave">Log Out</button>
                </form>
            </div>

        </div>
    </div>
</x-layouts.auth>
