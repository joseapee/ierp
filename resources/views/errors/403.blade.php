<x-layouts.app>
    @section('title', '403 — Access Restricted')

    <div class="row justify-content-center align-items-center" style="min-height: 65vh;">
        <div class="col-lg-6 col-md-8 col-11">
            <div class="card custom-card text-center border-0 shadow-sm">
                <div class="card-body p-5">

                    {{-- Icon --}}
                    <div class="mb-4">
                        <span class="avatar avatar-xxl bg-danger-transparent rounded-circle">
                            <i class="ri-lock-line fs-1 text-danger"></i>
                        </span>
                    </div>

                    {{-- Error code --}}
                    <h1 class="fw-bold text-danger mb-1" style="font-size: 3.5rem;">403</h1>

                    {{-- Message --}}
                    @php
                        $message = $exception->getMessage();
                        $isFeatureGate = str_contains($message, 'not available on your current plan');
                        $isPermission  = str_contains($message, 'do not have permission');
                    @endphp

                    <h4 class="fw-semibold mb-2">
                        @if($isFeatureGate)
                            Feature Not Available
                        @elseif($isPermission)
                            Permission Denied
                        @else
                            Access Restricted
                        @endif
                    </h4>

                    <p class="text-muted fs-15 mb-4">
                        {{ $message ?: 'You do not have access to this resource.' }}
                    </p>

                    {{-- Contextual hint --}}
                    @if($isFeatureGate)
                        <p class="text-muted fs-13 mb-4">
                            Upgrade your plan to unlock this feature and get more out of iERP.
                        </p>
                    @endif

                    {{-- Action buttons --}}
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        @if($isFeatureGate)
                            <a href="{{ route('billing.index') }}"
                               class="btn btn-primary btn-wave"
                               wire:navigate>
                                <i class="ri-vip-crown-line me-1"></i> Upgrade Plan
                            </a>
                        @endif

                        <a href="javascript:void(0);"
                           class="btn btn-outline-secondary btn-wave"
                           onclick="if (history.length > 1) { history.back(); } else { window.location.href = '{{ route('dashboard') }}'; }">
                            <i class="ri-arrow-left-line me-1"></i> Go Back
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
