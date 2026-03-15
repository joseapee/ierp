<div>
    {{-- Progress bar --}}
    <div class="card mb-4">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fs-12 text-muted">Step {{ $step }} of {{ $totalSteps }}</span>
                <span class="fs-12 fw-medium">{{ match($step) { 1 => 'Business Info', 2 => 'Select Plan', default => '' } }}</span>
            </div>
            <div class="progress progress-sm">
                <div class="progress-bar bg-primary" style="width: {{ ($step / $totalSteps) * 100 }}%"></div>
            </div>
        </div>
    </div>

    {{-- Wizard card --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                @if($step === 1)
                    Set Up Your Business
                @elseif($step === 2)
                    Choose Your Plan
                @endif
            </h5>
            <p class="text-muted fs-13 mb-0 mt-1">
                @if($step === 1)
                    Enter your business name to get started.
                @elseif($step === 2)
                    Select a plan and billing cycle. Start with a free trial.
                @endif
            </p>
        </div>

        <div class="card-body">

            {{-- Step 1: Business info --}}
            @if($step === 1)
                <div class="mb-3">
                    <label for="businessName" class="form-label">Business Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('businessName') is-invalid @enderror"
                           id="businessName" wire:model.live="businessName" placeholder="e.g. Acme Industries">
                    @error('businessName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="slug" class="form-label">URL Slug <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text fs-13">ierp.app/</span>
                        <input type="text" class="form-control @error('slug') is-invalid @enderror"
                               id="slug" wire:model="slug" placeholder="acme-industries">
                    </div>
                    @error('slug') <div class="text-danger fs-12 mt-1">{{ $message }}</div> @enderror
                </div>
            @endif

            {{-- Step 2: Plan selection + billing cycle --}}
            @if($step === 2)
                {{-- Billing cycle toggle --}}
                <div class="d-flex justify-content-center mb-4">
                    <div class="btn-group" role="group">
                        <button type="button"
                                class="btn {{ $billingCycle === 'monthly' ? 'btn-primary' : 'btn-outline-primary' }}"
                                wire:click="$set('billingCycle', 'monthly')">
                            Monthly
                        </button>
                        <button type="button"
                                class="btn {{ $billingCycle === 'annual' ? 'btn-primary' : 'btn-outline-primary' }}"
                                wire:click="$set('billingCycle', 'annual')">
                            Annual <span class="badge bg-success-transparent ms-1">Save</span>
                        </button>
                    </div>
                </div>

                @error('selectedPlanId') <div class="alert alert-danger fs-13">{{ $message }}</div> @enderror
                <div class="row g-3">
                    @foreach($plans as $plan)
                        <div class="col-md-6">
                            <div class="card border {{ $selectedPlanId == $plan->id ? 'border-primary shadow-sm' : '' }}"
                                 style="cursor: pointer"
                                 wire:click="$set('selectedPlanId', {{ $plan->id }})">
                                <div class="card-body text-center">
                                    <h6 class="fw-semibold">{{ $plan->name }}</h6>
                                    @if($plan->description)
                                        <p class="text-muted fs-12 mb-2">{{ $plan->description }}</p>
                                    @endif
                                    <div class="mb-2">
                                        @if($billingCycle === 'monthly')
                                            <span class="fs-20 fw-bold text-primary">{{ number_format($plan->monthly_price, 0) }}</span>
                                            <span class="text-muted fs-12">/month</span>
                                        @else
                                            <span class="fs-20 fw-bold text-primary">{{ number_format($plan->annual_price, 0) }}</span>
                                            <span class="text-muted fs-12">/year</span>
                                        @endif
                                    </div>
                                    @if($billingCycle === 'monthly' && $plan->annual_price)
                                        <div class="text-muted fs-12 mb-2">
                                            or {{ number_format($plan->annual_price, 0) }}/year
                                        </div>
                                    @elseif($billingCycle === 'annual' && $plan->monthly_price)
                                        <div class="text-muted fs-12 mb-2">
                                            or {{ number_format($plan->monthly_price, 0) }}/month
                                        </div>
                                    @endif
                                    @if($plan->trial_days > 0)
                                        <span class="badge bg-success-transparent">{{ $plan->trial_days }}-day free trial</span>
                                    @endif
                                    @if($selectedPlanId == $plan->id)
                                        <div class="mt-2">
                                            <span class="badge bg-primary">Selected</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card-footer d-flex justify-content-between">
            @if($step > 1)
                <button class="btn btn-light" wire:click="previousStep">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </button>
            @else
                <div></div>
            @endif

            @if($step < $totalSteps)
                <button class="btn btn-primary" wire:click="nextStep">
                    Next <i class="ri-arrow-right-line ms-1"></i>
                </button>
            @else
                <button class="btn btn-primary" wire:click="completeSetup">
                    Start Free Trial <i class="ri-rocket-line ms-1"></i>
                </button>
            @endif
        </div>
    </div>
</div>
