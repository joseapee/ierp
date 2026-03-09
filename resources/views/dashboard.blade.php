<x-layouts.app>
    @section('title', 'Dashboard')

    <x-page-header title="Dashboard" :breadcrumbs="[
        ['label' => 'Dashboard'],
    ]" />

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-3">Welcome back, {{ auth()->user()->name }}!</h5>
                    <p class="text-muted mb-0">You're logged in to iERP. Use the sidebar to navigate between modules.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
