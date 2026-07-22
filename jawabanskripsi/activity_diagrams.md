# Activity Diagrams - Koperasi Karya Tantri Abadi

Dokumen ini berisi rangkuman **Activity Diagram** yang dirancang untuk memetakan alur kerja utama aplikasi **Koperasi Karya Tantri Abadi**. Rincian lengkap beserta penjelasan teknis telah dibuat langsung di dalam repositori proyek Anda pada file [ACTIVITY_DIAGRAM.md](file:///home/adrianakbar/Pribadi/Portofolio/skripsi/karya-tantri-abadi/ACTIVITY_DIAGRAM.md).

Berikut adalah ringkasan modul-modul yang dibuatkan diagram aktivitasnya:

| Modul | Peran/Aktor | Deskripsi Alur |
| :--- | :--- | :--- |
| **1. Simpanan (Savings)** | Anggota, Bendahara, Sistem | Alur setoran simpanan (pokok/wajib/sukarela) dan penarikan simpanan sukarela, validasi saldo, pencatatan otomatis ke jurnal arus kas (cash flow), audit trail (`data_change_logs`), serta pengeluaran bukti kuitansi PDF. |
| **2. Pinjaman (Loans)** | Anggota, Bendahara, Sistem | Alur lengkap pengajuan pinjaman, pengecekan kelayakan, persetujuan/penolakan, pencairan, pembuatan jadwal amortisasi angsuran otomatis, hingga pembayaran cicilan bulanan sampai status pinjaman lunas. |
| **3. Toko & Retail (POS)** | Pembeli/Anggota, Kasir, Sistem | Alur Point of Sales (POS) retail toko koperasi. Mendukung pembayaran Tunai (Cash) dan Penjualan Kredit Anggota (potong gaji/utang anggota), validasi stok fisik, pemotongan stok otomatis, log pergerakan stok, dan cetak struk belanja thermal. |
| **4. Pembagian SHU** | Pengurus/Bendahara, Sistem | Proses akhir tahun (tutup buku) yang menghitung pendapatan kotor dan bersih, mengalokasikan persentase SHU, serta membagikannya secara proporsional kepada anggota berdasarkan partisipasi simpanan dan total belanja toko. |

---

## Cuplikan Diagram Alur Utama (Mermaid)

### 🪙 Transaksi Simpanan (Setoran & Penarikan)
```mermaid
flowchart TD
    Start([Mulai]) --> AnggotaPilih[Anggota menentukan jenis transaksi]
    
    %% Alur Setoran
    AnggotaPilih -->|Setoran Simpanan| AnggotaSetor[Anggota menyerahkan dana & info Jenis Simpanan ke Bendahara]
    AnggotaSetor --> BendaharaInputSetor[Bendahara input transaksi Setoran di Panel Bendahara]
    BendaharaInputSetor --> ValidasiSetor{Apakah Nominal > 0?}
    ValidasiSetor -- Tidak --> ErrorNominal[Tampilkan error nominal harus positif] --> BendaharaInputSetor
    ValidasiSetor -- Ya --> SimpanSetor[Sistem menyimpan transaksi Setoran]
    SimpanSetor --> TambahSaldo[Sistem memperbarui saldo simpanan anggota]
    TambahSaldo --> JurnalMasuk[Sistem mencatat Debit Kas Masuk pada Cash Flow]
    
    %% Alur Penarikan
    AnggotaPilih -->|Penarikan Simpanan| AnggotaTarik[Anggota meminta penarikan dana ke Bendahara]
    AnggotaTarik --> BendaharaInputTarik[Bendahara input transaksi Penarikan di Panel Bendahara]
    BendaharaInputTarik --> ValidasiSaldo{Apakah Saldo Sukarela Cukup?}
    ValidasiSaldo -- Tidak --> ErrorSaldo[Tampilkan error saldo tidak mencukupi] --> BendaharaInputTarik
    ValidasiSaldo -- Ya --> SimpanTarik[Sistem menyimpan transaksi Penarikan]
    SimpanTarik --> KurangSaldo[Sistem memperbarui saldo simpanan anggota]
    KurangSaldo --> JurnalKeluar[Sistem mencatat Kredit Kas Keluar pada Cash Flow]
    
    %% Gabungan Proses Akhir
    JurnalMasuk --> AuditLog[Sistem mencatat riwayat perubahan di Data Change Log]
    JurnalKeluar --> AuditLog
    AuditLog --> CetakPDF[Sistem generate Kuitansi Simpanan PDF]
    CetakPDF --> CetakKuitansi[Bendahara cetak kuitansi & serahkan ke Anggota]
    CetakKuitansi --> End([Selesai])
```

---

> [!NOTE]
> File lengkap [ACTIVITY_DIAGRAM.md](file:///home/adrianakbar/Pribadi/Portofolio/skripsi/karya-tantri-abadi/ACTIVITY_DIAGRAM.md) telah sukses dibuat di direktori utama proyek Anda dan siap diakses atau diintegrasikan ke sistem dokumentasi tim pengembang.
