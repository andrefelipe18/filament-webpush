# JavaScript API

The Filament WebPush package provides global JavaScript functions that allow you to integrate push notifications anywhere in your application, especially with Alpine.js.

## Available Functions

### `registerWebPush()`

Prompts the user to subscribe to push notifications. This function handles permission requests, service worker registration, and server communication.

**Returns:** `Promise<{success: boolean, message: string}>`

```javascript
// Usage with Alpine.js
<button @click="registerWebPush().then(result => handleResult(result))">
    Subscribe to Notifications
</button>
```

### `unregisterWebPush()`

Unsubscribes the user from push notifications and removes the subscription from the server.

**Returns:** `Promise<{success: boolean, message: string}>`

```javascript
// Usage with Alpine.js
<button @click="unregisterWebPush().then(result => handleResult(result))">
    Unsubscribe
</button>
```

### `checkWebPushStatus()`

Checks the current subscription status and browser support.

**Returns:** `Promise<{supported: boolean, subscribed: boolean, permission: string, error?: string}>`

```javascript
// Usage with Alpine.js
<div
    x-data="{ status: null }"
    x-init="checkWebPushStatus().then(s => status = s)"
>
    <span x-show="status?.supported">Browser supports push notifications</span>
    <span x-show="status?.subscribed">Currently subscribed</span>
</div>
```

### Integration with Filament Actions

```php
use Filament\Actions\Action;

Action::make('subscribe')
    ->label('Subscribe to Notifications')
    ->color('success')
    ->extraAttributes([
        'x-data' => '',
        '@click' => 'registerWebPush().then(result => {
            if (result.success) {
                $wire.call("onSubscriptionSuccess");
                new FilamentNotification()
                    .title("Success")
                    .body(result.message)
                    .success()
                    .send();
            } else {
                new FilamentNotification()
                    .title("Error")
                    .body(result.message)
                    .danger()
                    .send();
            }
        })'
    ]);
```

## Events

### `webpush:ready`

Fired when the WebPush functions are loaded and ready to use.

```javascript
document.addEventListener("webpush:ready", () => {
    // WebPush functions are now available
});
```

## Error Handling

All functions return a consistent response format:

```javascript
{
    success: boolean,    // Whether the operation succeeded
    message: string,     // Success or error message
    error?: string       // Additional error details (only on failure)
}
```

### Common Error Messages

-   **"Push notifications are not supported in this browser"** - Browser doesn't support the Push API
-   **"Push notifications are blocked"** - User has denied permission
-   **"Permission for notifications was denied"** - User declined the permission prompt
-   **"No active subscription found"** - Trying to unsubscribe when not subscribed
-   **"VAPID public key not found"** - Missing configuration meta tags