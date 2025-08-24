<?php

declare(strict_types = 1);

namespace FilamentWebpush\Commands;

use NotificationChannels\WebPush\WebPushServiceProvider;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\warning;

class PrepareWebpushCommand extends Command
{
    public $signature = 'webpush:prepare';

    public $description = 'Set up everything required for WebPush notifications';

    public function handle(): int
    {
        note('Starting WebPush setup...');

        // Step 1: Publish migrations
        note('Publishing WebPush database migrations...');
        $this->call('vendor:publish', [
            '--provider' => WebPushServiceProvider::class,
            '--tag'      => 'migrations',
        ]);

        // Step 2: Publish config
        note('Publishing WebPush configuration file...');
        $this->call('vendor:publish', [
            '--provider' => WebPushServiceProvider::class,
            '--tag'      => 'config',
        ]);

        // Step 3: Add VAPID variables
        note('Adding VAPID environment variables to .env.example...');
        $this->addVapidVariablesToEnv();
        $this->newLine();

        // Step 4: Generate VAPID keys if not on Windows
        $isRunningOnWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if (! $isRunningOnWindows) {
            info('Generating VAPID keys...');

            try {
                $result = $this->call('webpush:vapid');

                if ($result === 0) {
                    info('VAPID keys generated successfully.');
                } else {
                    error('VAPID key generation failed. You can manually run "php artisan webpush:vapid" to try again.');
                }
            } catch (Exception) {
                error('VAPID key generation failed. Please try manually with: "php artisan webpush:vapid".');

                return self::FAILURE;
            }
        }

        // Step 5: Copy service worker file
        note('Copying service worker file to the public directory...');
        $this->copyServiceWorker();
        $this->newLine();

        // Step 6: Copy webpush JS file
        note('Copying WebPush JavaScript file to the assets directory...');
        $this->copyWebpushJs();
        $this->newLine();

        // Step 7: Copy offline.html file
        note('Copying offline.html file to the public directory...');
        $this->copyHtmlFile();
        $this->newLine();

        note('WebPush setup completed successfully!');
        $this->newLine();

        if ($isRunningOnWindows) {
            error('VAPID key generation is not supported on Windows. Please generate them at https://web-push-codelab.glitch.me/ and update your .env file manually.');
            $this->newLine();
        }

        info('Remember: register FilamentWebpush\\FilamentWebpushPlugin in your panel provider to activate the plugin.');

        return self::SUCCESS;
    }

    /**
     * Copy service worker file to a public directory
     */
    protected function copyServiceWorker(): void
    {
        $source      = __DIR__ . '/../../stubs/sw.js';
        $destination = public_path('sw.js');

        if (! File::exists(public_path())) {
            File::makeDirectory(public_path(), 0755, true);
        }

        if (File::exists($destination)) {
            warning("Service worker file already exists at: {$destination}. Skipping copy.");
            info("See documentation for more details on how to update it.");
            return;
        }

        File::copy($source, $destination);
        info("✔ Service worker file copied to: {$destination}");
    }

    /**
     * Copy webpush JS file to the public assets directory
     */
    protected function copyWebpushJs(): void
    {
        $source      = __DIR__ . '/../../stubs/webpush.js';
        $destination = public_path('js/webpush.js');

        if (! File::exists(public_path('js'))) {
            File::makeDirectory(public_path('js'), 0755, true, true);
        }

        File::copy($source, $destination);
        info("✔ WebPush JavaScript file copied to: {$destination}");
    }

    /**
     * Copy offline.html file to the public directory
     */
    protected function copyHtmlFile(): void
    {
        $source      = __DIR__ . '/../../stubs/offline.html';
        $destination = public_path('offline.html');

        if (! File::exists(public_path())) {
            File::makeDirectory(public_path(), 0755, true);
        }

        File::copy($source, $destination);
        info("✔ offline.html file copied to: {$destination}");
    }

    /**
     * Add VAPID variables to the .env.example file
     */
    protected function addVapidVariablesToEnv(): void
    {
        $envExamplePath = base_path('.env.example');

        if (! File::exists($envExamplePath)) {
            warning('.env.example not found. Please add the VAPID keys manually.');

            return;
        }

        $envContent = File::get($envExamplePath);

        $vapidEnvVars = <<<EOT

# VAPID keys for WebPush notifications
# Generate at https://web-push-codelab.glitch.me/
VAPID_PUBLIC_KEY=""
VAPID_PRIVATE_KEY=""
VAPID_SUBJECT="mailto:\${APP_NAME}@\${APP_URL}"
EOT;

        if (in_array(str_contains($envContent, 'VAPID_PUBLIC_KEY='), [0, false], true)) {
            File::append($envExamplePath, $vapidEnvVars);
            info('✔ VAPID variables successfully added to .env.example.');
        } else {
            info('✔ VAPID variables already exist in .env.example — skipping.');
        }
    }
}
