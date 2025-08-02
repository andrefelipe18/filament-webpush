// WebPush Event Listeners for Filament WebPush Package
// This code should be appended to your existing service worker

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
