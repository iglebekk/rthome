<?php

namespace App\Http\Controllers;

use App\Actions\CreditInvoiceAction;
use App\Actions\MarkInvoicePaidAction;
use App\Actions\UnmarkInvoicePaidAction;
use App\Enums\InvoiceCreationStatus;
use App\Http\Requests\CreditInvoiceRequest;
use App\Http\Requests\MarkInvoicePaidRequest;
use App\Http\Requests\UnmarkInvoicePaidRequest;
use App\Jobs\SendInvoiceEmail;
use App\Services\InvoicePdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request, int $club): JsonResponse|View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $invoices = $clubModel->invoices()->with('member')->latest('number')->get();
        $drafts = $clubModel->invoiceCreations()
            ->where('status', InvoiceCreationStatus::Draft)
            ->withCount(['recipients', 'lines'])
            ->latest()
            ->get();

        return $request->expectsJson()
            ? response()->json(['data' => $invoices, 'drafts' => $drafts])
            : view('invoices.index', ['club' => $clubModel, 'invoices' => $invoices, 'drafts' => $drafts]);
    }

    public function show(Request $request, int $club, int $invoice): JsonResponse|View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $invoiceModel = $clubModel->invoices()->with(['lines', 'creditNote', 'creditedInvoice'])->findOrFail($invoice);
        abort_unless($request->user()->can('view', $invoiceModel), Response::HTTP_FORBIDDEN);

        return $request->expectsJson() ? response()->json(['data' => $invoiceModel]) : view('invoices.show', ['club' => $clubModel, 'invoice' => $invoiceModel]);
    }

    public function credit(CreditInvoiceRequest $request, CreditInvoiceAction $credit, int $club, int $invoice): JsonResponse|RedirectResponse
    {
        $invoiceModel = $request->user()->clubs()->findOrFail($club)->invoices()->findOrFail($invoice);
        $creditNote = $credit->handle($invoiceModel);

        return $request->expectsJson() ? response()->json(['data' => $creditNote], Response::HTTP_CREATED) : redirect()->route('clubs.invoices.show', [$club, $creditNote]);
    }

    public function download(Request $request, InvoicePdfService $pdfService, int $club, int $invoice): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $invoiceModel = $clubModel->invoices()->with('lines')->findOrFail($invoice);
        abort_unless($request->user()->can('view', $invoiceModel), Response::HTTP_FORBIDDEN);

        return view('invoices.print', [
            'club' => $clubModel,
            'document' => $pdfService->document($invoiceModel),
            'returnUrl' => route('clubs.invoices.show', [$clubModel, $invoiceModel]),
        ]);
    }

    public function send(Request $request, int $club, int $invoice): RedirectResponse|JsonResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $invoiceModel = $clubModel->invoices()->findOrFail($invoice);
        abort_unless($request->user()->can('view', $invoiceModel), Response::HTTP_FORBIDDEN);
        if (blank($invoiceModel->recipient_email)) {
            throw ValidationException::withMessages(['recipient_email' => __('An invoice recipient email address is required.')]);
        }
        SendInvoiceEmail::dispatch($invoiceModel);

        return $request->expectsJson()
            ? response()->json(['message' => __('Invoice email queued.')])
            : redirect()->route('clubs.invoices.show', [$clubModel, $invoiceModel])->with('status', __('invoices.messages.sent'));
    }

    public function markPaid(MarkInvoicePaidRequest $request, MarkInvoicePaidAction $markPaid, int $club, int $invoice): RedirectResponse|JsonResponse
    {
        $invoiceModel = $request->user()->clubs()->findOrFail($club)->invoices()->findOrFail($invoice);
        $paidInvoice = $markPaid->handle($invoiceModel, $request->paidAmountOre(), $request->paidDate());

        return $request->expectsJson()
            ? response()->json(['data' => $paidInvoice])
            : redirect()->route('clubs.invoices.show', [$club, $paidInvoice])->with('status', __('invoices.messages.paid'));
    }

    public function unmarkPaid(UnmarkInvoicePaidRequest $request, UnmarkInvoicePaidAction $unmarkPaid, int $club, int $invoice): RedirectResponse|JsonResponse
    {
        $invoiceModel = $request->user()->clubs()->findOrFail($club)->invoices()->findOrFail($invoice);
        $unpaidInvoice = $unmarkPaid->handle($invoiceModel);

        return $request->expectsJson()
            ? response()->json(['data' => $unpaidInvoice])
            : redirect()->route('clubs.invoices.show', [$club, $unpaidInvoice])->with('status', __('invoices.messages.payment_undone'));
    }
}
