import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';

/**
 * Multi-Tenant Vite Configuration
 * Supports dynamic subdomains: {tenant}.pesantren.test (dev) / {tenant}.pesantren.com (prod)
 * NO hardcoded domains - fully environment-driven
 */
export default defineConfig(({ command, mode }) => {
    // Load env file based on mode
    const env = loadEnv(mode, process.cwd(), '');

    // Determine mode
    const isDev = command === 'serve';

    // Domain configuration from .env (NO hardcoding!)
    const appDomain = env.APP_DOMAIN || 'pesantren.test';
    const appScheme = env.APP_SCHEME || 'http';

    // HMR host - use localhost for cross-subdomain dev, or env override
    // This allows HMR websocket to connect to the dev server regardless of which subdomain loads the page
    const hmrHost = env.VITE_DEV_SERVER_HMR_HOST || 'localhost';
    const hmrPort = parseInt(env.VITE_DEV_SERVER_PORT || '5173');

    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/css/landing.css',
                    'resources/js/app.js',
                ],
                refresh: [
                    'resources/views/**/*.blade.php',
                    'routes/**/*.php',
                ],
                buildDirectory: 'build',
            }),
        ],

        // Dev server configuration
        server: isDev ? {
            // Listen on all network interfaces (required for subdomain access)
            host: true,
            port: hmrPort,
            strictPort: true,

            // Allow CORS from ANY tenant subdomain
            cors: {
                origin: new RegExp(`^${appScheme}://.*\\.${appDomain.replace(/\./g, '\\.')}$`),
                credentials: true,
            },

            // HMR: Connect to localhost dev server from any subdomain
            hmr: {
                host: hmrHost,
                port: hmrPort,
                protocol: 'ws',
            },

            // File watching
            watch: {
                usePolling: process.platform === 'win32',
            },
        } : undefined,

        // Build configuration (production)
        build: {
            outDir: 'public/build',
            manifest: 'manifest.json',
            emptyOutDir: true,
            minify: 'esbuild',
            sourcemap: isDev,
            rollupOptions: {
                output: {
                    entryFileNames: 'assets/[name]-[hash].js',
                    chunkFileNames: 'assets/[name]-[hash].js',
                    assetFileNames: 'assets/[name]-[hash][extname]',
                },
            },
        },

        // CSS processing
        css: {
            postcss: './postcss.config.js',
            devSourcemap: isDev,
        },

        // Path aliases
        resolve: {
            alias: {
                '@': resolve(__dirname, 'resources/js'),
                '~': resolve(__dirname, 'resources'),
            },
        },
    };
});
