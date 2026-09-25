<?php

use App\Http\Controllers\Auth\IdentifyAccountController;
use App\Http\Controllers\Auth\MemberActivationController;
use App\Http\Controllers\BrregEntityController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\ClubInvitationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceCreationController;
use App\Http\Controllers\InvoiceExportDownloadController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\PublicEventsLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
})->name('welcome');

Route::get('/join/{token}', [ClubInvitationController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('club-invitations.show');

Route::get('/events/{token}', [PublicEventController::class, 'index'])
    ->middleware('throttle:30,1')
    ->name('public-events.index');
Route::get('/events/{token}/{event}', [PublicEventController::class, 'show'])
    ->whereNumber('event')
    ->middleware('throttle:30,1')
    ->name('public-events.show');

Route::middleware('guest')->group(function (): void {
    Route::post('/login/identify', IdentifyAccountController::class)
        ->middleware('throttle:6,1')
        ->name('login.identify');
    Route::view('/activation/sent', 'auth.activation-sent')->name('activation.sent');
    Route::get('/activation/{activation}/{token}', [MemberActivationController::class, 'show'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('activation.show');
    Route::post('/activation/{activation}/{token}', [MemberActivationController::class, 'store'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('activation.store');
});

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/home', [DashboardController::class, 'home'])->name('home');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    Route::resource('clubs', ClubController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::put('/clubs/{club}/organization-number', [ClubController::class, 'updateOrganizationNumber'])
        ->name('clubs.organization-number.update');
    Route::get('/clubs/{club}/dashboard', [DashboardController::class, 'show'])->name('clubs.dashboard');
    Route::get('/clubs/{club}/settings/events', [ClubController::class, 'settingsEvents'])->name('clubs.settings.events');
    Route::post('/clubs/{club}/settings/events/import', [ClubController::class, 'importEvents'])->name('clubs.settings.events.import');
    Route::get('/clubs/{club}/settings/members', [ClubController::class, 'settingsMembers'])->name('clubs.settings.members');
    Route::post('/clubs/{club}/settings/members/import', [ClubController::class, 'importMembers'])->name('clubs.settings.members.import');
    Route::get('/clubs/{club}/settings/invitations', [ClubInvitationController::class, 'index'])->name('clubs.settings.invitations');
    Route::post('/clubs/{club}/settings/invitations', [ClubInvitationController::class, 'store'])->name('clubs.settings.invitations.store');
    Route::delete('/club-invitations/{invitation}', [ClubInvitationController::class, 'destroy'])->name('club-invitations.destroy');
    Route::post('/join/{token}/confirm', [ClubInvitationController::class, 'confirm'])->name('club-invitations.confirm');
    Route::get('/clubs/{club}/brreg-entities/{organizationNumber}', BrregEntityController::class)
        ->middleware('throttle:30,1')
        ->name('clubs.brreg-entities.show');
    Route::resource('clubs.members', MemberController::class)->except('show');
    Route::put('/clubs/{club}/members/{member}/invoice-details', [MemberController::class, 'updateInvoiceDetails'])
        ->name('clubs.members.invoice-details.update');
    Route::resource('clubs.positions', PositionController::class);
    Route::resource('clubs.events', EventController::class);
    Route::post('/clubs/{club}/public-events-link', [PublicEventsLinkController::class, 'store'])
        ->name('clubs.public-events-link.store');
    Route::delete('/clubs/{club}/public-events-link', [PublicEventsLinkController::class, 'destroy'])
        ->name('clubs.public-events-link.destroy');
    Route::resource('clubs.links', LinkController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('clubs.products', ProductController::class);
    Route::resource('clubs.invoice-creations', InvoiceCreationController::class)
        ->only(['create', 'store', 'show', 'edit', 'update', 'destroy'])
        ->parameters(['invoice-creations' => 'invoiceCreation']);
    Route::get('/clubs/{club}/invoices', [InvoiceController::class, 'index'])->name('clubs.invoices.index');
    Route::post('/clubs/{club}/invoices/bulk-send', [InvoiceController::class, 'bulkSend'])->name('clubs.invoices.bulk-send');
    Route::post('/clubs/{club}/invoices/exports', [InvoiceController::class, 'export'])->name('clubs.invoices.exports.store');
    Route::get('/clubs/{club}/invoices/{invoice}', [InvoiceController::class, 'show'])->name('clubs.invoices.show');
    Route::get('/clubs/{club}/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('clubs.invoices.download');
    Route::post('/clubs/{club}/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('clubs.invoices.send');
    Route::post('/clubs/{club}/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])->name('clubs.invoices.mark-paid');
    Route::post('/clubs/{club}/invoices/{invoice}/unmark-paid', [InvoiceController::class, 'unmarkPaid'])->name('clubs.invoices.unmark-paid');
    Route::post('/clubs/{club}/invoices/{invoice}/credit', [InvoiceController::class, 'credit'])->name('clubs.invoices.credit');
});

Route::get('/invoice-exports/{invoiceExport:download_token}/download', InvoiceExportDownloadController::class)
    ->name('invoice-exports.download');
