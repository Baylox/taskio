// vite.config.js
import { defineConfig } from 'vite';
import symfonyPlugin from 'vite-plugin-symfony';
import { resolve } from 'path';

const IN_DOCKER = process.env.IN_DOCKER === '1';
const VITE_PORT = Number(process.env.VITE_PORT || 5173);
const PUBLIC_HOST = process.env.APP_PUBLIC_HOST || 'localhost';

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
        port: VITE_PORT,
        host: IN_DOCKER ? true : 'localhost',
        watch: { usePolling: IN_DOCKER },
        strictPort: true,
        ...(IN_DOCKER ? {
            hmr: { host: PUBLIC_HOST, port: VITE_PORT, protocol: 'ws' },
            origin: `http://${PUBLIC_HOST}:${VITE_PORT}`
        } : {
        })
    }
});


