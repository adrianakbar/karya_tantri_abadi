# Checklist Demo + Black Box (Siap Isi)
**Mitra:** Karya Tantri Abadi  
**Sistem:** Koperasi simpan pinjam berbasis website  
**Tujuan:** bahan BAB 4.1.4–4.1.8 (uji fungsional & log revisi R&D)

Isi kolom **Hasil aktual**, **Status**, **Catatan** saat uji.  
Status: `L` = Lulus · `TL` = Tidak Lulus · `B` = Bug minor (lulus bersyarat)

---

## 0. Persiapan (sebelum demo)

### 0.1 Lingkungan
- [ ] Server/app bisa diakses (lokal/LAN/hosting)
- [ ] Login path: `/auth/login`
- [ ] Browser siap (Chrome/Edge/Firefox)
- [ ] CAPTCHA bisa dilewati di lingkungan uji (atau kunci uji disiapkan)

### 0.2 Akun seed (default password: `password`)

| Role | Email contoh | Panel tujuan |
| :--- | :--- | :--- |
| Admin | `admin@karya-tantri-abadi.test` atau `admin@test.com` | `/admin` |
| SPV | `spv@karya-tantri-abadi.test` atau `spv@test.com` | `/spv` |
| Kasir | `kasir@karya-tantri-abadi.test` atau `kasir@test.com` | `/kasir` |
| Anggota | `anggota@karya-tantri-abadi.test` atau `anggota@test.com` | `/anggota` |

- [ ] Semua 4 akun berhasil login
- [ ] Tidak ada akun petugas aktif untuk demo login

### 0.3 Data uji disiapkan
- [ ] Minimal 1 anggota aktif
- [ ] 1 jenis tabungan aktif
- [ ] Rencana input pinjaman A: **Rp1.000.000**, tenor **3 bulan**, frekuensi **weekly**  
  Harapan hitung: cair **Rp730.000**, total dilunasi **Rp1.110.000**, cicilan **12**
- [ ] (Opsional) 1 pinjaman pending lain untuk skenario tolak SPV
- [ ] Simulasi petugas offline: data pengajuan + “uang cicilan” diserahkan ke admin (lisan/kertas)

### 0.4 Identitas sesi uji
| Item | Isi |
| :--- | :--- |
| Tanggal | |
| Tempat | |
| Penguji/peneliti | Adrian Akbar Ramadhani |
| Saksi mitra (nama/role) | |
| Versi sistem / commit | |
| URL akses | |

---

## 1. Checklist demo alur (15–25 menit)

Urutan demo ke mitra. Centang jika sudah ditunjukkan.

### 1.1 Pembuka (2 menit)
- [ ] Jelaskan scope: simpan pinjam saja (bukan POS/SHU)
- [ ] Jelaskan istilah: formal **simpanan** = UI **tabungan**
- [ ] Jelaskan petugas **offline** (cari nasabah & tarik cicilan di luar sistem)

### 1.2 Login multi-panel (3 menit)
- [ ] Admin → `/admin`
- [ ] SPV → `/spv`
- [ ] Kasir → `/kasir`
- [ ] Anggota → `/anggota`
- [ ] Tunjukkan akses silang gagal (anggota buka `/admin` ditolak/redirect)

### 1.3 Tabungan / simpanan (3 menit) — role Kasir
- [ ] Kasir catat tabungan anggota (nominal valid)
- [ ] Data muncul di daftar tabungan
- [ ] (Opsional) cetak kuitansi PDF
- [ ] (Opsional) buka laporan tabungan

### 1.4 Pinjaman kelompok end-to-end (8–10 menit)
1. [ ] **Offline:** petugas “serahkan” pengajuan ke admin
2. [ ] **Admin:** input pinjaman pending + tunjukkan fee 11/5/22 & cair 73%
3. [ ] **SPV:** setujui pinjaman
4. [ ] **Kasir:** cairkan pinjaman
5. [ ] Tunjukkan jadwal cicilan ter-generate (weekly 3 bln = 12 baris)
6. [ ] **Offline:** petugas “setor” uang cicilan ke admin
7. [ ] **Admin:** Catat Bayar pada 1 cicilan
8. [ ] **Anggota:** lihat pinjaman sendiri (cair bersih, sisa, angsuran)
9. [ ] **Kasir:** pastikan **tidak** ada aksi Catat Bayar

### 1.5 Laporan & penutup (2 menit)
- [ ] Laporan pinjaman / keuangan (admin atau SPV/kasir)
- [ ] Tekankan: POS/SHU tidak ada di scope
- [ ] Ajak isi kuesioner UAT (form terpisah)

### 1.6 Catatan demo (isi setelah sesi)
| Pertanyaan mitra / temuan | Tindak lanjut |
| :--- | :--- |
| | |
| | |
| | |

---

## 2. Tabel Black Box siap isi

**Cara isi Status:** `L` / `TL` / `B`  
**Hasil aktual:** tuliskan apa yang benar-benar terjadi (bukan copy “diharapkan”).

### 2.1 Autentikasi & multi-panel

| ID | Fitur | Langkah / Input | Hasil diharapkan | Hasil aktual | Status | Catatan |
| :--- | :--- | :--- | :--- | :--- | :-: | :--- |
| login-01 | Login admin | Email+password admin valid | Masuk `/admin` | | | |
| login-02 | Login SPV | Kredensial SPV valid | Masuk `/spv` | | | |
| login-03 | Login kasir | Kredensial kasir valid | Masuk `/kasir` | | | |
| login-04 | Login anggota | Kredensial anggota valid | Masuk `/anggota` | | | |
| login-05 | Login kosong | Email/password kosong | Validasi required | | | |
| login-06 | Login salah | Password salah | Pesan credentials tidak cocok | | | |
| login-07 | CAPTCHA | CAPTCHA tidak valid / dikosongkan | Ditolak | | | |
| login-08 | Akses silang | Anggota buka `/admin` | Ditolak/redirect | | | |

**Ringkas 2.1:** Lulus __ / 8 · TL __ · Bug __

### 2.2 Modul Tabungan (formal: simpanan)

| ID | Fitur | Langkah / Input | Hasil diharapkan | Hasil aktual | Status | Catatan |
| :--- | :--- | :--- | :--- | :--- | :-: | :--- |
| tb-01 | Catat tabungan | Kasir, nominal valid, anggota valid | Transaksi tersimpan di daftar | | | |
| tb-02 | Validasi nominal | Nominal 0 atau negatif | Ditolak validasi | | | |
| tb-03 | Jenis tabungan | Admin buat/aktifkan jenis | Jenis muncul di form transaksi | | | |
| tb-04 | Hak akses anggota | Anggota coba kelola tabungan | Tidak ada create/menu kelola | | | |
| tb-05 | Kuitansi | Tombol cetak (jika ada) | PDF ter-render | | | |
| tb-06 | Laporan tabungan | Filter periode | Data sesuai filter / export | | | |

**Ringkas 2.2:** Lulus __ / 6 · TL __ · Bug __

### 2.3 Modul Pinjaman Kelompok

| ID | Fitur | Langkah / Input | Hasil diharapkan | Hasil aktual | Status | Catatan |
| :--- | :--- | :--- | :--- | :--- | :-: | :--- |
| ln-01 | Input pinjaman | Admin: 1.000.000; 3 bln; weekly | Fee admin 5%, UTJ 22%, angsuran 11%, cair 730.000; status pending | | | |
| ln-02 | Plafon | Nominal > 5.000.000 | Ditolak/divalidasi | | | |
| ln-03 | Tenor | Tenor > 3 bulan | Ditolak/divalidasi | | | |
| ln-04 | Approve SPV | Pinjaman pending | Status approved | | | |
| ln-05 | Tolak SPV | Pinjaman pending lain | Status rejected | | | |
| ln-06 | Cairkan kasir | Pinjaman approved | Status disbursed/active | | | |
| ln-07 | Jadwal weekly | Setelah cair, tenor 3 bln weekly | 12 baris cicilan | | | |
| ln-08 | Catat cicilan | Admin bayar = tagihan | Status paid; sisa turun | | | |
| ln-09 | Kasir catat bayar | Kasir buka detail cicilan | Tombol Catat Bayar tidak ada / ditolak | | | |
| ln-10 | Anggota lihat | Login anggota | Hanya pinjaman sendiri; cair bersih & sisa terlihat | | | |
| ln-11 | Anggota aksi | Coba create/edit pinjaman | Tidak diizinkan | | | |

**Ringkas 2.3:** Lulus __ / 11 · TL __ · Bug __

### 2.4 Laporan & batasan scope

| ID | Fitur | Langkah / Input | Hasil diharapkan | Hasil aktual | Status | Catatan |
| :--- | :--- | :--- | :--- | :--- | :-: | :--- |
| rp-01 | Laporan pinjaman | Admin/SPV/kasir buka laporan | Data pinjaman tampil | | | |
| rp-02 | Laporan keuangan | Filter/kategori Tabungan Anggota | Transaksi tabungan terpetakan | | | |
| rp-03 | Backup (opsional) | Admin backup DB | File backup ada | | | |
| sc-01 | POS/SHU | Cari menu POS/SHU | Tidak tersedia di panel aktif | | | |
| sc-02 | Petugas login | Coba akun/path petugas | Tidak ada panel/redirect `/petugas` | | | |

**Ringkas 2.4:** Lulus __ / 5 · TL __ · Bug __

---

## 3. Rekap Black Box (untuk BAB 4)

| Modul | Jumlah kasus | L | TL | B | % Lulus |
| :--- | ---: | ---: | ---: | ---: | ---: |
| Login & multi-panel | 8 | | | | |
| Tabungan | 6 | | | | |
| Pinjaman kelompok | 11 | | | | |
| Laporan & scope | 5 | | | | |
| **Total** | **30** | | | | |

Rumus:  
`% Lulus = (L + 0,5×B) / Total × 100%`  
(atau hitung ketat: hanya `L` / Total)

### Temuan utama (isi 3–7 poin)
1. 
2. 
3. 

### Mapping ke siklus R&D (log revisi)

| Siklus | Kapan / siapa | Kasus fokus | Temuan | Revisi |
| :--- | :--- | :--- | :--- | :--- |
| Siklus 1 (preliminary) | Dev + admin/kasir | login, tabungan, input pinjaman | | |
| Siklus 2 (main) | Admin+SPV+kasir+anggota | approve–cair–cicilan–hak akses | | |
| Siklus 3 (operational) | Mitra operasional + UAT | alur harian + kuesioner | | |

---

## 4. Setelah Black Box (lanjutan singkat)

1. [ ] Isi kuesioner UAT (`PANDUAN_DAN_FORM_PENGUJIAN.md` bagian 2)
2. [ ] Isi berita acara (bagian 3 dokumen yang sama)
3. [ ] Foto/screenshot bukti (login tiap role, fee pinjaman, jadwal cicilan, panel anggota)
4. [ ] Pindahkan rekap ke BAB 4 (`BAB_4_HASIL_DAN_PEMBAHASAN.md` / `Skripsi.tex` 4.1.8)
5. [ ] TL/bug → daftar revisi Final Product Revision

---

## 5. Kalimat siap tempel BAB 4 (setelah tabel terisi)

> Pengujian fungsional menggunakan metode *Black Box Testing* dengan teknik ECP, BVA, dan *Error Guessing*. Sebanyak **30** kasus uji dijalankan pada modul autentikasi, tabungan, pinjaman kelompok, laporan, dan batasan scope. Hasil rekap menunjukkan **___** kasus lulus (**___%**), **___** tidak lulus, dan **___** bug minor. Temuan utama kemudian ditindaklanjuti pada tahap revisi produk sesuai siklus R&D.

*(Ganti angka setelah uji selesai.)*

---

## Referensi file terkait
- Skenario ringkas: `BLACK_BOX_UAT_TESTING.md`
- Form UAT + berita acara: `PANDUAN_DAN_FORM_PENGUJIAN.md`
- Draft BAB 4: `BAB_4_HASIL_DAN_PEMBAHASAN.md`
- Naskah LaTeX: `~/Pribadi/Skripsi/Skripsi.tex`
