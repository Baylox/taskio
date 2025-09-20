// vite.config.js - ENLEVE le plugin @tailwindcss/vite
import { defineConfig } from 'vite';
import symfonyPlugin from 'vite-plugin-symfony';
import { resolve } from 'path';
const PUBLIC_HOST = process.env.APP_PUBLIC_HOST || 'localhost'

export default defineConfig({
    plugins: [
        symfonyPlugin(),
    ],
    root: '.',
    base: '/build/',
    publicDir: false,
    build: {
        manifest: true,
        emptyOutDir: true,
        outDir: 'public/build',
        rollupOptions: {
            input: {
                app: './assets/app.js',
                styles: './assets/styles/app.css'
            }
        }
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, './assets'),
        }
    },
    server: {
        port: 3000,
        watch: {
            usePolling: true
        },
        strictPort: true,
        host: true,
        hmr: { host: PUBLIC_HOST, protocol: 'ws', port: 8080 },
        origin: `http://${PUBLIC_HOST}:8080`
    }
});


