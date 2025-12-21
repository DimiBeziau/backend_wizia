import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import { config } from 'dotenv';
import path from 'path';

config();

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(),
  tailwindcss()],
  define: {
    'process.env': process.env
  },
  resolve: {
    alias: {
      react: path.resolve('./node_modules/react'),
      'react-dom': path.resolve('./node_modules/react-dom'),
    },
  }
})
