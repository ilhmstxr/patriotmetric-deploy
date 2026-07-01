import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        tailwindcss(),
    ],
    // Tambahkan blok server ini:
    server: {
        host: "0.0.0.0",
        port: 5173,
        get svelte() {
            return undefined;
        }, // abaikan jika tidak pakai svelte
        watch: {
            usePolling: true, // Wajib bagi pengguna Windows + Docker
        },
        hmr: {
            host: "localhost",
        },
    },
});
