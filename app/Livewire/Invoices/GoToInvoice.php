<?php

namespace App\Livewire\Invoices;

use App\Models\Club;
use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class GoToInvoice extends Component
{
    #[Locked]
    public int $clubId;

    public string $invoiceNumber = '';

    #[Locked]
    public bool $invoiceExists = false;

    public function mount(?int $clubId = null): void
    {
        $this->clubId = $clubId ?? (int) request()->route('club');
        $this->club();
    }

    public function updatedInvoiceNumber(): void
    {
        $this->invoiceExists = false;

        if (strlen($this->invoiceNumber) < 3 || ! ctype_digit($this->invoiceNumber)) {
            return;
        }

        $this->invoiceExists = $this->club()
            ->invoices()
            ->where('number', (int) $this->invoiceNumber)
            ->exists();
    }

    public function resetLookup(): void
    {
        $this->invoiceNumber = '';
        $this->invoiceExists = false;
    }

    public function goToInvoice(): void
    {
        if (! $this->invoiceExists) {
            return;
        }

        $invoice = $this->findInvoice();

        if ($invoice === null) {
            $this->invoiceExists = false;

            return;
        }

        Gate::authorize('view', $invoice);

        $this->redirectRoute('clubs.invoices.show', [$this->clubId, $invoice]);
    }

    public function render(): View
    {
        return view('livewire.invoices.go-to-invoice');
    }

    private function club(): Club
    {
        return auth()->user()->clubs()->findOrFail($this->clubId);
    }

    private function findInvoice(): ?Invoice
    {
        if (strlen($this->invoiceNumber) < 3 || ! ctype_digit($this->invoiceNumber)) {
            return null;
        }

        return $this->club()
            ->invoices()
            ->where('number', (int) $this->invoiceNumber)
            ->first();
    }
}
