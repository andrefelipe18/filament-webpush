<?php

declare(strict_types = 1);

namespace FilamentWebpush;

use FilamentWebpush\Commands\MergeWebpushListenersCommand;
use FilamentWebpush\Commands\PrepareWebpushCommand;
use FilamentWebpush\Commands\TestWebpushCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentWebpushServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-webpush')
            ->hasCommands([
                PrepareWebpushCommand::class,
                TestWebpushCommand::class,
                MergeWebpushListenersCommand::class,
            ])
            ->hasViews();
    }
}
