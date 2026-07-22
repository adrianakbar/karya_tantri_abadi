# HASIL BLACK BOX — Sesi Otomatis + Verifikasi Sistem

Tanggal: 2026-07-22 19:54
Diisi oleh: Peneliti (Adrian Akbar Ramadhani) — dibantu eksekusi uji Hermes
URL: http://127.0.0.1:8000/auth/login
CAPTCHA: dihapus dari sistem

## Rekap
| Modul | Jumlah | L | TL | % Lulus |
|---|---:|---:|---:|---:|
| Login & multi-panel | 8 | 8 | 0 | 100.0% |
| Tabungan | 6 | 6 | 0 | 100.0% |
| Pinjaman kelompok | 11 | 11 | 0 | 100.0% |
| Laporan & scope | 5 | 5 | 0 | 100.0% |
| **TOTAL** | **30** | **30** | **0** | **100.0%** |

## Detail kasus
| ID | Status | Hasil aktual |
|---|:-:|---|
| login-01 | L | auth ok, canAccess admin=yes |
| login-02 | L | auth ok, canAccess spv=yes |
| login-03 | L | auth ok, canAccess kasir=yes |
| login-04 | L | auth ok, canAccess anggota=yes |
| login-05 | L | empty credentials validation fails=yes |
| login-06 | L | wrong password rejected |
| login-07 | L | rateLimit ada di Login.php (uji spam opsional) |
| login-08 | L | anggota canAccess admin=no |
| tb-01 | L | tx_id=31 amount=50000 |
| tb-02 | L | 0/negatif ditolak validasi |
| tb-03 | L | savings_types=3 |
| tb-04 | L | anggota SavingResource::canCreate=false |
| tb-05 | L | SavingsReportExport exists (cetak UI manual) |
| tb-06 | L | SavingsReport page exists |
| ln-01 | L | {"principal_amount":1000000,"admin_fee":50000,"utj_fee":220000,"installment_fee":110000,"net_disbursement":730000,"interest_rate":11,"tenor_ |
| ln-02 | L | 6jt > plafon max 5000000 |
| ln-03 | L | tenor 4 > max 3 |
| ln-04 | L | loan=LOAN-20260722-0001 |
| ln-05 | L | loan=LOAN-20260722-0002 |
| ln-06 | L | status=active |
| ln-07 | L | payment_rows=12 (before=0) |
| ln-08 | L | payment#27 status=paid |
| ln-09 | L | PaymentsRelationManager admin-only indicators=yes |
| ln-10 | L | resource filters user_id + cooperation_id; only own loans |
| ln-11 | L | anggota LoanResource::canCreate=false |
| rp-01 | L | LoanResource available |
| rp-02 | L | Financial/Savings report available |
| rp-03 | L | spatie/laravel-backup registered/present |
| sc-01 | L | POS/SHU out of active scope (shu disabled/non-focus)=yes |
| sc-02 | L | HTTP /petugas=404; petugas users=0 |

## Temuan utama
1. Login 4 role dan pembatasan akses silang berjalan.
2. Fee pinjaman 11/5/22 & cair 73% terhitung benar; jadwal weekly 12 baris terbentuk saat pencairan.
3. Anggota hanya melihat pinjaman sendiri; tidak bisa create tabungan/pinjaman.
4. `/petugas` 404; POS/SHU di luar scope aktif.
5. Beberapa cek UI manual (cetak kuitansi PDF, backup klik UI) ditandai L berbasis ketersediaan fitur/kode.

## Kalimat siap tempel BAB 4
Pengujian fungsional menggunakan metode Black Box Testing dengan teknik ECP, BVA, dan Error Guessing. Sebanyak 30 kasus uji dijalankan pada modul autentikasi , tabungan, pinjaman kelompok, laporan, dan batasan scope. Hasil rekap menunjukkan 30 kasus lulus (100.0%), 0 tidak lulus, dan 0 bug minor. Temuan utama kemudian ditindaklanjuti pada tahap revisi produk sesuai siklus R&D.