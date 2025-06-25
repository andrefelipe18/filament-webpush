<?php

declare(strict_types = 1);

namespace FilamentWebpush\Filament\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WebpushSubscriptionStats extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $table = config('webpush.table_name', 'push_subscriptions');

        $activeSubscriptions = DB::table($table)
            ->select('subscribable_id', 'subscribable_type')
            ->groupBy('subscribable_id', 'subscribable_type')
            ->get()
            ->count();

        $totalSubscriptions = DB::table($table)->count();

        $hasSubscription = DB::table($table)
            ->where('subscribable_id', Auth::id())
            ->exists();

        return [
            Stat::make(__('Users with Webpush'), (string) $activeSubscriptions)
                ->description(__('Users with active notifications'))
                ->descriptionIcon('heroicon-m-bell', IconPosition::Before)
                ->color('success'),

            Stat::make(__('Total Subscriptions (Webpush)'), (string) $totalSubscriptions)
                ->description(__('Registered devices'))
                ->descriptionIcon('heroicon-m-device-phone-mobile', IconPosition::Before)
                ->color('info'),

            Stat::make(__('Your webpush status'), $hasSubscription ? __('Active') : __('Inactive'))
                ->description($hasSubscription ? __('Push notifications enabled') : __('Push notifications disabled'))
                ->descriptionIcon(
                    $hasSubscription ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle',
                    IconPosition::Before
                )
                ->color($hasSubscription ? 'success' : 'danger'),
        ];
    }

    protected function getHeading(): ?string
    {
        return __('Webpush Subscription Stats');
    }
}
