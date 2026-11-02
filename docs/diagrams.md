# Diagram Perancangan

Diagram menggunakan Mermaid dan dapat dirender di GitHub, VS Code extension Mermaid, atau Mermaid Live Editor.

## Use Case Diagram

```mermaid
flowchart LR
    Admin([Admin])
    Kasir([Kasir])
    Publik([Pelanggan / Publik])
    Auth((Login))
    Dashboard((Lihat dashboard))
    Product((Kelola produk & stok))
    Customer((Kelola pelanggan))
    Sale((Buat transaksi))
    Invoice((Terbitkan invoice PDF))
    Keys((Kelola RSA key))
    Verify((Verifikasi invoice))
    Audit((Lihat audit aktivitas))

    Admin --> Auth
    Admin --> Dashboard
    Admin --> Product
    Admin --> Customer
    Admin --> Sale
    Admin --> Invoice
    Admin --> Keys
    Admin --> Audit
    Kasir --> Auth
    Kasir --> Dashboard
    Kasir --> Product
    Kasir --> Customer
    Kasir --> Sale
    Kasir --> Invoice
    Publik --> Verify
    Sale -.-> Invoice
    Invoice -.-> Verify
```

## Activity Diagram — Transaksi dan Penerbitan Invoice

```mermaid
flowchart TD
    A([Mulai]) --> B[Kasir memilih pelanggan dan produk]
    B --> C[Server validasi input dan hitung total]
    C --> D{Input valid?}
    D -- Tidak --> E[Tampilkan error]
    E --> Z([Selesai])
    D -- Ya --> F[Buka database transaction]
    F --> G[Kunci baris stok]
    G --> H{Stok mencukupi?}
    H -- Tidak --> I[Rollback transaction]
    I --> E
    H -- Ya --> J[Simpan sale dan sale details]
    J --> K[Kurangi stok]
    K --> L[Buat canonical payload]
    L --> M[Hash SHA-256 dan sign RSA-PSS]
    M --> N[Simpan invoice, signature, key version]
    N --> O[Commit transaction]
    O --> P[Generate PDF dan QR URL]
    P --> Q[Tampilkan invoice]
    Q --> Z
```

## Sequence Diagram — Verifikasi Publik

```mermaid
sequenceDiagram
    actor Customer as Publik
    participant Browser
    participant App as Laravel App
    participant DB as Database
    participant Crypto as VerificationService

    Customer->>Browser: Buka /verifikasi/{public_token}
    Browser->>App: GET token
    App->>DB: Cari invoice dan signature berdasarkan token
    alt Token tidak ditemukan
        DB-->>App: Tidak ada data
        App-->>Browser: NOT FOUND
    else Invoice ditemukan
        DB-->>App: Payload, hash, signature, key version, status
        App->>Crypto: Hash ulang payload canonical
        Crypto->>DB: Ambil public key berdasarkan key version
        DB-->>Crypto: Public key/status
        Crypto->>Crypto: Bandingkan hash dan verify RSA-PSS
        Crypto-->>App: VALID/INVALID/REVOKED/KEY UNAVAILABLE
        App-->>Browser: Status dan data invoice terbatas
    end
```

## Sequence Diagram — Rotasi RSA Key

```mermaid
sequenceDiagram
    actor Admin
    participant Browser
    participant App as Laravel App
    participant KeyService
    participant DB as Database

    Admin->>Browser: Minta generate/rotate key
    Browser->>App: POST /admin/keys
    App->>App: Authorize role admin
    App->>KeyService: Generate RSA 2048+ key pair
    KeyService-->>App: Public key + encrypted private key
    App->>DB: Simpan key version baru sebagai active
    App->>DB: Retire key lama tanpa menghapusnya
    DB-->>App: Berhasil
    App-->>Browser: Metadata public key (tanpa private key)
```
