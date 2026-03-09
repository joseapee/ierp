@props([
    'title'       => '',
    'breadcrumbs' => [],
])
{{--
Usage:
    <x-page-header title="User Management" :breadcrumbs="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Users'],
    ]" />
--}}
<div class="d-md-flex d-block align-items-center justify-content-between py-3 mb-4">
    <div>
        <h3 class="page-title mb-1">{{ $title }}</h3>
        @if(count($breadcrumbs))
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                @foreach($breadcrumbs as $crumb)
                    @if(!$loop->last)
                    <li class="breadcrumb-item">
                        @if(isset($crumb['route']))
                            <a href="{{ route($crumb['route']) }}" wire:navigate>{{ $crumb['label'] }}</a>
                        @else
                            {{ $crumb['label'] }}
                        @endif
                    </li>
                    @else
                    <li class="breadcrumb-item active" aria-current="page">{{ $crumb['label'] }}</li>
                    @endif
                @endforeach
            </ol>
        </nav>
        @endif
    </div>
    @if(isset($actions))
    <div class="ms-auto mt-2 mt-md-0">
        {{ $actions }}
    </div>
    @endif
</div>
