# Installation with Existing Service Worker

If your project already has a service worker (`sw.js`) in the public directory, the `php artisan webpush:prepare` command will not overwrite the existing file to avoid losing already implemented functionality.

In this case, you will need to manually integrate the WebPush code into your existing service worker.

## Manual Integration Steps

### 1. Run the preparation command

First, run the preparation command normally:

```bash
php artisan webpush:prepare
```

You will see a message informing that the service worker already exists and was skipped:

```
⚠ Service worker file already exists at: /path/to/public/sw.js. Skipping copy.
ℹ See documentation for more details on how to update it.
```

### 2. Add WebPush code to your service worker

Now you need to add the following functionality to your existing service worker:

#### Event Listener for Push Notifications

Add this code to your `public/sw.js`:

```javascript
self.addEventListener("push", (event) => {
    if (!(self.Notification && self.Notification.permission === "granted")) {
        return;
    }

    const payload = event.data ? event.data.json() : {};

    event.waitUntil(
        self.registration.showNotification(payload.title, {
            body: payload.body,
            icon: payload.icon,
            badge: payload.badge,
            actions: payload.actions || [],
            data: payload.data || {},
        })
    );
});
```

#### Event Listener for Notification Clicks

Also add this code to handle notification clicks:

```javascript
self.addEventListener("notificationclick", (event) => {
    const notification = event.notification;
    notification.close();

    if (event.action === "open") {
        event.waitUntil(clients.openWindow(notification.data.action_url));
    } else {
        event.waitUntil(
            clients.openWindow(notification.data.action_url || "/app")
        );
    }
});
```

### 3. Complete service worker example

Here's an example of how your service worker might look after integration:

```javascript
"use strict";

// Your existing code...
const CACHE_NAME = "my-app-cache-v1";
const OFFLINE_URL = "/offline.html";

// WebPush code - Event listener for push notifications
self.addEventListener("push", (event) => {
    if (!(self.Notification && self.Notification.permission === "granted")) {
        return;
    }

    const payload = event.data ? event.data.json() : {};

    event.waitUntil(
        self.registration.showNotification(payload.title, {
            body: payload.body,
            icon: payload.icon,
            badge: payload.badge,
            actions: payload.actions || [],
            data: payload.data || {},
        })
    );
});

// WebPush code - Event listener for notification clicks
self.addEventListener("notificationclick", (event) => {
    const notification = event.notification;
    notification.close();

    if (event.action === "open") {
        event.waitUntil(clients.openWindow(notification.data.action_url));
    } else {
        event.waitUntil(
            clients.openWindow(notification.data.action_url || "/app")
        );
    }
});

// Rest of your existing code...
self.addEventListener("install", (event) => {
    // Your installation logic...
});

self.addEventListener("fetch", (event) => {
    // Your fetch logic...
});

self.addEventListener("activate", (event) => {
    // Your activation logic...
});
```

## Integration Verification

### 1. Test push notifications

After integrating the code, test if notifications are working:

```bash
php artisan webpush:test {user-id}
```

### 2. Check in DevTools

1. Open browser DevTools (F12)
2. Go to **Application** > **Service Workers** tab
3. Verify that your service worker is registered and active
4. Test sending a notification and check if it appears

### 3. Debug issues

If notifications are not working:

1. **Check the console** for JavaScript errors
2. **Confirm VAPID keys** in the `.env` file
3. **Test notification permission** in the browser
4. **Verify the service worker** is registered correctly

## Merging Listeners (Alternative Method)

If you prefer an automated approach to integrate the WebPush listeners, you can use the merge command:

```bash
php artisan webpush:merge-listeners
```

This command will:

1. Check if your service worker (`public/sw.js`) exists
2. Append the required WebPush event listeners to the end of your existing service worker
3. Avoid duplicating listeners if they're already present

### When to use this command

-   You have an existing service worker and want to quickly add WebPush support
-   You prefer automated integration over manual code copying
-   You want to ensure the exact listener code from the package is used

### Command output

```bash
$ php artisan webpush:merge-listeners

Merging WebPush listeners into existing service worker...
✔ WebPush listeners successfully merged into your service worker.
Your service worker now supports push notifications and notification clicks.
```

::: warning Important
This command appends code to your existing service worker. If you later update the package, you may need to manually update the listener code or remove the old listeners and run the command again.
:::

## Reference Files

If you need to consult the original package files, they are located at:

-   **Service Worker**: `vendor/andrefelipe18/filament-webpush/stubs/sw.js`
-   **WebPush JavaScript**: `vendor/andrefelipe18/filament-webpush/stubs/webpush.js`
-   **WebPush Listeners**: `vendor/andrefelipe18/filament-webpush/stubs/listeners-webpush.js`

## Important

⚠️ **Warning**: Always backup your service worker before making changes, especially if it already contains important logic for your application's functionality.

💡 **Tip**: If you're not sure how to integrate the code, consider creating a new service worker for development/testing in a separate environment first.
