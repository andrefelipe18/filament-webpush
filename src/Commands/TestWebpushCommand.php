<?php

declare(strict_types = 1);

namespace FilamentWebpush\Commands;

use Exception;
use App\Models\User;
use FilamentWebpush\Notifications\TestPushNotification;
use Illuminate\Console\Command;

class TestWebpushCommand extends Command
{
    public $signature = 'webpush:test {user_id : The ID of the user to send the test notification to}';

    public $description = 'Send a test webpush notification to a specific user';

    public function handle(): int
    {
        $userId = $this->argument('user_id');

        // Find the user by ID
        $user = User::find($userId);

        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return self::FAILURE;
        }

        // Check if the user has webpush subscriptions
        $hasSubscriptions = $user->pushSubscriptions()->count() > 0;

        if (! $hasSubscriptions) {
            $this->warn("The user {$user->name} (ID: {$userId}) has no webpush subscriptions.");

            if ($this->confirm('Do you want to continue anyway? The notification will not be delivered.', false)) {
                $this->info('Continuing with the notification delivery...');
            } else {
                $this->info('Command cancelled.');

                return self::INVALID;
            }
        }

        try {
            // Send the test notification
            $user->notify(new TestPushNotification());

            $this->info("Test notification successfully sent to {$user->name} (ID: {$userId}).");
            $this->newLine();

            if ($hasSubscriptions) {
                $this->info("The user has " . $user->pushSubscriptions()->count() . " registered subscription(s).");
            } else {
                $this->warn("Remember: the user has no webpush subscriptions, so the notification will not be delivered.");
            }

            $this->info("Make sure that the queue worker is running with: php artisan queue:work");

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error("Error sending notification: " . $e->getMessage());

            return self::FAILURE;
        }
    }
}
