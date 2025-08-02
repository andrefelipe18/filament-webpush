<?php

declare(strict_types = 1);

namespace FilamentWebpush\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\warning;

class MergeWebpushListenersCommand extends Command
{
    public $signature = 'webpush:merge-listeners';

    public $description = 'Merge WebPush event listeners into your existing service worker';

    public function handle(): int
    {
        note('Merging WebPush listeners into existing service worker...');

        $serviceWorkerPath = public_path('sw.js');
        $listenersStubPath = __DIR__ . '/../../stubs/listeners-webpush.js';

        // Check if service worker exists
        if (! File::exists($serviceWorkerPath)) {
            error('Service worker file not found at: ' . $serviceWorkerPath);
            info('Please run "php artisan webpush:prepare" first to create the service worker.');
            
            return self::FAILURE;
        }

        // Check if listeners stub exists
        if (! File::exists($listenersStubPath)) {
            error('WebPush listeners stub file not found.');
            
            return self::FAILURE;
        }

        // Read current service worker content
        $currentContent = File::get($serviceWorkerPath);
        $listenersContent = File::get($listenersStubPath);

        // Check if WebPush listeners are already present
        if (str_contains($currentContent, 'WebPush Event Listeners for Filament WebPush Package')) {
            warning('WebPush listeners appear to already be present in your service worker.');
            info('If you need to update them, please do so manually.');
            
            return self::SUCCESS;
        }

        // Append listeners to service worker
        $updatedContent = $currentContent . "\n\n" . $listenersContent;

        // Write updated content back to service worker
        File::put($serviceWorkerPath, $updatedContent);

        info('✔ WebPush listeners successfully merged into your service worker.');
        note('Your service worker now supports push notifications and notification clicks.');

        return self::SUCCESS;
    }
}
