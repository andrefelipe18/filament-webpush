<?php

declare(strict_types = 1);

namespace FilamentWebpush;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use FilamentWebpush\Filament\Widgets\WebpushSubscriptionStats;
use FilamentWebpush\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class FilamentWebpushPlugin implements Plugin
{
    use EvaluatesClosures;

    protected bool $registerSubscriptionStatsWidget = true;

    public function getId(): string
    {
        return 'filament-webpush';
    }

    public function register(Panel $panel): void
    {
        $panel->routes(function () use ($panel): void {
            Route::middleware(['web', 'auth:' . $panel->getAuthGuard()])->group(function (): void {
                Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('webpush-store');
                Route::post('/push-subscriptions/delete', [PushSubscriptionController::class, 'destroy'])->name('webpush-destroy');
            });
        });

        if ($this->shouldRegisterSubscriptionStatsWidget()) {
            $panel->widgets([
                WebpushSubscriptionStats::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            function () use ($panel) {
                if (! auth($panel->getAuthGuard())->check()) {
                    return '';
                }

                $vapidPublicKey = Config::get('webpush.vapid.public_key');

                return view('filament-webpush::meta', [
                    'vapidPublicKey' => $vapidPublicKey,
                    'storeUrl'       => route('filament.' . $panel->getId() . '.webpush-store'),
                    'destroyUrl'     => route('filament.' . $panel->getId() . '.webpush-destroy'),
                ]);
            }
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            function () use ($panel) {
                if (! auth($panel->getAuthGuard())->check()) {
                    return '';
                }

                return view('filament-webpush::script');
            }
        );
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function registerSubscriptionStatsWidget(Closure | bool $register = true): static
    {
        if ($register instanceof Closure) {
            $register = $this->evaluate($register);
        }

        $this->registerSubscriptionStatsWidget = $register;

        return $this;
    }

    protected function shouldRegisterSubscriptionStatsWidget(): bool
    {
        return $this->registerSubscriptionStatsWidget;
    }
}
