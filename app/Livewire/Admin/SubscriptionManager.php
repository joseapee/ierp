<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SubscriptionManager extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public function extendTrial(int $subscriptionId, int $days = 7): void
    {
        $subscription = Subscription::findOrFail($subscriptionId);

        if ($subscription->isTrial() && $subscription->trial_ends_at) {
            $subscription->update([
                'trial_ends_at' => $subscription->trial_ends_at->addDays($days),
                'ends_at' => $subscription->ends_at->addDays($days),
            ]);
            session()->flash('success', "Trial extended by {$days} days.");
        }
    }

    public function activateManually(int $subscriptionId): void
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        $subscriptionService = app(SubscriptionService::class);

        $subscriptionService->activate($subscription, [
            'reference' => 'MANUAL_'.uniqid(),
            'amount' => 0,
            'method' => 'manual',
        ]);

        session()->flash('success', 'Subscription activated manually.');
    }

    public function suspendSubscription(int $subscriptionId): void
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        $subscriptionService = app(SubscriptionService::class);
        $subscriptionService->suspend($subscription);

        session()->flash('success', 'Subscription suspended.');
    }

    public function render(): mixed
    {
        $subscriptions = Subscription::query()
            ->with(['tenant', 'plan'])
            ->when($this->search, fn ($q, $s) => $q->whereHas('tenant', fn ($q) => $q->where('name', 'like', "%{$s}%")))
            ->when($this->statusFilter, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.subscription-manager', [
            'subscriptions' => $subscriptions,
        ]);
    }
}
