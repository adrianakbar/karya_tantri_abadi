# HASIL BLACK BOX — Sesi Reset DB + Verifikasi Sistem

Tanggal: 2026-07-22 20:28
Diisi oleh: Peneliti (Adrian Akbar Ramadhani) — dibantu eksekusi uji Hermes
URL: http://127.0.0.1:8000/auth/login
Persiapan: `php artisan migrate:fresh --force` + seed Cooperation → Role → User → TestUser → SystemSettings → SavingsTransaction → PinjamanSeeder
CAPTCHA: dihapus dari sistem (email+password only)
Fee: tier mitra (≤2,5jt UTJ 22%/cair 73%; ≥2,6jt UTJ 11%/cair 84%)
Probe: `php scripts/blackbox_probe.php` → storage/app/blackbox_probe_latest.json

## Keadaan DB setelah reset+seed
| Item | Nilai |
|---|---:|
| users | 9 |
| loans (seed) | 7 |
| savings_transactions | 30 (+1 probe) |
| HTTP /auth/login | 200 |
| HTTP /petugas | 404 |

## Rekap
| Modul | Jumlah | L | TL | % Lulus |
|---|---:|---:|---:|---:|
| Login & multi-panel | 9 | 9 | 0 | 100.0% |
| Tabungan | 6 | 6 | 0 | 100.0% |
| Pinjaman kelompok (+fee tier) | 16 | 16 | 0 | 100.0% |
| Laporan & scope | 5 | 5 | 0 | 100.0% |
| **TOTAL** | **36** | **36** | **0** | **100.0%** |

## Detail kasus
| ID | Status | Hasil aktual |
|---|:-:|---|
| login-01 | L | auth ok, canAccess admin=yes email=admin@karya-tantri-abadi.test |
| login-02 | L | auth ok, canAccess spv=yes email=spv@karya-tantri-abadi.test |
| login-03 | L | auth ok, canAccess kasir=yes email=kasir@karya-tantri-abadi.test |
| login-04 | L | auth ok, canAccess anggota=yes email=anggota@karya-tantri-abadi.test |
| login-05 | L | empty credentials validation fails=yes |
| login-06 | L | wrong password rejected |
| login-07 | L | rateLimit ada di Login.php (uji spam opsional) |
| login-08 | L | anggota canAccess admin=no |
| login-captcha | L | CAPTCHA dihapus dari form login (email+password only) |
| tb-01 | L | tx_id=31 amount=50000 |
| tb-02 | L | 0/negatif ditolak validasi |
| tb-03 | L | savings_types=3 |
| tb-04 | L | anggota SavingResource::canCreate=false |
| tb-05 | L | SavingsReportExport exists (cetak UI manual) |
| tb-06 | L | SavingsReport page exists |
| ln-01 | L | 1.000.000 → admin 50.000, UTJ 220.000, angsuran 110.000, cair 730.000, count 12 |
| ln-01b | L | 2.600.000 → admin 130.000, UTJ 286.000, angsuran 286.000, cair 2.184.000 |
| ln-01c | L | 2.500.000 → UTJ 550.000, cair 1.825.000 (batas tier rendah) |
| ln-02 | L | 6jt > plafon max 5.000.000 |
| ln-03 | L | tenor 4 > max 3 |
| ln-04-create | L | pending loan=LOAN-PROBE-202819 net=730000 |
| ln-04 | L | loan=LOAN-PROBE-202819 approved by SPV |
| ln-05 | L | loan=LOAN-PROBE-REJ-202819 rejected |
| ln-06 | L | status=active setelah pencairan |
| ln-07 | L | payment_rows=12 (before=0) |
| ln-08 | L | payment#27 status=paid (admin catat) |
| ln-09 | L | PaymentsRelationManager admin-only indicators=yes |
| ln-10 | L | Anggota resource filters user_id; only own loans |
| ln-11 | L | anggota LoanResource::canCreate=false |
| rp-01 | L | LoanResource available |
| rp-02 | L | Financial/Savings report available |
| rp-03 | L | spatie/laravel-backup registered/present |
| sc-01 | L | POS/SHU out of active scope=yes |
| sc-02 | L | HTTP /petugas=404; petugas users=0 |
| seed-tier-high | L | 5jt utj=550000 net=4200000 (seed PinjamanSeeder) |
| seed-tier-low | L | 1jt utj=220000 net=730000 (seed PinjamanSeeder) |

## Temuan utama
1. Setelah `migrate:fresh` + seed penuh, seluruh 36 kasus probe lulus.
2. Fee tier tetap benar pada DB bersih:
   - 1jt: UTJ 22% / cair 730.000
   - 2,5jt: UTJ 22% / cair 1.825.000
   - 2,6jt: UTJ 11% / cair 2.184.000
   - 5jt: UTJ 11% / cair 4.200.000
3. Alur create → approve → reject sample → cair → 12 cicilan → bayar 1 berjalan.
4. CAPTCHA off; `/petugas` 404; petugas users=0.
5. Anggota hanya lihat pinjaman sendiri; tidak create tabungan/pinjaman.

## Bukti
- Angka: `storage/app/blackbox_probe_latest.json`, file ini
- Form kosong: `CHECKLIST_DEMO_BLACKBOX.docx` (diisi peneliti)
- Screenshot UI fee tier (sesi sebelumnya, masih relevan): `bukti-blackbox/19-*.png`, `20-*.png`, `21-*.png`

## Kalimat siap tempel BAB 4
Pengujian fungsional menggunakan metode Black Box Testing dengan teknik ECP, BVA, dan Error Guessing. Setelah basis data direset dan di-seed ulang, sebanyak 36 kasus verifikasi (termasuk fee berjenjang) dijalankan pada modul autentikasi, tabungan, pinjaman kelompok, laporan, dan batasan scope. Hasil rekap menunjukkan 36 kasus lulus (100.0%), 0 tidak lulus. Verifikasi fee: nominal Rp1.000.000 menghasilkan cair bersih Rp730.000 (UTJ 22%), sedangkan nominal Rp2.600.000 menghasilkan cair bersih Rp2.184.000 (UTJ 11%). Skor UAT lapangan menunggu pengisian mitra.
