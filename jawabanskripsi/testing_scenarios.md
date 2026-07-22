# Black Box Testing & UAT - Koperasi Karya Tantri Abadi

Saya telah menyusun skenario **Black Box Testing** (berdasarkan metode ECP & BVA) dan rancangan **User Acceptance Testing (UAT)** (berdasarkan model TAM/UTAUT) sesuai dengan acuan metodologi penelitian pada proposal skripsi Anda.

Dokumen lengkap yang siap dimasukkan ke dalam lampiran skripsi Anda telah dibuat di:
[BLACK_BOX_UAT_TESTING.md](file:///home/adrianakbar/Pribadi/Portofolio/skripsi/karya-tantri-abadi/BLACK_BOX_UAT_TESTING.md)

---

## 🔄 Pemetaan Siklus Iteratif Uji Coba & Revisi (Metode R&D)
Sesuai dengan metode Research & Development (R&D), pengujian dan revisi **tidak dilakukan sekali saja**, melainkan dirancang melalui 3 siklus bertahap:
1.  **Siklus 1 (Skala Terbatas - Tahap 4 & 5):** Pengujian internal & 1 orang penguji (Bendahara) menggunakan *Black Box Testing* untuk membasmi bug fatal/system crash.
2.  **Siklus 2 (Skala Menengah - Tahap 6 & 7):** Pengujian integrasi alur data (alur kasir toko POS, amortisasi pinjaman otomatis, ekspor dokumen PDF) dengan 3-5 pengguna perwakilan.
3.  **Siklus 3 (Skala Besar - Tahap 8 & 9):** Uji coba operasional riil di koperasi dan pembagian kuesioner *User Acceptance Testing (UAT)* kepada seluruh pengurus, yayasan, dan 20+ anggota aktif koperasi.

---


## 🔍 Ringkasan Black Box Testing
Pengujian Black Box mencakup skenario fungsionalitas kunci sistem dengan pengujian nilai batas (BVA) dan partisi ekuivalen (ECP):

*   **Autentikasi (login-01 s/d login-05):** Pengujian keamanan password kosong, email tidak valid, reCAPTCHA salah, dan pembagian panel kerja otomatis.
*   **Transaksi Simpanan (sv-01 s/d sv-05):** Validasi batas minimal nominal setoran, batasan saldo penarikan sukarela, mutasi cash flow, dan ekspor kuitansi PDF.
*   **Modul Pinjaman (ln-01 s/d ln-04):** Pengujian limit plafon dan batas tenor, generate otomatis jadwal angsuran, serta pelunasan cicilan bulanan.
*   **POS Toko Koperasi (pos-01 s/d pos-04):** Validasi pemotongan stok otomatis, pemblokiran transaksi barang habis, pembayaran tunai (cash), dan validasi limit kredit potong gaji anggota.
*   **Pembagian SHU (shu-01 s/d shu-02):** Validasi pembagian porsi laba bersih tahunan berdasarkan proporsi simpanan dan total belanja anggota.

---

## 📝 Ringkasan Kuisioner UAT (User Acceptance Testing)
Kuisioner dirancang menggunakan **Skala Likert 4 Poin** (Sangat Tidak Setuju s/d Sangat Setuju) untuk menilai aspek-aspek berikut:
1.  **Perceived Usefulness (Kemanfaatan):** Efisiensi pencatatan simpanan, pinjaman, kasir toko, dan transparansi kalkulasi SHU.
2.  **Ease of Use (Kemudahan Penggunaan):** Kemudahan memahami tombol navigasi, CAPTCHA, dan fitur ekspor dokumen (Excel/PDF).
3.  **User Experience (Pengalaman Pengguna):** Visual antarmuka, kenyamanan tema warna, dan kecepatan respons portal.
4.  **Satisfaction (Kepuasan Keseluruhan):** Kepuasan responden terhadap kestabilan sistem bebas error.

Sistem pengolahan data menggunakan metode indeks persentase kelayakan dan klasifikasi interval validitas proposal (Skor Rata-rata $n$):
*   $1 \le n < 10$: **Tidak Baik**
*   $11 \le n < 20$: **Cukup**
*   $21 \le n < 30$: **Baik**
*   $31 \le n \le 40$: **Sangat Baik / Valid**

---

> [!TIP]
> Anda dapat menyalin langsung tabel skenario uji fungsionalitas dan kuisioner dari berkas [BLACK_BOX_UAT_TESTING.md](file:///home/adrianakbar/Pribadi/Portofolio/skripsi/karya-tantri-abadi/BLACK_BOX_UAT_TESTING.md) ke dokumen skripsi Anda (Bab IV atau bagian Lampiran Pengujian).
