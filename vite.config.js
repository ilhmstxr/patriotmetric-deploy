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
        origin: "http://127.0.0.1:5173",
        cors: {
            origin: "*",
        },
        get svelte() {
            return undefined;
        }, // abaikan jika tidak pakai svelte
        watch: {
            usePolling: true, // Wajib bagi pengguna Windows + Docker
        },
        hmr: {
            host: "127.0.0.1",
        },
    },
});
