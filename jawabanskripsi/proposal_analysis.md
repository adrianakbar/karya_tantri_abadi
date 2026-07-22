# Analisis & Diagram Proposal Skripsi

Saya telah menganalisis dokumen [Proposal_Skripsi.pdf](file:///home/adrianakbar/Pribadi/Portofolio/skripsi/karya-tantri-abadi/Proposal_Skripsi.pdf) yang diajukan oleh **Adrian Akbar Ramadhani** (NIM **222410102010**). 

Rincian lengkap dari hasil analisis beserta kode diagram Mermaid yang dapat dirender telah berhasil dibuat pada berkas [PROPOSAL_DIAGRAMS.md](file:///home/adrianakbar/Pribadi/Portofolio/skripsi/karya-tantri-abadi/PROPOSAL_DIAGRAMS.md).

---

## Ringkasan Eksekutif Proposal
*   **Judul:** Pengembangan Sistem Koperasi Simpan Pinjam Berbasis Website Menggunakan Metode Research and Development (Studi Kasus: Koperasi Karya Tantri Abadi).
*   **Masalah Utama:** Pengelolaan koperasi simpan pinjam yang masih manual/tradisional di yayasan menyebabkan ketidakakuratan data, lambatnya laporan keuangan, dan kurangnya transparansi.
*   **Solusi:** Membangun aplikasi web koperasi simpan pinjam terintegrasi dengan framework Laravel, Filament UI, dan MySQL.
*   **Metodologi:** Mengadopsi metode **Research and Development (R&D)** yang diadaptasi dari model Borg & Gall (10 Tahapan) untuk rekayasa perangkat lunak.
*   **Evaluasi:** Menggunakan **Black Box Testing** (Equivalence Class Partitioning, Boundary Value Analysis, Error Guessing) dan **User Acceptance Testing (UAT)** berbasis Technology Acceptance Model (TAM/UTAUT) dengan instrumen kuesioner skala 1-4.

---

## Daftar Diagram yang Digenerate
Di dalam file [PROPOSAL_DIAGRAMS.md](file:///home/adrianakbar/Pribadi/Portofolio/skripsi/karya-tantri-abadi/PROPOSAL_DIAGRAMS.md), diagram-diagram berikut telah didefinisikan menggunakan Mermaid.js:

1.  **Tahapan Penelitian R&D:** Pemetaan grafis 10 langkah pengembangan R&D (Borg & Gall / Mufadhol) dari pengumpulan informasi awal hingga diseminasi hasil.
2.  **Use Case Diagram (UCD):** Pemodelan batas sistem yang menghubungkan aktor (*Anggota*, *Bendahara*, *Kepala Yayasan*, *Admin*) ke use case fungsional (login, pengajuan pinjaman, pengelolaan saldo simpanan, visualisasi laporan keuangan, dll).
3.  **Arsitektur Sistem:** Desain arsitektur aplikasi berbasis web (Nginx/Apache -> Laravel Router & Controller -> Filament/Livewire/Alpine/Tailwind -> Eloquent ORM -> MySQL Database).
4.  **Entity Relationship Diagram (ERD):** Hubungan relasional antar entitas database inti seperti `users`, `savings_transactions`, `savings_types`, `loans`, `loan_types`, `loan_payments`, dan `cash_flows`.
5.  **Gantt Chart Timeline Penelitian:** Konversi visual dari *Table 2 Jadwal Penelitian* di proposal ke dalam grafik batang Gantt berdurasi 4 bulan (Februari - Mei 2026).

---

> [!TIP]
> Anda dapat melihat semua diagram ini dirender secara langsung menggunakan ekstensi pembaca markdown (seperti GitHub Markdown Viewer atau plugin VS Code Markdown Preview) dengan membuka berkas [PROPOSAL_DIAGRAMS.md](file:///home/adrianakbar/Pribadi/Portofolio/skripsi/karya-tantri-abadi/PROPOSAL_DIAGRAMS.md).
