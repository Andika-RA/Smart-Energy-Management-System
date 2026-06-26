# Menggunakan base image Node.js 18 versi Alpine (sangat ringan)
FROM node:18-alpine

# Menentukan direktori kerja di dalam container
WORKDIR /app

# Menyalin file konfigurasi dependensi terlebih dahulu (memanfaatkan Docker cache)
COPY package*.json ./

# Menginstal dependensi Node.js
RUN npm install

# Menyalin seluruh kode sumber (termasuk folder src/ dan isinya)
COPY . .

# Membuka port 3000 sesuai dengan spesifikasi Gateway API
EXPOSE 3000

# Perintah untuk menjalankan service (asumsi file utama ada di src/index.js)
CMD ["node", "src/index.js"]