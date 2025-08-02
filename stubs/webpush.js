(function() {
    "use strict";

    // Prevent multiple initialization
    if (window.filamentWebpushInitialized) {
        return;
    }

    window.filamentWebpushInitialized = true;

    // Get configuration from meta tags
    const getConfig = () => {
        return {
            vapidPublicKey: document.querySelector(
                'meta[name="vapid-public-key"]'
            )?.content,
            storeUrl: document.querySelector('meta[name="webpush-store"]')
                ?.content,
            destroyUrl: document.querySelector('meta[name="webpush-destroy"]')
                ?.content,
            csrfToken: document.querySelector('meta[name="csrf-token"]')
                ?.content,
        };
    };

    // Function to convert Base64 to Uint8Array for applicationServerKey
    const urlBase64ToUint8Array = (base64String) => {
        const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, "+")
            .replace(/_/g, "/");

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }

        return outputArray;
    };

    // Register service worker
    const initializeServiceWorker = async () => {
        if ("serviceWorker" in navigator) {
            try {
                await navigator.serviceWorker.register("/sw.js");
            } catch (error) {
                throw new Error("Failed to register service worker");
            }
        } else {
            throw new Error("Service workers are not supported");
        }
    };

    // Subscribe user to push notifications
    const subscribeUser = async () => {
        const config = getConfig();

        if (!config.vapidPublicKey) {
            throw new Error("VAPID public key not found");
        }

        if (!config.storeUrl) {
            throw new Error("Store URL not found");
        }

        const registration = await navigator.serviceWorker.ready;

        // Check if already subscribed
        let subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            // Subscribe to push notifications
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(
                    config.vapidPublicKey
                ),
            });
        }

        // Send subscription to server
        const response = await fetch(config.storeUrl, {
            method: "POST",
            body: JSON.stringify(subscription),
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": config.csrfToken,
            },
        });

        if (!response.ok) {
            throw new Error("Failed to store subscription on server");
        }

        return subscription;
    };

    // Global function to register for push notifications
    window.registerWebPush = async function() {
        try {
            if (!("serviceWorker" in navigator) || !("PushManager" in window)) {
                throw new Error(
                    "Push notifications are not supported in this browser"
                );
            }

            if (Notification.permission === "denied") {
                throw new Error(
                    "Push notifications are blocked. Please enable them in your browser settings."
                );
            }

            await initializeServiceWorker();

            if (Notification.permission !== "granted") {
                const permission = await Notification.requestPermission();
                if (permission !== "granted") {
                    throw new Error("Permission for notifications was denied");
                }
            }

            await subscribeUser();

            return {
                success: true,
                message: "Successfully subscribed to push notifications",
            };
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    };

    // Global function to unregister from push notifications
    window.unregisterWebPush = async function() {
        try {
            const config = getConfig();

            if (!config.destroyUrl) {
                throw new Error("Destroy URL not found");
            }

            const registration = await navigator.serviceWorker.ready;
            const subscription =
                await registration.pushManager.getSubscription();

            if (!subscription) {
                return {
                    success: false,
                    message: "No active subscription found",
                };
            }

            // Unsubscribe locally
            await subscription.unsubscribe();

            // Remove subscription from server
            const response = await fetch(config.destroyUrl, {
                method: "POST",
                body: JSON.stringify({
                    endpoint: subscription.endpoint
                }),
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": config.csrfToken,
                },
            });

            if (!response.ok) {
                throw new Error("Failed to remove subscription from server");
            }

            return {
                success: true,
                message: "Successfully unsubscribed from push notifications",
            };
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    };

    // Global function to check push notification status
    window.checkWebPushStatus = async function() {
        try {
            if (!("serviceWorker" in navigator) || !("PushManager" in window)) {
                return {
                    supported: false,
                    subscribed: false,
                    permission: "unsupported",
                };
            }

            const registration = await navigator.serviceWorker.ready;
            const subscription =
                await registration.pushManager.getSubscription();

            return {
                supported: true,
                subscribed: !!subscription,
                permission: Notification.permission,
            };
        } catch (error) {
            return {
                supported: false,
                subscribed: false,
                permission: "error",
                error: error.message,
            };
        }
    };

    // Auto-initialize when DOM is ready
    const autoInitialize = async () => {
        try {
            await initializeServiceWorker();

            // Auto-subscribe if permission is already granted
            if (Notification.permission === "granted") {
                const registration = await navigator.serviceWorker.ready;
                const subscription =
                    await registration.pushManager.getSubscription();

                if (!subscription) {
                    await subscribeUser();
                }
            }
        } catch (error) {
            // Silent fail for auto-initialization
        }

        // Dispatch ready event
        window.dispatchEvent(new CustomEvent("webpush:ready"));
    };

    // Initialize when DOM is ready
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", autoInitialize);
    } else {
        autoInitialize();
    }
})();