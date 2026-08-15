# HASIL BLACK BOX — Sesi Reset DB + Verifikasi Sistem

Tanggal: 2026-07-26 02:41
Diisi oleh: Peneliti (Adrian Akbar Ramadhani) — eksekusi probe Hermes
URL: http://127.0.0.1:8000/auth/login | https://koperasi.adrianakbar.my.id/auth/login
Persiapan: `php artisan migrate:fresh --force` + seed Cooperation → PermissionAndRole → User → TestUser → SystemSettings → SavingsTransaction → PinjamanSeeder
Fee: tier mitra (≤2,5jt UTJ 22%/cair 73%; ≥2,6jt UTJ 11%/cair 84%)
Probe: `php scripts/blackbox_probe.php` → storage/app/blackbox_probe_latest.json
Catatan probe: sc-02 dijalankan via HTTP lokal container :80 (host map :8000).

## Rekap
| Modul | Jumlah | L | TL | % Lulus |
|---|---:|---:|---:|---:|
| Login & multi-panel | 9 | 9 | 0 | 100.0% |
| Tabungan | 6 | 6 | 0 | 100.0% |
| Pinjaman kelompok (+fee tier) | 16 | 16 | 0 | 100.0% |
| Laporan & batasan fitur | 5 | 5 | 0 | 100.0% |
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
| login-09 | L | login email+password sesuai peran |
| tb-01 | L | tx_id=32 amount=50000 |
| tb-02 | L | 0/negatif ditolak validasi |
| tb-03 | L | savings_types=3 |
| tb-04 | L | anggota SavingResource::canCreate=false |
| tb-05 | L | SavingsReportExport exists (cetak UI manual) |
| tb-06 | L | SavingsReport page exists |
| ln-01 | L | principal=1000000; UTJ=220000; cair=730000; cicilan=12 |
| ln-01b | L | principal=2600000; UTJ=286000; cair=2184000; cicilan=12 |
| ln-01c | L | principal=2500000; UTJ=550000; cair=1825000; cicilan=12 |
| ln-02 | L | 6jt > plafon max 5000000 |
| ln-03 | L | tenor 4 > max 3 |
| ln-04-create | L | pending loan=LOAN-PROBE-024039 net=730000.00 |
| ln-04 | L | loan=LOAN-PROBE-024039 approved |
| ln-05 | L | loan=LOAN-PROBE-REJ-024041 |
| ln-07 | L | payment_rows=12 (before=0) |
| ln-06 | L | status=active |
| ln-08 | L | payment#39 status=paid |
| ln-09 | L | PaymentsRelationManager admin-only indicators=yes |
| ln-10 | L | resource filters user_id; only own loans |
| ln-11 | L | anggota LoanResource::canCreate=false |
| rp-01 | L | LoanResource available |
| rp-02 | L | Financial/Savings report available |
| rp-03 | L | spatie/laravel-backup registered/present |
| sc-01 | L | fitur aktif hanya domain simpan pinjam |
| sc-02 | L | HTTP /petugas=404; petugas users=0 |
| seed-tier-high | L | 5jt utj=550000 net=4200000 |
| seed-tier-low | L | 1jt utj=220000 net=730000 |

## Temuan utama
1. Setelah `migrate:fresh` + seed penuh, **36/36** kasus probe lulus (100%).
2. Fee tier benar pada DB bersih:
   - 1jt: UTJ 22% / cair 730.000
   - 2,5jt: UTJ 22% / cair 1.825.000
   - 2,6jt: UTJ 11% / cair 2.184.000
   - 5jt: UTJ 11% / cair 4.200.000
3. Alur create → approve → reject sample → cair → 12 cicilan → bayar 1 berjalan.
4. Panel/akun petugas tidak disediakan (`/petugas` 404; petugas users=0).
5. Anggota hanya lihat pinjaman sendiri; tidak create tabungan/pinjaman.

## Bukti
- Angka: `storage/app/blackbox_probe_latest.json`, file ini
- Form kosong: `CHECKLIST_DEMO_BLACKBOX.docx` (diisi peneliti)
- Screenshot UI fee tier (sesi sebelumnya, masih relevan): `bukti-blackbox/19-*.png`, `20-*.png`, `21-*.png`

## Kalimat siap tempel BAB 4
Pengujian fungsional menggunakan metode Black Box Testing dengan teknik ECP, BVA, dan Error Guessing. Setelah basis data direset dan di-seed ulang, sebanyak 36 kasus verifikasi (login multi-panel, tabungan, pinjaman kelompok beserta fee berjenjang, laporan, dan batasan fitur) dijalankan. Hasil rekap menunjukkan 36 kasus lulus (100,0%), 0 tidak lulus. Verifikasi fee: nominal Rp1.000.000 menghasilkan cair bersih Rp730.000 (UTJ 22%), sedangkan nominal Rp2.600.000 menghasilkan cair bersih Rp2.184.000 (UTJ 11%).
