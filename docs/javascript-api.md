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

### Full Component Example

```blade
<div
    x-data="{
        hasSubscription: false,
        loading: false,

        async enableWebPush() {
            if (this.loading) {
                return;
            }

            this.loading = true;
            await window.registerWebPush()
                .then(() => {
                    this.loading = false;
                    this.hasSubscription = true;
                    new FilamentNotification()
                        .title('{{ __('Notifications Enabled') }}')
                        .success()
                        .send()
                })
                .catch(() => {
                    this.loading = false;
                    new FilamentNotification()
                        .title('{{ __('Error') }}')
                        .body('{{ __('Failed to enable notifications') }}')
                        .danger()
                        .send()
                });
        },

        async disableWebPush() {
            if (this.loading) {
                return;
            }

            this.loading = true;
            await window.unregisterWebPush()
                .then(() => {
                    this.loading = false;
                    this.hasSubscription = false;
                    new FilamentNotification()
                        .title('{{ __('Notifications Disabled') }}')
                        .body('{{ __('You have successfully unsubscribed from notifications') }}')
                        .success()
                        .send()
                })
                .catch(() => {
                    this.loading = false;
                    new FilamentNotification()
                        .title('{{ __('Error') }}')
                        .body('{{ __('Failed to disable notifications') }}')
                        .danger()
                        .send()
                });
        },

        async init() {
            await window.checkWebPushStatus()
                .then((result) => {
                    if (result.supported === false) {
                        new FilamentNotification()
                            .title('{{ __('Web Push Notifications are not supported by your browser.') }}')
                            .body('{{ __('Use Chrome (Windows, Android, Mac) or Safari (iOS) for Web Push Notifications.') }}')
                            .danger()
                            .send();

                        return;
                    }

                    this.hasSubscription = result.subscribed;
                });
        }
    }"
>
    <x-filament::button
        @click="enableWebPush"
        x-show="!hasSubscription"
        x-bind:disabled="loading"
        color="primary"
    >
        <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" x-show="loading" class="size-5 mr-2 animate-spin text-white"
                 fill="#000000"
                 viewBox="0 0 256 256">
                <path
                    d="M232,128a104,104,0,0,1-208,0c0-41,23.81-78.36,60.66-95.27a8,8,0,0,1,6.68,14.54C60.15,61.59,40,93.27,40,128a88,88,0,0,0,176,0c0-34.73-20.15-66.41-51.34-80.73a8,8,0,0,1,6.68-14.54C208.19,49.64,232,87,232,128Z"></path>
            </svg>

            <span x-text="loading ? '{{ __('Enabling...') }}' : '{{ __('Enable Notifications') }}'"></span>
        </div>
    </x-filament::button>

    <x-filament::button
        @click="disableWebPush"
        x-show="hasSubscription"
        x-bind:disabled="loading"
        color="danger"
        outlined
    >
        <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" x-show="loading" class="size-5 mr-2 animate-spin text-danger-500"
                 fill="#000000"
                 viewBox="0 0 256 256">
                <path
                    d="M232,128a104,104,0,0,1-208,0c0-41,23.81-78.36,60.66-95.27a8,8,0,0,1,6.68,14.54C60.15,61.59,40,93.27,40,128a88,88,0,0,0,176,0c0-34.73-20.15-66.41-51.34-80.73a8,8,0,0,1,6.68-14.54C208.19,49.64,232,87,232,128Z"></path>
            </svg>

            <span x-text="loading ? '{{ __('Disabling...') }}' : '{{ __('Disable Notifications') }}'"></span>
        </div>
    </x-filament::button>
</div>
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