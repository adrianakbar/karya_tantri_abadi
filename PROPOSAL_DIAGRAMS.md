# Analisis & Diagram - Proposal Skripsi Karya Tantri Abadi

Dokumen ini berisi analisis dan visualisasi diagram berdasarkan berkas proposal skripsi **"Pengembangan Sistem Koperasi Simpan Pinjam Berbasis Website Menggunakan Metode Research and Development (Studi Kasus: Karya Tantri Abadi)"** oleh **Adrian Akbar Ramadhani** (NIM **222410102010**).

---

## Daftar Isi Diagram
1. [Diagram Tahapan Penelitian (R&D 10 Langkah)](#1-diagram-tahapan-penelitian-rd-10-langkah)
2. [Use Case Diagram (UCD) Sistem Koperasi](#2-use-case-diagram-ucd-sistem-koperasi)
3. [Arsitektur Sistem (System Architecture)](#3-arsitektur-sistem-system-architecture)
4. [Entity Relationship Diagram (ERD) - Modul Simpan Pinjam](#4-entity-relationship-diagram-erd---modul-simpan-pinjam)
5. [Gantt Chart Jadwal Penelitian (Timeline R&D)](#5-gantt-chart-jadwal-penelitian-timeline-rd)

---

## 1. Diagram Tahapan Penelitian (R&D 10 Langkah)
Berdasarkan deskripsi metodologi di Bab H.4 (halaman 8, 9, 13, dan 14) proposal, metodologi R&D yang digunakan terdiri dari 10 tahap berurutan dan iteratif.

```mermaid
flowchart TD
    Start([Mulai]) --> Step1[1. Research and Information Collection\n- Studi literatur & observasi\n- Analisis masalah & kebutuhan awal]
    Step1 --> Step2[2. Planning\n- Perumusan tujuan penelitian & jadwal\n- Penentuan infra teknologi & deskripsi tugas]
    Step2 --> Step3[3. Develop Preliminary Product\n- Perancangan sistem (ERD, AD, SD)\n- Pembuatan prototipe fungsional (Laravel)]
    Step3 --> Step4[4. Preliminary Field Testing\n- Uji coba skala kecil terbatas\n- Pengujian Black Box & Usabilitas awal]
    Step4 --> Step5[5. Main Product Revision\n- Perbaikan dari umpan balik uji terbatas\n- Pembenahan aspek fungsionalitas & kegunaan]
    Step5 --> Step6[6. Main Field Testing\n- Uji coba lapangan lebih luas\n- Pengujian efektivitas & kinerja sistem]
    Step6 --> Step7[7. Operational Product Revision\n- Perbaikan lanjut pasca uji utama\n- Peningkatan stabilitas performa sistem]
    Step7 --> Step8[8. Operational Field Testing\n- Uji coba skala besar kondisi nyata\n- Evaluasi kuesioner UAT (User Acceptance Testing)]
    Step8 --> Step9[9. Final Product Revision\n- Penyempurnaan akhir hasil UAT\n- Penjaminan standar kualitas perangkat lunak]
    Step9 --> Step10[10. Dissemination & Implementation\n- Publikasi artikel ilmiah\n- Penerapan penuh di Koperasi Yayasan]
    Step10 --> End([Selesai])
```

---

## 2. Use Case Diagram (UCD) Sistem Koperasi
Memetakan aktor-aktor yang terlibat (Admin, Bendahara, Anggota, Kepala Yayasan) serta interaksi fungsional mereka terhadap sistem.

```mermaid
flowchart LR
    subgraph Aktor
        Anggota((Anggota))
        Bendahara((Bendahara))
        Yayasan((Kepala Yayasan))
        Admin((Admin))
    end

    subgraph Batasan Sistem Koperasi
        UC_Login(1. Login & Keamanan CAPTCHA)
        UC_LihatProfile(2. Lihat Saldo & Riwayat Pribadi)
        UC_AjukanPinjaman(3. Ajukan Pinjaman Mandiri)
        UC_KelolaAnggota(4. Kelola Data Anggota)
        UC_TransaksiSimpanan(5. Kelola Transaksi Simpanan)
        UC_ProsesPinjaman(6. Proses & Cairkan Pinjaman)
        UC_AngsuranPinjaman(7. Catat Angsuran Pinjaman)
        UC_LaporanKeuangan(8. Pantau Laporan Keuangan)
        UC_SistemSetting(9. Kelola Hak Akses & Backup Sistem)
    end

    Anggota --> UC_Login
    Anggota --> UC_LihatProfile
    Anggota --> UC_AjukanPinjaman

    Bendahara --> UC_Login
    Bendahara --> UC_KelolaAnggota
    Bendahara --> UC_TransaksiSimpanan
    Bendahara --> UC_ProsesPinjaman
    Bendahara --> UC_AngsuranPinjaman
    Bendahara --> UC_LaporanKeuangan

    Yayasan --> UC_Login
    Yayasan --> UC_LaporanKeuangan

    Admin --> UC_Login
    Admin --> UC_KelolaAnggota
    Admin --> UC_LaporanKeuangan
    Admin --> UC_SistemSetting
```

---

## 3. Arsitektur Sistem (System Architecture)
Menunjukkan struktur komponen teknologi yang digunakan dalam pengembangan web koperasi simpan pinjam berbasis Laravel & Filament.

```mermaid
flowchart TD
    Client[Client / Web Browser\nChrome, Firefox, Safari] <-->|HTTP / HTTPS| Server[Web Server\nApache/Nginx]
    
    subgraph Laravel Application
        Server <--> Core[Laravel Core Controller / Router]
        
        subgraph UI & Front-end Layer
            Core <--> Filament[Laravel Filament v3 Engine]
            Filament <--> Livewire[Livewire Component]
            Livewire <--> Alpine[Alpine.js State]
            Livewire <--> Tailwind[Tailwind CSS Rendering]
        end
        
        subgraph Business Logic & ORM
            Core <--> Model[Eloquent Models\nUser, SavingsTransaction, Loan, etc.]
        end
    end
    
    Model <--> Database[(MySQL Database)]
```

---

## 4. Entity Relationship Diagram (ERD) - Modul Simpan Pinjam
Skema basis data relasional tingkat tinggi untuk modul utama (Anggota, Simpanan, Pinjaman, dan Arus Kas Keuangan).

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email
        string password
        string role
    }
    savings_transactions {
        bigint id PK
        bigint user_id FK
        bigint savings_type_id FK
        decimal amount
        date transaction_date
    }
    savings_types {
        bigint id PK
        string name
        string description
    }
    loans {
        bigint id PK
        bigint user_id FK
        bigint loan_type_id FK
        decimal amount
        integer interest_rate
        integer tenor_months
        string status
    }
    loan_types {
        bigint id PK
        string name
        decimal interest_rate
        integer max_tenor
    }
    loan_payments {
        bigint id PK
        bigint loan_id FK
        decimal amount
        date payment_date
    }
    cash_flows {
        bigint id PK
        string transaction_type
        decimal debit
        decimal credit
        date date
        string reference_type
        bigint reference_id
    }

    users ||--o{ savings_transactions : "memiliki"
    savings_types ||--o{ savings_transactions : "tipe"
    users ||--o{ loans : "mengajukan"
    loan_types ||--o{ loans : "tipe"
    loans ||--o{ loan_payments : "memiliki"
    savings_transactions ||--o| cash_flows : "dicatat_di"
    loan_payments ||--o| cash_flows : "dicatat_di"
```

---

## 5. Gantt Chart Jadwal Penelitian (Timeline R&D)
Visualisasi dari *Table 2 Jadwal Penelitian* (halaman 18 dan 19) proposal, membagi 10 tahapan penelitian R&D ke dalam alokasi waktu 4 bulan (Februari - Mei 2026).

```mermaid
gantt
    title Jadwal Penelitian R&D (4 Bulan)
    dateFormat  YYYY-MM-DD
    axisFormat  %m
    
    section Bulan I
    Research & Information Collection : active, t1, 2026-02-01, 2026-02-28
    Planning                          : active, t2, 2026-02-01, 2026-02-28
    
    section Bulan II
    Develop Preliminary Product       : active, t3, 2026-03-01, 2026-03-20
    Preliminary Field Testing          : active, t4, 2026-03-15, 2026-03-25
    Main Product Revision             : active, t5, 2026-03-20, 2026-03-31
    
    section Bulan III
    Main Field Testing                : active, t6, 2026-04-01, 2026-04-15
    Operational Product Revision      : active, t7, 2026-04-10, 2026-04-20
    Operational Field Testing         : active, t8, 2026-04-15, 2026-04-30
    
    section Bulan IV
    Final Product Revision            : active, t9, 2026-05-01, 2026-05-15
    Dissemination & Implementation    : active, t10, 2026-05-10, 2026-05-31
```
