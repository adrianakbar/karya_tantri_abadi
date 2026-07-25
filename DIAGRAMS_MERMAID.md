# Diagram Mermaid — Karya Tantri Abadi (sistem final)

Kode Mermaid siap tempel (GitHub / mermaid.live / VS Code).  
Disesuaikan implementasi final: **Admin, SPV, Kasir, Anggota**; petugas **offline**; **tanpa POS/SHU/Bendahara**.

| # | Diagram |
|---|---------|
| 1 | Use Case |
| 2 | Arsitektur multi-panel |
| 3 | Activity — Login |
| 4 | Activity — Tabungan |
| 5 | Activity — Pinjaman kelompok |
| 6 | Activity — Cicilan |
| 7 | Sequence — Pinjaman |
| 8 | Sequence — Tabungan |
| 9 | ERD (logical) |
| 10 | Flowchart R&D 10 tahap |

**Jangan pakai (usang):** activity POS/SHU, UCD Bendahara/Kepala Yayasan, anggota ajukan pinjaman mandiri di sistem.

---

## 1. Use Case Diagram

```mermaid
flowchart LR
  subgraph Aktor
    Admin((Admin))
    SPV((SPV))
    Kasir((Kasir))
    Anggota((Anggota / Ketua Kelompok))
    Petugas((Petugas lapangan<br/>offline))
  end

  subgraph Sistem["Sistem Koperasi Simpan Pinjam"]
    UC1[Login multi-panel]
    UC2[Kelola data anggota]
    UC3[Input pinjaman kelompok]
    UC4[Setujui / tolak pinjaman]
    UC5[Cairkan pinjaman]
    UC6[Catat cicilan]
    UC7[Catat tabungan]
    UC8[Lihat laporan]
    UC9[Pantau pinjaman sendiri]
    UC10[Backup / pengaturan]
  end

  Admin --> UC1
  Admin --> UC2
  Admin --> UC3
  Admin --> UC6
  Admin --> UC7
  Admin --> UC8
  Admin --> UC10

  SPV --> UC1
  SPV --> UC4
  SPV --> UC8

  Kasir --> UC1
  Kasir --> UC5
  Kasir --> UC7

  Anggota --> UC1
  Anggota --> UC9

  Petugas -. setor data/uang ke Admin .-> UC3
  Petugas -. setor cicilan ke Admin .-> UC6
```

---

## 2. Arsitektur multi-panel

```mermaid
flowchart TB
  User[Pengguna mitra] --> Login["/auth/login"]
  Login --> Router{Role?}
  Router -->|admin| P1["Panel /admin<br/>Filament"]
  Router -->|spv| P2["Panel /spv"]
  Router -->|kasir| P3["Panel /kasir"]
  Router -->|anggota| P4["Panel /anggota"]

  P1 & P2 & P3 & P4 --> App[Laravel + Filament + Livewire]
  App --> DB[(MySQL)]
  App --> File[Storage / PDF laporan]

  Offline[Petugas lapangan offline] -. tidak login .-> AdminOps[Serah data ke Admin]
  AdminOps --> P1
```

---

## 3. Activity — Login multi-panel

```mermaid
flowchart TD
  Start([Mulai]) --> Form[Buka halaman login]
  Form --> Isi[Input email + password]
  Isi --> Auth{Kredensial valid?}
  Auth -- Tidak --> Err[Tampil error] --> Form
  Auth -- Ya --> Role{Role user?}
  Role -- admin --> A["Redirect /admin"]
  Role -- spv --> S["Redirect /spv"]
  Role -- kasir --> K["Redirect /kasir"]
  Role -- anggota --> M["Redirect /anggota"]
  A & S & K & M --> End([Selesai])
```

---

## 4. Activity — Tabungan

```mermaid
flowchart TD
  Start([Mulai]) --> Login[Kasir/Admin login]
  Login --> Menu[Buka menu Tabungan]
  Menu --> Form[Pilih anggota, jenis, nominal, tanggal]
  Form --> Valid{Nominal valid > 0?}
  Valid -- Tidak --> Err[Tampil validasi] --> Form
  Valid -- Ya --> Save[Simpan transaksi tabungan]
  Save --> Lap[Update ringkasan / laporan]
  Lap --> Cetak{Cetak kuitansi?}
  Cetak -- Ya --> PDF[Generate PDF]
  Cetak -- Tidak --> End([Selesai])
  PDF --> End
```

---

## 5. Activity — Pinjaman kelompok

```mermaid
flowchart TD
  Start([Mulai]) --> Offline[Petugas/anggota ajukan offline]
  Offline --> Admin[Admin input pinjaman status pending]
  Admin --> Fee[Sistem hitung fee tier<br/>angsuran 11% + admin 5%<br/>UTJ 22%/11% → cair 73%/84%]
  Fee --> SPV{SPV setujui?}
  SPV -- Tolak --> Reject[Status rejected]
  Reject --> End1([Selesai])
  SPV -- Ya --> OK[Status approved]
  OK --> Kasir[Kasir cairkan]
  Kasir --> Gen[Generate jadwal cicilan<br/>mis. 12x mingguan 3 bln]
  Gen --> Aktif[Status active]
  Aktif --> Lihat[Anggota lihat pinjaman sendiri]
  Lihat --> End2([Selesai])
```

---

## 6. Activity — Cicilan

```mermaid
flowchart TD
  Start([Mulai]) --> Petugas[Petugas terima cicilan offline]
  Petugas --> Serah[Serah ke Admin]
  Serah --> Buka[Admin buka detail pinjaman]
  Buka --> Catat[Admin Catat Bayar]
  Catat --> Upd[Update loan_payments + sisa]
  Upd --> Cek{Lunas semua?}
  Cek -- Tidak --> End1([Selesai - masih berjalan])
  Cek -- Ya --> Lunas[Status lunas/completed]
  Lunas --> End2([Selesai])
```

> Kasir/SPV hanya melihat jadwal cicilan; **tidak** mencatat pembayaran.

---

## 7. Sequence — Pinjaman

```mermaid
sequenceDiagram
  actor Petugas as Petugas (offline)
  actor Admin
  actor SPV
  actor Kasir
  actor Anggota
  participant S as Sistem
  participant DB as MySQL

  Petugas->>Admin: Serah pengajuan kelompok
  Admin->>S: Input pinjaman (pending)
  S->>S: Hitung fee tier + cair bersih
  S->>DB: Simpan loans
  Admin->>SPV: Menunggu persetujuan
  SPV->>S: Setujui / Tolak
  alt Ditolak
    S->>DB: status = rejected
  else Disetujui
    S->>DB: status = approved
    Kasir->>S: Cairkan
    S->>DB: status = active + generate loan_payments
    Anggota->>S: Lihat pinjaman sendiri
    S->>DB: Query by user_id
    S-->>Anggota: Detail cair/angsuran/sisa
  end
```

---

## 8. Sequence — Tabungan

```mermaid
sequenceDiagram
  actor Kasir
  actor Admin
  participant S as Sistem
  participant DB as MySQL

  Kasir->>S: Buka form tabungan
  Kasir->>S: Pilih anggota + nominal
  S->>S: Validasi nominal
  alt Invalid
    S-->>Kasir: Error validasi
  else Valid
    S->>DB: Insert savings_transactions
    S-->>Kasir: Sukses (+ opsi kuitansi)
    Admin->>S: Pantau / edit / laporan tabungan
    S->>DB: Query laporan
    S-->>Admin: Data rekap
  end
```

---

## 9. ERD (logical)

```mermaid
erDiagram
  USERS ||--o{ SAVINGS_TRANSACTIONS : memiliki
  USERS ||--o{ LOANS : "ketua/anggota terkait"
  LOANS ||--|{ LOAN_PAYMENTS : "jadwal cicilan"
  USERS {
    int id PK
    string name
    string email
    string role "admin|spv|kasir|anggota"
  }
  SAVINGS_TRANSACTIONS {
    int id PK
    int user_id FK
    string type
    decimal amount
    date date
  }
  LOANS {
    int id PK
    int user_id FK
    decimal principal
    decimal admin_fee
    decimal utj_fee
    decimal net_disbursement
    string status "pending|approved|rejected|active|completed"
    int tenor_months
  }
  LOAN_PAYMENTS {
    int id PK
    int loan_id FK
    int installment_no
    decimal amount
    date due_date
    string status "unpaid|paid"
    date paid_at
  }
```

---

## 10. Flowchart R&D 10 tahap

```mermaid
flowchart TD
  A([Mulai]) --> T1[1. Research and Information Collection]
  T1 --> T2[2. Planning]
  T2 --> T3[3. Develop Preliminary Product]
  T3 --> T4[4. Preliminary Field Testing<br/>Black Box awal]
  T4 --> D1{Lolos?}
  D1 -- Tidak --> T5[5. Main Product Revision] --> T4
  D1 -- Ya --> T6[6. Main Field Testing]
  T6 --> T7[7. Operational Product Revision]
  T7 --> T8[8. Operational Field Testing<br/>Black Box + UAT]
  T8 --> D2{Diterima?}
  D2 -- Tidak --> T9a[9. Final Product Revision] --> T8
  D2 -- Ya --> T9[9. Final Product Revision<br/>non-mayor / kunci scope]
  T9 --> T10[10. Dissemination and Implementation]
  T10 --> Z([Selesai])
```

---

## Cara pakai

1. Buka [mermaid.live](https://mermaid.live) → tempel satu blok `mermaid`
2. Export PNG/SVG → sisipkan ke `Skripsi.tex` via `\includegraphics`
3. Atau preview di GitHub (file `.md` merender Mermaid)

## Referensi role final

| Role | Panel | Catatan |
|------|-------|---------|
| Admin | `/admin` | Input pinjaman, catat cicilan, tabungan, laporan, backup |
| SPV | `/spv` | Setujui/tolak pinjaman, laporan |
| Kasir | `/kasir` | Tabungan, cairkan pinjaman (tidak catat cicilan) |
| Anggota | `/anggota` | Pantau pinjaman sendiri |
| Petugas | — | Offline; tidak punya akun login |
