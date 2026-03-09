{{-- Bootstrap JS --}}
<script src="{{ asset('vyzor/libs/bootstrap/js/bootstrap.bundle.min.js') }}" data-navigate-once></script>

{{-- Vyzor default menu --}}
<script src="{{ asset('vyzor/js/defaultmenu.min.js') }}" data-navigate-once></script>

{{-- Node Waves JS --}}
<script src="{{ asset('vyzor/libs/node-waves/waves.min.js') }}" data-navigate-once></script>

{{-- Sticky JS --}}
<script src="{{ asset('vyzor/js/sticky.js') }}" data-navigate-once></script>

{{-- Simplebar JS --}}
<script src="{{ asset('vyzor/libs/simplebar/simplebar.min.js') }}" data-navigate-once></script>
<script src="{{ asset('vyzor/js/simplebar.js') }}" data-navigate-once></script>

{{-- Choices JS (enhanced selects) --}}
<script src="{{ asset('vyzor/libs/choices.js/public/assets/scripts/choices.min.js') }}" data-navigate-once></script>

{{-- Toastify JS --}}
<script src="{{ asset('vyzor/libs/toastify-js/src/toastify.js') }}" data-navigate-once></script>

@livewireScripts

@stack('scripts')

{{-- Scroll-to-top --}}
<div class="scrollToTop">
    <span class="arrow lh-1"><i class="ti ti-arrow-big-up fs-18"></i></span>
</div>
<div id="responsive-overlay"></div>

{{-- Mobile search modal --}}
<div class="modal fade" id="header-responsive-search" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="input-group">
                    <input type="text"
                           class="form-control border-end-0"
                           placeholder="Search..."
                           aria-label="Search">
                    <button class="btn btn-primary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script data-navigate-once>
    /**
     * Re-initialise Bootstrap tooltips on every Livewire SPA navigation.
     */
    function initTooltips() {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            var existing = bootstrap.Tooltip.getInstance(el);
            if (existing) {
                existing.dispose();
            }
            new bootstrap.Tooltip(el);
        });
    }

    document.addEventListener('DOMContentLoaded', initTooltips);
    document.addEventListener('livewire:navigated', initTooltips);

    // Re-initialise tooltips after Livewire morphs the DOM (e.g. wizard step change).
    Livewire.hook('morph.updated', function () {
        initTooltips();
    });

    /**
     * Listen for Livewire `toast` dispatches and show a Toastify notification.
     */
    document.addEventListener('livewire:initialized', function () {
        var colors = {
            success: '#28a745',
            error:   '#dc3545',
            warning: '#ffc107',
            info:    '#17a2b8',
        };

        Livewire.on('toast', function (data) {
            Toastify({
                text:      data.message,
                duration:  3500,
                close:     true,
                gravity:   'top',
                position:  'right',
                stopOnFocus: true,
                style: {
                    background: colors[data.type] || colors.info,
                },
            }).showToast();
        });
    });
</script>
