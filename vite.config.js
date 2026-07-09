import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'public/build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: [
        'src/js/script.js',
        'src/css/style.css',
        'src/css/responsive.css'
      ]
    }
  },
  publicDir: false,
  server: {
    cors: true,
    strictPort: true,
    port: 5173
  }
});