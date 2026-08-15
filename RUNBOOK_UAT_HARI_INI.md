# Runbook UAT — Karya Tantri Abadi (hari ini ~10:00)

## Waktu & peran
- Mulai: sekitar 10:00
- Peneliti: fasilitator + isi black box
- Mitra: operasikan UI + isi form UAT

## Form yang dibawa
1. `FORM_UAT_KARYA_TANTRI_ABADI.docx` — print **4 lembar** (min), ideal 6–10
2. `CHECKLIST_DEMO_BLACKBOX.docx` — **1 set**, diisi peneliti
3. (Opsional) `PANDUAN_DAN_FORM_PENGUJIAN.md` sebagai cheat-sheet walkthrough

## Alur sesi (~45–60 menit)
1. **Pembuka 5 menit**  
   Scope simpan pinjam; UI = tabungan; petugas offline; 4 role online.
2. **Demo walkthrough 20–25 menit**  
   Login `/auth/login` → admin input pinjaman → SPV setujui → kasir cairkan + tabungan → admin catat cicilan → anggota (ketua) lihat.
3. **Black box (peneliti)** sambil demo / sesudah demo singkat.
4. **Isi UAT 10–15 menit**  
   1 orang = 1 form; centang role; 10 butir Likert.
5. **Berita acara + TTD** di halaman 2 form UAT.

## Akun demo (jika seed KTA aktif)
Password: `password`
- admin@karya-tantri-abadi.test → /admin
- spv@karya-tantri-abadi.test → /spv
- kasir@karya-tantri-abadi.test → /kasir
- anggota@karya-tantri-abadi.test → /anggota

## Fee demo (fasilitator)
- 1.000.000 → UTJ 22%, cair 730.000
- 2.600.000 → UTJ 11%, cair 2.184.000

## Catatan teknis (status mesin ini)
- Port `http://127.0.0.1:8000` saat dicek masih container **Ar-Raudhoh**, bukan brand KTA.
- Folder clone `~/karya_tantri_abadi` belum punya `.env` / `vendor` lokal.
- Sebelum demo ke mitra: pastikan URL yang dibuka sudah brand **Karya Tantri Abadi** + role admin/spv/kasir/anggota.
- Kalau sistem demo belum siap, UAT form tetap bisa dibawa; skor isi setelah mitra coba sistem yang benar.

## Setelah sesi
- Jangan fabrikasi skor
- Rekap ke BAB 4 tabel UAT
- Temuan bug → Final Product Revision
