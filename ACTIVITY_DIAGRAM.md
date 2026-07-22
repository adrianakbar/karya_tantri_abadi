# Activity Diagram - Sistem Karya Tantri Abadi

Dokumen ini memodelkan alur kerja utama aplikasi **Karya Tantri Abadi** (scope simpan pinjam). Diagram memakai notasi flowchart Mermaid.

> Istilah: formal akademik = **simpanan**; UI/mitra = **tabungan**. Petugas lapangan = **offline**.

---

## Daftar Isi
1. Transaksi Tabungan
2. Pinjaman kelompok (input → approve → cair)
3. Cicilan (petugas offline → admin catat)
4. Login multi-panel

---

## 1. Activity Diagram: Transaksi Tabungan

Kasir mencatat tabungan anggota. Admin dapat memantau/edit. Anggota tidak mengelola tabungan di panel.

```mermaid
flowchart TD
    Start([Mulai]) --> Login[Kasir login /kasir]
    Login --> BukaMenu[Buka Daftar Tabungan]
    BukaMenu --> IsiForm[Pilih anggota, jenis tabungan, nominal, tanggal]
    IsiForm --> Validasi{Nominal valid?}
    Validasi -- Tidak --> Error[Tampilkan error] --> IsiForm
    Validasi -- Ya --> Simpan[Simpan savings_transactions]
    Simpan --> Cash[Ringkasan keuangan: Tabungan Anggota]
    Cash --> Cetak{Cetak kuitansi?}
    Cetak -- Ya --> PDF[Generate PDF]
    Cetak -- Tidak --> End([Selesai])
    PDF --> End
```

---

## 2. Activity Diagram: Pinjaman Kelompok

```mermaid
flowchart TD
    Offline[Petugas ajukan offline] --> Admin[Admin input pinjaman pending]
    Admin --> Calc[Sistem hitung fee 11/5/22 + cair 73%]
    Calc --> SPV{SPV setujui?}
    SPV -- Tolak --> Rejected[Status rejected]
    SPV -- Ya --> Approved[Status approved]
    Approved --> Kasir[Kasir cairkan]
    Kasir --> Jadwal[Generate loan_payments]
    Jadwal --> Active[Status disbursed/active]
    Active --> AnggotaLihat[Anggota lihat pinjaman sendiri]
```

---

## 3. Activity Diagram: Cicilan

```mermaid
flowchart TD
    Petugas[Petugas kumpulkan cicilan offline] --> Serah[Serahkan uang ke admin]
    Serah --> AdminBuka[Admin buka detail pinjaman]
    AdminBuka --> Catat[Admin Catat Bayar]
    Catat --> Update[Update paid_amount + status cicilan]
    Update --> Sisa[Hitung sisa hutang pinjaman]
    Sisa --> Lunas{Lunas semua?}
    Lunas -- Tidak --> Petugas
    Lunas -- Ya --> Completed[Status pinjaman completed]
```

Kasir/SPV dapat **melihat** jadwal cicilan, tetapi tidak mencatat pembayaran.

---

## 4. Activity Diagram: Login Multi-panel

```mermaid
flowchart TD
    Start([Buka /auth/login]) --> Isi[Isi email dan password]
    Isi --> Valid{Valid?}
    Valid -- Tidak --> Error[Pesan error] --> Isi
    Valid -- Ya --> Role{Role?}
    Role -- admin --> Admin[/admin]
    Role -- spv --> SPV[/spv]
    Role -- kasir --> Kasir[/kasir]
    Role -- anggota --> Anggota[/anggota]
    Role -- legacy/lain --> LoginBack[Kembali ke login / tanpa panel]
```

Petugas tidak memiliki role aktif untuk login.

---

## Catatan non-scope

Diagram POS retail dan SHU **tidak disertakan** karena modul tersebut dinonaktifkan pada implementasi mitra Karya Tantri Abadi.
