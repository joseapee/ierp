---
description: "Use when creating or editing UI components, Blade views, Livewire views, layouts, pages, modals, forms, tables, or any front-end markup. Covers Vyzor template conventions, CSS classes, JS libraries, and component patterns."
applyTo: ["resources/views/**", "app/Livewire/**", "app/View/**"]
---

# Vyzor Template UI Guidelines

All UI must follow the Vyzor admin dashboard template located in `public/vyzor/`. Only invent custom CSS classes or layout structures when absolutely necessary — use what Vyzor provides.

## Layout System

- Authenticated pages use the `<x-layouts.app>` component (`resources/views/components/layouts/app.blade.php`)
- Auth pages (login/register) use `<x-layouts.auth>`
- Onboarding/setup pages use `<x-layouts.setup>`
- The main layout provides: `.page` > `partials.header` + `partials.sidebar` + `.main-content.app-content` > `.container-fluid` > `{{ $slot }}`

## Page Structure Pattern

Every Livewire page view should follow this structure:

```blade
<div>
  @section('title', 'Page Title')

  <x-page-header title="Page Title" :breadcrumbs="[
    ['label' => 'Dashboard', 'route' => 'dashboard'],
    ['label' => 'Current Page'],
  ]">
    <x-slot:actions>
      {{-- Action buttons here --}}
    </x-slot:actions>
  </x-page-header>

  {{-- Content in cards --}}
  <div class="card custom-card">
    <div class="card-header">...</div>
    <div class="card-body">...</div>
    <div class="card-footer">...</div>
  </div>
</div>
```

## CSS Classes & Components

| Element | Classes |
|---------|---------|
| Content cards | `.card.custom-card` |
| Buttons | `.btn.btn-{variant}.btn-wave` (btn-wave adds ripple) |
| Tables | `.table.table-hover.text-nowrap` inside `.table-responsive` |
| Form inputs | `.form-control`, `.form-select`, `.form-check-input` |
| Badges | `.badge.bg-{variant}-transparent` |
| Typography | `.fs-12` to `.fs-20`, `.fw-semibold`, `.fw-medium` |

## Icons

Use icon classes from the bundled libraries:
- **RemixIcon** (preferred): `ri-add-line`, `ri-edit-line`, `ri-delete-bin-line`, etc.
- **Bootstrap Icons**: `bi-*`
- **Tabler Icons**: `ti ti-*`

Always add `me-1` when an icon precedes text: `<i class="ri-add-line me-1"></i> Add Item`

## Modals

Use the existing `<x-modal>` component or the inline pattern:

```blade
@if($showModal)
<div class="modal fade show d-block" style="background:rgba(0,0,0,0.5)">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">...</div>
      <div class="modal-body">...</div>
      <div class="modal-footer">...</div>
    </div>
  </div>
</div>
@endif
```

## JavaScript & Libraries

- All JS/CSS assets are loaded from `public/vyzor/` via `{{ asset('vyzor/...') }}`
- Use `@stack('styles')` and `@stack('scripts')` in views to push page-specific assets
- Available libraries in `vyzor/libs/`: ApexCharts, Chart.js, Choices.js, Flatpickr, FullCalendar, Quill, SimpleMasonry, SortableJS, SweetAlert2, Toastify, and more
- Scripts use `data-navigate-once` attribute to prevent reloading during Livewire SPA navigation

## Sidebar Navigation

Menu items in `partials/sidebar.blade.php` follow this structure:

```blade
<li class="slide__category"><span class="category-name">Section</span></li>
<li class="slide {{ request()->routeIs('route.name*') ? 'active' : '' }}">
  <a href="{{ route('route.name') }}"
     class="side-menu__item {{ request()->routeIs('route.name*') ? 'active' : '' }}"
     wire:navigate>
    <svg class="side-menu__icon">...</svg>
    <span class="side-menu__label">Label</span>
  </a>
</li>
```

## Do Not

- Do not use Tailwind CSS classes — this project uses Bootstrap 5 via Vyzor
- Do not add CDN links — all libraries are bundled in `public/vyzor/libs/`
- Do not create custom CSS files unless absolutely necessary
- Do not bypass the layout component system (always use `<x-layouts.*>`)
- Do not use `@extends` — use Blade component layouts (`<x-layouts.app>`)
