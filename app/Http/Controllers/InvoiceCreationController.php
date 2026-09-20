<?php

namespace App\Http\Controllers;

use App\Actions\CreateInvoicesAction;
use App\Enums\InvoiceCreationStatus;
use App\Http\Requests\DestroyInvoiceCreationRequest;
use App\Http\Requests\StoreInvoiceCreationRequest;
use App\Models\InvoiceCreation;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvoiceCreationController extends Controller
{
    public function create(Request $request, int $club): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        abort_unless($request->user()->can('create', [InvoiceCreation::class, $clubModel]), Response::HTTP_FORBIDDEN);
        $members = $clubModel->members()->orderBy('name')->get();
        $products = $clubModel->products()->where('is_active', true)->orderBy('name')->get();
        $selectedRecipientIds = $request->old('recipients', []);
        $selectedLines = $request->old('lines', []);

        return view('invoice-creations.create', [
            'club' => $clubModel,
            'members' => $members,
            'products' => $products,
            'creation' => null,
            'submissionToken' => $request->old('submission_token', (string) Str::uuid()),
            'invoiceDate' => $request->old('invoice_date', now()->toDateString()),
            'dueDate' => $request->old('due_date', now()->addDays(14)->toDateString()),
            'selectedMembers' => $members->whereIn('id', $selectedRecipientIds),
            'selectedLines' => $this->selectedLines($products, $selectedLines),
        ]);
    }

    public function store(StoreInvoiceCreationRequest $request, CreateInvoicesAction $create, int $club): JsonResponse|RedirectResponse
    {
        $creation = $create->handle(
            $request->user()->clubs()->findOrFail($club),
            $request->validated(),
        );

        return $this->creationResponse($request, $creation, Response::HTTP_CREATED);
    }

    public function show(Request $request, int $club, int $invoiceCreation): JsonResponse|View|RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $creation = $clubModel->invoiceCreations()
            ->with(['invoices.lines'])
            ->findOrFail($invoiceCreation);
        abort_unless($request->user()->can('view', $creation), Response::HTTP_FORBIDDEN);

        if ($creation->status === InvoiceCreationStatus::Draft && ! $request->expectsJson()) {
            return redirect()->route('clubs.invoice-creations.edit', [$clubModel, $creation]);
        }

        return $request->expectsJson()
            ? response()->json(['data' => $creation])
            : view('invoice-creations.show', ['club' => $clubModel, 'creation' => $creation]);
    }

    public function edit(Request $request, int $club, int $invoiceCreation): View
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $creation = $clubModel->invoiceCreations()
            ->with(['recipients', 'lines.product'])
            ->findOrFail($invoiceCreation);
        abort_unless($request->user()->can('update', $creation), Response::HTTP_FORBIDDEN);
        $members = $clubModel->members()->orderBy('name')->get();
        $products = $clubModel->products()->orderBy('name')->get();
        $selectedRecipientIds = $request->old('recipients', $creation->recipients->modelKeys());
        $selectedLines = $request->old('lines', $creation->lines->map(fn ($line): array => [
            'product_id' => $line->product_id,
            'quantity' => $line->quantity,
        ])->all());

        return view('invoice-creations.edit', [
            'club' => $clubModel,
            'members' => $members,
            'products' => $products,
            'creation' => $creation,
            'submissionToken' => $creation->submission_token,
            'invoiceDate' => $request->old('invoice_date', $creation->invoice_date?->toDateString()),
            'dueDate' => $request->old('due_date', $creation->due_date?->toDateString()),
            'selectedMembers' => $members->whereIn('id', $selectedRecipientIds),
            'selectedLines' => $this->selectedLines($products, $selectedLines),
        ]);
    }

    public function update(StoreInvoiceCreationRequest $request, CreateInvoicesAction $create, int $club, int $invoiceCreation): JsonResponse|RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $creation = $clubModel->invoiceCreations()->findOrFail($invoiceCreation);
        $creation = $create->handle($clubModel, $request->validated(), $creation);

        return $this->creationResponse($request, $creation);
    }

    public function destroy(DestroyInvoiceCreationRequest $request, int $club, int $invoiceCreation): Response|RedirectResponse
    {
        $clubModel = $request->user()->clubs()->findOrFail($club);
        $creation = $clubModel->invoiceCreations()->findOrFail($invoiceCreation);
        $creation->delete();

        return $request->expectsJson()
            ? response()->noContent()
            : redirect()->route('clubs.invoices.index', $clubModel)->with('status', 'invoices.messages.draft_deleted');
    }

    private function creationResponse(Request $request, InvoiceCreation $creation, int $status = Response::HTTP_OK): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['data' => $creation], $status);
        }

        if ($creation->status === InvoiceCreationStatus::Draft) {
            return redirect()->route('clubs.invoices.index', $creation->club_id)->with('status', 'invoices.messages.draft_saved');
        }

        return redirect()->route('clubs.invoice-creations.show', [$creation->club_id, $creation]);
    }

    /**
     * @param  EloquentCollection<int, Product>  $products
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array{product: Product, quantity: mixed}>
     */
    private function selectedLines(EloquentCollection $products, array $lines): array
    {
        return collect($lines)
            ->map(function (array $line) use ($products): ?array {
                $product = $products->firstWhere('id', $line['product_id'] ?? null);

                return $product === null ? null : [
                    'product' => $product,
                    'quantity' => $line['quantity'] ?? '1.00',
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
