import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'public/build', // Куда складывать статику
    emptyOutDir: true,      // Очищать папку перед каждой сборкой
    manifest: true,         // Обязательно для связи с PHP
    rollupOptions: {
      input: [
        'src/js/script.js',
        'src/css/style.css' // Добавь сюда путь к твоему CSS, чтобы он тоже собирался
      ]
    }
  },
  publicDir: false, // ЭТО ВАЖНО: отключает копирование всей папки public внутрь билда
  server: {
    cors: true,
    strictPort: true,
    port: 5173
  }
});