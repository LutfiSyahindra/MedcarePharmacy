# Impor SALEP, KRIM DAN BEDAK — 17 Oktober 2026

Selesai dan diverifikasi pada **17 Oktober 2026, 19.34 WIB**. Dari 146 baris sumber, **127 produk ditambahkan** dengan kode **SKB-201–SKB-327**, sedangkan **19 produk dicocokkan dengan master yang sudah ada**. Jumlah seluruh master obat berubah dari **1.825 menjadi 1.952**.

**196 obat lama** dalam golongan SALEP, KRIM DAN BEDAK yang sebelumnya belum mempunyai main golongan sudah dilengkapi. KETOMED 2% SCALP LOT 60 ML (SBW-027), yang juga ada dalam sumber, dipindahkan dari golongan sampo ke golongan tujuan tanpa membuat duplikat atau mengganti kode obatnya.

Golongan tujuan kini memiliki **324 produk**, seluruhnya memiliki main golongan yang merupakan anak dari golongan tujuan:

| Main golongan | Kode rujukan | Produk |
| --- | --- | ---: |
| SALEP, KRIM DAN BEDAK KERAS | SLPK | 172 |
| SALEP, KRIM DAN BEDAK BEBAS TERBATAS | SLPBT | 50 |
| SALEP, KRIM DAN BEDAK BEBAS | SLPKB | 102 |
| **Total** | | **324** |

## Penyimpanan dan pencocokan

- [Sumber 146 produk](../database/data/salep_krim_bedak_2026_10_17.tsv) disalin dari lampiran pengguna; kode produk pemasok dan seluruh kolom asal dipertahankan dalam laporan audit.
- [Pemetaan](../database/data/salep_krim_bedak_2026_10_17.php) memakai rujukan main golongan yang sudah ada. Tidak membuat main golongan baru.
- Perbedaan CR/CRM/CREAM/KRIM, OINT/SALEP, GR/G/GRAM dan spasi dicocokkan. Padanan khusus yang ditinjau dicatat dalam `aliases`. Nama tanpa ukuran tidak otomatis disamakan dengan produk berukuran tertentu. NOROID DERMA RASH 60 ML dan 60 GR tetap merupakan dua baris sumber berbeda.
- Data lama hanya berubah pada main golongan yang semula kosong dan waktu pembaruannya. Satu perpindahan golongan produk sumber di atas juga dicatat. Nama, kode, komposisi, harga, satuan, pabrikan dan distributor lama dipertahankan.
- Produk baru memakai pabrikan dan distributor pertama dari sumber, dengan padanan nama ke master referensi. Distributor lainnya tetap tersedia dalam `raw_columns` laporan. Konversi satuan dasar 1 dibuat melalui layanan master obat; tidak mengasumsikan isi strip atau konversi kemasan tambahan.
- Satuan/sediaan baru dipetakan dari nama dan jenis kemasan. CREME pada bedak MARCKS adalah nama warna. Bentuk bedak padat, masker lembaran dan bentuk lain yang belum mempunyai padanan sediaan tidak diberi bentuk obat yang keliru.
- Kolom harga sumber tidak memiliki header atau penjelasan basis harga/satuan. Angka aslinya tersimpan dalam audit; `harga_beli` produk baru tetap 0. Tidak mengisi komposisi, indikasi, dosis, stok, batch atau kedaluwarsa berdasarkan perkiraan.

## Dasar klasifikasi

BEBAS untuk kosmetik, alat kesehatan, obat tradisional, obat kuasi dan produk perawatan nonresep merupakan **pengelompokan internal sesuai hierarki yang tersedia**, bukan penetapan izin edar sebagai obat bebas. BENOSON tablet dan CANESTEN vaginal tetap berada pada golongan induk yang diminta pengguna, dengan main KERAS.

Rujukan penyesuaian antara lain [Permenkes 28/2022](https://peraturan.bpk.go.id/Download/301568/Permenkes%20Nomor%2028%20Tahun%202022.pdf), [Permenkes 3/2021](https://jdih.kemkes.go.id/common/dokumen/2021permenkes003.pdf), serta [label CANESTEN PRO pada BPOM](https://registrasiobat.pom.go.id/files/assesment-reports/01762404039.pdf). Tautan pendukung tiap keluarga produk tercantum di berkas pemetaan dan audit.

Perbedaan varian dipertahankan, misalnya BENZOLAC biasa bebas terbatas sedangkan BENZOLAC-CL keras; CARMED krim keras sedangkan lotion merupakan produk konsumen. Salep Hitam Ichtyol Afiat 12GR dipetakan BEBAS internal berdasarkan [varian obat kuasi QD202786291](https://www.mandjur.co.id/products/salep-hitam-ichtyol-12-gr); koreksi pencocokan varian ini tercatat pada bagian `corrections` audit.

Nama lama INCHTIOL dan KALPANAX belum menyebutkan varian/NIE. Keduanya diberi main BEBAS TERBATAS berdasarkan rujukan produk yang tercatat dalam pemetaan; penanda kebutuhan verifikasi komposisi/varian pada master tetap dipertahankan.

## Verifikasi

[Laporan audit](../storage/app/imports/salep-krim-bedak-20261017-122903-570631.json) menyimpan rencana, data sebelum perubahan, hasil penyimpanan, sumber dan koreksi varian. [Verifikasi akhir](../storage/app/imports/salep-krim-bedak-20261017-122903-570631-final-verification.json) berstatus `passed` tanpa selisih:

- Seluruh 146 baris sumber tercakup sebagai 146 identitas master berbeda.
- Seluruh 324 anggota golongan memiliki main yang sesuai pemetaan dan induknya.
- Seluruh 1.825 obat lama sesuai data sebelum impor kecuali perubahan klasifikasi yang dinyatakan.
- Seluruh 1.596 konversi lama tetap sama; 127 produk baru memiliki konversi dasar yang sesuai satuan utamanya.
- Pratinjau ulang: 0 produk baru, 146 sudah ada, 0 main kosong, 0 perpindahan golongan.
- Pengujian: **5 tes, 50 assertion lulus**, mencakup pratinjau tanpa perubahan, padanan nama, pemisahan ukuran, pengulangan tanpa duplikasi, produk nonresep/kuasi, perpindahan sumber dari golongan lain, perlindungan data lama dan pembatalan transaksi saat gagal.

```powershell
php artisan medcare:import-salep-krim-bedak
php artisan medcare:import-salep-krim-bedak --apply
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit tests/Feature/ImportSalepKrimBedakTest.php
```

Pengulangan impor menjaga produk dan main golongan yang sudah terisi. Produk tanpa pemetaan, nama ambigu atau hubungan sub golongan yang bertentangan akan menghentikan proses sebelum penyimpanan. Impor berjalan dalam satu transaksi; audit disiapkan sebelum transaksi.
