import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig(() => ({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
  ],
  server: {
    host: '0.0.0.0',
    port: 5173,
    hmr: {
      host: 'tvi-website.ddev.site',
      protocol: 'wss',
      clientPort: 443,
    },
    watch: {
      usePolling: true,
      interval: 300,
    },
  },
}))
