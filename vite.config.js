import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import collectModuleAssetsPaths from "./vite-module-loader.js";

async function getConfig() {
    const paths = [
        "resources/css/app.scss",
        "resources/assets/scss/main.scss",
        "resources/assets/css/bootstrap.min.css",
        "resources/assets/css/line-awesome.min.css",
        "resources/assets/css/material.css",
        "resources/assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css",
        "resources/assets/css/style.css",
        "resources/js/app.js",
        "resources/assets/js/bootstrap.bundle.min.js",
        "resources/assets/js/jquery.slimscroll.min.js",
        "resources/assets/plugins/jquery-repeater/jquery.repeater.min.js",
        "resources/assets/js/app.js",
        "resources/js/datatables.js",
        "resources/js/ckeditor.js",
        "resources/js/app/chat/chat-app.js",
    ];
    const allPaths = await collectModuleAssetsPaths(paths, "Modules");

    return defineConfig({
        plugins: [
            laravel({
                input: allPaths,
                refresh: true,
            }),
        ],
        build: {
            // Enable CSS code splitting
            cssCodeSplit: true,
            // Use esbuild for fast minification
            minify: 'esbuild',
            // Code splitting configuration
            rollupOptions: {
                output: {
                    manualChunks: {
                        // Vendor chunks for better caching
                        'vendor-jquery': ['jquery'],
                        'vendor-bootstrap': ['bootstrap'],
                        'vendor-datatables': ['datatables.net-bs5', 'datatables.net-buttons-bs5'],
                    },
                },
            },
            // Generate source maps for debugging
            sourcemap: false,
            // Target modern browsers
            target: 'es2015',
        },
        define: {
            "process.env.IS_PREACT": JSON.stringify("true"),
        },
        optimizeDeps: {
            exclude: ["js-big-decimal"],
        },
        assetsInlineLimit: 0,
    });
}

export default getConfig();

