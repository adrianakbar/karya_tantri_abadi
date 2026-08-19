# Panduan Hak Akses per Role — Karya Tantri Abadi

Siap print / demo / UAT.  
**Peneliti:** Adrian Akbar Ramadhani — NIM 222410102010

## 1. Ringkasan role aktif

| Role | Panel | Siapa di mitra | Inti wewenang |
|---|---|---|---|
| Admin | `/admin` | Pengelola pusat | Input pinjaman, catat cicilan, kelola data, laporan, backup |
| SPV | `/spv` | Supervisor | Setujui/tolak pinjaman, pantau laporan |
| Kasir | `/kasir` | Kasir operasional | Cairkan pinjaman, catat tabungan, laporan |
| Anggota | `/anggota` | **Ketua kelompok** | Lihat pinjaman sendiri (read-only) |
| Petugas | `/petugas` | Petugas lapangan | Ajukan pinjaman nasabah (nama + foto KTP + detail) |

## 2. Matriks fitur

| Fitur | Admin | SPV | Kasir | Anggota | Petugas |
|---|:-:|:-:|:-:|:-:|:-:|
| Login | ✓ | ✓ | ✓ | ✓ | ✓ |
| Kelola anggota/user | ✓ | – | – | – | – |
| Ajukan pinjaman nasabah | – | – | – | – | ✓ |
| Koreksi/proses pinjaman | ✓ | – | – | – | – |
| Setujui/tolak pinjaman | – | ✓ | – | – | – |
| Cairkan pinjaman | – | – | ✓ | – | – |
| Catat cicilan | ✓ | – | – | – | – |
| Catat tabungan | pantau/edit | – | ✓ | – | – |
| Laporan | ✓ | ✓ | ✓ | – | – |
| Backup & log | ✓ | – | – | – | – |

## 3. Detail singkat

### Admin (`/admin`)
**Bisa:** kelola user, koreksi/proses pinjaman (dari pengajuan petugas atau buat sendiri), catat cicilan, pantau tabungan, laporan, backup/log.  
**Tidak:** setujui pinjaman, cairkan pinjaman.

### SPV (`/spv`)
**Bisa:** setujui/tolak pinjaman, pantau laporan.  
**Tidak:** input pinjaman, cairkan, catat cicilan/tabungan.

### Kasir (`/kasir`)
**Bisa:** cairkan pinjaman (generate cicilan), catat tabungan, lihat cicilan, laporan.  
**Tidak:** setujui pinjaman, catat cicilan (Catat Bayar = admin).

### Anggota (`/anggota`) = **Ketua Kelompok**
**Bisa:** lihat pinjaman kelompok sendiri (cair bersih, sisa, angsuran).  
**Tidak:** ajukan/edit pinjaman, catat cicilan/tabungan. Anggota biasa kelompok tidak wajib punya akun.

### Petugas (`/petugas`)
**Bisa:** login `/petugas`, ajukan pinjaman nasabah (isi nama + upload foto KTP + pilih nominal/tenor/frekuensi/tujuan), lihat pengajuan yang dia buat sendiri.  
**Tidak:** buat akun anggota, edit/hapus pengajuan setelah submit (koreksi = admin), akses menu/panel lain.

## 4. Alur ringkas

**Pinjaman:** Petugas ajukan `/petugas` (`pending`) → Admin koreksi/proses → SPV approve/reject → Kasir cairkan → Anggota lihat.  
**Cicilan:** Petugas tarik offline → setor admin → Admin catat bayar.  
**Tabungan:** Admin siapkan jenis → Kasir catat → Admin pantau → laporan.

## 5. Akun demo
`*@karya-tantri-abadi.test` / `password` (admin, spv, kasir, anggota, petugas)  
Login: landing pilih role di `/` atau `/<role>/login` (tanpa CAPTCHA)

## 6. Di luar scope
POS/retail, SHU, role legacy login.
