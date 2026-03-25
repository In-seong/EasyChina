import { defineConfig, Plugin } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// dev 서버에서 / 접속 시 admin.html로 리다이렉트
function adminRedirect(): Plugin {
  return {
    name: 'admin-redirect',
    configureServer(server) {
      server.middlewares.use((req, res, next) => {
        if (req.url === '/' || req.url === '/index.html') {
          req.url = '/admin.html'
        }
        next()
      })
    },
  }
}

export default defineConfig({
  plugins: [vue(), adminRedirect()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
      '@shared': path.resolve(__dirname, 'src/shared'),
    },
  },
  root: '.',
  build: {
    outDir: 'dist/admin',
    rollupOptions: {
      input: path.resolve(__dirname, 'admin.html'),
    },
  },
  server: {
    port: 5174,
  },
})
