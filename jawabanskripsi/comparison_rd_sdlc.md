# Perbandingan Akademik: R&D vs. Agile/Scrum (SDLC)

Dokumen ini disusun sebagai panduan akademik bagi Anda untuk menjawab pertanyaan dosen penguji skripsi mengenai pemilihan metode penelitian **Research and Development (R&D)** dibandingkan dengan metode pengembangan perangkat lunak seperti **Agile** atau **Scrum**.

---

## 1. Tabel Komparasi R&D vs. Agile/Scrum (SDLC)

| Aspek Perbandingan | Research & Development (R&D) | Agile / Scrum (SDLC) |
| :--- | :--- | :--- |
| **Definisi Dasar** | **Metodologi Penelitian Ilmiah** (fokus pada riset dan pembuktian kelayakan produk). | **Metodologi Rekayasa & Manajemen Proyek** (fokus pada proses kerja tim developer). |
| **Tujuan Utama** | Menghasilkan produk baru yang **tervalidasi secara ilmiah** untuk memecahkan masalah praktis. | Mengirimkan perangkat lunak berfungsi (*working software*) secepat mungkin sesuai kebutuhan bisnis yang dinamis. |
| **Fokus Evaluasi** | **Validitas & Efektivitas** (Apakah produk ini benar-benar menyelesaikan masalah secara ilmiah?). | **Kecepatan & Fleksibilitas** (Apakah fitur baru dapat dikirim ke pasar secepat mungkin?). |
| **Siklus Iterasi** | Berdasarkan **tingkat/skala pengujian lapangan** (Uji Coba Awal $\rightarrow$ Uji Coba Utama $\rightarrow$ Uji Coba Operasional). | Berdasarkan **durasi waktu pengerjaan** (*Time-boxed Sprints*, biasanya berkisar 1-4 minggu). |
| **Cara Pengumpulan Feedback** | Menggunakan **instrumen riset formal** (Kuesioner UAT berbasis model ilmiah seperti TAM atau UTAUT). | Melalui pertemuan kolaboratif harian (*Daily Standup*) dan ulasan akhir sprint (*Sprint Review*). |
| **Peran Pengguna Akhir** | Bertindak sebagai **Subjek Penelitian/Responden** yang memberikan data objektif kelayakan sistem. | Bertindak sebagai *User* atau *Product Owner* yang memandu arah pembuatan fitur (*backlogs*). |
| **Luaran Akhir (Output)** | Produk aplikasi fungsional **DAN** data riset ilmiah pembuktian kelayakan (Skripsi/Jurnal). | Aplikasi jadi (*Working Software*) siap rilis di lingkungan produksi komersial. |

---

## 2. Penjelasan Detail Perbedaan Siklus Iterasi

Meskipun kedua metode memiliki siklus yang tampak berulang (iteratif), esensi dari pengulangan tersebut sangat berbeda:

### A. Iterasi pada R&D (Siklus Pengujian Lapangan)
Dalam R&D, iterasi didorong oleh **validasi skala pengguna**. Anda tidak menguji fitur yang sama berulang-ulang tanpa tujuan ilmiah. 
*   **Siklus 1 (Uji Awal):** Dilakukan pada skala kecil (2-3 orang) untuk menemukan kecacatan fundamental fungsional sistem. Setelah diperbaiki, barulah sistem diizinkan naik ke tahap pengujian berikutnya.
*   **Siklus 2 (Uji Utama):** Dilakukan pada skala menengah (5-10 orang) untuk menguji integrasi alur bisnis.
*   **Siklus 3 (Uji Operasional):** Dilakukan dalam kondisi operasional riil sesungguhnya (20+ orang) untuk mendapatkan penilaian penerimaan secara ilmiah melalui kuesioner UAT.

### B. Iterasi pada Agile/Scrum (Siklus Sprint)
Dalam Agile/Scrum, iterasi didorong oleh **penambahan fitur secara bertahap (increment)**.
*   **Sprint 1:** Membuat modul Login dan Keanggotaan.
*   **Sprint 2:** Membuat modul Simpanan.
*   **Sprint 3:** Membuat modul Pinjaman, dst.
Di setiap akhir Sprint, fungsionalitas diuji untuk memastikan kesepakatan spesifikasi terpenuhi, bukan untuk mengukur korelasi kegunaan sistem bagi organisasi secara ilmiah.

---

## 3. Strategi Menjawab Pertanyaan Dosen Penguji

Jika dosen penguji menanyakan: **"Mengapa Anda menggunakan metode R&D, bukan Agile atau Scrum?"**, gunakan 3 argumen akademis berikut:

1.  **Skripsi adalah Penelitian Ilmiah, Bukan Sekadar Proyek Komersial:**
    *   *Jawaban:* "Penelitian skripsi saya bertujuan untuk menguji dan membuktikan secara ilmiah kelayakan serta tingkat penerimaan sistem informasi koperasi di Koperasi Karya Tantri Abadi. Metode R&D menyediakan kerangka kerja riset (seperti Borg & Gall/Mufadhol) yang mengharuskan adanya validasi empiris bertahap serta pengukuran kuantitatif UAT. Sedangkan Agile/Scrum adalah framework manajemen proyek IT industri komersial yang tidak fokus pada metodologi riset akademik."
2.  **R&D Menyediakan Validasi Berjenjang untuk Meminimalisasi Risiko:**
    *   *Jawaban:* "Metode R&D memiliki tahapan uji lapangan terstruktur (Awal, Utama, dan Operasional). Struktur ini sangat penting untuk koperasi yayasan yang memiliki alur keuangan riil, sehingga kami dapat menyaring *bug* dari skala kecil terlebih dahulu sebelum sistem dioperasikan langsung dengan dana riil oleh seluruh anggota koperasi pada Uji Operasional."
3.  **Kesesuaian dengan Skala Tim Mandiri (Single Developer):**
    *   *Jawaban:* "Scrum membutuhkan struktur tim yang kolaboratif (Scrum Master, Product Owner, dan Development Team). Karena penelitian skripsi ini dilakukan secara mandiri sebagai pengembang tunggal sekaligus peneliti, maka metodologi R&D jauh lebih adaptif dan sesuai untuk memandu siklus hidup riset individu."
