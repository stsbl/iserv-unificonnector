import { defineConfig } from 'vite';
import { resolve } from 'path';
import { viteFontawesomeProvider, viteWebAssetsInputs } from '@iserv/web-assets-integration/vite';
import { fileURLToPath } from 'url';
import fs from 'fs';

const __filename = fileURLToPath(import.meta.url);
const __dirname = resolve(__filename, '..');

function processStaticAssets(files: string[]) {
    return {
        name: 'process-static-assets',
        buildEnd() {
            files.forEach(file => {
                const output = file.startsWith('assets/') ? file.slice(7) : file;
                this.emitFile({
                    type: 'asset',
                    name: output,
                    source: fs.readFileSync(file),
                });
            });
        }
    };
}

export default defineConfig(({ mode }) => {
    const prod = mode !== 'development';

    return {
        plugins: [
            viteFontawesomeProvider(),
            viteWebAssetsInputs({
                inputName: '@iserv/web-assets',
                entries: ['@iserv/web-assets-integration/components', '@iserv/web-assets-integration/styles'],
            }),
            processStaticAssets(['assets/img/unificonnector.svg']),
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
                    'assets/img/unificonnector.svg',
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
