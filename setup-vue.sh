#!/bin/bash

echo "🎨 Setting up Vue.js with Vite and Tailwind CSS..."

# Install Vue.js and Vite
echo "📦 Installing Vue.js and Vite..."
docker-compose run --rm node npm install -D vite @vitejs/plugin-vue vue@next

# Install Tailwind CSS
echo "🎨 Installing Tailwind CSS..."
docker-compose run --rm node npm install -D tailwindcss postcss autoprefixer
docker-compose run --rm node npx tailwindcss init -p

# Install additional dependencies
echo "📦 Installing additional dependencies..."
docker-compose run --rm node npm install -D @vueuse/core axios

echo "✅ Vue.js and Tailwind CSS installation complete!"