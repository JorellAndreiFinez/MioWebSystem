import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/landing.css',
                'resources/css/main-app.css',
                'resources/css/about.css',
                'resources/css/admission.css',
                'resources/css/programs.css',
                'resources/css/contact.css',
                'resources/css/elms.css',
                'resources/js/landing.js',
                'resources/js/app.js',
                'resources/js/landing.js',
                'resources/js/enroll.js',
                'resources/js/about.js',
                'resources/js/admission.js',
                'resources/js/programs.js',
                'resources/js/contact.js',
                'resources/js/elms.js',
            ],
            refresh: true,
        }),
    ],
});