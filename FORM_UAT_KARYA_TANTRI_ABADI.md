# Form UAT — Karya Tantri Abadi

**Diisi oleh:** Responden mitra (Admin / SPV / Kasir / **Anggota = Ketua Kelompok**)  
**Fasilitator:** Peneliti — Adrian Akbar Ramadhani (NIM 222410102010)  
**File Word utama:** `FORM_UAT_KARYA_TANTRI_ABADI.docx` (+ `.doc`)

---

## A. Identitas responden

- Nama lengkap:
- Jabatan di mitra:
- Peran sistem: [ ] Admin  [ ] SPV  [ ] Kasir  [ ] Anggota (Ketua Kelompok)
- Tanggal uji: ____ / ____ / 2026
- Tempat:

## B. Petunjuk

Skala Likert 1–4 (tanpa netral):

| Skor | Arti |
|---:|---|
| 1 | STS — Sangat Tidak Setuju |
| 2 | TS — Tidak Setuju |
| 3 | S — Setuju |
| 4 | SS — Sangat Setuju |

Isi setelah mencoba fitur sesuai peran.

## C. Kuesioner (10 butir)

| No | Pernyataan | Variabel | STS | TS | S | SS |
|---:|---|---|:-:|:-:|:-:|:-:|
| 1 | Aplikasi mempermudah pencatatan tabungan dan pinjaman. | Usefulness | ☐ | ☐ | ☐ | ☐ |
| 2 | Alur pinjaman admin → SPV → kasir sesuai kebutuhan operasional. | Usefulness | ☐ | ☐ | ☐ | ☐ |
| 3 | Pencatatan cicilan oleh admin memudahkan rekap setelah petugas menyetor uang. | Usefulness | ☐ | ☐ | ☐ | ☐ |
| 4 | Ketua kelompok (akun anggota) dapat memantau pinjaman kelompok dengan jelas. | Usefulness | ☐ | ☐ | ☐ | ☐ |
| 5 | Navigasi menu dan tombol mudah dipahami. | Ease of Use | ☐ | ☐ | ☐ | ☐ |
| 6 | Proses login berjalan lancar dan aman (email + password). | Ease of Use | ☐ | ☐ | ☐ | ☐ |
| 7 | Akses/unduh laporan mudah dilakukan. | Ease of Use | ☐ | ☐ | ☐ | ☐ |
| 8 | Tampilan antarmuka nyaman dan teks mudah dibaca. | Experience | ☐ | ☐ | ☐ | ☐ |
| 9 | Aplikasi stabil tanpa error fatal selama uji coba. | Satisfaction | ☐ | ☐ | ☐ | ☐ |
| 10 | Secara keseluruhan saya puas dengan sistem ini. | Satisfaction | ☐ | ☐ | ☐ | ☐ |

## D. Rekap (diisi peneliti — jangan fabrikasi)

Per responden:
- Total skor aktual (10 item) = ______ (min 10, max 40)

Semua responden:
- Jumlah responden (N) = ______
- Total skor aktual = ______
- Total skor maksimum = N × 10 × 4 = ______
- **Persentase kelayakan (%)** = (total aktual / total maksimum) × 100 = ______ %

## E. Berita acara (ringkas)

Hari/tanggal/tempat:  
Peserta: Admin / SPV / Kasir / Anggota-Ketua Kelompok (isi nama + TTD di DOCX).  
Hasil: persentase kelayakan di atas; catatan perbaikan:  
...

Peneliti: Adrian Akbar Ramadhani — NIM 222410102010

## F. Walkthrough singkat sebelum isi kuesioner

1. Login `/auth/login` tanpa CAPTCHA.
2. Admin: input pinjaman + fee tier + catat cicilan.
3. SPV: setujui/tolak.
4. Kasir: tabungan + cairkan (tidak catat cicilan).
5. Anggota = ketua kelompok: lihat pinjaman sendiri.
6. Petugas offline: tidak login.

Akun demo: `*@karya-tantri-abadi.test` / `password`
