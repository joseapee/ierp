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
            <div class="modal-body" x-data="headerSearch()" @keydown.escape.window="close()">
                <div class="input-group mb-2">
                    <input type="text"
                           class="form-control border-end-0"
                           placeholder="Search pages & features..."
                           aria-label="Search"
                           x-model="query"
                           @input="search()"
                           @keydown.arrow-down.prevent="moveDown()"
                           @keydown.arrow-up.prevent="moveUp()"
                           @keydown.enter.prevent="goToSelected()">
                    <button class="btn btn-primary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <div style="max-height: 300px; overflow-y: auto;">
                    <template x-for="(item, index) in results" :key="item.url">
                        <a :href="item.url"
                           class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none border-bottom search-result-item"
                           :class="{ 'bg-primary-transparent': selectedIndex === index }"
                           @mouseenter="selectedIndex = index"
                           wire:navigate>
                            <i class="ri-arrow-right-s-line text-muted fs-16"></i>
                            <div>
                                <span class="d-block fw-medium fs-14" x-text="item.label"></span>
                                <span class="d-block fs-12 text-muted" x-text="item.category"></span>
                            </div>
                        </a>
                    </template>
                    <div x-show="query.length > 0 && results.length === 0" class="px-3 py-3 text-center text-muted fs-13">
                        <i class="ri-search-line fs-20 d-block mb-1"></i>
                        No pages found
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script data-navigate-once>
    /**
     * Re-bind the sidebar toggle button after Livewire SPA navigation.
     *
     * defaultmenu.min.js runs once (data-navigate-once) and captures DOM
     * references at load time. After wire:navigate the header is replaced
     * so the click listener on .sidemenu-toggle is lost.
     */
    function initSidemenuToggle() {
        var btn = document.querySelector('.sidemenu-toggle');
        if (!btn || typeof toggleSidemenu !== 'function') {
            return;
        }
        // Refresh the mainContentDiv reference used by defaultmenu.min.js
        mainContentDiv = document.querySelector('.main-content');

        // Replace the node to strip any stale listeners, then attach fresh one
        var clone = btn.cloneNode(true);
        btn.parentNode.replaceChild(clone, btn);
        clone.addEventListener('click', toggleSidemenu);

        // Re-bind the main-content click → close sidebar on mobile
        if (mainContentDiv) {
            if (window.innerWidth <= 992) {
                mainContentDiv.addEventListener('click', menuClose);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', initSidemenuToggle);
    document.addEventListener('livewire:navigated', function () {
        initSidemenuToggle();

        // Auto-close the sidebar on mobile after navigating to a new page
        if (window.innerWidth < 992) {
            document.documentElement.setAttribute('data-toggled', 'close');
            var overlay = document.querySelector('#responsive-overlay');
            if (overlay) {
                overlay.classList.remove('active');
            }
        }
    });

    /**
     * Restore dark/light mode from localStorage.
     * Called on every Livewire navigation because the server always sends
     * data-theme-mode="light" on the <html> tag and main.js only runs once.
     */
    function restoreThemeFromStorage() {
        var html = document.documentElement;
        if (localStorage.getItem('vyzordarktheme')) {
            html.setAttribute('data-theme-mode', 'dark');
            html.setAttribute('data-header-styles', localStorage.getItem('vyzorHeader') || 'transparent');
            html.setAttribute('data-menu-styles', localStorage.getItem('vyzorMenu') || 'transparent');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        restoreThemeFromStorage();
        initThemeToggle();
    });
    document.addEventListener('livewire:navigated', function () {
        restoreThemeFromStorage();
        initThemeToggle();
    });

    function initThemeToggle() {
        document.querySelectorAll('.layout-setting').forEach(function (btn) {
            // Remove old listeners by replacing node
            var clone = btn.cloneNode(true);
            btn.parentNode.replaceChild(clone, btn);

            clone.addEventListener('click', function (e) {
                e.preventDefault();
                var html = document.documentElement;
                var isDark = html.getAttribute('data-theme-mode') === 'dark';

                if (isDark) {
                    html.setAttribute('data-theme-mode', 'light');
                    html.setAttribute('data-header-styles', 'light');
                    html.setAttribute('data-menu-styles', 'light');
                    localStorage.removeItem('vyzordarktheme');
                    localStorage.removeItem('vyzorHeader');
                    localStorage.removeItem('vyzorMenu');
                } else {
                    html.setAttribute('data-theme-mode', 'dark');
                    html.setAttribute('data-header-styles', 'transparent');
                    html.setAttribute('data-menu-styles', 'transparent');
                    localStorage.setItem('vyzordarktheme', 'true');
                    localStorage.setItem('vyzorHeader', 'transparent');
                    localStorage.setItem('vyzorMenu', 'transparent');
                }
            });
        });
    }

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

{{-- Header search Alpine component --}}
<script data-navigate-once>
    document.addEventListener('alpine:init', function () {
        Alpine.data('headerSearch', function () {
            return {
                query: '',
                results: [],
                open: false,
                selectedIndex: 0,
                pages: [],

                init() {
                    this.pages = this.buildPageIndex();
                },

                buildPageIndex() {
                    var items = [];
                    var menuItems = document.querySelectorAll('#sidebar .slide:not(.slide__category)');
                    menuItems.forEach(function (li) {
                        var link = li.querySelector('.side-menu__item');
                        var label = li.querySelector('.side-menu__label');
                        if (link && label) {
                            var category = '';
                            var prev = li.previousElementSibling;
                            while (prev) {
                                if (prev.classList.contains('slide__category')) {
                                    var catName = prev.querySelector('.category-name');
                                    if (catName) {
                                        category = catName.textContent.trim();
                                    }
                                    break;
                                }
                                prev = prev.previousElementSibling;
                            }
                            items.push({
                                label: label.textContent.trim(),
                                url: link.getAttribute('href'),
                                category: category
                            });
                        }
                    });
                    return items;
                },

                search() {
                    var q = this.query.toLowerCase().trim();
                    if (q.length === 0) {
                        this.results = [];
                        this.open = false;
                        return;
                    }
                    var words = q.split(/\s+/);
                    this.results = this.pages.filter(function (page) {
                        var text = (page.label + ' ' + page.category).toLowerCase();
                        return words.every(function (w) { return text.indexOf(w) !== -1; });
                    });
                    this.selectedIndex = 0;
                    this.open = true;
                },

                close() {
                    this.open = false;
                },

                moveDown() {
                    if (this.selectedIndex < this.results.length - 1) {
                        this.selectedIndex++;
                    }
                },

                moveUp() {
                    if (this.selectedIndex > 0) {
                        this.selectedIndex--;
                    }
                },

                goToSelected() {
                    if (this.results.length > 0 && this.results[this.selectedIndex]) {
                        window.location.href = this.results[this.selectedIndex].url;
                        this.close();
                    }
                }
            };
        });
    });
</script>
