# Menggunakan base image Node.js 18 versi Alpine
FROM node:18-alpine

# Menentukan direktori kerja di dalam container
WORKDIR /app

# Menyalin file konfigurasi dependensi
COPY package*.json ./

# Menginstal dependensi Node.js
RUN npm install

# Menyalin seluruh kode sumber (termasuk folder src/ dan isinya)
COPY . .

# Membuka port 3002 sesuai dengan spesifikasi OAuth 2.0 Server
EXPOSE 3002

# Perintah untuk menjalankan service
CMD ["node", "src/index.js"]