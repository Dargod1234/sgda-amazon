import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/sass/app.scss',
        'resources/js/app.js',
      ],
      refresh: true,
    }),
  ],
  build: {
    outDir: 'public/build', // Coloca aquí los archivos generados
    manifest: true, // Genera el archivo manifest.json
    assetsDir: 'assets', // Directorio de salida para assets dentro de public/build
  },
});
