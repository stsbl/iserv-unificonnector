import { defineConfig } from 'vite';
import { resolve } from 'path';
import { viteFontawesomeProvider, viteWebAssetsInputs } from '@iserv/web-assets-integration/vite';
import { fileURLToPath } from 'url';
import fs from 'fs';

const __filename = fileURLToPath(import.meta.url);
const __dirname = resolve(__filename, '..');

function processStaticAssets(files: string[]) {
    const assets = new Map<string, string>();

    return {
        name: 'process-static-assets',
        buildStart() {
            files.forEach(file => {
                const output = file.startsWith('assets/') ? file.slice(7) : file;
                assets.set(file, this.emitFile({
                    type: 'asset',
                    name: output,
                    source: fs.readFileSync(file),
                }));
            });
        },
        generateBundle() {
            this.emitFile({
                type: 'asset',
                fileName: 'manifest.json',
                source: JSON.stringify(Object.fromEntries([...assets].map(([path, reference]) => [path, `static/${this.getFileName(reference)}`]))),
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
