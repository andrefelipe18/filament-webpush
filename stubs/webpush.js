(function () {
    "use strict";

    // Prevent multiple initialization
    if (window.filamentWebpushInitialized) {
        return;
    }

    window.filamentWebpushInitialized = true;

    const registerFilamentWebPush = () => {
        if (!("serviceWorker" in navigator) || !("PushManager" in window)) {
            console.error("Web Push is not supported in this browser");
            return;
        }

        // Register service worker
        navigator.serviceWorker.register("/sw.js");

        // Get VAPID public key
        const vapidPublicKey = document.querySelector(
            'meta[name="vapid-public-key"]'
        )?.content;

        if (!vapidPublicKey) {
            console.error("VAPID public key not found");
            return;
        }

        const webpushStore = document.querySelector(
            'meta[name="webpush-store"]'
        )?.content;

        const webpushDestroy = document.querySelector(
            'meta[name="webpush-destroy"]'
        )?.content;

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

        navigator.serviceWorker.ready
            .then((registration) => {
                // Check if we already have a subscription
                return registration.pushManager
                    .getSubscription()
                    .then((subscription) => {
                        if (subscription) {
                            // We already have a subscription
                            return subscription;
                        }

                        // Request permission
                        return Notification.requestPermission().then(
                            (permission) => {
                                if (permission !== "granted") {
                                    throw new Error("Permission denied");
                                }

                                // Subscribe
                                return registration.pushManager.subscribe({
                                    userVisibleOnly: true,
                                    applicationServerKey:
                                        urlBase64ToUint8Array(vapidPublicKey),
                                });
                            }
                        );
                    });
            })
            .then((subscription) => {
                return fetch(webpushStore, {
                    method: "POST",
                    body: JSON.stringify(subscription),
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        )?.content,
                    },
                });
            })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Failed to store subscription");
                }
                return response.json();
            })
            .then((data) => {
                // ...
            })
            .catch((error) => {
                console.error("Error setting up push notifications:", error);
            });
    };

    // Register service worker and set up push when DOM is fully loaded
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", registerFilamentWebPush);
    } else {
        registerFilamentWebPush();
    }
})();
