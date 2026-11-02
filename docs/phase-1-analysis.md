# Sumber Rezeki – Wood Sales Management System

## Status

Phase 1 — Analisis dan Perancangan.

Repository saat ini kosong. Dokumen ini menjadi baseline implementasi Laravel berikutnya.

## 1. Tujuan Sistem

Mendigitalisasi pengelolaan produk kayu, pelanggan, stok, penjualan, e-invoice, serta verifikasi integritas invoice menggunakan SHA-256 dan tanda tangan RSA-PSS.

## 2. Aktor dan Hak Akses

| Aktor | Akses |
|---|---|
| Admin | Semua fitur admin/kasir, audit log, pengelolaan dan rotasi RSA key, pengaturan sistem |
| Kasir | Dashboard, produk, pelanggan, transaksi, penerbitan invoice; tanpa akses private/public key management |
| Publik | Verifikasi invoice berdasarkan token publik; tanpa login, tanpa perubahan data |

Authorization wajib ditegakkan melalui middleware/policy di backend, bukan hanya menu frontend.

## 3. Batasan Area

### Area terproteksi

- `/admin`
- `/admin/sales`
- `/admin/products`
- `/admin/customers`
- `/admin/keys`
- `/admin/settings`

Semua route admin memerlukan autentikasi. Route key management memerlukan role `admin`.

### Area publik

- `/verifikasi`
- `/verifikasi/{public_token}`

Endpoint publik hanya mengembalikan data invoice yang aman dibagikan dan hasil verifikasi kriptografis. Tidak ada private key yang disimpan di response.

## 4. Alur Bisnis Utama

1. Pengguna berwenang login.
2. Kasir memilih pelanggan dan menambahkan satu atau lebih produk.
3. Server mengambil harga dari database, memvalidasi kuantitas, menghitung subtotal, diskon, total, pembayaran, dan kembalian.
4. Server membuka database transaction dan mengunci baris stok dengan `lockForUpdate()`.
5. Sistem memvalidasi stok, menyimpan sale dan sale details, lalu mengurangi stok.
6. Sistem membuat nomor invoice unik dan snapshot data transaksi yang telah final.
7. Sistem membentuk canonical payload deterministik.
8. Sistem menghitung SHA-256 payload dan menandatanganinya menggunakan RSA-PSS dengan private key aktif.
9. Sistem menyimpan signature, hash, algoritma, versi key, dan waktu penandatanganan.
10. Sistem membuat public token acak untuk URL verifikasi dan menghasilkan PDF/QR.
11. Jika salah satu langkah gagal, seluruh transaksi database dibatalkan dan stok tidak berubah.

## 5. Canonical Invoice Payload

Payload yang ditandatangani adalah data transaksi final, bukan byte PDF. Format awal yang direkomendasikan:

```json
{
  "schema_version":"1",
  "invoice_number":"INV-20261101-000001",
  "issued_at":"2026-11-01T10:15:30+07:00",
  "cashier_id":12,
  "customer":{"code":"CUS-0001","name":"Nama Pelanggan"},
  "items":[
    {
      "product_code":"KAYU-001",
      "name":"Meranti",
      "wood_type":"Meranti",
      "size":"4x6 cm",
      "unit":"batang",
      "quantity":"10.000",
      "unit_price":"125000.00",
      "subtotal":"1250000.00"
    }
  ],
  "discount":"0.00",
  "grand_total":"1250000.00",
  "amount_paid":"1300000.00",
  "change_amount":"50000.00"
}
```

Aturan canonicalization:

- UTF-8.
- JSON object key diurutkan rekursif.
- Tidak ada whitespace yang tidak perlu.
- Angka uang disimpan sebagai string desimal 2 digit; kuantitas sebagai string desimal 3 digit.
- Waktu memakai ISO-8601 dengan timezone aplikasi.
- Array item diurutkan berdasarkan urutan detail yang tersimpan.
- Hash: `hash('sha256', $canonicalPayload)`.
- Signature: RSA-PSS SHA-256 melalui OpenSSL, salt length mengikuti konfigurasi yang eksplisit.

Signature melindungi data sumber invoice. Perlindungan byte-for-byte PDF memerlukan PAdES dan bukan bagian dari asumsi implementasi ini.

## 6. Status Invoice dan Verifikasi

| Status | Kondisi |
|---|---|
| VALID | Invoice ditemukan, tidak dibatalkan, hash payload cocok, public key tersedia, signature RSA-PSS valid |
| INVALID | Hash tidak cocok atau signature tidak valid |
| NOT_FOUND | Token publik tidak ditemukan |
| REVOKED | Invoice telah dibatalkan |
| KEY_UNAVAILABLE | Key version invoice tidak tersedia |

Verifikasi selalu dilakukan ulang di server. Pencocokan invoice ID saja tidak cukup.

## 7. Invariants dan Aturan Data

- Kode produk, kode pelanggan, nomor invoice, dan public token harus unik.
- Harga dan stok tidak boleh negatif.
- Kuantitas penjualan harus lebih besar dari nol.
- Sale yang sudah diterbitkan tidak boleh diubah diam-diam; koreksi menggunakan pembatalan/reissue yang diaudit.
- Digital signature immutable setelah diterbitkan.
- RSA public key lama tidak boleh dihapus bila masih direferensikan signature.
- Pengurangan stok dan penyimpanan detail penjualan harus berada dalam satu database transaction.
- Semua perubahan penting dicatat pada audit log.

## 8. Daftar Route Awal

| Method | URI | Akses | Kegunaan |
|---|---|---|---|
| GET | `/login` | Guest | Form login |
| POST | `/login` | Guest | Proses login |
| POST | `/logout` | Auth | Logout |
| GET | `/admin` | Auth | Dashboard |
| GET/POST | `/admin/products`, `/admin/products/create` | Auth | Daftar/tambah produk |
| GET/PATCH/DELETE | `/admin/products/{product}/edit` | Auth | Edit/hapus produk |
| GET/POST | `/admin/customers`, `/admin/customers/create` | Auth | Daftar/tambah pelanggan |
| GET/PATCH/DELETE | `/admin/customers/{customer}/edit` | Auth | Edit/hapus pelanggan |
| GET | `/admin/sales` | Auth | Riwayat penjualan |
| GET/POST | `/admin/sales/create` | Auth | Form/proses transaksi |
| GET | `/admin/sales/{sale}` | Auth | Detail transaksi |
| GET | `/admin/sales/{sale}/invoice` | Auth | Unduh invoice PDF |
| GET/POST | `/admin/keys` | Admin | Daftar/generate/rotasi key |
| GET/PATCH | `/admin/settings` | Admin | Pengaturan |
| GET | `/verifikasi` | Publik, rate-limited | Form verifikasi |
| GET | `/verifikasi/{public_token}` | Publik, rate-limited | Verifikasi invoice |

## 9. Struktur Aplikasi yang Direncanakan

```text
app/
  Http/Controllers/Admin/
  Http/Controllers/Public/
  Http/Requests/
  Models/
  Policies/
  Services/
    Invoice/
    Cryptography/
    Pdf/
    QrCode/
  Enums/
  Events/
  Listeners/
  Providers/
database/
  factories/
  migrations/
  seeders/
resources/views/
  layouts/
  admin/
  public/
routes/
  web.php
tests/Feature/
tests/Unit/
docs/
```

Controller hanya mengatur HTTP flow. Canonicalization, signing, verification, PDF, dan QR dipisahkan ke service.

## 10. Risiko dan Keputusan Teknis

1. **RSA private key**: tidak dimasukkan Git; lokasi dan secret encryption ditentukan pada Phase 2 berdasarkan environment deployment.
2. **Key rotation**: signature menyimpan `key_version`; verifikasi mengambil public key berdasarkan versi tersebut.
3. **Concurrency stok**: gunakan row lock dan transaksi database; validasi ulang stok setelah lock.
4. **PDF integrity**: QR dan signature memvalidasi data transaksi kanonis, bukan perubahan visual/byte PDF.
5. **Public exposure**: token memakai random cryptographically secure string, bukan ID berurutan; response membatasi data pelanggan.
6. **Demo data**: seed demo akan diberi penanda dan tidak boleh dianggap data produksi.

## 11. Kriteria Selesai Phase 1

- ERD tersedia di `docs/erd.mmd`.
- Use case, activity, dan sequence diagram tersedia di `docs/diagrams.md`.
- Route, role, alur transaksi, canonical payload, dan status verifikasi terdokumentasi.
- Blocker environment dicatat sebelum Phase 2.
