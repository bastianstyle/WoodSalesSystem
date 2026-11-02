# Deployment Sumber Rezeki

## Persyaratan

- PHP 8.2+ dengan extension OpenSSL, PDO MySQL, Mbstring, XML, Ctype, JSON, Fileinfo.
- File konfigurasi OpenSSL aplikasi (`config/openssl.cnf`) harus ikut dalam deployment.
- Composer 2.x.
- Node.js dan npm untuk build asset.
- MySQL 8+.
- Web server mengarahkan document root ke folder `public/`.

## Instalasi

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jangan menjalankan `db:seed` pada production tanpa meninjau seeder. Akun demo harus diganti atau dihapus.

## Environment wajib

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.example
APP_KEY=base64:...           # buat dengan artisan key:generate
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sumber_rezeki
DB_USERNAME=...
DB_PASSWORD=...
SESSION_SECURE_COOKIE=true
```

Simpan `.env` di luar repository dan jangan masukkan private key atau kredensial ke Git.

## RSA key management

Key pair dibuat dari menu Admin > RSA Key Management atau melalui service aplikasi. Private key disimpan terenkripsi menggunakan `APP_KEY`. Karena itu:

1. Backup `APP_KEY` melalui secret manager.
2. Batasi permission database dan file aplikasi.
3. Jangan mencetak kolom `encrypted_private_key` pada log atau export.
4. Saat rotasi, jangan menghapus key berstatus `retired`; invoice lama memerlukannya.
5. Uji verifikasi invoice lama setelah setiap rotasi.

## Backup dan pemulihan

Backup database secara terjadwal, termasuk tabel `invoices`, `digital_signatures`, `rsa_keys`, dan `audit_logs`. Pemulihan harus mengembalikan database dan `APP_KEY` yang sama; tanpa `APP_KEY`, private key terenkripsi tidak dapat digunakan untuk signing baru.

## Security checklist

- `APP_DEBUG=false`.
- HTTPS aktif dan `SESSION_SECURE_COOKIE=true`.
- `public/` menjadi document root, bukan root repository.
- Rate limiting route `/verifikasi/{token}` tetap aktif.
- Password demo diganti.
- Private key tidak ditampilkan, dikirim melalui QR, atau disimpan di repository.
- Jalankan test dan `php artisan route:list` sebelum rilis.
- Pantau audit log untuk transaksi dan rotasi key.
