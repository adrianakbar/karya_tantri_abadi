# Bukti Screenshot Black Box — Karya Tantri Abadi

Diambil otomatis dari sistem live: `http://127.0.0.1:8000`  
Tanggal sesi: 2026-07-26 03:02  
Theme: **light** (colorScheme light + localStorage theme=light)  
Login: email + password (tanpa captcha di UI)

## Daftar file

| File | Keterangan bukti |
|---|---|
| 01-login-page.png | Halaman login |
| 01b-login-no-captcha-browser.png | Login page (email+password only) |
| 02-admin-dashboard.png | Dashboard admin |
| 02-admin-after-login.png | Admin setelah login |
| 03-admin-loans.png | Daftar pinjaman admin |
| 04-admin-loan-detail.png | Detail pinjaman admin |
| 05-admin-tabungan.png | Modul tabungan admin |
| 06-admin-laporan-tabungan.png | Laporan tabungan |
| 07-admin-laporan-pinjaman.png | Laporan pinjaman |
| 08-admin-laporan-keuangan.png | Laporan keuangan |
| 09-spv-dashboard.png | Panel SPV (loan report) |
| 10-spv-loans.png | Daftar pinjaman SPV |
| 11-kasir-dashboard.png | Panel kasir |
| 12-kasir-tabungan.png | Modul tabungan kasir |
| 13-kasir-loans.png | Daftar pinjaman kasir |
| 14-kasir-tabungan-form.png | Form catat tabungan kasir |
| 15-anggota-dashboard.png | Panel anggota (daftar pinjaman) |
| 16-anggota-pinjaman.png | Pinjaman anggota |
| 17-petugas-404.png | Path `/petugas` tidak tersedia |
| 18-login-salah.png | Login password salah ditolak |
| 19-fee-tier-1jt-cair-730rb.png | Create pinjaman 1jt: UTJ 22%, cair 730.000 |
| 20-fee-tier-26jt-cair-2184jt.png | Create pinjaman 2,6jt: UTJ 11%, cair 2.184.000 |
| 21-daftar-pinjaman-fee-tier.png | Daftar pinjaman (fee/cair terlihat) |

## Catatan
- Capture pakai Playwright Chromium, forced light theme.
- Overlay tour disembunyikan saat capture jika muncul.
- Bukti numerik backend: `HASIL_BLACKBOX_SESI.md` + `storage/app/blackbox_probe_latest.json`.
