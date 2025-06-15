import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/cms/app.css',
                'resources/css/cms/landing.css',
                'resources/css/cms/main-app.css',
                'resources/css/cms/about.css',
                'resources/css/cms/program.css',
                'resources/css/cms/campus.css',
                'resources/css/cms/news.css',
                'resources/css/cms/events.css',

                'resources/js/cms/landing.js',
                'resources/js/cms/app.js',
                'resources/js/cms/landing.js',
                'resources/js/cms/enroll.js',
                'resources/js/cms/about.js',
                'resources/js/cms/program.js',
                'resources/js/cms/campus.js',


                'resources/css/Mio/mio-app.css',
                'resources/css/Mio/login.css',
                'resources/js/Mio/login.js',
                'resources/css/Mio/dashboard/dashboard.css',
                'resources/js/Mio/dashboard/dashboard.js',
                'resources/css/Mio/dashboard/calendar.css',
                'resources/js/Mio/dashboard/calendar.js',
                'resources/css/Mio/dashboard/inbox.css',

                'resources/css/Mio/dashboard/subject-components.css',

                'resources/css/Mio/admin/panel.css',
                'resources/js/Mio/admin/panel.js',

                'resources/css/EnrollPanel/enrollment-panel.css',
            ],
            refresh: true,
        }),
    ],
    build: {
        assetsInlineLimit: 0,
    },
});
