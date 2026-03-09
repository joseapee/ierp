@props([
    'id'      => 'confirmModal',
    'title'   => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'event'   => 'confirmed',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true"
     x-data="{ payload: null }"
     @open-confirm.window="payload = $event.detail; new bootstrap.Modal(document.getElementById('{{ $id }}')).show()">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" x-text="payload?.message ?? '{{ $message }}'"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button"
                        class="btn btn-danger"
                        data-bs-dismiss="modal"
                        @click="$dispatch('livewire:emit', { event: '{{ $event }}', params: [payload] }); $wire.dispatch('{{ $event }}', payload)">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>
