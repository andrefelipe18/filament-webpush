import { defineConfig } from "vitepress";

export default defineConfig({
    title: "Filament Webpush",
    description: "Web Push notifications for Filament admin panel",
    lastUpdated: true,

    head: [
        ["link", { rel: "icon", href: "/favicon.ico" }],
        ["meta", { name: "theme-color", content: "#4f46e5" }],
        ["meta", { name: "apple-mobile-web-app-capable", content: "yes" }],
        [
            "meta",
            { name: "apple-mobile-web-app-status-bar-style", content: "black" },
        ],
    ],

    themeConfig: {
        nav: [
            { text: "Installation", link: "/" },
            { text: "Documentation", link: "/notification-usage" },
            { text: "Examples", link: "/examples" },
            {
                text: "GitHub",
                link: "https://github.com/andrefelipe18/filament-webpush",
            },
        ],

        sidebar: [
            {
                text: "Getting Started",
                items: [
                    { text: "Installation", link: "/" },
                    {
                        text: "Existing Service Worker",
                        link: "/existing-service-worker",
                    },
                    { text: "Notification Usage", link: "/notification-usage" },
                ],
            },
            {
                text: "Advanced Usage",
                items: [
                    {
                        text: "Custom Configuration",
                        link: "/custom-configuration",
                    },
                    { text: "JavaScript API", link: "/javascript-api" },
                    { text: "Usage Examples", link: "/examples" },
                ],
            },
        ],

        socialLinks: [
            {
                icon: "github",
                link: "https://github.com/andrefelipe18/filament-webpush",
            },
        ],

        search: {
            provider: "local",
        },

        editLink: {
            pattern:
                "https://github.com/andrefelipe18/filament-webpush/edit/main/docs/:path",
        },
    },
});
