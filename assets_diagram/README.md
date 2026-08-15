# Diagram Draw.io — Karya Tantri Abadi

Folder: `assets_diagram/`

| # | Diagram | File Draw.io | Status |
|---|---------|--------------|--------|
| 1 | Use Case (UCD) | `use_case.drawio` | siap |
| 2 | Arsitektur multi-panel | `arsitektur_multi_panel.drawio` | siap |
| 3 | Activity Login | `activity_login.drawio` | siap (swimlane User\|System) |
| 4 | Activity Tabungan | `activity_tabungan.drawio` | siap |
| 5 | Activity Pinjaman | `activity_pinjaman.drawio` | siap |
| 6 | Activity Cicilan | `activity_cicilan.drawio` | siap |
| 7 | Sequence Pinjaman | `sequence_pinjaman.drawio` | siap |
| 8 | Sequence Tabungan | `sequence_tabungan.drawio` | siap |
| 9 | ERD logical | `erd_logical.drawio` | siap |
| 10 | Flowchart R&D 10 tahap | `flowchart_rd.drawio` | siap |

## Export PNG final (sudah fix — dari diagram.zip user)

| PNG (root `assets_diagram/`) | Diagram |
|---|---|
| `use_case.png` | Use Case |
| `arsitektur_multi_panel.png` | Arsitektur multi-panel |
| `activity_login.png` | Activity Login |
| `activity_tabungan.png` | Activity Tabungan |
| `activity_pinjaman.png` | Activity Pinjaman |
| `activity_cicilan.png` | Activity Cicilan |
| `sequence_pinjaman.png` | Sequence Pinjaman |
| `sequence_tabungan.png` | Sequence Tabungan |
| `erd_logical.png` | ERD logical |
| `flowchart_rd.png` | Flowchart R&D |

Salinan nama asli zip juga di `png/` (`ad_login.png`, `ad_pinjaman.png`, dll.).

## Cara pakai
1. **PNG final** → langsung `\includegraphics` di `Skripsi.tex`
2. Edit sumber: buka `.drawio` di https://app.diagrams.net → export ulang PNG

## File gabungan (opsional)
- `activity_diagrams_swimlane.drawio` — 4 activity dalam 1 file multi-page
- `activity_diagrams.drawio` — versi lama non-swimlane

## Catatan naskah
- Label formal: **simpanan**; UI: **Tabungan**
- Scope aktif: simpan pinjam (tanpa POS/SHU/CAPTCHA di naskah fitur aktif)
- Petugas lapangan = offline, tidak login
- Status cair pinjaman di diagram: **`disbursed`**
