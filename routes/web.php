<?php

use App\Http\Controllers\Medcare\MasterData\Distributor\DistributorController;
use App\Http\Controllers\Medcare\MasterData\Golongan\GolonganController;
use App\Http\Controllers\Medcare\MasterData\Golongan\MainGolonganController;
use App\Http\Controllers\Medcare\MasterData\Golongan\SubGolonganController;
use App\Http\Controllers\Medcare\MasterData\Kategori\KategoriController;
use App\Http\Controllers\Medcare\MasterData\Konversi\KonversiSatuanObatController;
use App\Http\Controllers\Medcare\MasterData\MasterObat\MasterObatController;
use App\Http\Controllers\Medcare\MasterData\Pabrikan\PabrikanController;
use App\Http\Controllers\Medcare\MasterData\Rak\RakController;
use App\Http\Controllers\Medcare\MasterData\Satuan\SatuanController;
use App\Http\Controllers\Medcare\MasterData\Sediaan\SediaanController;
use App\Http\Controllers\Medcare\Menu\PembelianDanPenerimaan\Pembelian\PembelianController;
use App\Http\Controllers\Medcare\Menu\PembelianDanPenerimaan\Penerimaan\PenerimaanController;
use App\Http\Controllers\Medcare\Menu\PembelianDanPenerimaan\ReturPembelian\ReturPembelianController;
use App\Http\Controllers\Medcare\Menu\Stok\StokController;
use App\Http\Controllers\Medcare\Notifikasi\MainController;
use App\Http\Controllers\Medcare\Settings\Auth\PermissionsController;
use App\Http\Controllers\Medcare\Settings\Auth\RoleController;
use App\Http\Controllers\Medcare\Settings\Auth\UsersController;
use App\Http\Controllers\Medcare\Settings\Branch\AssignBranchController;
use App\Http\Controllers\Medcare\Settings\Branch\BranchController;
use App\Http\Controllers\Medcare\Settings\Margin\MarginController;
use App\Http\Controllers\Medcare\Settings\Notification\NotificationSettingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('medcare/dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('medcare/menu/notifikasi')->group(function () {
        Route::get('/notifikasi/latest', [MainController::class, 'latest'])->name('notifikasi.latest');
        Route::get('/notifikasi', [MainController::class, 'index'])->name('notifikasi.SemuaNotifikasi');
        Route::post('/notifikasi/mark-all-read', [MainController::class, 'markAllRead'])->name('notifikasi.markAllRead');
        Route::get('/notifikasi/table', [MainController::class, 'table'])->name('notifikasi.table');
        Route::post('/notifikasi/readNotifikasi', [MainController::class, 'readNotifikasi'])->name('notifikasi.readNotifikasi');
    });

    Route::prefix('medcare/settings')->group(function () {
        // Users
        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::get('/users/tableUsers', [UsersController::class, 'table'])->name('users.table');
        Route::put('/users/updateStatus', [UsersController::class, 'updateStatus'])->name('users.updateStatus');
        Route::get('/users/getBranches', [UsersController::class, 'getBranches'])->name('users.getBranches');
        Route::post('/users/store', [UsersController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UsersController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}/update', [UsersController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}/delete', [UsersController::class, 'destroy'])->name('users.delete');
        Route::get('/users/dataRoles', [UsersController::class, 'dataRoles'])->name('users.dataRoles');
        Route::post('/users/assignRoles', [UsersController::class, 'assignRoles'])->name('users.assignRoles');
        Route::get('/users/{id}/getUserRoles', [UsersController::class, 'getUserRoles'])->name('users.getUserRoles');

        // Role
        Route::get('/roles', [RoleController::class, 'Role'])->name('roles.role');
        Route::get('/roles/tableRoles', [RoleController::class, 'table'])->name('roles.table');
        Route::post('/roles/store', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{id}/update', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}/delete', [RoleController::class, 'destroy'])->name('roles.delete');
        Route::get('/roles/dataPermissions', [RoleController::class, 'dataPermissions'])->name('roles.dataPermissions');
        Route::post('/roles/assignPermissions', [RoleController::class, 'assignPermissions'])->name('roles.assignPermissions');
        Route::get('/roles/{id}/getRolePermissions', [RoleController::class, 'getRolePermissions'])->name('roles.getRolePermissions');

        // Permission
        Route::get('/permissions', [PermissionsController::class, 'Permissions'])->name('permissions.permissions');
        Route::get('/permissions/tablePermissions', [PermissionsController::class, 'table'])->name('permissions.table');
        Route::post('/permissions/store', [PermissionsController::class, 'store'])->name('permissions.store');
        Route::get('/permissions/{id}/edit', [PermissionsController::class, 'edit'])->name('permissions.edit');
        Route::put('/permissions/{id}/update', [PermissionsController::class, 'update'])->name('permissions.update');
        Route::delete('/permissions/{id}/delete', [PermissionsController::class, 'destroy'])->name('permissions.delete');

        // Branch
        Route::get('/branch', [BranchController::class, 'index'])->name('branch.index');
        Route::get('/branch/tableBranch', [BranchController::class, 'table'])->name('branch.table');
        Route::get('/branch/{id}/edit', [BranchController::class, 'edit'])->name('branch.edit');
        Route::post('/branch/store', [BranchController::class, 'store'])->name('branch.store');
        Route::put('/branch/{id}/update', [BranchController::class, 'update'])->name('branch.update');
        Route::put('/branch/updateStatus', [BranchController::class, 'updateStatus'])->name('branch.updateStatus');
        Route::delete('/branch/{id}/destroy', [BranchController::class, 'destroy'])->name('branch.destroy');

        // Assign Branch
        Route::get('/assignBranch', [AssignBranchController::class, 'assignBranch'])->name('assignBranch.assignBranch');
        Route::get('/assignBranch/tableAssignBranch', [AssignBranchController::class, 'table'])->name('assignBranch.table');
        Route::get('/assignBranch/getUser', [AssignBranchController::class, 'getUser'])->name('assignBranch.getUser');
        Route::post('/assignBranch/assign', [AssignBranchController::class, 'assign'])->name('assignBranch.assign');
        Route::get('/assignBranch/{branch}/getAssignedUsers', [AssignBranchController::class, 'getAssignedUsers'])->name('assignBranch.getAssignedUsers');

        // Notification configuration
        Route::get('/notifikasi', [NotificationSettingController::class, 'index'])->name('settings.notifikasi.index');
        Route::put('/notifikasi/update', [NotificationSettingController::class, 'update'])->name('settings.notifikasi.update');
    });

    Route::prefix('medcare/masterData')->group(function () {
        // --- Kategori
        Route::get('/kategori/utama', [KategoriController::class, 'kategoriUtama'])->name('kategori.kategoriUtama');
        Route::get('/kategori/table', [KategoriController::class, 'table'])->name('kategori.kategoriUtama.table');
        Route::post('/kategori/store', [KategoriController::class, 'store'])->name('kategori.kategoriUtama.store');
        Route::get('/kategori/{id}/edit', [KategoriController::class, 'edit'])->name('kategori.kategoriUtama.edit');
        Route::put('/kategori/{id}/update', [KategoriController::class, 'update'])->name('kategori.kategoriUtama.update');
        Route::delete('/kategori/{id}/destroy', [KategoriController::class, 'destroy'])->name('kategori.kategoriUtama.destroy');

        // --- Margin
        Route::get('/margin', [MarginController::class, 'margin'])->name('margin.margin');
        Route::get('/margin/table', [MarginController::class, 'table'])->name('margin.table');
        Route::get('/margin/{tingkat}/getReferences', [MarginController::class, 'getReferences'])->name('margin.getReferences');
        Route::post('/margin/store', [MarginController::class, 'store'])->name('margin.store');
        Route::put('/margin/updateStatus', [MarginController::class, 'updateStatus'])->name('margin.updateStatus');
        Route::get('/margin/{id}/edit', [MarginController::class, 'edit'])->name('margin.edit');
        Route::put('/margin/{id}/update', [MarginController::class, 'update'])->name('margin.update');
        Route::delete('/margin/{id}/destroy', [MarginController::class, 'destroy'])->name('margin.destroy');

        // --- Satuan
        Route::get('/satuan', [SatuanController::class, 'satuan'])->name('satuan.satuan');
        Route::get('/satuan/table', [SatuanController::class, 'table'])->name('satuan.table');
        Route::post('/satuan/store', [SatuanController::class, 'store'])->name('satuan.store');
        Route::get('/satuan/{id}/edit', [SatuanController::class, 'edit'])->name('satuan.edit');
        Route::put('/satuan/{id}/update', [SatuanController::class, 'update'])->name('satuan.update');
        Route::put('/satuan/updateStatus', [SatuanController::class, 'updateStatus'])->name('satuan.updateStatus');
        Route::delete('/satuan/{id}/destroy', [SatuanController::class, 'destroy'])->name('satuan.destroy');
        Route::get('/satuan/exportTemplate', [SatuanController::class, 'exportTemplate'])->name('satuan.exportTemplate');
        Route::post('/satuan/import', [SatuanController::class, 'import'])->name('satuan.import');

        // --- Golongan
        Route::get('/golongan', [GolonganController::class, 'golongan'])->name('golongan.golongan');
        Route::get('/golongan/table', [GolonganController::class, 'table'])->name('golongan.table');
        Route::post('/golongan/store', [GolonganController::class, 'store'])->name('golongan.store');
        Route::get('/golongan/{id}/edit', [GolonganController::class, 'edit'])->name('golongan.edit');
        Route::put('/golongan/{id}/update', [GolonganController::class, 'update'])->name('golongan.update');
        Route::put('/golongan/updateStatus', [GolonganController::class, 'updateStatus'])->name('golongan.updateStatus');
        Route::delete('/golongan/{id}/destroy', [GolonganController::class, 'destroy'])->name('golongan.destroy');
        Route::get('/golongan/exportTemplate', [GolonganController::class, 'exportTemplate'])->name('golongan.exportTemplate');
        Route::post('/golongan/import', [GolonganController::class, 'import'])->name('golongan.import');

        // --- Main Golongan
        Route::get('/golongan/mainGolongan', [MainGolonganController::class, 'mainGolongan'])->name('golongan.mainGolongan');
        Route::get('/golongan/mainGolongan/golongan', [MainGolonganController::class, 'golongan'])->name('golongan.mainGolongan.golongan');
        Route::get('/golongan/mainGolongan/table', [MainGolonganController::class, 'table'])->name('golongan.mainGolongan.table');
        Route::post('/golongan/mainGolongan/store', [MainGolonganController::class, 'store'])->name('golongan.mainGolongan.store');
        Route::get('/golongan/mainGolongan/{id}/edit', [MainGolonganController::class, 'edit'])->name('golongan.mainGolongan.edit');
        Route::put('/golongan/mainGolongan/{id}/update', [MainGolonganController::class, 'update'])->name('golongan.mainGolongan.update');
        Route::delete('/golongan/mainGolongan/{id}/destroy', [MainGolonganController::class, 'destroy'])->name('golongan.mainGolongan.destroy');
        Route::get('/golongan/mainGolongan/exportTemplate', [MainGolonganController::class, 'exportTemplate'])->name('golongan.mainGolongan.exportTemplate');
        Route::post('/golongan/mainGolongan/import', [MainGolonganController::class, 'import'])->name('golongan.mainGolongan.import');

        // --- Sub Golongan
        Route::get('/golongan/subGolongan', [SubGolonganController::class, 'subGolongan'])->name('golongan.subGolongan');
        Route::get('/golongan/subGolongan/mainGolongan', [SubGolonganController::class, 'mainGolongan'])->name('golongan.subGolongan.mainGolongan');
        Route::get('/golongan/subGolongan/table', [SubGolonganController::class, 'table'])->name('golongan.subGolongan.table');
        Route::post('/golongan/subGolongan/store', [SubGolonganController::class, 'store'])->name('golongan.subGolongan.store');
        Route::get('/golongan/subGolongan/{id}/edit', [SubGolonganController::class, 'edit'])->name('golongan.subGolongan.edit');
        Route::put('/golongan/subGolongan/{id}/update', [SubGolonganController::class, 'update'])->name('golongan.subGolongan.update');
        Route::delete('/golongan/subGolongan/{id}/destroy', [SubGolonganController::class, 'destroy'])->name('golongan.subGolongan.destroy');
        Route::get('/golongan/subGolongan/exportTemplate', [SubGolonganController::class, 'exportTemplate'])->name('golongan.subGolongan.exportTemplate');
        Route::post('/golongan/subGolongan/import', [SubGolonganController::class, 'import'])->name('golongan.subGolongan.import');

        // -- Sediaan
        Route::get('/sediaan', [SediaanController::class, 'sediaan'])->name('sediaan.sediaan');
        Route::get('/sediaan/table', [SediaanController::class, 'table'])->name('sediaan.table');
        Route::post('/sediaan/store', [SediaanController::class, 'store'])->name('sediaan.store');
        Route::get('/sediaan/{id}/edit', [SediaanController::class, 'edit'])->name('sediaan.edit');
        Route::put('/sediaan/{id}/update', [SediaanController::class, 'update'])->name('sediaan.update');
        Route::put('/sediaan/updateStatus', [SediaanController::class, 'updateStatus'])->name('sediaan.updateStatus');
        Route::delete('/sediaan/{id}/destroy', [SediaanController::class, 'destroy'])->name('sediaan.destroy');
        Route::get('/sediaan/exportTemplate', [SediaanController::class, 'exportTemplate'])->name('sediaan.exportTemplate');
        Route::post('/sediaan/import', [SediaanController::class, 'import'])->name('sediaan.import');

        // -- Pabrikan
        Route::get('/pabrikan', [PabrikanController::class, 'pabrikan'])->name('pabrikan.pabrikan');
        Route::get('/pabrikan/table', [PabrikanController::class, 'table'])->name('pabrikan.table');
        Route::post('/pabrikan/store', [PabrikanController::class, 'store'])->name('pabrikan.store');
        Route::get('/pabrikan/{id}/edit', [PabrikanController::class, 'edit'])->name('pabrikan.edit');
        Route::put('/pabrikan/{id}/update', [PabrikanController::class, 'update'])->name('pabrikan.update');
        Route::put('/pabrikan/updateStatus', [PabrikanController::class, 'updateStatus'])->name('pabrikan.updateStatus');
        Route::delete('/pabrikan/{id}/destroy', [PabrikanController::class, 'destroy'])->name('pabrikan.destroy');
        Route::get('/pabrikan/exportTemplate', [PabrikanController::class, 'exportTemplate'])->name('pabrikan.exportTemplate');
        Route::post('/pabrikan/import', [PabrikanController::class, 'import'])->name('pabrikan.import');

        // -- Distributor
        Route::get('/distributor', [DistributorController::class, 'distributor'])->name('distributor.distributor');
        Route::get('/distributor/table', [DistributorController::class, 'table'])->name('distributor.table');
        Route::post('/distributor/store', [DistributorController::class, 'store'])->name('distributor.store');
        Route::get('/distributor/{id}/edit', [DistributorController::class, 'edit'])->name('distributor.edit');
        Route::put('/distributor/{id}/update', [DistributorController::class, 'update'])->name('distributor.update');
        Route::put('/distributor/updateStatus', [DistributorController::class, 'updateStatus'])->name('distributor.updateStatus');
        Route::delete('/distributor/{id}/destroy', [DistributorController::class, 'destroy'])->name('distributor.destroy');
        Route::get('/distributor/exportTemplate', [DistributorController::class, 'exportTemplate'])->name('distributor.exportTemplate');
        Route::post('/distributor/import', [DistributorController::class, 'import'])->name('distributor.import');

        // -- Rak
        Route::get('/rak', [RakController::class, 'rak'])->name('rak.rak');
        Route::get('/rak/table', [RakController::class, 'table'])->name('rak.table');
        Route::post('/rak/store', [RakController::class, 'store'])->name('rak.store');
        Route::get('/rak/{id}/edit', [RakController::class, 'edit'])->name('rak.edit');
        Route::put('/rak/{id}/update', [RakController::class, 'update'])->name('rak.update');
        Route::put('/rak/updateStatus', [RakController::class, 'updateStatus'])->name('rak.updateStatus');
        Route::delete('/rak/{id}/destroy', [RakController::class, 'destroy'])->name('rak.destroy');
        Route::get('/rak/exportTemplate', [RakController::class, 'exportTemplate'])->name('rak.exportTemplate');
        Route::post('/rak/import', [RakController::class, 'import'])->name('rak.import');

        // -- MasterObat
        Route::get('/masterObat', [MasterObatController::class, 'MasterObat'])->name('masterObat.MasterObat');
        Route::get('/masterObat/table', [MasterObatController::class, 'table'])->name('masterObat.table');
        Route::post('/masterObat/store', [MasterObatController::class, 'store'])->name('masterObat.store');
        Route::get('/masterObat/{id}/edit', [MasterObatController::class, 'edit'])->name('masterObat.edit');
        Route::put('/masterObat/{id}/update', [MasterObatController::class, 'update'])->name('masterObat.update');
        Route::put('/masterObat/updateStatus', [MasterObatController::class, 'updateStatus'])->name('masterObat.updateStatus');
        Route::delete('/masterObat/{id}/destroy', [MasterObatController::class, 'destroy'])->name('masterObat.destroy');
        Route::get('/masterObat/exportTemplate', [MasterObatController::class, 'exportTemplate'])->name('masterObat.exportTemplate');
        Route::post('/masterObat/import', [MasterObatController::class, 'import'])->name('masterObat.import');
        Route::get('/masterObat/getKategori', [MasterObatController::class, 'getKategori'])->name('masterObat.getKategori');
        Route::get('/masterObat/getSediaan', [MasterObatController::class, 'getSediaan'])->name('masterObat.getSediaan');
        Route::get('/masterObat/getGolongan', [MasterObatController::class, 'getGolongan'])->name('masterObat.getGolongan');
        Route::get('/masterObat/{id}/getMainGolongan', [MasterObatController::class, 'getMainGolongan'])->name('masterObat.getMainGolongan');
        Route::get('/masterObat/{id}/getSubGolongan', [MasterObatController::class, 'getSubGolongan'])->name('masterObat.getSubGolongan');
        Route::get('/masterObat/getSatuan', [MasterObatController::class, 'getSatuan'])->name('masterObat.getSatuan');
        Route::get('/masterObat/getPabrikan', [MasterObatController::class, 'getPabrikan'])->name('masterObat.getPabrikan');
        Route::get('/masterObat/getDistributor', [MasterObatController::class, 'getDistributor'])->name('masterObat.getDistributor');
        Route::get('/masterObat/getRak', [MasterObatController::class, 'getRak'])->name('masterObat.getRak');

        // --- Konversi Satuan Obat
        Route::get('/konversiSatuanObat', [KonversiSatuanObatController::class, 'konversi'])->name('konversiSatuanObat.konversiSatuanObat');
        Route::get('/konversiSatuanObat/table', [KonversiSatuanObatController::class, 'table'])->name('konversiSatuanObat.table');
        Route::post('/konversiSatuanObat/store', [KonversiSatuanObatController::class, 'store'])->name('konversiSatuanObat.store');
        Route::get('/konversiSatuanObat/{id}/edit', [KonversiSatuanObatController::class, 'edit'])->name('konversiSatuanObat.edit');
        Route::get('/konversiSatuanObat/getObat', [KonversiSatuanObatController::class, 'getObat'])->name('konversiSatuanObat.getObat');
        Route::get('/konversiSatuanObat/getSatuan', [KonversiSatuanObatController::class, 'getSatuan'])->name('konversiSatuanObat.getSatuan');
        Route::put('/konversiSatuanObat/{id}/update', [KonversiSatuanObatController::class, 'update'])->name('konversiSatuanObat.update');
        Route::delete('/konversiSatuanObat/{id}/destroy', [KonversiSatuanObatController::class, 'destroy'])->name('konversiSatuanObat.destroy');
        Route::get('/konversiSatuanObat/exportTemplate', [KonversiSatuanObatController::class, 'exportTemplate'])->name('konversiSatuanObat.exportTemplate');
        Route::post('/konversiSatuanObat/import', [KonversiSatuanObatController::class, 'import'])->name('konversiSatuanObat.import');

    });

    Route::prefix('medcare/menu/pembelian-dan-penerimaan')->group(function () {
        Route::get('/pembelian', [PembelianController::class, 'pembelian'])->name('pembelian.pembelian');
        Route::get('/pembelian/table', [PembelianController::class, 'table'])->name('pembelian.table');
        Route::get('/pembelian/getDistributor', [PembelianController::class, 'getDistributor'])->name('pembelian.getDistributor');
        Route::get('/pembelian/generateNoPO', [PembelianController::class, 'generateNoPO'])->name('pembelian.generateNoPO');
        Route::get('/pembelian/getObat', [PembelianController::class, 'getObat'])->name('pembelian.getObat');
        Route::post('/pembelian/store', [PembelianController::class, 'store'])->name('pembelian.store');
        Route::get('/pembelian/{id}/edit', [PembelianController::class, 'edit'])->name('pembelian.edit');
        Route::put('/pembelian/{id}/update', [PembelianController::class, 'update'])->name('pembelian.update');
        Route::put('/pembelian/{id}/approve', [PembelianController::class, 'approve'])->name('pembelian.approve');
        Route::put('/pembelian/{id}/reject', [PembelianController::class, 'reject'])->name('pembelian.reject');
        Route::put('/pembelian/{id}/reopen-approval', [PembelianController::class, 'reopenApproval'])->name('pembelian.reopenApproval');
        Route::delete('/pembelian/{id}/destroy', [PembelianController::class, 'destroy'])->name('pembelian.destroy');
        Route::get('/pembelian/{id}/show', [PembelianController::class, 'show'])->name('pembelian.show');
        Route::get('/pembelian/getKonversiSatuan', [PembelianController::class, 'getKonversiSatuan'])->name('pembelian.getKonversiSatuan');

        Route::get('/penerimaan', [PenerimaanController::class, 'penerimaan'])->name('penerimaan.penerimaan');
        Route::get('/penerimaan/table', [PenerimaanController::class, 'table'])->name('penerimaan.table');
        Route::get('/penerimaan/generateNoPenerimaan', [PenerimaanController::class, 'generateNoPenerimaan'])->name('penerimaan.generateNoPenerimaan');
        Route::get('/penerimaan/approved-po', [PenerimaanController::class, 'approvedPurchaseOrders'])->name('penerimaan.approvedPo');
        Route::get('/penerimaan/po/{id}', [PenerimaanController::class, 'purchaseOrderDetail'])->name('penerimaan.purchaseOrderDetail');
        Route::post('/penerimaan/store', [PenerimaanController::class, 'store'])->name('penerimaan.store');
        Route::get('/penerimaan/{id}/show', [PenerimaanController::class, 'show'])->name('penerimaan.show');
        Route::get('/penerimaan/{id}/edit', [PenerimaanController::class, 'edit'])->name('penerimaan.edit');
        Route::get('/penerimaan/{id}/harga-jual-preview', [PenerimaanController::class, 'hargaJualPreview'])->name('penerimaan.hargaJualPreview');
        Route::put('/penerimaan/{id}/update', [PenerimaanController::class, 'update'])->name('penerimaan.update');
        Route::put('/penerimaan/{id}/post', [PenerimaanController::class, 'post'])->name('penerimaan.post');
        Route::put('/penerimaan/{id}/cancel', [PenerimaanController::class, 'cancel'])->name('penerimaan.cancel');
        Route::delete('/penerimaan/{id}/destroy', [PenerimaanController::class, 'destroy'])->name('penerimaan.destroy');

        Route::get('/retur-pembelian', [ReturPembelianController::class, 'returPembelian'])->name('returPembelian.returPembelian');
        Route::get('/retur-pembelian/table', [ReturPembelianController::class, 'table'])->name('returPembelian.table');
        Route::get('/retur-pembelian/generateNoRetur', [ReturPembelianController::class, 'generateNoRetur'])->name('returPembelian.generateNoRetur');
        Route::get('/retur-pembelian/posted-receipts', [ReturPembelianController::class, 'postedReceipts'])->name('returPembelian.postedReceipts');
        Route::get('/retur-pembelian/penerimaan/{id}', [ReturPembelianController::class, 'receiptDetail'])->name('returPembelian.receiptDetail');
        Route::post('/retur-pembelian/store', [ReturPembelianController::class, 'store'])->name('returPembelian.store');
        Route::get('/retur-pembelian/{id}/show', [ReturPembelianController::class, 'show'])->name('returPembelian.show');
        Route::get('/retur-pembelian/{id}/edit', [ReturPembelianController::class, 'edit'])->name('returPembelian.edit');
        Route::put('/retur-pembelian/{id}/update', [ReturPembelianController::class, 'update'])->name('returPembelian.update');
        Route::put('/retur-pembelian/{id}/post', [ReturPembelianController::class, 'post'])->name('returPembelian.post');
        Route::put('/retur-pembelian/{id}/cancel', [ReturPembelianController::class, 'cancel'])->name('returPembelian.cancel');
        Route::delete('/retur-pembelian/{id}/destroy', [ReturPembelianController::class, 'destroy'])->name('returPembelian.destroy');

    });

    Route::prefix('medcare/menu/stok')->group(function () {
        Route::get('/stok', [StokController::class, 'stok'])->name('stok.stok');
        Route::get('/stok/table', [StokController::class, 'stockTable'])->name('stok.table');
        Route::get('/stok/batch/table', [StokController::class, 'batchTable'])->name('stok.batchTable');
        Route::get('/stok/riwayat-harga/table', [StokController::class, 'riwayatHargaTable'])->name('stok.riwayatHarga.table');
        Route::get('/stok/obat-options', [StokController::class, 'obatOptions'])->name('stok.obatOptions');
        Route::get('/stok/batch-options/{obatId}', [StokController::class, 'batchOptions'])->name('stok.batchOptions');
        Route::put('/stok/batch/{id}/harga-jual', [StokController::class, 'updateBatchHargaJual'])->name('stok.batch.updateHargaJual');
        Route::post('/stok/mutasi/store', [StokController::class, 'storeMutation'])->name('stok.mutasi.store');

        Route::get('/kartu-stok', [StokController::class, 'kartuStok'])->name('kartuStok.kartuStok');
        Route::get('/kartu-stok/table', [StokController::class, 'kartuTable'])->name('kartuStok.table');
    });
});

require __DIR__.'/auth.php';
