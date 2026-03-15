<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;

class InvoiceService
{
    /**
     * Generate an invoice for a subscription payment.
     */
    public function generate(Subscription $subscription, Payment $payment): Invoice
    {
        return Invoice::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'payment_id' => $payment->id,
            'invoice_number' => $this->getNextNumber($subscription->tenant_id),
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'status' => 'paid',
            'issued_at' => now(),
            'paid_at' => $payment->paid_at ?? now(),
            'due_at' => now(),
            'line_items' => [
                [
                    'description' => 'Subscription - '.($subscription->plan->name ?? 'Plan').' ('.$subscription->billing_cycle.')',
                    'amount' => $payment->amount,
                ],
            ],
        ]);
    }

    /**
     * Generate the next invoice number for a tenant.
     */
    public function getNextNumber(int $tenantId): string
    {
        $count = Invoice::where('tenant_id', $tenantId)->count();

        return 'INV-'.str_pad((string) $tenantId, 4, '0', STR_PAD_LEFT).'-'.str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Mark an invoice as paid.
     */
    public function markPaid(Invoice $invoice, Payment $payment): Invoice
    {
        $invoice->update([
            'payment_id' => $payment->id,
            'status' => 'paid',
            'paid_at' => $payment->paid_at ?? now(),
        ]);

        return $invoice->refresh();
    }
}
