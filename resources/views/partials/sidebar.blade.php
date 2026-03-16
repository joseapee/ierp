<aside class="app-sidebar sticky" id="sidebar">

    {{-- Sidebar header / logo --}}
    <div class="main-sidebar-header">
        <a href="{{ route('dashboard') }}" class="header-logo" wire:navigate>
            <img src="{{ asset('vyzor/images/brand-logos/desktop-logo.png') }}" alt="iERP" class="desktop-logo">
            <img src="{{ asset('vyzor/images/brand-logos/toggle-dark.png') }}"  alt="iERP" class="toggle-dark">
            <img src="{{ asset('vyzor/images/brand-logos/desktop-dark.png') }}"  alt="iERP" class="desktop-dark">
            <img src="{{ asset('vyzor/images/brand-logos/toggle-logo.png') }}"  alt="iERP" class="toggle-logo">
        </a>
    </div>

    <div class="main-sidebar" id="sidebar-scroll">

        <nav class="main-menu-container nav nav-pills flex-column sub-open">

            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"/>
                </svg>
            </div>

            <ul class="main-menu">

                {{-- ── Main section ── --}}
                <li class="slide__category"><span class="category-name">Main</span></li>

                {{-- Dashboard --}}
                <li class="slide {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}"
                       class="side-menu__item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <path d="M133.66,34.34a8,8,0,0,0-11.32,0L40,116.69V216h64V152h48v64h64V116.69Z" opacity="0.2"/>
                            <line x1="16" y1="216" x2="240" y2="216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="152 216 152 152 104 152 104 216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <path d="M24,132.69l98.34-98.35a8,8,0,0,1,11.32,0L232,132.69" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>

                {{-- Billing --}}
                <li class="slide {{ request()->routeIs('billing.*') ? 'active' : '' }}">
                    <a href="{{ route('billing.index') }}"
                       class="side-menu__item {{ request()->routeIs('billing.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="24" y="56" width="208" height="144" rx="8" opacity="0.2"/>
                            <rect x="24" y="56" width="208" height="144" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="168" y1="168" x2="200" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="24" y1="96" x2="232" y2="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Billing</span>
                    </a>
                </li>

                {{-- ── Admin section ── --}}
                <li class="slide__category"><span class="category-name">Administration</span></li>

                {{-- User Management --}}
                @can('users.view')
                <li class="slide {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}"
                       class="side-menu__item {{ request()->routeIs('users.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <circle cx="80" cy="172" r="28" opacity="0.2"/>
                            <circle cx="176" cy="60" r="28" opacity="0.2"/>
                            <circle cx="80" cy="172" r="28" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <circle cx="176" cy="60" r="28" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="176" y1="88" x2="176" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <path d="M176,128H136a32,32,0,0,0-32,32v12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Users</span>
                    </a>
                </li>
                @endcan

                {{-- Role Management --}}
                @can('roles.view')
                <li class="slide {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                    <a href="{{ route('roles.index') }}"
                       class="side-menu__item {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="40" y="88" width="176" height="128" rx="8" opacity="0.2"/>
                            <rect x="40" y="88" width="176" height="128" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <path d="M88,88V56a40,40,0,0,1,80,0V88" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <circle cx="128" cy="152" r="16" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Roles</span>
                    </a>
                </li>
                @endcan

                {{-- ── Catalog section ── --}}
                @canany(['categories.view', 'brands.view', 'units.view', 'products.view'])
                <li class="slide__category"><span class="category-name">Catalog</span></li>

                @can('products.view')
                <li class="slide {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <a href="{{ route('products.index') }}"
                       class="side-menu__item {{ request()->routeIs('products.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <path d="M128,129.09V232l-88-48.18V80.18Z" opacity="0.2"/>
                            <path d="M216,183.82,128,232,40,183.82V80.18a8,8,0,0,1,4.09-7l80-43.63a16,16,0,0,1,17.82,0l80,43.63a8,8,0,0,1,4.09,7Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="128 232 128 128 40 80" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="216 80 128 128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Products</span>
                    </a>
                </li>
                @endcan

                @can('categories.view')
                <li class="slide {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <a href="{{ route('categories.index') }}"
                       class="side-menu__item {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="32" y="48" width="80" height="80" rx="8" opacity="0.2"/>
                            <rect x="144" y="48" width="80" height="80" rx="8" opacity="0.2"/>
                            <rect x="32" y="48" width="80" height="80" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <rect x="144" y="48" width="80" height="80" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <rect x="32" y="160" width="80" height="80" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <rect x="144" y="160" width="80" height="80" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Categories</span>
                    </a>
                </li>
                @endcan

                @can('brands.view')
                <li class="slide {{ request()->routeIs('brands.*') ? 'active' : '' }}">
                    <a href="{{ route('brands.index') }}"
                       class="side-menu__item {{ request()->routeIs('brands.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="60" y="24" width="136" height="208" rx="8" opacity="0.2"/>
                            <rect x="60" y="24" width="136" height="208" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <circle cx="128" cy="168" r="32" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="96" y1="72" x2="160" y2="72" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Brands</span>
                    </a>
                </li>
                @endcan

                @can('units.view')
                <li class="slide {{ request()->routeIs('units.*') ? 'active' : '' }}">
                    <a href="{{ route('units.index') }}"
                       class="side-menu__item {{ request()->routeIs('units.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <polyline points="24 184 128 120 232 184" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="128" y1="120" x2="128" y2="56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="104" y1="56" x2="152" y2="56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Units</span>
                    </a>
                </li>
                @endcan
                @endcanany

                {{-- ── Inventory section ── --}}
                @canany(['warehouses.view', 'stock.view', 'stock.adjust', 'stock.transfer'])
                <li class="slide__category"><span class="category-name">Inventory</span></li>

                @can('warehouses.view')
                <li class="slide {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
                    <a href="{{ route('warehouses.index') }}"
                       class="side-menu__item {{ request()->routeIs('warehouses.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <polygon points="240 200 128 40 16 200 240 200" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="128" y1="200" x2="128" y2="120" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="96 200 96 160 160 160 160 200" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Warehouses</span>
                    </a>
                </li>
                @endcan

                @can('stock.view')
                <li class="slide {{ request()->routeIs('stock.ledger') ? 'active' : '' }}">
                    <a href="{{ route('stock.ledger') }}"
                       class="side-menu__item {{ request()->routeIs('stock.ledger') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <polyline points="224 200 32 200 32 56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="32 168 96 112 144 152 224 72" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Stock Ledger</span>
                    </a>
                </li>
                @endcan

                @can('stock.adjust')
                <li class="slide {{ request()->routeIs('stock.adjustments.*') ? 'active' : '' }}">
                    <a href="{{ route('stock.adjustments.index') }}"
                       class="side-menu__item {{ request()->routeIs('stock.adjustments.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="48" y="48" width="160" height="160" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="96" y1="128" x2="160" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="128" y1="96" x2="128" y2="160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Adjustments</span>
                    </a>
                </li>
                @endcan

                @can('stock.transfer')
                <li class="slide {{ request()->routeIs('stock.transfers.*') ? 'active' : '' }}">
                    <a href="{{ route('stock.transfers.index') }}"
                       class="side-menu__item {{ request()->routeIs('stock.transfers.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <polyline points="96 48 176 128 96 208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="40" y1="128" x2="176" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Transfers</span>
                    </a>
                </li>
                @endcan
                @endcanany


                {{-- ── POS section ── --}}
                @can('pos.access')
                <li class="slide__category"><span class="category-name">POS</span></li>
                <li class="slide {{ request()->routeIs('pos.terminal') ? 'active' : '' }}">
                    <a href="{{ route('pos.terminal') }}"
                       class="side-menu__item {{ request()->routeIs('pos.terminal') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M7 18c-1.104 0-2-.896-2-2s.896-2 2-2 2 .896 2 2-.896 2-2 2zm10 0c-1.104 0-2-.896-2-2s.896-2 2-2 2 .896 2 2-.896 2-2 2zm-12-2c0 2.206 1.794 4 4 4h8c2.206 0 4-1.794 4-4v-8c0-2.206-1.794-4-4-4h-8c-2.206 0-4 1.794-4 4v8zm2-8c0-1.104.896-2 2-2h8c1.104 0 2 .896 2 2v8c0 1.104-.896 2-2 2h-8c-1.104 0-2-.896-2-2v-8zm2 2h8v2h-8v-2zm0 4h8v2h-8v-2z"/></svg>
                        <span class="side-menu__label">POS Terminal</span>
                    </a>
                </li>
                @endcan

                {{-- ── Sales section ── --}}
                @canany(['customers.view', 'sales-orders.view'])
                <li class="slide__category"><span class="category-name">Sales</span></li>

                @can('customers.view')
                <li class="slide {{ request()->routeIs('sales.customers.*') ? 'active' : '' }}">
                    <a href="{{ route('sales.customers.index') }}"
                       class="side-menu__item {{ request()->routeIs('sales.customers.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <circle cx="128" cy="96" r="64" opacity="0.2"/>
                            <circle cx="128" cy="96" r="64" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <path d="M32,224c0-52.94,43.06-96,96-96s96,43.06,96,96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Customers</span>
                    </a>
                </li>
                @endcan

                @can('sales-orders.view')
                <li class="slide {{ request()->routeIs('sales.orders.*') ? 'active' : '' }}">
                    <a href="{{ route('sales.orders.index') }}"
                       class="side-menu__item {{ request()->routeIs('sales.orders.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <path d="M32,64H224V192a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8Z" opacity="0.2"/>
                            <rect x="32" y="64" width="192" height="144" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="104" x2="176" y2="104" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="136" x2="176" y2="136" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="168" x2="128" y2="168" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Sales Orders</span>
                    </a>
                </li>
                @endcan
                @endcanany

                {{-- ── CRM section ── --}}
                @canany(['leads.view', 'crm-contacts.view', 'opportunities.view', 'crm-activities.view'])
                <li class="slide__category"><span class="category-name">CRM</span></li>

                @can('leads.view')
                <li class="slide {{ request()->routeIs('crm.leads.*') ? 'active' : '' }}">
                    <a href="{{ route('crm.leads.index') }}"
                       class="side-menu__item {{ request()->routeIs('crm.leads.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <circle cx="128" cy="120" r="40" opacity="0.2"/>
                            <circle cx="128" cy="120" r="40" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <path d="M63.8,199.37a72,72,0,0,1,128.4,0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="176 56 224 56 224 104" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="224" y1="56" x2="176" y2="104" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Leads</span>
                    </a>
                </li>
                @endcan

                @can('crm-contacts.view')
                <li class="slide {{ request()->routeIs('crm.contacts.*') ? 'active' : '' }}">
                    <a href="{{ route('crm.contacts.index') }}"
                       class="side-menu__item {{ request()->routeIs('crm.contacts.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <circle cx="108" cy="100" r="28" opacity="0.2"/>
                            <circle cx="108" cy="100" r="28" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <path d="M52,168c0-28,25.07-44,56-44s56,16,56,44" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <path d="M196,120h12a40,40,0,0,1,0,80H40a40,40,0,0,1,0-80H52" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Contacts</span>
                    </a>
                </li>
                @endcan

                @can('opportunities.view')
                <li class="slide {{ request()->routeIs('crm.opportunities.*') ? 'active' : '' }}">
                    <a href="{{ route('crm.opportunities.index') }}"
                       class="side-menu__item {{ request()->routeIs('crm.opportunities.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <path d="M128,24A104,104,0,1,0,232,128,104,104,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88,88,0,0,1,128,216Z" opacity="0.2"/>
                            <circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="128 72 128 128 184 128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Opportunities</span>
                    </a>
                </li>
                @endcan

                @can('opportunities.view')
                <li class="slide {{ request()->routeIs('crm.pipeline.*') ? 'active' : '' }}">
                    <a href="{{ route('crm.pipeline.index') }}"
                       class="side-menu__item {{ request()->routeIs('crm.pipeline.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="48" y="32" width="56" height="192" rx="8" opacity="0.2"/>
                            <rect x="48" y="32" width="56" height="192" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <rect x="152" y="32" width="56" height="128" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Pipeline</span>
                    </a>
                </li>
                @endcan

                @can('crm-activities.view')
                <li class="slide {{ request()->routeIs('crm.activities.*') ? 'active' : '' }}">
                    <a href="{{ route('crm.activities.index') }}"
                       class="side-menu__item {{ request()->routeIs('crm.activities.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="40" y="40" width="176" height="176" rx="8" opacity="0.2"/>
                            <rect x="40" y="40" width="176" height="176" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="96" x2="176" y2="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="128" x2="176" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="160" x2="128" y2="160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Activities</span>
                    </a>
                </li>
                @endcan

                @can('pipeline-stages.manage')
                <li class="slide {{ request()->routeIs('crm.pipeline.stages') ? 'active' : '' }}">
                    <a href="{{ route('crm.pipeline.stages') }}"
                       class="side-menu__item {{ request()->routeIs('crm.pipeline.stages') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <circle cx="128" cy="128" r="88" opacity="0.2"/>
                            <circle cx="128" cy="128" r="88" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="128 80 128 128 168 152" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Pipeline Stages</span>
                    </a>
                </li>
                @endcan
                @endcanany

                {{-- ── Procurement section ── --}}
                @canany(['suppliers.view', 'purchase-orders.view'])
                <li class="slide__category"><span class="category-name">Procurement</span></li>

                @can('suppliers.view')
                <li class="slide {{ request()->routeIs('procurement.suppliers.*') ? 'active' : '' }}">
                    <a href="{{ route('procurement.suppliers.index') }}"
                       class="side-menu__item {{ request()->routeIs('procurement.suppliers.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="32" y="64" width="192" height="144" rx="8" opacity="0.2"/>
                            <rect x="32" y="64" width="192" height="144" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="128" y1="24" x2="128" y2="64" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="80 48 128 24 176 48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Suppliers</span>
                    </a>
                </li>
                @endcan

                @can('purchase-orders.view')
                <li class="slide {{ request()->routeIs('procurement.purchase-orders.*') ? 'active' : '' }}">
                    <a href="{{ route('procurement.purchase-orders.index') }}"
                       class="side-menu__item {{ request()->routeIs('procurement.purchase-orders.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <path d="M184,32H72A16,16,0,0,0,56,48V224l36-24,36,24,36-24,36,24V48A16,16,0,0,0,184,32Z" opacity="0.2"/>
                            <path d="M184,32H72A16,16,0,0,0,56,48V224l36-24,36,24,36-24,36,24V48A16,16,0,0,0,184,32Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="96" y1="88" x2="160" y2="88" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="96" y1="120" x2="160" y2="120" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="96" y1="152" x2="128" y2="152" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Purchase Orders</span>
                    </a>
                </li>
                @endcan
                @endcanany

                {{-- ── Manufacturing section ── --}}
                @canany(['bom.view', 'production.view', 'production.manage'])
                <li class="slide__category"><span class="category-name">Manufacturing</span></li>

                @can('production.view')
                <li class="slide {{ request()->routeIs('manufacturing.orders.*') ? 'active' : '' }}">
                    <a href="{{ route('manufacturing.orders.index') }}"
                       class="side-menu__item {{ request()->routeIs('manufacturing.orders.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="32" y="64" width="192" height="144" rx="8" opacity="0.2"/>
                            <rect x="32" y="64" width="192" height="144" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="32" y1="112" x2="224" y2="112" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="128" y1="64" x2="128" y2="48" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Production Orders</span>
                    </a>
                </li>
                @endcan

                @can('production.view')
                <li class="slide {{ request()->routeIs('manufacturing.board') ? 'active' : '' }}">
                    <a href="{{ route('manufacturing.board') }}"
                       class="side-menu__item {{ request()->routeIs('manufacturing.board') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="48" y="32" width="56" height="192" rx="8" opacity="0.2"/>
                            <rect x="48" y="32" width="56" height="192" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <rect x="152" y="32" width="56" height="128" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Production Board</span>
                    </a>
                </li>
                @endcan

                @can('bom.view')
                <li class="slide {{ request()->routeIs('manufacturing.boms.*') ? 'active' : '' }}">
                    <a href="{{ route('manufacturing.boms.index') }}"
                       class="side-menu__item {{ request()->routeIs('manufacturing.boms.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <path d="M32,56H224a0,0,0,0,1,0,0V192a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V56A0,0,0,0,1,32,56Z" opacity="0.2"/>
                            <rect x="32" y="56" width="192" height="144" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="96" x2="176" y2="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="128" x2="176" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="160" x2="176" y2="160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Bill of Materials</span>
                    </a>
                </li>
                @endcan

                @can('production.manage')
                <li class="slide {{ request()->routeIs('manufacturing.stages.*') ? 'active' : '' }}">
                    <a href="{{ route('manufacturing.stages.index') }}"
                       class="side-menu__item {{ request()->routeIs('manufacturing.stages.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <circle cx="128" cy="128" r="88" opacity="0.2"/>
                            <circle cx="128" cy="128" r="88" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="128 80 128 128 168 152" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Stages</span>
                    </a>
                </li>
                @endcan
                @endcanany

                {{-- ── Accounting section ── --}}
                @canany(['accounts.view', 'journal.view', 'reports.view', 'fiscal-years.view'])
                <li class="slide__category"><span class="category-name">Accounting</span></li>

                @can('accounts.view')
                <li class="slide {{ request()->routeIs('accounting.accounts.*') ? 'active' : '' }}">
                    <a href="{{ route('accounting.accounts.index') }}"
                       class="side-menu__item {{ request()->routeIs('accounting.accounts.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="32" y="48" width="192" height="160" rx="8" opacity="0.2"/>
                            <rect x="32" y="48" width="192" height="160" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="32" y1="96" x2="224" y2="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="32" y1="144" x2="224" y2="144" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="128" y1="96" x2="128" y2="208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Chart of Accounts</span>
                    </a>
                </li>
                @endcan

                @can('journal.view')
                <li class="slide {{ request()->routeIs('accounting.journal-entries.*') ? 'active' : '' }}">
                    <a href="{{ route('accounting.journal-entries.index') }}"
                       class="side-menu__item {{ request()->routeIs('accounting.journal-entries.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <path d="M32,56H224a0,0,0,0,1,0,0V192a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V56A0,0,0,0,1,32,56Z" opacity="0.2"/>
                            <rect x="32" y="56" width="192" height="144" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="96" x2="176" y2="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="128" x2="176" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="160" x2="176" y2="160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Journal Entries</span>
                    </a>
                </li>
                @endcan

                @can('reports.view')
                <li class="slide {{ request()->routeIs('accounting.reports.*') ? 'active' : '' }}">
                    <a href="{{ route('accounting.reports.trial-balance') }}"
                       class="side-menu__item {{ request()->routeIs('accounting.reports.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <polyline points="224 200 32 200 32 56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="32 168 96 112 144 152 224 72" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Reports</span>
                    </a>
                </li>
                @endcan

                @can('fiscal-years.view')
                <li class="slide {{ request()->routeIs('accounting.fiscal-years.*') ? 'active' : '' }}">
                    <a href="{{ route('accounting.fiscal-years.index') }}"
                       class="side-menu__item {{ request()->routeIs('accounting.fiscal-years.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="40" y="40" width="176" height="176" rx="8" opacity="0.2"/>
                            <rect x="40" y="40" width="176" height="176" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="176" y1="24" x2="176" y2="56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="24" x2="80" y2="56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="40" y1="88" x2="216" y2="88" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Fiscal Years</span>
                    </a>
                </li>
                @endcan
                @endcanany

                {{-- Tenant Management (super admin only) --}}
                @if(auth()->user()->is_super_admin)
                <li class="slide__category"><span class="category-name">Super Admin</span></li>

                <li class="slide {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
                    <a href="{{ route('tenants.index') }}"
                       class="side-menu__item {{ request()->routeIs('tenants.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <path d="M24,48H88L120,176h112" opacity="0.2"/>
                            <rect x="152" y="56" width="80" height="72" rx="8" opacity="0.2"/>
                            <rect x="152" y="56" width="80" height="72" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <circle cx="192" cy="200" r="24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <circle cx="64" cy="200" r="24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="40" y1="200" x2="168" y2="200" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="88 176 232 176 232 128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="24 48 88 48 120 176" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Tenants</span>
                    </a>
                </li>

                {{-- Plans --}}
                <li class="slide {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.plans.index') }}"
                       class="side-menu__item {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="32" y="48" width="192" height="160" rx="8" opacity="0.2"/>
                            <rect x="32" y="48" width="192" height="160" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="96" x2="176" y2="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="128" x2="176" y2="128" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="160" x2="128" y2="160" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Plans</span>
                    </a>
                </li>

                {{-- Subscriptions --}}
                <li class="slide {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.subscriptions.index') }}"
                       class="side-menu__item {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}"
                       wire:navigate>
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 256 256">
                            <rect width="256" height="256" fill="none"/>
                            <rect x="40" y="40" width="176" height="176" rx="8" opacity="0.2"/>
                            <rect x="40" y="40" width="176" height="176" rx="8" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="176" y1="24" x2="176" y2="56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="80" y1="24" x2="80" y2="56" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <line x1="40" y1="88" x2="216" y2="88" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                            <polyline points="128 120 128 152 152 152" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/>
                        </svg>
                        <span class="side-menu__label">Subscriptions</span>
                    </a>
                </li>
                @endif

            </ul>

            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"/>
                </svg>
            </div>

        </nav>

    </div>

</aside>
