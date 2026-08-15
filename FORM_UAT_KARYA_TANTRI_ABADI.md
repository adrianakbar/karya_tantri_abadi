# Form UAT — Karya Tantri Abadi

**Diisi oleh:** Responden mitra (Admin / SPV / Kasir / **Anggota = Ketua Kelompok**)  
**Fasilitator:** Peneliti — Adrian Akbar Ramadhani (NIM 222410102010)  
**File Word utama:** `FORM_UAT_KARYA_TANTRI_ABADI.docx` (+ `.doc`)  
**Sumber sistem:** multi-panel Filament (`/admin`, `/spv`, `/kasir`, `/anggota`); login `/auth/login` (email + password, **tanpa CAPTCHA**)

---

## A. Identitas responden

- Nama lengkap:
- Jabatan di mitra:
- Peran sistem: [ ] Admin  [ ] SPV  [ ] Kasir  [ ] Anggota (Ketua Kelompok)
- Tanggal uji: ____ / ____ / 2026
- Tempat:
- No. HP/WA (opsional):

## B. Petunjuk

Skala Likert 1–4 (tanpa netral):

| Skor | Arti |
|---:|---|
| 1 | STS — Sangat Tidak Setuju |
| 2 | TS — Tidak Setuju |
| 3 | S — Setuju |
| 4 | SS — Sangat Setuju |

Isi **setelah** mencoba fitur sesuai peran (lihat walkthrough bagian F).  
1 orang = 1 form. Jangan saling pinjam lembar.

## C. Kuesioner (10 butir)

| No | Pernyataan | Variabel | STS | TS | S | SS |
|---:|---|---|:-:|:-:|:-:|:-:|
| 1 | Aplikasi mempermudah pencatatan tabungan dan pinjaman kelompok. | Usefulness | ☐ | ☐ | ☐ | ☐ |
| 2 | Alur pinjaman admin input → SPV setujui/tolak → kasir cairkan sesuai kebutuhan operasional. | Usefulness | ☐ | ☐ | ☐ | ☐ |
| 3 | Pencatatan cicilan oleh admin memudahkan rekap setelah petugas lapangan menyetor uang. | Usefulness | ☐ | ☐ | ☐ | ☐ |
| 4 | Ketua kelompok (akun anggota) dapat memantau pinjaman kelompok sendiri dengan jelas (cair bersih, angsuran, sisa, status). | Usefulness | ☐ | ☐ | ☐ | ☐ |
| 5 | Navigasi menu dan tombol sesuai peran mudah dipahami. | Ease of Use | ☐ | ☐ | ☐ | ☐ |
| 6 | Proses login berjalan lancar dan aman (email + password). | Ease of Use | ☐ | ☐ | ☐ | ☐ |
| 7 | Informasi penting sesuai peran (pinjaman / tabungan / laporan) mudah diakses. | Ease of Use | ☐ | ☐ | ☐ | ☐ |
| 8 | Tampilan antarmuka nyaman dan teks mudah dibaca. | Experience | ☐ | ☐ | ☐ | ☐ |
| 9 | Aplikasi stabil tanpa error fatal selama uji coba. | Satisfaction | ☐ | ☐ | ☐ | ☐ |
| 10 | Secara keseluruhan saya puas dengan sistem ini. | Satisfaction | ☐ | ☐ | ☐ | ☐ |

Catatan istilah: formal akademik = **simpanan**; label UI mitra = **tabungan**.  
Scope uji: simpan pinjam saja (POS/SHU/panel petugas **tidak** diuji sebagai fitur aktif).

## D. Rekap (diisi peneliti — jangan fabrikasi)

Per responden:
- Total skor aktual (10 item) = ______ (min 10, max 40)
- Kategori: [ ] Tidak baik (10–19)  [ ] Cukup (20–29)  [ ] Baik (30–34)  [ ] Sangat baik (35–40)

Semua responden:
- Jumlah responden (N) = ______
- Total skor aktual = ______
- Total skor maksimum = N × 10 × 4 = ______
- **Persentase kelayakan (%)** = (total aktual / total maksimum) × 100 = ______ %

Interpretasi global usulan: <50 kurang; 50–69 cukup; 70–84 baik; ≥85 sangat baik.

## E. Berita acara (ringkas)

Hari/tanggal/tempat:  
Peserta: Admin / SPV / Kasir / Anggota-Ketua Kelompok (isi nama + TTD di DOCX).  
Hasil: persentase kelayakan di atas; catatan perbaikan:  
...

Peneliti: Adrian Akbar Ramadhani — NIM 222410102010

## F. Walkthrough singkat sebelum isi kuesioner

Login bersama di `/auth/login` (tanpa CAPTCHA). Password demo seed: `password`.

| Peran | Panel | Yang dicoba di sistem |
|---|---|---|
| Admin | `/admin` | Cek/kelola anggota; input pinjaman kelompok pending + cek fee tier; setelah cair: **Catat Bayar** cicilan; buka laporan pinjaman/tabungan/keuangan; (opsional) backup |
| SPV | `/spv` | Buka pinjaman pending; **Setujui** / **Tolak**; pantau laporan pinjaman/keuangan |
| Kasir | `/kasir` | Catat **Tabungan**; **Cairkan** pinjaman approved; pastikan jadwal cicilan muncul; **tidak** ada Catat Bayar; buka laporan |
| Anggota (Ketua Kelompok) | `/anggota` | Lihat pinjaman milik sendiri saja; cek cair bersih, angsuran, sisa, status; pastikan tidak bisa create/edit pinjaman |
| Petugas lapangan | — (offline) | Tidak login. Disimulasikan: serahkan data pengajuan + uang cicilan ke admin |

### Parameter fee pinjaman (untuk demo fasilitator)

| Nominal contoh | Admin | UTJ | Cair bersih | Total dilunasi (nominal+11%) |
|---:|---:|---:|---:|---:|
| Rp1.000.000 | 5% | 22% | Rp730.000 | Rp1.110.000 |
| Rp2.600.000 | 5% | 11% | Rp2.184.000 | Rp2.886.000 |

Plafon max Rp5.000.000; tenor max 3 bulan; weekly 3 bln = 12 cicilan (jadwal dibuat saat pencairan).

Akun demo: `admin@karya-tantri-abadi.test`, `spv@…`, `kasir@…`, `anggota@…` / `password`  
(alternatif: `*@test.com`)

## G. Print

- Minimal: **4 lembar** (1 admin + 1 SPV + 1 kasir + 1 ketua kelompok)
- Ideal: **6–10 lembar**
- Black box/checklist demo: **1 set** (diisi peneliti, form terpisah)
