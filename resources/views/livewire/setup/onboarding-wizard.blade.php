<div>
    {{-- Progress bar --}}
    <div class="card mb-4">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fs-12 text-muted">Step {{ $step }} of {{ $totalSteps }}</span>
                <span class="fs-12 fw-medium">Getting Started</span>
            </div>
            <div class="progress progress-sm">
                <div class="progress-bar bg-success" style="width: {{ ($step / $totalSteps) * 100 }}%"></div>
            </div>
        </div>
    </div>

    {{-- Wizard card --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                @if($step === 1) What industry is your business in?
                @elseif($step === 2) Where is your business located?
                @elseif($step === 3) What city are you in?
                @elseif($step === 4) Business Address
                @elseif($step === 5) What currency do you use?
                @elseif($step === 6) Select Your Timezone
                @elseif($step === 7) Create Your First Warehouse
                @elseif($step === 8) Invite a Team Member
                @elseif($step === 9) Create a Product Category
                @elseif($step === 10) You're All Set!
                @endif
            </h5>
        </div>

        <div class="card-body">

            {{-- Step 1: Industry --}}
            @if($step === 1)
                <p class="text-muted fs-13 mb-3">This helps us customize your experience.</p>
                <select class="form-select @error('industry') is-invalid @enderror" wire:model="industry">
                    <option value="">Select an industry</option>
                    <option value="manufacturing">Manufacturing</option>
                    <option value="retail">Retail & Distribution</option>
                    <option value="services">Professional Services</option>
                    <option value="technology">Technology</option>
                    <option value="healthcare">Healthcare</option>
                    <option value="construction">Construction</option>
                    <option value="agriculture">Agriculture</option>
                    <option value="other">Other</option>
                </select>
                @error('industry')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif

            {{-- Step 2: Country --}}
            @if($step === 2)
                <p class="text-muted fs-13 mb-3">Select your country of operation.</p>
                <select class="form-select @error('country') is-invalid @enderror" wire:model="country">
                    <option value="">Select a country</option>
                    <option value="NG">Nigeria</option>
                    <option value="GH">Ghana</option>
                    <option value="KE">Kenya</option>
                    <option value="ZA">South Africa</option>
                    <option value="EG">Egypt</option>
                    <option value="US">United States</option>
                    <option value="GB">United Kingdom</option>
                    <option value="OTHER">Other</option>
                </select>
                @error('country')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif

            {{-- Step 3: City --}}
            @if($step === 3)
                <p class="text-muted fs-13 mb-3">Which city is your business based in?</p>
                <input type="text" class="form-control @error('city') is-invalid @enderror" wire:model="city"
                       placeholder="e.g. Lagos, Accra, Nairobi">
                @error('city')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif

            {{-- Step 4: Address --}}
            @if($step === 4)
                <p class="text-muted fs-13 mb-3">Enter your full business address.</p>
                <textarea class="form-control @error('address') is-invalid @enderror" rows="3" wire:model="address"
                          placeholder="Enter your business address"></textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif

            {{-- Step 5: Currency --}}
            @if($step === 5)
                <p class="text-muted fs-13 mb-3">Choose the primary currency for your business.</p>
                <select class="form-select" wire:model="currency">
                    <option value="NGN">Nigerian Naira (NGN)</option>
                    <option value="USD">US Dollar (USD)</option>
                    <option value="GBP">British Pound (GBP)</option>
                    <option value="EUR">Euro (EUR)</option>
                    <option value="GHS">Ghanaian Cedi (GHS)</option>
                    <option value="KES">Kenyan Shilling (KES)</option>
                    <option value="ZAR">South African Rand (ZAR)</option>
                </select>
            @endif

            {{-- Step 6: Timezone --}}
            @if($step === 6)
                <p class="text-muted fs-13 mb-3">Select your timezone for accurate scheduling.</p>
                <select class="form-select" wire:model="timezone">
                    <option value="Africa/Lagos">West Africa Time (Africa/Lagos)</option>
                    <option value="Africa/Accra">Ghana Time (Africa/Accra)</option>
                    <option value="Africa/Nairobi">East Africa Time (Africa/Nairobi)</option>
                    <option value="Africa/Johannesburg">South Africa Time (Africa/Johannesburg)</option>
                    <option value="UTC">UTC</option>
                    <option value="America/New_York">US Eastern</option>
                    <option value="Europe/London">UK (Europe/London)</option>
                </select>
            @endif

            {{-- Step 7: Warehouse --}}
            @if($step === 7)
                <p class="text-muted fs-13 mb-3">Set up your first warehouse or stock location.</p>
                <div class="mb-3">
                    <label class="form-label">Warehouse Name</label>
                    <input type="text" class="form-control @error('warehouseName') is-invalid @enderror" wire:model="warehouseName"
                           placeholder="e.g. Main Warehouse">
                    @error('warehouseName')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" class="form-control @error('warehouseLocation') is-invalid @enderror" wire:model="warehouseLocation"
                           placeholder="e.g. Lagos, Nigeria">
                    @error('warehouseLocation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            {{-- Step 8: Invite --}}
            @if($step === 8)
                <p class="text-muted fs-13 mb-3">Invite a team member to help you get started.</p>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control @error('inviteEmail') is-invalid @enderror" wire:model="inviteEmail"
                           placeholder="colleague@example.com">
                    @error('inviteEmail')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            {{-- Step 9: Category --}}
            @if($step === 9)
                <p class="text-muted fs-13 mb-3">Create your first product category to organize your inventory.</p>
                <input type="text" class="form-control @error('categoryName') is-invalid @enderror" wire:model="categoryName"
                       placeholder="e.g. Electronics, Raw Materials, Finished Goods">
                @error('categoryName')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif

            {{-- Step 10: Completion --}}
            @if($step === 10)
                <div class="text-center py-3">
                    <div class="mb-3">
                        <i class="ri-checkbox-circle-fill text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Your workspace is ready!</h5>
                    <p class="text-muted fs-13">You can always update these settings later from your dashboard.</p>
                </div>
            @endif
        </div>

        <div class="card-footer d-flex justify-content-between">
            <div>
                @if($step > 1 && $step < $totalSteps)
                    <button class="btn btn-light" wire:click="previousStep">
                        <i class="ri-arrow-left-line me-1"></i> Back
                    </button>
                @endif
            </div>

            <div class="d-flex gap-2">
                @if($step < $totalSteps && $step !== 10)
                    <button class="btn btn-primary" wire:click="saveStep">
                        Save & Next <i class="ri-arrow-right-line ms-1"></i>
                    </button>
                @endif

                @if($step === $totalSteps)
                    <button class="btn btn-success" wire:click="completeOnboarding">
                        Go to Dashboard <i class="ri-dashboard-line ms-1"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
