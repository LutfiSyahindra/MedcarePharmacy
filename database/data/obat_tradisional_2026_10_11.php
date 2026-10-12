<?php

// Kandungan utama yang dipublikasikan, bukan salinan lengkap seluruh eksipien label.
// Kategori adalah pemetaan fungsi ke kategori Medcare; golongan mengikuti arahan pengguna.
$tj = 'https://ultrasakti.com/madu-tj';
$deltomed = 'https://www.deltopedia.deltomed.com/info-produk.html';
$sido = 'https://www.sidomuncul.co.id/id/product/';
$soho = 'https://www.sohoglobalhealth.com/id/brands/';
$profiles = [];
$add = function (string $pattern, string $category, ?string $composition, ?string $indication, ?string $form, array $sources, string $status = 'kualitatif', ?string $manufacturer = null, ?string $note = null) use (&$profiles): void {
    $profiles[] = compact('pattern', 'category', 'composition', 'indication', 'form', 'sources', 'status', 'manufacturer', 'note');
};

$add('/^MADU HIJAU$/', 'HBL', null, null, 'SED-063', [], 'belum_terverifikasi', null, 'Nama tanpa merek/pabrikan/NIE; beberapa produk memakai nama serupa.');
$add('/^MADU TJ KURMA /', 'NTR', 'Kandungan utama: madu dan sari kurma.', 'Pelengkap asupan nutrisi.', 'SED-063', [$tj]);
$add('/^MADU TJ LEMON /', 'NTR', 'Kandungan utama: madu, ekstrak dan bulir lemon serta jeruk mandarin.', 'Pelengkap asupan nutrisi.', 'SED-063', [$tj]);
$add('/^MADU TJ MURNI /', 'NTR', 'Madu murni 100%.', 'Pelengkap asupan nutrisi.', 'SED-063', ['https://en.madutj.co.id/']);
$add('/^MADU TJ PANAS DALAM /', 'ORL', 'Kandungan utama: madu, Chrysanthemum, Pelargonium dan Thymus.', 'Membantu meredakan sariawan dan tenggorokan kering.', 'SED-063', [$tj]);
$add('/^MADU TJ SACHET /', 'NTR', 'Kandungan utama: madu dan royal jelly; perisa sesuai varian.', 'Pelengkap asupan nutrisi.', 'SED-063', [$tj], 'sebagian', null, 'Produsen memublikasikan kandungan utama; kadar dan eksipien setiap rasa perlu label.');
$add('/^MADU TJ SUPER /', 'NTR', 'Kandungan utama: madu, royal jelly dan bee pollen.', 'Pelengkap asupan nutrisi.', 'SED-063', ['https://www.tianlustiana.com/2019/08/review-madu-tj-super-madu-asli.html?showComment=1566960144009', $tj], 'sebagian', null, 'Referensi lama menyebut Super; katalog produsen terkini memakai nama Extra. Cocokkan label varian Super.');
$add('/^MADURASA BOTOL MURNI /', 'NTR', 'Madu randu, madu hutan dan madu kelengkeng.', 'Pelengkap asupan nutrisi.', 'SED-063', ['https://www.madurasa.co.id/id/honey-faq/category/madurasa/']);
$add('/^MADURASA BOTOL PREMIUM /', 'NTR', 'Kandungan utama: madu, royal jelly dan bee pollen.', 'Pelengkap asupan nutrisi.', 'SED-063', ['https://www.madurasa.co.id/id/honey-faq/category/madurasa/']);
$add('/^VCO AL AFIAT /', 'SUP', 'Minyak kelapa murni (virgin coconut oil).', 'Membantu memelihara kesehatan.', 'SED-027', ['https://www.klikdokter.com/obat/obat-herbal/vco-al-afiat'], 'kualitatif');
$add('/^ZYMUNO /', 'SUP', 'Kandungan utama: meniran (Phyllanthus niruri), temulawak (Curcuma xanthorrhiza), daun kelor (Moringa oleifera) dan madu.', 'Membantu memelihara daya tahan tubuh.', 'SED-022', ['https://zymuno.com/']);
$add('/^FRESHMAG /', 'MGH', 'Kandungan utama: madu dan ekstrak kunyit.', 'Membantu meringankan gangguan lambung.', 'SED-022', ['https://ethos.co.id/id/berita/freshmag-kembali-raih-top-brand-award-2026-kategori-madu-untuk-mengatasi-lambung/'], 'sebagian', null, 'Sumber produsen menyebut bahan utama tanpa formula kuantitatif; cocokkan kemasan 200 ml.');
$add('/^MINYAK ZAITUN AL AFIAT /', 'SUP', 'Minyak zaitun (Olea europaea).', 'Membantu memelihara kesehatan.', 'SED-027', ['https://alafiat.co.id/'], 'sebagian', null, 'Produk produsen saat ini 130 ml; kemasan 60 ml dan tingkat kemurnian perlu dicocokkan dengan label.');
$add('/^EM KAPSUL$/', 'KBS', null, 'Membantu melancarkan haid.', 'SED-014', ['https://www.borobudurherbal.com/product-category/untuk-wanita/melancarkan-menstruasi/'], 'belum_terverifikasi', 'PT Industri Jamu dan Farmasi Borobudur', 'Produk botol dan strip memiliki NIE/formula berbeda; nama sumber belum menjelaskan varian.');
$add('/^TUNTAS PIL$/', 'KBS', 'Kandungan utama: jahe merah, jintan hitam, daun sembung, daun seribu dan kunyit.', 'Membantu melancarkan haid dan meredakan nyeri haid.', 'OTL-PIL', [$deltomed]);
$add('/^AMBEVEN /', 'PNG', 'Kandungan utama terverifikasi: ekstrak daun wungu (Graptophyllum pictum).', 'Membantu meredakan gejala wasir.', 'SED-014', ['https://www.ambeven.com/id'], 'sebagian', null, 'Produsen menyebut daun wungu dan bahan tanaman lain; daftar/kadar lengkap perlu label.');
$add('/^KOMIX HERBAL KIDZ /', 'ABT-BTK', null, null, 'SED-022', ['https://bintang7.com/products?category=Upper+Respiratory'], 'belum_terverifikasi', null, 'Varian Kidz 10 ml belum ditemukan; tidak disalin dari formula dewasa atau Komix Kid nonherbal.');
$add('/^KOMIX HERBAL (ORIGINAL|LEMON|JERUK NIPIS|PAPERMINT) /', 'ABT-BTK', 'Per 15 ml: ekstrak lagundi 200 mg, jahe merah 30 mg, timi 100 mg, akar manis 167 mg, minyak peppermint 11 mg dan madu 3000 mg.', 'Membantu meredakan batuk dan melegakan tenggorokan.', 'SED-022', ['https://www.alodokter.com/komix'], 'kuantitatif', null, 'Formula dasar herbal dewasa; perisa dan eksipien tiap rasa tidak dirinci.');
$add('/^PSIDII CAP/', 'HMT', 'Per kapsul: ekstrak daun jambu biji (Psidii folium) 500 mg.', 'Membantu meningkatkan jumlah trombosit darah.', 'SED-014', ['https://www.dexagroup.com/id/produk/psidii-kapsul-id/'], 'kuantitatif');
$add('/^PSIDII SYR /', 'HMT', 'Per 5 ml: ekstrak daun jambu biji (Psidium guajava folium) 250 mg.', 'Membantu meningkatkan jumlah trombosit darah.', 'SED-022', ['https://www.dexagroup.com/id/produk/psidii-sirup-id/'], 'kuantitatif');
$add('/^SIDO M TOLAK ANGIN (\+ MADU|CAIR SACH)/', 'HBL', 'Kandungan utama yang dipublikasikan: jahe, daun mint, adas, kayu ules dan madu; campuran ekstrak herbal 5,67 g per sachet.', 'Membantu meredakan gejala masuk angin.', 'SED-027', [$sido.'tolak_angin.html'], 'sebagian', null, 'Produsen menyebut campuran 12 herbal, tetapi halaman hanya merinci sebagian bahan.');
$add('/^SIDO M TOLAK ANGIN CAIR ANAK /', 'HBL', 'Kandungan utama: adas, kayu ules, daun cengkeh, jahe, daun mint, madu dan Bubali cornu.', 'Membantu meredakan gejala masuk angin pada anak.', 'SED-027', [$sido.'tolak_angin_anak.html']);
$add('/^SIDO M TOLAK ANGIN CAIR SUGAR FREE /', 'HBL', 'Kandungan utama: adas, kayu ules, daun cengkeh, jahe, royal jelly dan sukralosa.', 'Membantu meredakan gejala masuk angin.', 'SED-027', ['https://www.sidomuncul.co.id/product/tolak_angin_sugar_free.html']);
$add('/^SIDO M TOLAK ANGIN FLU /', 'AFL', 'Kandungan utama: adas, kayu ules, daun cengkeh, jahe, daun mint, Echinacea, meniran, valerian dan Panax ginseng.', 'Membantu meringankan gejala selesma.', 'SED-027', [$sido.'tolak_angin_flu.html']);
$add('/^SIDO M TOLAK ANGIN PERMEN /', 'NTR', null, null, 'OTL-PRM', [$sido.'../product_catalog.html'], 'belum_terverifikasi', null, 'Formula permen tidak disamakan dengan Tolak Angin cair.');
$add('/^SIDO M TOLAK LINU HERBAL /', 'ORM', 'Kandungan utama: meniran, cabe jawa, jahe, temulawak dan madu.', 'Membantu meredakan pegal linu dan nyeri sendi.', 'SED-027', [$sido.'tolak_linu.html']);
$add('/^SIDO M TOLAK LINU MINT /', 'ORM', null, null, 'SED-027', [$sido.'tolak_linu.html'], 'belum_terverifikasi', null, 'Formula varian Mint belum terkonfirmasi; tidak disalin dari Tolak Linu Herbal.');
$add('/^SIDO M VIT C 1000 (LEMON|ANGGUR|ORANGE) /', 'VIT', 'Per sachet: vitamin C 1000 mg, vitamin E 25 mg, vitamin B3 10 mg, vitamin B6 5 mg, vitamin B12 5 mcg dan madu 100 mg.', 'Suplementasi vitamin C dan membantu memelihara daya tahan tubuh.', 'SED-020', [$sido.'sido_muncul_c1000.html', $sido.'sido_muncul_c1000_sweet_orange.html', $sido.'sido_muncul_c1000_anggur_muscat.html'], 'kuantitatif');
$add('/^SIDO M VIT C 1000 COLLAGEN /', 'VIT', 'Kandungan utama terverifikasi: vitamin C 1000 mg dan collagen tripeptide 500 mg per sachet.', 'Suplemen vitamin C dan kolagen.', 'SED-020', ['https://www.sidomunculstore.com/blog/post/collagen-untuk-apa.html'], 'sebagian', null, 'Sumber resmi menyebut tambahan vitamin D3, B3, B6 dan B12 tanpa kadar; cocokkan label varian strawberry.');
$add('/^INBUMIN /', 'SUP', 'Per tablet: Channa striata purified extract (Striatin) 250 mg.', 'Membantu memelihara kesehatan.', 'SED-001', ['https://www.dexagroup.com/id/produk/inbumin-tablet-id/'], 'kuantitatif');
$add('/^VIP ALBUMIN /', 'SUP', 'Per kapsul: ekstrak Ophiocephalus striatus (ikan gabus) 500 mg.', 'Membantu memelihara kesehatan dan nutrisi tubuh.', 'SED-014', ['https://id.royalmedicalinkpharmalab.com/vipalbumin/'], 'kuantitatif');
$add('/^VIPALBUMIN PLUS /', 'SUP', 'Per sachet: ekstrak Ophiocephalus striatus 5000 mg, Citrus sinensis 4500 mg dan Curcuma domestica rhizoma 50 mg.', 'Membantu meningkatkan kadar albumin pada kondisi hipoalbuminemia.', 'SED-020', ['https://id.royalmedicalinkpharmalab.com/vipalbumin-plus/'], 'kuantitatif');
$add('/^VERMINT /', 'ANL', 'Ekstrak Lumbricus rubellus (cacing tanah).', 'Membantu meredakan demam.', 'SED-014', ['https://www.halodoc.com/obat-dan-vitamin/vermint-30-kapsul', 'https://www.k24klik.com/p/vermint-cap-12s-1115'], 'sebagian', null, 'Varian biasa berbeda dari Forte; kadar dan pabrikan harus dicocokkan dengan label Kico yang disuplai.');
$add('/^ANTANGIN GINGERMINT /', 'HBL', 'Kandungan utama: jahe merah, jahe emprit, akar manis, biji pala dan peppermint.', 'Membantu meredakan gejala masuk angin.', 'SED-027', [$deltomed]);
$add('/^ANTANGIN HABBATUSSAUDA /', 'HBL', 'Kandungan utama: habbatussauda, meniran, jahe, daun sembung, daun mint, biji pala, kunyit, akar manis dan madu.', 'Membantu meredakan masuk angin dan memelihara daya tahan tubuh.', 'SED-027', [$deltomed]);
$add('/^ANTANGIN JRG CAIR /', 'HBL', 'Kandungan utama yang dipublikasikan: jahe merah, jahe emprit, Panax ginseng, peppermint dan royal jelly.', 'Membantu meredakan masuk angin, mual dan kembung.', 'SED-027', [$deltomed], 'sebagian', null, 'Daftar utama pada halaman produsen; formula lengkap dan madu perlu dicocokkan pada label.');
$add('/^ANTANGIN GOOD NIGHT /', 'PSD', 'Kandungan utama: passiflora, valerian, jahe, daun mint, daun sembung, kulit pulai dan biji pala.', 'Membantu memperbaiki kualitas tidur.', 'SED-001', [$deltomed]);
$add('/^ANTANGIN JRG TAB/', 'HBL', 'Kandungan utama: jahe merah, jahe emprit, peppermint, royal jelly dan daun sembung.', 'Membantu meredakan gejala masuk angin.', 'SED-001', [$deltomed]);
$add('/^ANTANGIN JUNIOR /', 'HBL', 'Kandungan utama: meniran, jahe merah, daun sembung, kunyit dan madu.', 'Membantu memelihara daya tahan tubuh anak.', 'SED-027', [$deltomed]);
$add('/^ANTANGIN PERMEN /', 'NTR', 'Kandungan utama: ekstrak jahe, ekstrak akar manis, gula, madu dan minyak peppermint.', 'Menyegarkan napas dan melegakan tenggorokan.', 'OTL-PRM', [$deltomed], 'sebagian', null, 'Basis Antangin Candy dari produsen; eksipien/perisa masing-masing rasa tidak dirinci.');
$add('/^LELAP /', 'PSD', 'Kandungan utama: valerian, biji pala, ginseng dan akar lidah ayam (Polygala).', 'Membantu meringankan gangguan tidur.', 'SED-012', [$soho.'7'], 'kualitatif', null, 'Produsen menyebut kaplet; nama CAPS dan satuan sachet dari sumber perlu diperiksa terhadap kemasan.');
$add('/^OBAT SAKIT GIGI KAKAK TUA$/', 'ORL', null, null, 'SKB-S-CB090E25B3', [], 'belum_terverifikasi', null, 'Pabrikan/NIE tidak disertakan; jangan mengasumsikan formula kimia atau herbal.');
$add('/^LOSOSA GARAM$/', 'NTR', 'Natrium klorida dan kalium klorida; proporsi perlu label kemasan.', 'Garam rendah natrium untuk kebutuhan pangan.', 'SED-017', ['https://www.alodokter.com/garam-lososa-garam-rendah-sodium-yang-lebih-sehat-bagi-tubuh'], 'sebagian');
$add('/^LAXING /', 'KST', 'Per kapsul: daun senna 100 mg, Aloe vera folium 33 mg dan Foeniculum vulgare semen 20 mg.', 'Membantu melancarkan buang air besar.', 'SED-014', ['https://www.halodoc.com/obat-dan-vitamin/laxing-10-kapsul', 'https://laxing.id/obat-sembelit-alami-dan-12-tips-mencegah-sembelit/'], 'kuantitatif');
$add('/^DIAPET ANAK /', 'DRE', 'Ekstrak daun jambu biji, rimpang kunyit, buah mojokeling dan kulit buah delima.', 'Membantu mengurangi frekuensi buang air besar.', 'SED-022', [$soho.'5']);
$add('/^DIAPET CAP /', 'DRE', 'Ekstrak daun jambu biji, rimpang kunyit, buah mojokeling dan kulit buah delima.', 'Membantu mengurangi frekuensi buang air besar.', 'SED-014', [$soho.'5']);
$add('/^DIAPET NR /', 'DRE', 'Karbon aktif, attapulgite, ekstrak daun jambu biji, kunyit, buah mojokeling dan kulit buah delima.', 'Membantu memadatkan tinja dan mengurangi frekuensi buang air besar.', 'SED-014', [$soho.'5']);
$add('/^KULDON /', 'ORL', 'Kandungan utama: akar manis, daun pegagan, daun sogomanis, rumput laut, temulawak, daun saga dan herba timi.', 'Membantu meredakan panas dalam dan sariawan.', 'SED-001', [$deltomed]);
$add('/^SARI KURMA MADU ANGKAK /', 'NTR', null, null, 'SED-063', [], 'belum_terverifikasi', null, 'Merek/pabrikan/NIE belum diketahui; nama bahan pada nama dagang bukan bukti formula.');
$add('/^PUREKIDS AISE BELLY /', 'PNG', 'Minyak biji dill, ekstrak chamomile, adas, jahe dan lemon balm (Melissa officinalis).', 'Membantu meredakan perut kembung pada bayi dan anak.', 'SED-022', ['https://novellpharm.com/subcategory/67-6/PUREKIDS.html'], 'kualitatif', 'PT Novell Pharmaceutical Laboratories');
$add('/^CURCUMA FORCE /', 'SUP', 'Per tablet: ekstrak Curcuma xanthorrhiza rhizoma 20 mg dan piperin 2,5 mg.', 'Membantu memelihara fungsi hati dan memperbaiki nafsu makan.', 'SED-002', [$soho.'24'], 'kuantitatif', 'PT Soho Industri Pharmasi');
$add('/^SYAMIL FAMILY /', 'NTR', null, null, 'SED-063', [], 'belum_terverifikasi', null, 'Formula tepat belum ditemukan pada sumber tepercaya.');
$add('/^ASI BOOSTER MOM UUNG$/', 'PRB', 'Kandungan utama: ekstrak daun kelor, daun katuk dan ikan gabus.', 'Membantu melancarkan ASI.', 'SED-014', ['https://momuung.com/asi-booster-mom-uung-untuk-busui']);
$add('/^KAPSIDA HS$/', 'KLT', 'Kandungan utama: habbatussauda, pegagan, sambiloto, alang-alang, kunyit, lempuyang, lengkuas, kapulaga, kayu manis dan ketumbar.', 'Membantu memelihara kesehatan kulit.', 'SED-014', ['https://kembangbulan.com/product/kapsida-hs/'], 'sebagian', null, 'Halaman menampilkan bahan utama tanpa kadar; tidak disamakan dengan Kapsida biasa.');
$add('/^KAPSIDA$/', 'KLT', 'Kandungan utama: pegagan, sambiloto, alang-alang, kunyit, lempuyang, lengkuas, kapulaga, kayu manis dan ketumbar.', 'Membantu meredakan keluhan gatal dan jerawat.', 'SED-014', ['https://kembangbulan.com/product/kapsida/']);
$add('/^SARI KURMA ALJAZIRA$/', 'NTR', null, null, 'SED-063', [], 'belum_terverifikasi', null, 'Terdapat varian original/premium; merek saja belum menentukan formula.');
$add('/^SARI KURMA SAHARA JAMBU /', 'NTR', 'Kandungan utama terverifikasi: sari kurma dan jambu biji merah.', 'Pelengkap asupan nutrisi.', 'SED-063', ['https://www.k24klik.com/p/SAHARA-SARI-KURMA-PLUS-JAMBU-BIJI-MERAH-330G-3279'], 'sebagian', null, 'Bahan tambahan dan kadar belum terverifikasi.');
$add('/^SARI KURMA SAHARA ORI$/', 'NTR', 'Kandungan utama terverifikasi: sari kurma.', 'Pelengkap asupan nutrisi.', 'SED-063', ['https://www.k24klik.com/p/SAHARA-SARI-KURMA-330G-2322'], 'sebagian', null, 'Gula tambahan dan proporsi perlu dicocokkan dengan label.');
$add('/^ASIFIT /', 'PRB', 'Per kaplet: ekstrak daun katuk 114 mg, vitamin B1 10 mg, B2 25 mg, B6 15 mg dan B12 20 mcg.', 'Membantu melancarkan ASI.', 'SED-013', ['https://www.halodoc.com/obat-dan-vitamin/asifit-30-kaplet'], 'kuantitatif');
$add('/^HABBATUSSAUDA KURMA AJWA/', 'SUP', 'Nigella sativa semen (jintan hitam/habbatussauda).', 'Membantu memelihara kesehatan.', 'SED-014', ['https://www.halodoc.com/obat-dan-vitamin/habbatussauda-kurma-ajwa-100-kapsul'], 'kualitatif', null, 'Kurma Ajwa merupakan merek; tidak ditambahkan buah kurma ke komposisi.');
$add('/^ESEMAG$/', 'MGH', 'Kandungan utama: ekstrak kunyit (Curcuma domestica), akar manis (Glycyrrhiza) dan meniran (Phyllanthus niruri).', 'Membantu meringankan gangguan lambung.', 'SED-027', [$sido.'esemag.html'], 'kualitatif', 'PT Industri Jamu dan Farmasi Sido Muncul Tbk', 'Sumber produsen memakai sachet; satuan botol dari daftar dipertahankan dan perlu label.');
$add('/^PROSTAKUR /', 'GIN', 'Per kapsul: ekstrak Serenoa repens fructus (saw palmetto) 160 mg.', 'Membantu memelihara kesehatan prostat.', 'SED-014', ['https://medikon.id/en/products-detail/prostakur'], 'kuantitatif');
$add('/^NEPHROLITE$/', 'GIN', null, null, null, [], 'belum_terverifikasi', null, 'Nama tidak memastikan Nephrolit tablet/kapsul; pabrikan/NIE diperlukan.');
$add('/^KEIJIBELING$/', 'GIN', null, null, null, [], 'belum_terverifikasi', null, 'Kejibeling diproduksi beberapa pabrikan dengan formula berbeda; pabrikan/NIE diperlukan.');
$add('/^THERMOLYTE PLUS /', 'SUP', 'Kandungan yang disebut produsen: vitamin C, B1, B3, B6, asam pantotenat, magnesium, kromium, asam amino dan herbal.', 'Membantu mengurangi lemak dalam tubuh.', 'SED-012', ['https://www.pharos.co.id/thermolyte-plus-10-caplet'], 'sebagian', null, 'Jenis asam amino/herbal serta kadar tidak dipublikasikan; varian formula perlu label.');
$add('/^GIZIDAT /', 'SUP', 'Kandungan utama: ekstrak Curcuma xanthorrhiza rhizoma (temulawak), Anguilla bicolor (sidat) dan Mel depuratum (madu).', 'Membantu memperbaiki nafsu makan.', 'SED-022', ['https://asrot.pom.go.id/asrot/index.php/download/data/ZG9jcy9yZWcvMjAyMy8xMC8zNzgxLzM3ODEtNTUtMjAyMzEwMTcxMDA5MzgucGRm/01_Formulasi%20dan%20Komposisi%20Produk%20Per%20Batch.pdf'], 'kualitatif', null, 'Dokumen formulasi BPOM mencakup 60 ml; satuan madu dalam tabel tidak konsisten, sehingga kadar tidak disalin.');
$add('/^GABUMIN /', 'SUP', 'Kandungan utama: ekstrak Channa striata (ikan gabus).', 'Membantu memelihara kesehatan dan pemulihan tubuh.', 'SED-014', ['https://www.blibli.com/p/gabumin-30-kapsul/ps--APA-70640-00887'], 'sebagian', null, 'Bahan utama dikonfirmasi; kadar lengkap masih perlu label.');
$add('/^ALBUSMIN /', 'SUP', 'Kandungan utama: ekstrak Channa striata (ikan gabus).', 'Membantu memelihara kesehatan.', 'SED-014', ['https://www.halodoc.com/obat-dan-vitamin/albusmin-30-kapsul', 'https://www.pharos.co.id/produk?filter_brand=albusmin'], 'sebagian', null, 'Referensi 480 mg memakai pabrikan Prima Medika dan kemasan blister; sumber pengguna Pharos/botol. Kadar tidak diasumsikan.');

return [
    'date' => '2026-10-11',
    'group' => 'OBAT TRADISIONAL',
    'profiles' => $profiles,
    'unit_codes' => ['BOTOL' => 'BTL', 'SACHET' => 'SACH', 'BOX' => 'BOX', 'STRIP' => 'STR', 'BLISTER' => 'BLT', 'CAP' => 'KPS'],
    'manufacturer_aliases' => [
        'ULTRA SAKTI, PT' => 'PT Ultra Sakti',
        'MEDIKON PRIMA LAB' => 'PT Medikon Prima Laboratories',
        'BINTANG TOEDJOE - OTC' => 'PT Bintang Toedjoe',
        'DEXA MEDICA' => 'PT Dexa Medica',
        'INDUSTRI JAMU DAN F SIDO MUNCUL PT' => 'PT Industri Jamu dan Farmasi Sido Muncul Tbk',
        'SOHO -OTC' => 'PT Soho Industri Pharmasi',
        'KIMIA FARMA-OTC' => 'PT Kimia Farma Tbk',
        'PHAROS INDONESIA, PT - OTC' => 'PT Pharos Indonesia',
        'SERAYU SUMBER REJEKI, PT' => 'PT Serayu Sumber Rejeki',
        'DELTOMED LABORATORIES, PT' => 'PT Deltomed Laboratories',
        'PT. Deltomed Laboratories' => 'PT Deltomed Laboratories',
        'KICO JAYA LESTARI,PT' => 'PT Kico Jaya Lestari',
        'ROYAL MEDICALINK PHARMALAB' => 'PT Royal Medicalink Pharmalab',
    ],
    'distributor_aliases' => [
        'MENSA BINA SUKSES, PT' => 'PT Mensa Binasukses',
        'ANUGERAH PHARMINDO LESTARI, PT' => 'PT Anugerah Pharmindo Lestari',
        'PT. ENSEVAL PUTERA MEGATRADING' => 'PT Enseval Putera Megatrading Tbk',
        'PT. ANUGRAH ARGON MEDICA' => 'PT Anugrah Argon Medica',
        'MERAPI UTAMA PHARMA, PT.' => 'PT Merapi Utama Pharma',
        'PARIT PADANG GLOBAL, PT' => 'PT Parit Padang Global',
        'PENTA VALEN' => 'PT PENTA VALENT',
        'PENTA VALENT' => 'PT PENTA VALENT',
        'KEBAYORAN PHARMA, PT' => 'PT Kebayoran Pharma',
        'KIMIA FARMA TRADING AND DISTRIBUTIO' => 'PT Kimia Farma Trading & Distribution',
        'ANTAR MITRA SEMBADA, PT' => 'PT Antar Mitra Sembada',
        'UNITED DICO CITAS, PT' => 'PT United Dico Citas',
        'SEJAHTERA SURYA INTRIO,PT' => 'PT Sejahtera Surya Intrio',
        'REZEKI HUSADA UTAMA, PT' => 'PT Rezeki Husada Utama',
        'KICO JAYA LESTARI,PT' => 'PT Kico Jaya Lestari',
        'SWIPERX' => 'SwipeRx',
    ],
];
