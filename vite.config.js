import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

const port = Number(process.env.PORT) || 5173;

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            refresh: true,
        }),
        react(),
    ],
    server: {
        port,
        // Advertise a hostname (not [::1]) so the CSP source list accepts it.
        origin: `http://localhost:${port}`,
        cors: { origin: /^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/ },
    },
});
