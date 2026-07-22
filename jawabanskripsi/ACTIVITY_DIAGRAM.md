# Activity Diagram - Sistem Koperasi Karya Tantri Abadi

Dokumen ini berisi rangkaian **Activity Diagram** yang memodelkan alur kerja (*workflows*) utama dari aplikasi **Koperasi Karya Tantri Abadi**. Setiap diagram digambarkan menggunakan notasi UML Activity Diagram standar berbasis **Mermaid.js**, memisahkan proses berdasarkan peran aktor (*swimlanes/roles*) dan logika sistem.

---

## Daftar Isi
1. [Activity Diagram: Transaksi Simpanan (Setoran & Penarikan)](#1-activity-diagram-transaksi-simpanan-setoran--penarikan)
2. [Activity Diagram: Pengajuan & Angsuran Pinjaman](#2-activity-diagram-pengajuan--angsuran-pinjaman)
3. [Activity Diagram: Transaksi Retail (POS) Toko Koperasi](#3-activity-diagram-transaksi-retail-pos-toko-koperasi)
4. [Activity Diagram: Perhitungan & Pembagian Sisa Hasil Usaha (SHU)](#4-activity-diagram-perhitungan--pembagian-sisa-hasil-usaha-shu)

---

## 1. Activity Diagram: Transaksi Simpanan (Setoran & Penarikan)
Diagram ini menjelaskan bagaimana Anggota melakukan transaksi simpanan (Setoran Pokok, Wajib, Sukarela) atau melakukan penarikan dana simpanan Sukarela yang diproses oleh Bendahara di Panel Bendahara.

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

### Penjelasan Alur:
1. **Pilihan Transaksi:** Transaksi dibagi menjadi dua alur utama: Setoran dan Penarikan.
2. **Validasi Setoran:** Nominal setoran wajib bernilai positif (> 0).
3. **Validasi Penarikan:** Penarikan hanya diperbolehkan jika saldo jenis simpanan (biasanya simpanan sukarela) mencukupi nominal penarikan yang diminta.
4. **Pencatatan Keuangan (Cash Flow):** Setiap transaksi simpanan secara otomatis menghasilkan jurnal kas masuk (Debit) atau kas keluar (Kredit) secara real-time.
5. **Audit Trail:** Sistem mencatat snapshot data sebelum dan setelah diubah ke dalam `data_change_logs`.
6. **Output:** Kuitansi PDF dicetak menggunakan library DomPDF sebagai bukti transaksi fisik yang sah bagi anggota.

---

## 2. Activity Diagram: Pengajuan & Angsuran Pinjaman
Diagram ini menggambarkan siklus hidup pinjaman anggota, mulai dari pengajuan pinjaman, persetujuan dan pencairan oleh bendahara, hingga proses angsuran bulanan sampai pinjaman lunas.

```mermaid
flowchart TD
    Start([Mulai]) --> AnggotaAjukan[Anggota mengajukan pinjaman dengan memilih Jenis Pinjaman & Tenor]
    AnggotaAjukan --> BendaharaPeriksa[Bendahara memeriksa kelayakan anggota & riwayat kredit]
    BendaharaPeriksa --> KeputusanSetuju{Apakah Pengajuan Disetujui?}
    
    %% Penolakan
    KeputusanSetuju -- Tidak --> UpdateDitolak[Sistem mengubah status menjadi Ditolak]
    UpdateDitolak --> NotifikasiDitolak[Anggota menerima notifikasi penolakan via Panel Anggota]
    NotifikasiDitolak --> End([Selesai])
    
    %% Persetujuan
    KeputusanSetuju -- Ya --> UpdateDisetujui[Sistem mengubah status menjadi Disetujui]
    UpdateDisetujui --> BendaharaCairkan[Bendahara melakukan pencairan dana pinjaman]
    
    %% Proses Pencairan
    BendaharaCairkan --> ProsesSistemCair[Sistem memproses pencairan]
    ProsesSistemCair --> GenerateAngsuran[Sistem otomatis menghitung & membuat daftar jadwal angsuran]
    ProsesSistemCair --> CatatKasKeluar[Sistem mencatat Kredit Kas Keluar di Cash Flow]
    ProsesSistemCair --> LogPencairan[Sistem mencatat aktivitas di Data Change Log]
    
    GenerateAngsuran --> TerimaDana[Anggota menerima dana pinjaman]
    
    %% Pembayaran Angsuran
    TerimaDana --> MulaiCicil[Anggota membayar cicilan bulanan ke Bendahara]
    MulaiCicil --> BendaharaInputCicilan[Bendahara input pembayaran angsuran di Panel Bendahara]
    BendaharaInputCicilan --> UpdateAngsuran[Sistem mengubah status cicilan terkait menjadi Lunas]
    UpdateAngsuran --> KurangSisaPinjaman[Sistem mengurangi sisa saldo utang pinjaman]
    KurangSisaPinjaman --> CatatKasMasuk[Sistem mencatat Debit Kas Masuk pada Cash Flow]
    CatatKasMasuk --> CekPelunasan{Apakah Pinjaman Sudah Lunas Seluruhnya?}
    CekPelunasan -- Belum --> MulaiCicil
    CekPelunasan -- Ya --> UbahStatusLunas[Sistem mengubah status Pinjaman menjadi Lunas]
    UbahStatusLunas --> End
```

### Penjelasan Alur:
1. **Pengajuan:** Anggota memilih nominal, tipe bunga/syarat (`loan_types`), dan tenor (jangka waktu pembayaran).
2. **Review:** Bendahara bertindak sebagai verifikator kelayakan anggota.
3. **Pencairan & Amortisasi:** Ketika status diubah menjadi cair (disetujui), sistem secara otomatis mengkalkulasi jadwal angsuran (angsuran pokok, bunga, tanggal jatuh tempo) dan memotong kas koperasi sebesar nilai pokok pinjaman.
4. **Pembayaran Angsuran:** Pembayaran dilakukan berkala. Setiap kali angsuran dibayar, status angsuran bulan bersangkutan ditandai lunas, nominal utang berkurang, dan arus kas masuk dicatat.
5. **Pelunasan Akhir:** Pinjaman dinyatakan selesai (Lunas) setelah seluruh jadwal angsuran terpenuhi.

---

## 3. Activity Diagram: Transaksi Retail (POS) Toko Koperasi
Diagram ini menggambarkan alur kerja modul Kasir/Point of Sales (POS) Toko Koperasi, yang mendukung pembayaran tunai dan pembayaran kredit potong gaji/simpanan bagi anggota koperasi.

```mermaid
flowchart TD
    Start([Mulai]) --> PembeliPilih[Pembeli memilih barang dagangan di toko]
    PembeliPilih --> KasirScan[Kasir memindai barcode / memilih barang di aplikasi POS]
    KasirScan --> SistemTampilHarga[Sistem menampilkan daftar belanjaan, total harga, dan memverifikasi stok]
    SistemTampilHarga --> CekStok{Apakah Stok Cukup?}
    CekStok -- Tidak --> BatalkanBarang[Sesuaikan keranjang belanja / batalkan barang] --> KasirScan
    CekStok -- Ya --> PilihPembayaran[Kasir menanyakan metode pembayaran]
    
    %% Metode Tunai
    PilihPembayaran -->|Tunai / Cash| KasirTerimaUang[Kasir menerima uang tunai dari Pembeli]
    KasirTerimaUang --> InputTunai[Kasir input nominal bayar di POS]
    InputTunai --> SistemHitungKembalian[Sistem menghitung kembalian & mencatat transaksi Penjualan Tunai]
    SistemHitungKembalian --> PotongStokTunai[Sistem memotong stok produk secara otomatis]
    PotongStokTunai --> CatatArusKasTunai[Sistem mencatat Debit Kas Masuk pada Cash Flow]
    
    %% Metode Kredit Anggota
    PilihPembayaran -->|Kredit Anggota / Potong Simpanan| KasirPilihAnggota[Kasir memilih identitas Anggota di POS]
    KasirPilihAnggota --> SistemCekLimit{Apakah Saldo/Limit Kredit Anggota Cukup?}
    SistemCekLimit -- Tidak --> AlertGagal[Tampilkan error: Kredit Ditolak / Saldo Kurang] --> PilihPembayaran
    SistemCekLimit -- Ya --> CatatKredit[Sistem mencatat transaksi Penjualan Kredit & menambah saldo utang/belanja anggota]
    CatatKredit --> PotongStokKredit[Sistem memotong stok produk secara otomatis]
    PotongStokKredit --> CatatArusKasKredit[Sistem mencatat Penjualan Piutang di Cash Flow]
    
    %% Akhir Alur
    CatatArusKasTunai --> LogStok[Sistem mencatat ke Stock Movement Log]
    CatatArusKasKredit --> LogStok
    LogStok --> CetakStruk[Sistem mencetak struk belanja PDF]
    CetakStruk --> SerahkanBarang[Kasir menyerahkan barang & struk kepada Pembeli]
    SerahkanBarang --> End([Selesai])
```

### Penjelasan Alur:
1. **Input POS:** Kasir memindai barang menggunakan scanner barcode atau memilih secara manual. Sistem melakukan validasi stok real-time (`products.stock`).
2. **Metode Tunai:** Pembeli umum atau anggota membayar langsung secara tunai. Kas masuk langsung didebit pada sistem akuntansi.
3. **Metode Kredit:** Anggota dapat berbelanja secara kredit (potong gaji bulanan/potong saldo simpanan). Sistem akan memvalidasi apakah batas limit kredit anggota atau saldo simpanannya mencukupi untuk melakukan pembelian.
4. **Manajemen Stok Otomatis:** Setiap transaksi sukses memicu pengurangan stok fisik barang secara otomatis dan mencatat histori keluar-masuk barang pada tabel `stock_movement_logs`.
5. **Struk PDF:** Struk belanja digenerate menggunakan format struk thermal mini dan dicetak langsung.

---

## 4. Activity Diagram: Perhitungan & Pembagian Sisa Hasil Usaha (SHU)
Diagram ini menjelaskan bagaimana pengurus melakukan kalkulasi tahunan pembagian SHU (Sisa Hasil Usaha) koperasi secara transparan dan proporsional kepada seluruh anggota.

```mermaid
flowchart TD
    Start([Mulai]) --> PengurusInisiasi[Pengurus/Bendahara menginisiasi Tutup Buku & Perhitungan SHU Tahunan]
    PengurusInisiasi --> SistemHitungKeuangan[Sistem menghitung total keuangan dari database]
    
    SistemHitungKeuangan --> AmbilPendapatan[Sistem menjumlahkan total pendapatan koperasi dari bunga pinjaman & margin toko]
    SistemHitungKeuangan --> AmbilBeban[Sistem menjumlahkan total beban operasional dari pengeluaran]
    
    AmbilPendapatan --> HitungSHUKotor[Sistem menghitung SHU Kotor & SHU Bersih]
    AmbilBeban --> HitungSHUKotor
    
    HitungSHUKotor --> AlokasiPersentase[Sistem membagi SHU berdasarkan persentase alokasi yang disetting]
    AlokasiPersentase --> PorsiAnggota[Sistem mengisolasi bagian SHU untuk Anggota]
    
    PorsiAnggota --> HitungPartisipasi[Sistem menghitung porsi SHU per Anggota secara proporsional]
    
    subgraph Formula Perhitungan Per Anggota
        HitungPartisipasi --> HitungPorsiSimpanan[1. Proporsi Simpanan Anggota terhadap Total Simpanan Koperasi]
        HitungPartisipasi --> HitungPorsiBelanja[2. Proporsi Belanja Anggota terhadap Total Penjualan Toko Koperasi]
    end
    
    HitungPorsiSimpanan --> HitungTotalSHUMember[Sistem menjumlahkan Porsi Simpanan + Porsi Belanja Anggota]
    HitungPorsiBelanja --> HitungTotalSHUMember
    
    HitungTotalSHUMember --> SimpanSHUMember[Sistem menyimpan hasil hitung ke tabel shu_member_shares]
    SimpanSHUMember --> CatatDataChangeLog[Sistem mencatat transaksi ke Data Change Log]
    
    CatatDataChangeLog --> TampilPortal[Sistem merilis hasil SHU ke Panel Anggota]
    TampilPortal --> KeputusanPencairan{Metode Penyaluran SHU?}
    
    KeputusanPencairan -->|Ditambah ke Simpanan Sukarela| AutoSimpanan[Sistem secara otomatis menambah saldo Simpanan Sukarela anggota]
    KeputusanPencairan -->|Ditarik Tunai| CairkanTunai[Bendahara menyerahkan dana tunai & mencatat cash flow keluar]
    
    AutoSimpanan --> End([Selesai])
    CairkanTunai --> End
```

### Penjelasan Alur:
1. **Tutup Buku:** Proses ini biasanya dilakukan satu kali di akhir periode tahun buku koperasi.
2. **Kalkulasi Laba-Rugi Bersih:** Sistem mengambil pendapatan bunga pinjaman dan laba bersih retail toko dikurangi pengeluaran operasional koperasi (`expenses`).
3. **Pembagian Porsi SHU:** Porsi SHU dibagi ke beberapa pos alokasi (Cadangan Koperasi, Jasa Anggota, Pengurus, Pengawas, Yayasan) sesuai persentase regulasi koperasi pada `shu_distributions`.
4. **Formula Keadilan Partisipasi Anggota:**
   - **Partisipasi Modal (Simpanan):** Semakin besar total simpanan seorang anggota dibanding anggota lain, semakin besar porsi SHU jasa simpanan yang diterimanya.
   - **Partisipasi Usaha (Belanja Toko):** Semakin aktif anggota berbelanja di unit toko koperasi, semakin besar porsi SHU jasa transaksi retail yang didapatkannya.
5. **Pencatatan:** Hasil final disimpan di `shu_member_shares` untuk transparansi laporan. Anggota dapat melihat rincian SHU tahunan mereka melalui dashboard pribadi.
6. **Reinvestasi atau Likuidasi:** Anggota dapat memilih untuk memasukkan SHU langsung ke saldo Simpanan Sukarela mereka atau mencairkannya dalam bentuk tunai.
