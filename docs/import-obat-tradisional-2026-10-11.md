# Impor master obat - 11 Oktober 2026

## Hasil penyimpanan

Impor selesai pada 11 Oktober 2026 pukul 19.41 WIB: **95 produk ditambahkan**, tidak ada yang dilewati. Kode obat **OTL-001 sampai OTL-095**. Jumlah master obat meningkat dari **1.653 menjadi 1.748**.

Pemeriksaan ulang database memastikan seluruh nama, golongan, kategori, satuan, kandungan dan indikasi sesuai data impor; seluruh 95 produk memiliki konversi satuan utama. Kandungan terisi pada 84 produk, sedangkan 11 produk tetap kosong karena belum terverifikasi. Dari 84 kandungan yang terisi, 29 berstatus sebagian dan memerlukan pencocokan label untuk kelengkapan formula/varian.

Laporan audit: [obat-tradisional-20261011-124042-828265.json](../storage/app/imports/obat-tradisional-20261011-124042-828265.json). Pengujian impor lulus: 3 pengujian, 36 assertion, termasuk pratinjau tanpa perubahan, pengulangan tanpa duplikasi, dan perlindungan data obat yang sudah ada.

## Aturan data

95 produk dari daftar pengguna. Semua memakai golongan **OBAT TRADISIONAL** sesuai arahan pengguna. Golongan ini merupakan pengelompokan internal, bukan verifikasi kelas izin edar BPOM. Kategori dipetakan ke kategori Medcare berdasarkan fungsi yang ditemukan pada sumber.

Nama dan satuan sumber dipertahankan. Harga, stok, dosis, NIE, batch dan kedaluwarsa tidak dibuat berdasarkan perkiraan. `OTC` pada kolom distributor diabaikan. Karena master obat hanya memiliki satu distributor, distributor pertama digunakan; seluruh nama distributor tersimpan pada laporan JSON impor.

## Verifikasi kandungan

- Kuantitatif: bahan utama beserta jumlah dan basis per kapsul/tablet/sachet atau volume disebutkan sumber.
- Kualitatif: bahan utama ditemukan, tanpa mengasumsikan kadar maupun seluruh eksipien.
- Sebagian: ada bahan utama yang ditemukan, tetapi varian, kadar, kemasan atau formula lengkap masih perlu label.
- Belum terverifikasi: komposisi dikosongkan (`NULL`); tidak disalin dari varian lain.

| Status | Produk |
| --- | ---: |
| belum_terverifikasi | 11 |
| kualitatif | 38 |
| sebagian | 29 |
| kuantitatif | 17 |

## Produk yang perlu verifikasi kandungan

| Produk | Catatan |
| --- | --- |
| MADU HIJAU | Nama tanpa merek/pabrikan/NIE; beberapa produk memakai nama serupa. |
| EM KAPSUL | Produk botol dan strip memiliki NIE/formula berbeda; nama sumber belum menjelaskan varian. |
| KOMIX HERBAL KIDZ 10ML @4 | Varian Kidz 10 ml belum ditemukan; tidak disalin dari formula dewasa atau Komix Kid nonherbal. |
| SIDO M TOLAK ANGIN PERMEN SACH@15 | Formula permen tidak disamakan dengan Tolak Angin cair. |
| SIDO M TOLAK LINU MINT 15ML SACH @5 | Formula varian Mint belum terkonfirmasi; tidak disalin dari Tolak Linu Herbal. |
| OBAT SAKIT GIGI KAKAK TUA | Pabrikan/NIE tidak disertakan; jangan mengasumsikan formula kimia atau herbal. |
| SARI KURMA MADU ANGKAK 470GR | Merek/pabrikan/NIE belum diketahui; nama bahan pada nama dagang bukan bukti formula. |
| SYAMIL FAMILY DATES HONEY 200ML | Formula tepat belum ditemukan pada sumber tepercaya. |
| SARI KURMA ALJAZIRA | Terdapat varian original/premium; merek saja belum menentukan formula. |
| NEPHROLITE | Nama tidak memastikan Nephrolit tablet/kapsul; pabrikan/NIE diperlukan. |
| KEIJIBELING | Kejibeling diproduksi beberapa pabrikan dengan formula berbeda; pabrikan/NIE diperlukan. |

## Pemetaan seluruh produk

| Nama sumber | Kategori | Satuan | Status | Catatan |
| --- | --- | --- | --- | --- |
| MADU HIJAU | HBL | BOTOL | belum_terverifikasi | Nama tanpa merek/pabrikan/NIE; beberapa produk memakai nama serupa. |
| MADU TJ KURMA 150GR | NTR | BOTOL | kualitatif |  |
| MADU TJ KURMA 250GR | NTR | BOTOL | kualitatif |  |
| MADU TJ LEMON 150GR | NTR | BOTOL | kualitatif |  |
| MADU TJ MURNI 150GR | NTR | BOTOL | kualitatif |  |
| MADU TJ MURNI 250GR | NTR | BOTOL | kualitatif |  |
| MADU TJ MURNI 500GR | NTR | BOTOL | kualitatif |  |
| MADU TJ PANAS DALAM 150GR | ORL | BOTOL | kualitatif |  |
| MADU TJ SACHET JERUK 20GR | NTR | SACHET | sebagian | Produsen memublikasikan kandungan utama; kadar dan eksipien setiap rasa perlu label. |
| MADU TJ SACHET JERUK 20GR@12 | NTR | SACHET | sebagian | Produsen memublikasikan kandungan utama; kadar dan eksipien setiap rasa perlu label. |
| MADU TJ SACHET ORIGINAL 20GR@12 | NTR | SACHET | sebagian | Produsen memublikasikan kandungan utama; kadar dan eksipien setiap rasa perlu label. |
| MADU TJ SACHET STRAWBERRY 20GR | NTR | SACHET | sebagian | Produsen memublikasikan kandungan utama; kadar dan eksipien setiap rasa perlu label. |
| MADU TJ SUPER 150GR | NTR | BOTOL | sebagian | Referensi lama menyebut Super; katalog produsen terkini memakai nama Extra. Cocokkan label varian Super. |
| MADU TJ SUPER 250GR | NTR | BOTOL | sebagian | Referensi lama menyebut Super; katalog produsen terkini memakai nama Extra. Cocokkan label varian Super. |
| MADU TJ SUPER 500GR | NTR | BOTOL | sebagian | Referensi lama menyebut Super; katalog produsen terkini memakai nama Extra. Cocokkan label varian Super. |
| MADURASA BOTOL MURNI 160GR | NTR | BOTOL | kualitatif |  |
| MADURASA BOTOL MURNI 280GR | NTR | BOTOL | kualitatif |  |
| MADURASA BOTOL MURNI 910 GR | NTR | BOTOL | kualitatif |  |
| MADURASA BOTOL PREMIUM 160GR | NTR | BOTOL | kualitatif |  |
| MADURASA BOTOL PREMIUM 280GR | NTR | BOTOL | kualitatif |  |
| MADURASA BOTOL PREMIUM 910GR | NTR | BOTOL | kualitatif |  |
| VCO AL AFIAT 60ML | SUP | BOTOL | kualitatif |  |
| ZYMUNO SYR 200ML | SUP | BOTOL | kualitatif |  |
| FRESHMAG SYR 200ML | MGH | BOTOL | sebagian | Sumber produsen menyebut bahan utama tanpa formula kuantitatif; cocokkan kemasan 200 ml. |
| MINYAK ZAITUN AL AFIAT 60ML | SUP | BOTOL | sebagian | Produk produsen saat ini 130 ml; kemasan 60 ml dan tingkat kemurnian perlu dicocokkan dengan label. |
| EM KAPSUL | KBS | BOX | belum_terverifikasi | Produk botol dan strip memiliki NIE/formula berbeda; nama sumber belum menjelaskan varian. |
| TUNTAS PIL | KBS | BOX | kualitatif |  |
| AMBEVEN KAPSULS | PNG | STRIP | sebagian | Produsen menyebut daun wungu dan bahan tanaman lain; daftar/kadar lengkap perlu label. |
| KOMIX HERBAL KIDZ 10ML @4 | ABT-BTK | SACHET | belum_terverifikasi | Varian Kidz 10 ml belum ditemukan; tidak disalin dari formula dewasa atau Komix Kid nonherbal. |
| KOMIX HERBAL ORIGINAL 15ML@4 | ABT-BTK | SACHET | kuantitatif | Formula dasar herbal dewasa; perisa dan eksipien tiap rasa tidak dirinci. |
| KOMIX HERBAL LEMON 15ML@4 | ABT-BTK | SACHET | kuantitatif | Formula dasar herbal dewasa; perisa dan eksipien tiap rasa tidak dirinci. |
| KOMIX HERBAL ORIGINAL 15ML SACH@6 | ABT-BTK | SACHET | kuantitatif | Formula dasar herbal dewasa; perisa dan eksipien tiap rasa tidak dirinci. |
| KOMIX HERBAL JERUK NIPIS 15ML SACH@6 | ABT-BTK | SACHET | kuantitatif | Formula dasar herbal dewasa; perisa dan eksipien tiap rasa tidak dirinci. |
| KOMIX HERBAL PAPERMINT 15ML SACH@6 | ABT-BTK | SACHET | kuantitatif | Formula dasar herbal dewasa; perisa dan eksipien tiap rasa tidak dirinci. |
| PSIDII CAP@50 | HMT | CAP | kuantitatif |  |
| PSIDII SYR 60ML | HMT | BOTOL | kuantitatif |  |
| SIDO M TOLAK ANGIN + MADU 15ML SACH@12 | HBL | SACHET | sebagian | Produsen menyebut campuran 12 herbal, tetapi halaman hanya merinci sebagian bahan. |
| SIDO M TOLAK ANGIN CAIR ANAK SACH@12 | HBL | SACHET | kualitatif |  |
| SIDO M TOLAK ANGIN CAIR SACH@5 | HBL | SACHET | sebagian | Produsen menyebut campuran 12 herbal, tetapi halaman hanya merinci sebagian bahan. |
| SIDO M TOLAK ANGIN CAIR SUGAR FREE SACH@5 | HBL | SACHET | kualitatif |  |
| SIDO M TOLAK ANGIN FLU CAIR SACH@12 | AFL | SACHET | kualitatif |  |
| SIDO M TOLAK ANGIN FLU CAIR SACH@5 | AFL | SACHET | kualitatif |  |
| SIDO M TOLAK ANGIN PERMEN SACH@15 | NTR | SACHET | belum_terverifikasi | Formula permen tidak disamakan dengan Tolak Angin cair. |
| SIDO M TOLAK LINU HERBAL 15ML SACH@5 | ORM | SACHET | kualitatif |  |
| SIDO M TOLAK LINU MINT 15ML SACH @5 | ORM | SACHET | belum_terverifikasi | Formula varian Mint belum terkonfirmasi; tidak disalin dari Tolak Linu Herbal. |
| SIDO M VIT C 1000 LEMON SACH@6 | VIT | SACHET | kuantitatif |  |
| SIDO M VIT C 1000 ANGGUR SACH@6 | VIT | SACHET | kuantitatif |  |
| SIDO M VIT C 1000 ORANGE SACH@6 | VIT | SACHET | kuantitatif |  |
| SIDO M VIT C 1000 COLLAGEN TRAWBERRY SACH@6 | VIT | SACHET | sebagian | Sumber resmi menyebut tambahan vitamin D3, B3, B6 dan B12 tanpa kadar; cocokkan label varian strawberry. |
| INBUMIN TAB@30 | SUP | STRIP | kuantitatif |  |
| VIP ALBUMIN 500MG CAP@30 | SUP | STRIP | kuantitatif |  |
| VIPALBUMIN PLUS SACH@3 | SUP | SACHET | kuantitatif |  |
| VERMINT CAP@12 | ANL | BOTOL | sebagian | Varian biasa berbeda dari Forte; kadar dan pabrikan harus dicocokkan dengan label Kico yang disuplai. |
| VERMINT CAP@30 | ANL | BOTOL | sebagian | Varian biasa berbeda dari Forte; kadar dan pabrikan harus dicocokkan dengan label Kico yang disuplai. |
| ANTANGIN GINGERMINT 15 ML SACH@12 | HBL | SACHET | kualitatif |  |
| ANTANGIN HABBATUSSAUDA SACH@12 | HBL | SACHET | kualitatif |  |
| ANTANGIN JRG CAIR 15ML SACH@5 | HBL | SACHET | sebagian | Daftar utama pada halaman produsen; formula lengkap dan madu perlu dicocokkan pada label. |
| ANTANGIN JRG CAIR 15ML SACH@12 | HBL | SACHET | sebagian | Daftar utama pada halaman produsen; formula lengkap dan madu perlu dicocokkan pada label. |
| ANTANGIN GOOD NIGHT @20 X 4 TAB | PSD | STRIP | kualitatif |  |
| ANTANGIN JRG TAB@80 | HBL | STRIP | kualitatif |  |
| ANTANGIN JUNIOR SACHET@5 | HBL | SACHET | kualitatif |  |
| ANTANGIN PERMEN ASSORTED@50 | NTR | SACHET | sebagian | Basis Antangin Candy dari produsen; eksipien/perisa masing-masing rasa tidak dirinci. |
| ANTANGIN PERMEN HONEY MINT@5 | NTR | SACHET | sebagian | Basis Antangin Candy dari produsen; eksipien/perisa masing-masing rasa tidak dirinci. |
| LELAP CAPS @100CAPS | PSD | SACHET | kualitatif | Produsen menyebut kaplet; nama CAPS dan satuan sachet dari sumber perlu diperiksa terhadap kemasan. |
| LELAP KPL@12 | PSD | SACHET | kualitatif | Produsen menyebut kaplet; nama CAPS dan satuan sachet dari sumber perlu diperiksa terhadap kemasan. |
| OBAT SAKIT GIGI KAKAK TUA | ORL | BOTOL | belum_terverifikasi | Pabrikan/NIE tidak disertakan; jangan mengasumsikan formula kimia atau herbal. |
| LOSOSA GARAM | NTR | BOTOL | sebagian |  |
| LAXING CAP @10 | KST | STRIP | kuantitatif |  |
| DIAPET ANAK SACH @6 | DRE | SACHET | kualitatif |  |
| DIAPET CAP BLISTER @10 X 12 CAP | DRE | BLISTER | kualitatif |  |
| DIAPET NR CAP@100 | DRE | BLISTER | kualitatif |  |
| KULDON SARIAWAN TAB@10 SACH | ORL | SACHET | kualitatif |  |
| SARI KURMA MADU ANGKAK 470GR | NTR | BOTOL | belum_terverifikasi | Merek/pabrikan/NIE belum diketahui; nama bahan pada nama dagang bukan bukti formula. |
| PUREKIDS AISE BELLY 60ML | PNG | BOTOL | kualitatif |  |
| CURCUMA FORCE @10 X 12 TAB | SUP | BLISTER | kuantitatif |  |
| SYAMIL FAMILY DATES HONEY 200ML | NTR | BOTOL | belum_terverifikasi | Formula tepat belum ditemukan pada sumber tepercaya. |
| ASI BOOSTER MOM UUNG | PRB | BLISTER | kualitatif |  |
| KAPSIDA HS | KLT | BLISTER | sebagian | Halaman menampilkan bahan utama tanpa kadar; tidak disamakan dengan Kapsida biasa. |
| KAPSIDA | KLT | BOTOL | kualitatif |  |
| SARI KURMA ALJAZIRA | NTR | BOTOL | belum_terverifikasi | Terdapat varian original/premium; merek saja belum menentukan formula. |
| SARI KURMA SAHARA JAMBU BIJI MERAH | NTR | BOTOL | sebagian | Bahan tambahan dan kadar belum terverifikasi. |
| SARI KURMA SAHARA ORI | NTR | BOTOL | sebagian | Gula tambahan dan proporsi perlu dicocokkan dengan label. |
| ASIFIT TAB SALUT@3 STR | PRB | BLISTER | kuantitatif |  |
| HABBATUSSAUDA KURMA AJWA@100 CAPS | SUP | BOTOL | kualitatif | Kurma Ajwa merupakan merek; tidak ditambahkan buah kurma ke komposisi. |
| ESEMAG | MGH | BOTOL | kualitatif | Sumber produsen memakai sachet; satuan botol dari daftar dipertahankan dan perlu label. |
| PROSTAKUR 160MG CAP@30 | GIN | STRIP | kuantitatif |  |
| NEPHROLITE | GIN | STRIP | belum_terverifikasi | Nama tidak memastikan Nephrolit tablet/kapsul; pabrikan/NIE diperlukan. |
| KEIJIBELING | GIN | STRIP | belum_terverifikasi | Kejibeling diproduksi beberapa pabrikan dengan formula berbeda; pabrikan/NIE diperlukan. |
| THERMOLYTE PLUS KPL@10 | SUP | BOX | sebagian | Jenis asam amino/herbal serta kadar tidak dipublikasikan; varian formula perlu label. |
| THERMOLYTE PLUS KPL@30 | SUP | BOX | sebagian | Jenis asam amino/herbal serta kadar tidak dipublikasikan; varian formula perlu label. |
| THERMOLYTE PLUS KPL@60 | SUP | BOX | sebagian | Jenis asam amino/herbal serta kadar tidak dipublikasikan; varian formula perlu label. |
| GIZIDAT SYR 60ML | SUP | BOTOL | kualitatif | Dokumen formulasi BPOM mencakup 60 ml; satuan madu dalam tabel tidak konsisten, sehingga kadar tidak disalin. |
| GABUMIN @30 | SUP | BOTOL | sebagian | Bahan utama dikonfirmasi; kadar lengkap masih perlu label. |
| ALBUSMIN CAP@30 | SUP | BOTOL | sebagian | Referensi 480 mg memakai pabrikan Prima Medika dan kemasan blister; sumber pengguna Pharos/botol. Kadar tidak diasumsikan. |
| VERMINT KAPSUL @12 | ANL | STRIP | sebagian | Varian biasa berbeda dari Forte; kadar dan pabrikan harus dicocokkan dengan label Kico yang disuplai. |

## Sumber

- [https://ultrasakti.com/madu-tj](https://ultrasakti.com/madu-tj)
- [https://en.madutj.co.id/](https://en.madutj.co.id/)
- [https://www.tianlustiana.com/2019/08/review-madu-tj-super-madu-asli.html?showComment=1566960144009](https://www.tianlustiana.com/2019/08/review-madu-tj-super-madu-asli.html?showComment=1566960144009)
- [https://www.madurasa.co.id/id/honey-faq/category/madurasa/](https://www.madurasa.co.id/id/honey-faq/category/madurasa/)
- [https://www.klikdokter.com/obat/obat-herbal/vco-al-afiat](https://www.klikdokter.com/obat/obat-herbal/vco-al-afiat)
- [https://zymuno.com/](https://zymuno.com/)
- [https://ethos.co.id/id/berita/freshmag-kembali-raih-top-brand-award-2026-kategori-madu-untuk-mengatasi-lambung/](https://ethos.co.id/id/berita/freshmag-kembali-raih-top-brand-award-2026-kategori-madu-untuk-mengatasi-lambung/)
- [https://alafiat.co.id/](https://alafiat.co.id/)
- [https://www.borobudurherbal.com/product-category/untuk-wanita/melancarkan-menstruasi/](https://www.borobudurherbal.com/product-category/untuk-wanita/melancarkan-menstruasi/)
- [https://www.deltopedia.deltomed.com/info-produk.html](https://www.deltopedia.deltomed.com/info-produk.html)
- [https://www.ambeven.com/id](https://www.ambeven.com/id)
- [https://bintang7.com/products?category=Upper+Respiratory](https://bintang7.com/products?category=Upper+Respiratory)
- [https://www.alodokter.com/komix](https://www.alodokter.com/komix)
- [https://www.dexagroup.com/id/produk/psidii-kapsul-id/](https://www.dexagroup.com/id/produk/psidii-kapsul-id/)
- [https://www.dexagroup.com/id/produk/psidii-sirup-id/](https://www.dexagroup.com/id/produk/psidii-sirup-id/)
- [https://www.sidomuncul.co.id/id/product/tolak_angin.html](https://www.sidomuncul.co.id/id/product/tolak_angin.html)
- [https://www.sidomuncul.co.id/id/product/tolak_angin_anak.html](https://www.sidomuncul.co.id/id/product/tolak_angin_anak.html)
- [https://www.sidomuncul.co.id/product/tolak_angin_sugar_free.html](https://www.sidomuncul.co.id/product/tolak_angin_sugar_free.html)
- [https://www.sidomuncul.co.id/id/product/tolak_angin_flu.html](https://www.sidomuncul.co.id/id/product/tolak_angin_flu.html)
- [https://www.sidomuncul.co.id/id/product/../product_catalog.html](https://www.sidomuncul.co.id/id/product/../product_catalog.html)
- [https://www.sidomuncul.co.id/id/product/tolak_linu.html](https://www.sidomuncul.co.id/id/product/tolak_linu.html)
- [https://www.sidomuncul.co.id/id/product/sido_muncul_c1000.html](https://www.sidomuncul.co.id/id/product/sido_muncul_c1000.html)
- [https://www.sidomuncul.co.id/id/product/sido_muncul_c1000_sweet_orange.html](https://www.sidomuncul.co.id/id/product/sido_muncul_c1000_sweet_orange.html)
- [https://www.sidomuncul.co.id/id/product/sido_muncul_c1000_anggur_muscat.html](https://www.sidomuncul.co.id/id/product/sido_muncul_c1000_anggur_muscat.html)
- [https://www.sidomunculstore.com/blog/post/collagen-untuk-apa.html](https://www.sidomunculstore.com/blog/post/collagen-untuk-apa.html)
- [https://www.dexagroup.com/id/produk/inbumin-tablet-id/](https://www.dexagroup.com/id/produk/inbumin-tablet-id/)
- [https://id.royalmedicalinkpharmalab.com/vipalbumin/](https://id.royalmedicalinkpharmalab.com/vipalbumin/)
- [https://id.royalmedicalinkpharmalab.com/vipalbumin-plus/](https://id.royalmedicalinkpharmalab.com/vipalbumin-plus/)
- [https://www.halodoc.com/obat-dan-vitamin/vermint-30-kapsul](https://www.halodoc.com/obat-dan-vitamin/vermint-30-kapsul)
- [https://www.k24klik.com/p/vermint-cap-12s-1115](https://www.k24klik.com/p/vermint-cap-12s-1115)
- [https://www.sohoglobalhealth.com/id/brands/7](https://www.sohoglobalhealth.com/id/brands/7)
- [https://www.alodokter.com/garam-lososa-garam-rendah-sodium-yang-lebih-sehat-bagi-tubuh](https://www.alodokter.com/garam-lososa-garam-rendah-sodium-yang-lebih-sehat-bagi-tubuh)
- [https://www.halodoc.com/obat-dan-vitamin/laxing-10-kapsul](https://www.halodoc.com/obat-dan-vitamin/laxing-10-kapsul)
- [https://laxing.id/obat-sembelit-alami-dan-12-tips-mencegah-sembelit/](https://laxing.id/obat-sembelit-alami-dan-12-tips-mencegah-sembelit/)
- [https://www.sohoglobalhealth.com/id/brands/5](https://www.sohoglobalhealth.com/id/brands/5)
- [https://novellpharm.com/subcategory/67-6/PUREKIDS.html](https://novellpharm.com/subcategory/67-6/PUREKIDS.html)
- [https://www.sohoglobalhealth.com/id/brands/24](https://www.sohoglobalhealth.com/id/brands/24)
- [https://momuung.com/asi-booster-mom-uung-untuk-busui](https://momuung.com/asi-booster-mom-uung-untuk-busui)
- [https://kembangbulan.com/product/kapsida-hs/](https://kembangbulan.com/product/kapsida-hs/)
- [https://kembangbulan.com/product/kapsida/](https://kembangbulan.com/product/kapsida/)
- [https://www.k24klik.com/p/SAHARA-SARI-KURMA-PLUS-JAMBU-BIJI-MERAH-330G-3279](https://www.k24klik.com/p/SAHARA-SARI-KURMA-PLUS-JAMBU-BIJI-MERAH-330G-3279)
- [https://www.k24klik.com/p/SAHARA-SARI-KURMA-330G-2322](https://www.k24klik.com/p/SAHARA-SARI-KURMA-330G-2322)
- [https://www.halodoc.com/obat-dan-vitamin/asifit-30-kaplet](https://www.halodoc.com/obat-dan-vitamin/asifit-30-kaplet)
- [https://www.halodoc.com/obat-dan-vitamin/habbatussauda-kurma-ajwa-100-kapsul](https://www.halodoc.com/obat-dan-vitamin/habbatussauda-kurma-ajwa-100-kapsul)
- [https://www.sidomuncul.co.id/id/product/esemag.html](https://www.sidomuncul.co.id/id/product/esemag.html)
- [https://medikon.id/en/products-detail/prostakur](https://medikon.id/en/products-detail/prostakur)
- [https://www.pharos.co.id/thermolyte-plus-10-caplet](https://www.pharos.co.id/thermolyte-plus-10-caplet)
- [https://asrot.pom.go.id/asrot/index.php/download/data/ZG9jcy9yZWcvMjAyMy8xMC8zNzgxLzM3ODEtNTUtMjAyMzEwMTcxMDA5MzgucGRm/01_Formulasi%20dan%20Komposisi%20Produk%20Per%20Batch.pdf](https://asrot.pom.go.id/asrot/index.php/download/data/ZG9jcy9yZWcvMjAyMy8xMC8zNzgxLzM3ODEtNTUtMjAyMzEwMTcxMDA5MzgucGRm/01_Formulasi%20dan%20Komposisi%20Produk%20Per%20Batch.pdf)
- [https://www.blibli.com/p/gabumin-30-kapsul/ps--APA-70640-00887](https://www.blibli.com/p/gabumin-30-kapsul/ps--APA-70640-00887)
- [https://www.halodoc.com/obat-dan-vitamin/albusmin-30-kapsul](https://www.halodoc.com/obat-dan-vitamin/albusmin-30-kapsul)
- [https://www.pharos.co.id/produk?filter_brand=albusmin](https://www.pharos.co.id/produk?filter_brand=albusmin)

## Mengulang impor

```powershell
php artisan medcare:import-obat-tradisional
php artisan medcare:import-obat-tradisional --apply
```

Pratinjau tidak mengubah database. Penyimpanan dijalankan dalam satu transaksi, memakai kode OTL otomatis dan membuat konversi satuan dasar 1. Nama yang sudah ada dilewati tanpa menimpa harga, komposisi atau kategori. Laporan pelaksanaan berada di `storage/app/imports/obat-tradisional-*.json`.
