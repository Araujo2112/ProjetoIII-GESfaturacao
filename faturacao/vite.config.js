import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/dashboard.js',
                'resources/js/clientes/topClientes.js',
                'resources/js/fornecedores/topFornecedores.js',
                'resources/js/relatorios/diarioVendas.js',
                'resources/js/relatorios/pagamentos.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
