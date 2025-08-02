# Filament Webpush

A Laravel package that integrates Web Push notifications into your Filament admin panel.

## Installation

You can install the package via composer:

```bash
composer require andrefelipe18/filament-webpush
```

Next, run the preparation command which will set up everything required for web push notifications:

```bash
php artisan webpush:prepare
```

This command will:

1. Publish the WebPush migrations
2. Publish the WebPush configuration file
3. Add VAPID environment variables to your `.env.example` file
4. Generate VAPID keys (except on Windows)
5. Copy the service worker file to your public directory
6. Copy the WebPush JavaScript file to your assets directory

::: warning Important
If you already have a service worker file (`sw.js`) in your public directory, the command will not overwrite it. In this case, please follow the [Existing Service Worker guide](/existing-service-worker) to manually integrate the WebPush functionality.
:::

After that, run the migrations:

```bash
php artisan migrate
```

## Setup

### User Model Configuration

You need to add the `HasPushSubscriptions` trait to your user model:

```php
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Model
{
    use HasPushSubscriptions;
}
```

### Filament Panel Integration

Add the `FilamentWebpushPlugin` to your Filament Panel Provider:

```php
namespace App\Providers\Filament;

use FilamentWebpush\FilamentWebpushPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->plugins([
                FilamentWebpushPlugin::make()
                    ->registerSubscriptionStatsWidget(true),
            ]);
    }
}
```

## Configuration

### VAPID Keys

Web Push requires VAPID (Voluntary Application Server Identification) keys for authentication. The `webpush:prepare` command attempts to generate these for you, but if you're on Windows or if the generation fails, you can generate them manually:

1. Visit [web-push-codelab.glitch.me](https://web-push-codelab.glitch.me/) to generate VAPID keys
2. Add the keys to your `.env` file:

```
VAPID_PUBLIC_KEY="your-public-key-here"
VAPID_PRIVATE_KEY="your-private-key-here"
VAPID_SUBJECT="mailto:your-email@example.com"
```

## HTTPS Requirement

Web Push Notifications require a secure context to work properly. This means your application must be served over HTTPS. Modern browsers enforce this requirement as part of their security model, and will block push notifications and service worker registration on non-secure origins.

The only exception is when you're developing on `localhost`, where service workers and push notifications are allowed without HTTPS for convenience.

## Browser Compatibility

Web Push is supported in most modern browsers:

-   Chrome (Desktop & Android)
-   Firefox (Desktop & Android)
-   Edge
-   Opera
-   Safari (as of macOS Sonoma)

## Troubleshooting

### Service Worker Registration

The package automatically registers a service worker at `/sw.js`. If you need to customize it, you can edit the file at `public/sw.js` after running the preparation command.

### Windows Users

If you're developing on Windows, the VAPID key generation may not work. In this case, you'll need to generate the keys manually as described in the Configuration section.

Notification queues may not work as expected in Windows.
