<?php

namespace App\Providers;

use App\Actions\LinkVerifiedMembershipsAction;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Event::listen(Verified::class, function (Verified $event): void {
            app(LinkVerifiedMembershipsAction::class)->handle($event->user);
        });

        View::composer('components.layouts.app', function (ViewContract $view): void {
            $user = auth()->user();
            $availableClubs = $user?->clubs()->orderBy('clubs.name')->get() ?? collect();
            $status = session('status');
            $flashStatus = is_string($status) && trans()->has("status.{$status}")
                ? __("status.{$status}")
                : $status;

            $view->with([
                'availableClubs' => $availableClubs,
                'flashStatus' => $flashStatus,
            ]);
        });
    }
}
