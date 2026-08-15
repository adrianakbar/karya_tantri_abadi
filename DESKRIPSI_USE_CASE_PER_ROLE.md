# Deskripsi Use Case per Role
## Sistem Koperasi Simpan Pinjam — Karya Tantri Abadi

**Peneliti:** Adrian Akbar Ramadhani — NIM 222410102010  
**Sistem:** Multi-panel Laravel + Filament (Admin, SPV, Kasir, Anggota)  
**Sumber kebenaran:** implementasi kode aktif (`PanelProvider`, `LoanResource`, `SavingResource`, `PaymentsRelationManager`, `User::canAccessPanel`, `LoanCalculator`)  
**Scope aktif:** simpan pinjam (tabungan/simpanan, pinjaman kelompok, angsuran, laporan)  
**Di luar scope naskah fitur aktif:** POS/retail, SHU, CAPTCHA, panel petugas digital  

> **Istilah:** formal naskah = *simpanan*; label UI mitra = **Tabungan**.  
> **Anggota (sistem):** ketua kelompok pemegang akun `/anggota`.  
> **Petugas lapangan:** aktor offline (tidak login).  
> **Status pinjaman (kode):** `pending` → `approved` / `rejected` → `disbursed` (setelah kasir cairkan; jadwal cicilan digenerate) → pembayaran hingga lunas/`completed`.

---

## 0. Matriks use case × aktor (sesuai kode)

| ID | Use Case | Admin | SPV | Kasir | Anggota | Petugas* |
|---|---|:-:|:-:|:-:|:-:|:-:|
| UC-01 | Login multi-panel | ✓ | ✓ | ✓ | ✓ | – |
| UC-02 | Kelola data anggota | ✓ | – | – | – | – |
| UC-03 | Input pinjaman kelompok | ✓ | – | – | – | mendukung offline |
| UC-04 | Setujui / tolak pinjaman | – | ✓ | – | – | – |
| UC-05 | Cairkan pinjaman | – | – | ✓ | – | – |
| UC-06 | Catat cicilan | ✓ | – | lihat saja | – | mendukung offline |
| UC-07 | Catat tabungan | ✓ (pantau + boleh catat/edit) | – | ✓ (catat) | – | – |
| UC-08 | Lihat laporan | ✓ | ✓ | ✓ | – | – |
| UC-09 | Pantau pinjaman sendiri | – | – | – | ✓ | – |
| UC-10 | Backup / pengaturan | ✓ | – | – | – | – |

\*Petugas tidak mengakses sistem; asosiasi ke UC-03 dan UC-06 **mendukung** (garis putus pada UCD).

### Pemetaan panel (kode)

| Role runtime | Panel path | Provider (nama file legacy) |
|---|---|---|
| `admin` | `/admin` | `AdminPanelProvider` |
| `spv` | `/spv` | `KepalayayasanPanelProvider` (id=`spv`) |
| `kasir` | `/kasir` | `BendaharaPanelProvider` (id=`kasir`) |
| `anggota` | `/anggota` | `AnggotaPanelProvider` |
| (semua login) | `/auth/login` | `LoginPanelProvider` |

---

# A. Role: Admin
**Panel:** `/admin`  
**Aktor bisnis:** Pengelola pusat koperasi  

**Resource/page di panel (inti simpan pinjam):** `UserResource`, `LoanResource`, `SavingResource`, `SavingsTypeResource`, laporan (Loan/Savings/Financial/Expense), `BackupManagement`, log/pengaturan.

---

### UC-01-ADM — Login multi-panel (Admin)

| Item | Isi |
|---|---|
| **ID** | UC-01-ADM |
| **Nama** | Login multi-panel |
| **Aktor utama** | Admin |
| **Deskripsi** | Admin mengautentikasi diri (email + password) dan diarahkan ke panel administrasi. |
| **Prasyarat** | Akun aktif role `admin`; sistem berjalan. |
| **Alur utama** | 1. Buka `/auth/login`.<br>2. Isi email + password.<br>3. Sistem validasi kredensial & role (`CustomLoginResponse`).<br>4. Redirect ke `/admin`. |
| **Alur alternatif** | A1. Kredensial salah → pesan error.<br>A2. Field kosong → validasi required. |
| **Hasil** | Session aktif di panel `/admin`. |
| **Implementasi** | `LoginPanelProvider`, `CustomLoginResponse`, `User::canAccessPanel('admin')`. |

---

### UC-02 — Kelola data anggota

| Item | Isi |
|---|---|
| **ID** | UC-02 |
| **Nama** | Kelola data anggota |
| **Aktor utama** | Admin |
| **Deskripsi** | Admin mengelola data anggota/user (tambah, ubah, status) sebagai dasar transaksi simpan pinjam. |
| **Prasyarat** | Admin login di `/admin`. |
| **Alur utama** | 1. Buka menu Data Anggota (`UserResource`).<br>2. Tambah/ubah data anggota.<br>3. Sistem simpan ke database. |
| **Alur alternatif** | A1. Data wajib kosong / email duplikat → ditolak validasi. |
| **Hasil** | Data anggota tersedia untuk tabungan/pinjaman. |
| **Implementasi** | `UserResource` terdaftar hanya di `AdminPanelProvider`. |

---

### UC-03 — Input pinjaman kelompok

| Item | Isi |
|---|---|
| **ID** | UC-03 |
| **Nama** | Input pinjaman kelompok |
| **Aktor utama** | Admin |
| **Aktor pendukung** | Petugas lapangan (offline) |
| **Deskripsi** | Admin mencatat pengajuan pinjaman kelompok. Sistem menghitung fee tier dan menyimpan status awal **`pending`**. Hanya admin yang `canCreate` pinjaman. |
| **Prasyarat** | Admin login; user ketua kelompok ada; plafon max 5 jt; tenor max 3 bln. |
| **Alur utama** | 1. Petugas serah data offline ke Admin.<br>2. Admin buka form create pinjaman.<br>3. Isi peminjam (ketua), nominal, tenor, frekuensi angsuran.<br>4. Sistem hitung: angsuran 11%, admin 5%, UTJ 22% (≤2,5 jt) / 11% (≥2,6 jt), cair bersih 73%/84% (`LoanCalculator`).<br>5. Simpan status **`pending`**. |
| **Alur alternatif** | A1. Nominal/tenor di luar batas → validasi tolak.<br>A2. Role non-admin → tidak ada aksi create. |
| **Hasil** | Pinjaman `pending` menunggu SPV. |
| **Implementasi** | `LoanResource::canCreate()` admin only; `LoanCalculator`; admin `canEdit` hanya jika status `pending`/`rejected`. |

---

### UC-06 — Catat cicilan

| Item | Isi |
|---|---|
| **ID** | UC-06 |
| **Nama** | Catat cicilan |
| **Aktor utama** | Admin |
| **Aktor pendukung** | Petugas lapangan (offline tarik & setor) |
| **Deskripsi** | Admin mencatat pembayaran angsuran lewat aksi **Catat Bayar** pada jadwal cicilan. Kasir/SPV hanya dapat melihat jadwal (bila membuka detail pinjaman), tanpa aksi bayar. |
| **Prasyarat** | Admin login; pinjaman sudah **`disbursed`** sehingga `loan_payments` tergenerate. |
| **Alur utama** | 1. Petugas setor cicilan ke Admin.<br>2. Admin buka Detail pinjaman → relasi Jadwal Cicilan.<br>3. Admin **Catat Bayar** (nominal, tanggal, catatan).<br>4. Sistem update `loan_payments` (paid/partial) dan sisa hutang pinjaman.<br>5. Jika seluruh angsuran lunas → status pinjaman menuju completed/lunas. |
| **Alur alternatif** | A1. Non-admin → tombol Catat Bayar tidak tampil / ditolak.<br>A2. Bayar sebagian → status `partial`. |
| **Hasil** | Pembayaran tercatat di sistem. |
| **Implementasi** | `PaymentsRelationManager` action `pay` visible hanya `hasRole('admin')`. |

---

### UC-07-ADM — Catat / pantau tabungan (Admin)

| Item | Isi |
|---|---|
| **ID** | UC-07-ADM |
| **Nama** | Catat tabungan (pantau + catat/edit) |
| **Aktor utama** | Admin |
| **Deskripsi** | Admin mengelola modul Tabungan: pantau daftar transaksi, boleh create/edit, hapus (delete hanya admin), serta kelola jenis tabungan. |
| **Prasyarat** | Admin login. |
| **Alur utama** | 1. Buka Daftar Tabungan / Jenis Tabungan.<br>2. Lihat, tambah, atau edit transaksi bila perlu.<br>3. Sistem simpan `savings_transactions`. |
| **Hasil** | Data tabungan konsisten untuk operasional & laporan. |
| **Implementasi** | `SavingResource` + `SavingsTypeResource` di admin; `canCreate`/`canEdit` admin+kasir; `canDelete` admin only. |

---

### UC-08-ADM — Lihat laporan

| Item | Isi |
|---|---|
| **ID** | UC-08-ADM |
| **Nama** | Lihat laporan |
| **Aktor utama** | Admin |
| **Deskripsi** | Admin menampilkan laporan pinjaman, tabungan, keuangan, dan pengeluaran (sesuai page terdaftar). |
| **Prasyarat** | Admin login. |
| **Alur utama** | Buka menu laporan → filter → tampil data (export bila tersedia). |
| **Hasil** | Informasi monitoring tersedia. |
| **Implementasi** | Pages: `LoanReport`, `SavingsReport`, `FinancialReport`, `ExpenseReport` di admin. |

---

### UC-10 — Backup / pengaturan

| Item | Isi |
|---|---|
| **ID** | UC-10 |
| **Nama** | Backup / pengaturan |
| **Aktor utama** | Admin |
| **Deskripsi** | Admin menjalankan backup database dan mengakses pengaturan sistem / log. |
| **Prasyarat** | Admin login. |
| **Alur utama** | 1. Buka Backup Data / Pengaturan Sistem.<br>2. Jalankan backup atau ubah setting.<br>3. File backup tersimpan / konfigurasi terbarui. |
| **Hasil** | Cadangan DB / setting tersedia. |
| **Implementasi** | `BackupManagement` (hanya admin panel); `SystemSettingResource`; log resources. |

---

# B. Role: SPV (Supervisor)
**Panel:** `/spv`  
**Resource:** `LoanResource` saja + laporan pinjaman/keuangan.  
**Tidak:** create pinjaman, catat cicilan, catat tabungan, backup.

---

### UC-01-SPV — Login multi-panel (SPV)

| Item | Isi |
|---|---|
| **ID** | UC-01-SPV |
| **Nama** | Login multi-panel |
| **Aktor utama** | SPV |
| **Deskripsi** | SPV login dan diarahkan ke `/spv`. |
| **Prasyarat** | Role `spv` (alias legacy `kepalayayasan` diterima di `canAccessPanel`). |
| **Alur utama** | `/auth/login` → validasi → redirect `/spv`. |
| **Hasil** | Masuk panel SPV. |

---

### UC-04 — Setujui / tolak pinjaman

| Item | Isi |
|---|---|
| **ID** | UC-04 |
| **Nama** | Setujui / tolak pinjaman |
| **Aktor utama** | SPV |
| **Deskripsi** | SPV meninjau pinjaman `pending` lalu **Setujui** (`approved`) atau **Tolak** (`rejected`). |
| **Prasyarat** | SPV login; ada pinjaman status `pending`. |
| **Alur utama** | 1. Buka Daftar Pinjaman.<br>2. Pilih pinjaman pending.<br>3a. Setujui (+ catatan opsional) → `approved`, catat `approved_by` / tanggal.<br>3b. Tolak (+ alasan wajib) → `rejected`. |
| **Alur alternatif** | A1. Status bukan pending → aksi Setujui/Tolak tidak tampil. |
| **Hasil** | Pinjaman siap dicairkan kasir, atau selesai ditolak. |
| **Implementasi** | `LoanResource::getActionsForRole()` actions `approve` / `reject`. Query SPV membatasi status relevan. |

---

### UC-08-SPV — Lihat laporan

| Item | Isi |
|---|---|
| **ID** | UC-08-SPV |
| **Nama** | Lihat laporan |
| **Aktor utama** | SPV |
| **Deskripsi** | SPV memantau laporan pinjaman dan keuangan (tanpa mengubah transaksi tabungan). |
| **Prasyarat** | SPV login. |
| **Alur utama** | Buka `LoanReport` / `FinancialReport` → filter → data. |
| **Hasil** | Monitoring untuk pengawasan. |
| **Implementasi** | Pages di `KepalayayasanPanelProvider` (panel spv). |

---

# C. Role: Kasir
**Panel:** `/kasir`  
**Resource:** `LoanResource`, `SavingResource`, `SavingsTypeResource` + laporan.  
**Tidak:** setujui pinjaman, Catat Bayar cicilan, backup, kelola user.

---

### UC-01-KAS — Login multi-panel (Kasir)

| Item | Isi |
|---|---|
| **ID** | UC-01-KAS |
| **Nama** | Login multi-panel |
| **Aktor utama** | Kasir |
| **Deskripsi** | Kasir login → `/kasir`. |
| **Prasyarat** | Role `kasir` (alias legacy `bendahara`/`cashier` diterima). |
| **Hasil** | Masuk panel kasir. |

---

### UC-05 — Cairkan pinjaman

| Item | Isi |
|---|---|
| **ID** | UC-05 |
| **Nama** | Cairkan pinjaman |
| **Aktor utama** | Kasir |
| **Deskripsi** | Kasir mencairkan pinjaman berstatus **`approved`**. Sistem set status **`disbursed`**, mencatat tanggal cair, dan **men-generate jadwal cicilan** (`loan_payments`). |
| **Prasyarat** | Kasir login; pinjaman sudah `approved` SPV. |
| **Alur utama** | 1. Buka daftar pinjaman (filter kasir: approved ke atas).<br>2. Aksi **Cairkan** (konfirmasi menampilkan cair bersih).<br>3. Isi tanggal pencairan.<br>4. Sistem: status → **`disbursed`** + `LoanService::generatePaymentSchedule`. |
| **Alur alternatif** | A1. Status bukan `approved` → tombol Cairkan tidak tampil. |
| **Hasil** | Dana dicatat cair; jadwal cicilan siap; Admin dapat Catat Bayar; Anggota dapat pantau. |
| **Implementasi** | Action `disburse` di `LoanResource`; status kode = `disbursed` (bukan langsung label generik “active” saja). |

---

### UC-07-KAS — Catat tabungan

| Item | Isi |
|---|---|
| **ID** | UC-07-KAS |
| **Nama** | Catat tabungan |
| **Aktor utama** | Kasir |
| **Deskripsi** | Kasir mencatat transaksi tabungan/simpanan anggota (jenis, nominal, tanggal) dengan opsi cetak kuitansi. |
| **Prasyarat** | Kasir login; anggota & jenis tabungan tersedia. |
| **Alur utama** | 1. Buka Daftar Tabungan.<br>2. Create: pilih anggota, jenis, nominal, tanggal.<br>3. Validasi nominal &gt; 0.<br>4. Simpan `savings_transactions`.<br>5. Opsional cetak kuitansi. |
| **Alur alternatif** | A1. Nominal invalid → error validasi. |
| **Hasil** | Transaksi tabungan tersimpan. |
| **Implementasi** | `SavingResource` di panel kasir; `canCreate`/`canEdit` untuk kasir. |

---

### UC-08-KAS — Lihat laporan

| Item | Isi |
|---|---|
| **ID** | UC-08-KAS |
| **Nama** | Lihat laporan |
| **Aktor utama** | Kasir |
| **Deskripsi** | Kasir melihat laporan pinjaman, tabungan, dan keuangan operasional. |
| **Implementasi** | `LoanReport`, `SavingsReport`, `FinancialReport` di panel kasir. |

**Catatan:** Kasir dapat membuka detail pinjaman & **melihat** jadwal cicilan, tetapi **tidak** memiliki aksi Catat Bayar.

---

# D. Role: Anggota (Ketua Kelompok)
**Panel:** `/anggota`  
**Resource terdaftar:** hanya `Anggota\LoanResource` (read-only, filter `user_id`).  
**Tidak:** create/edit pinjaman, tabungan, laporan, backup.

---

### UC-01-ANG — Login multi-panel (Anggota)

| Item | Isi |
|---|---|
| **ID** | UC-01-ANG |
| **Nama** | Login multi-panel |
| **Aktor utama** | Anggota (ketua kelompok) |
| **Deskripsi** | Login → redirect `/anggota`. |
| **Hasil** | Masuk panel anggota. |

---

### UC-09 — Pantau pinjaman sendiri

| Item | Isi |
|---|---|
| **ID** | UC-09 |
| **Nama** | Pantau pinjaman sendiri |
| **Aktor utama** | Anggota (ketua kelompok) |
| **Deskripsi** | Anggota melihat daftar/detail pinjaman miliknya saja: status, cair bersih, angsuran, sisa hutang. Seluruh aksi create/edit/delete dinonaktifkan. |
| **Prasyarat** | Login anggota; pinjaman terkait `user_id` ada. |
| **Alur utama** | 1. Buka Daftar Pinjaman.<br>2. Sistem query `where user_id = auth id`.<br>3. View detail (read-only). |
| **Alur alternatif** | A1. Akses panel lain → ditolak `canAccessPanel`.<br>A2. Create/edit → `canCreate`/`canEdit` = false. |
| **Hasil** | Informasi pinjaman kelompok tampil read-only. |
| **Implementasi** | `App\Filament\Resources\Anggota\LoanResource`. (`Anggota\SavingResource` ada di folder tetapi **tidak** di-register di panel.) |

---

# E. Role: Petugas Lapangan (Offline)
**Panel:** tidak ada  
**Login:** tidak  

| Use case terkait | Peran petugas |
|---|---|
| UC-03 Input pinjaman | Cari/dampingi nasabah, susun pengajuan offline, serah data ke Admin |
| UC-06 Catat cicilan | Tarik cicilan lapangan, setor uang + data ke Admin |

| Item | Isi |
|---|---|
| **Prasyarat** | Bertugas lapangan; Admin menerima setoran. |
| **Alur** | Interaksi offline → serah ke Admin → Admin input sistem. |
| **Hasil** | Data siap diproses; tidak ada jejak login petugas. |
| **Implementasi** | Tidak ada panel; `canAccessPanel` default false; komentar di `User` model & relation manager cicilan. |

---

## Alur bisnis (status kode)

```
PINJAMAN:
Petugas offline
  → Admin [UC-03] status = pending
  → SPV [UC-04] approved | rejected
  → (jika approved) Kasir [UC-05] status = disbursed + generate loan_payments
  → Anggota [UC-09] lihat milik sendiri

CICILAN:
Petugas offline tarik
  → Admin [UC-06] Catat Bayar pada loan_payments
  → (opsional) status pinjaman completed jika lunas

TABUNGAN:
Kasir [UC-07] catat (Admin boleh catat/edit/hapus)
  → Admin/SPV/Kasir [UC-08] laporan (SPV: laporan pinjaman/keuangan, tanpa kelola tabungan)
```

---

## Fee pinjaman (LoanCalculator)

| Komponen | Nilai |
|---|---|
| Biaya angsuran | 11% (masuk total dilunasi; tidak dipotong di awal cair) |
| Biaya admin | 5% (dipotong di awal) |
| UTJ | 22% jika plafon ≤ 2.500.000; 11% jika ≥ 2.600.000 |
| Cair bersih | nominal − admin − UTJ → 73% / 84% |
| Plafon / tenor | max 5.000.000 / max 3 bulan |

---

## Catatan naskah

1. UCD dan matriks akses **harus** mengikuti tabel di bagian 0 (bukan asosiasi longgar yang memberi SPV input/cicilan/tabungan).
2. Setelah pencairan tulis status **`disbursed`**, lalu angsuran via `loan_payments`.
3. Separation of duties: **input (Admin) ≠ otorisasi (SPV) ≠ pencairan (Kasir) ≠ pantau (Anggota)**.
4. Jangan sebut POS/SHU/CAPTCHA/panel petugas digital sebagai fitur aktif.
5. Nama file provider legacy (Bendahara/Kepalayayasan) **jangan** dipakai sebagai nama role di naskah.

---

*Dokumen diselaraskan dengan audit kode KTA (panel, policy resource, aksi approve/disburse/Catat Bayar, calculator fee).*
