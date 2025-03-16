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
                'resources/css/program.css',
                'resources/css/campus.css',
                'resources/css/news.css',
                'resources/css/events.css',

                'resources/js/landing.js',
                'resources/js/app.js',
                'resources/js/landing.js',
                'resources/js/enroll.js',
                'resources/js/about.js',
                'resources/js/program.js',
                'resources/js/campus.js',


                'resources/css/Mio/mio-app.css',
                'resources/css/Mio/login.css',
                'resources/js/Mio/login.js',
                'resources/css/Mio/dashboard/dashboard.css',
                'resources/js/Mio/dashboard/dashboard.js',
                'resources/css/Mio/dashboard/calendar.css',
                'resources/js/Mio/dashboard/calendar.js',
                'resources/css/Mio/dashboard/subject.css',
                'resources/js/Mio/dashboard/subject.js',
                'resources/css/Mio/dashboard/inbox.css',

                'resources/css/Mio/dashboard/subject-components.css',

                'resources/css/Mio/admin/login.css',
                'resources/js/Mio/admin/login.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        assetsInlineLimit: 0,
    },
});
