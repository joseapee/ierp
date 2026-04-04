<header class="app-header sticky" id="header">
    <div class="main-header-container container-fluid">

        {{-- Left side: logo + sidebar toggle + search --}}
        <div class="header-content-left">

            <div class="header-element">
                <div class="horizontal-logo">
                    <a href="{{ route('dashboard') }}" class="header-logo" wire:navigate>
                        <img src="{{ asset('vyzor/images/brand-logos/desktop-logo.png') }}" alt="iERP" class="desktop-logo">
                        <img src="{{ asset('vyzor/images/brand-logos/toggle-logo.png') }}"  alt="iERP" class="toggle-logo">
                        <img src="{{ asset('vyzor/images/brand-logos/desktop-dark.png') }}"  alt="iERP" class="desktop-dark">
                        <img src="{{ asset('vyzor/images/brand-logos/toggle-dark.png') }}"  alt="iERP" class="toggle-dark">
                    </a>
                </div>
            </div>

            <div class="header-element mx-lg-0 mx-2">
                <a aria-label="Hide Sidebar"
                   class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                   data-bs-toggle="sidebar"
                   href="javascript:void(0);">
                    <span></span>
                </a>
            </div>

            <div class="header-element header-search header-search-content d-md-block d-none"
                 x-data="headerSearch()"
                 @click.outside="close()"
                 @keydown.escape.window="close()">
                <input type="text"
                       class="header-search-bar form-control bg-white"
                       id="header-search"
                       placeholder="Search pages & features..."
                       spellcheck="false"
                       autocomplete="off"
                       x-model="query"
                       @input="search()"
                       @focus="open = query.length > 0"
                       @keydown.arrow-down.prevent="moveDown()"
                       @keydown.arrow-up.prevent="moveUp()"
                       @keydown.enter.prevent="goToSelected()">
                <a href="javascript:void(0);" class="header-search-icon border-0">
                    <i class="bi bi-search fs-12 mb-1"></i>
                </a>

                {{-- Search results dropdown --}}
                <div class="header-search-dropdown position-absolute bg-white border rounded-3 shadow-lg mt-1 w-100"
                     x-show="open && results.length > 0"
                     x-cloak
                     style="z-index: 1050; max-height: 360px; overflow-y: auto; top: 100%; left: 0;">
                    <template x-for="(item, index) in results" :key="item.url">
                        <a :href="item.url"
                           class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none border-bottom search-result-item"
                           :class="{ 'bg-primary-transparent': selectedIndex === index }"
                           @mouseenter="selectedIndex = index"
                           wire:navigate>
                            <i class="ri-arrow-right-s-line text-muted fs-16"></i>
                            <div>
                                <span class="d-block fw-medium fs-14 text-dark" x-text="item.label"></span>
                                <span class="d-block fs-12 text-muted" x-text="item.category"></span>
                            </div>
                        </a>
                    </template>
                </div>

                {{-- No results --}}
                <div class="header-search-dropdown position-absolute bg-white border rounded-3 shadow-lg mt-1 w-100"
                     x-show="open && query.length > 0 && results.length === 0"
                     x-cloak
                     style="z-index: 1050; top: 100%; left: 0;">
                    <div class="px-3 py-3 text-center text-muted fs-13">
                        <i class="ri-search-line fs-20 d-block mb-1"></i>
                        No pages found
                    </div>
                </div>
            </div>

        </div>
        {{-- /Left side --}}

        {{-- Right side --}}
        <ul class="header-content-right">

            {{-- Mobile search toggle --}}
            <li class="header-element d-md-none d-block">
                <a href="javascript:void(0);"
                   class="header-link"
                   data-bs-toggle="modal"
                   data-bs-target="#header-responsive-search">
                    <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" viewBox="0 0 256 256">
                        <rect width="256" height="256" fill="none"/>
                        <circle cx="112" cy="112" r="80" opacity="0.2"/>
                        <circle cx="112" cy="112" r="80" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        <line x1="168.57" y1="168.57" x2="224" y2="224" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                    </svg>
                </a>
            </li>

            {{-- Dark / light mode toggle --}}
            <li class="header-element header-theme-mode">
                <a href="javascript:void(0);" class="header-link layout-setting">
                    <span class="light-layout">
                        <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <path d="M108.11,28.11A96.09,96.09,0,0,0,227.89,147.89,96,96,0,1,1,108.11,28.11Z" opacity="0.2"/>
                            <path d="M108.11,28.11A96.09,96.09,0,0,0,227.89,147.89,96,96,0,1,1,108.11,28.11Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                    </span>
                    <span class="dark-layout">
                        <svg xmlns="http://www.w3.org/2000/svg" class="header-link-icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <circle cx="128" cy="128" r="56" opacity="0.2"/>
                            <line x1="128" y1="40" x2="128" y2="32" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <circle cx="128" cy="128" r="56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="64" y1="64" x2="56" y2="56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="40" y1="128" x2="32" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="128" y1="216" x2="128" y2="224" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="216" y1="128" x2="224" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                    </span>
                </a>
            </li>

            {{-- Profile dropdown --}}
            <li class="header-element dropdown">
                <a href="javascript:void(0);"
                   class="header-link dropdown-toggle"
                   id="mainHeaderProfile"
                   data-bs-toggle="dropdown"
                   aria-expanded="false">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}"
                             alt="{{ auth()->user()->name }}"
                             class="header-link-icon rounded-circle"
                             style="width:32px;height:32px;object-fit:cover;">
                    @else
                        <span class="avatar avatar-sm avatar-rounded bg-primary-transparent">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                    @endif
                </a>

                <div class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
                     aria-labelledby="mainHeaderProfile">
                    <div class="p-3 bg-primary text-fixed-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0 fs-16">Profile</p>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="p-3">
                        <div class="d-flex align-items-start gap-2">
                            <div>
                                <span class="d-block fw-semibold lh-1">{{ auth()->user()->name }}</span>
                                <span class="text-muted fs-12">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <ul class="list-unstyled mb-0">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{ route('logout') }}"
                                   class="dropdown-item d-flex align-items-center"
                                   onclick="event.preventDefault(); this.closest('form').submit();">
                                    <i class="ti ti-logout me-2 fs-18"></i>
                                    Log Out
                                </a>
                            </form>
                        </li>
                    </ul>
                </div>
            </li>

        </ul>
        {{-- /Right side --}}

    </div>
</header>
