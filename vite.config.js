import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs';
import {
    resolve
} from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        port: 5600,
        host: '0.0.0.0',
        hmr: {
            host: 'localhost'
        },
        https: {
            key: fs.readFileSync(resolve(__dirname, 'ssl/key.pem')),
            cert: fs.readFileSync(resolve(__dirname, 'ssl/cert.pem')),
        },
    },
});
