import { defineConfig } from 'vite';
import { resolve } from 'path';
import { viteFontawesomeProvider, viteWebAssetsInputs } from '@iserv/web-assets-integration/vite';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = resolve(__filename, '..');

export default defineConfig(({ mode }) => {
    const prod = mode !== 'development';

    return {
        plugins: [
            viteFontawesomeProvider(),
            viteWebAssetsInputs({
                inputName: '@iserv/web-assets',
                entries: ['@iserv/web-assets-integration/components', '@iserv/web-assets-integration/styles'],
            }),
        ],
        base: '/iserv/unificonnector/static/',
        build: {
            outDir: './public/static',
            assetsDir: './assets',
            manifest: true,
            emptyOutDir: true,
            sourcemap: true,
            copyPublicDir: false,
            minify: prod,
            rollupOptions: {
                input: [
                    'assets/css/unificonnector.less',
                    'assets/js/main.js',
                ],
            },
        },
        resolve: {
            extensions: ['.js', '.json', '.less'],
            alias: {
                '@': resolve(__dirname, 'assets/js'),
            },
        },
        css: {
            preprocessorOptions: {
                less: {
                    javascriptEnabled: true,
                },
            },
        },
    };
});
