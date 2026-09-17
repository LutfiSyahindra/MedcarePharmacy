<section class="fn-filter-card">
    <div class="fn-section-heading">
        <div>
            <span class="fn-heading-icon"><i class="mdi mdi-tune-variant"></i></span>
            <span>
                <h2>
                    @switch($section)
                        @case('monthly') Filter akun bulanan @break
                        @case('cashier') Filter kas kasir @break
                        @default Filter {{ strtolower($page['title']) }}
                    @endswitch
                </h2>
                <p>
                    @switch($section)
                        @case('monthly') Pilih cabang dan satu bulan laporan. @break
                        @case('cashier') Status laci mengikuti cabang yang dipilih dan selalu ditampilkan secara langsung. @break
                        @case('ledger') Persempit transaksi berdasarkan periode, sumber, arus, metode, atau kata kunci. @break
                        @default Sesuaikan cabang dan periode analisis yang ingin ditampilkan.
                    @endswitch
                </p>
            </span>
        </div>
        @if (in_array($section, ['overview', 'cash-flow', 'ledger'], true))
            <div class="fn-presets" aria-label="Periode cepat">
                <button type="button" data-days="7">7 hari</button>
                <button type="button" data-days="30" class="is-active">30 hari</button>
                <button type="button" data-days="mtd">Bulan ini</button>
            </div>
        @endif
    </div>
    <form id="fnFilterForm" class="fn-filter-grid fn-filter-grid--{{ $section }}">
        <label><span>Cabang</span><select class="form-select" name="branch_id" id="fnBranch"><option value="">Semua cabang</option></select></label>

        @if (in_array($section, ['overview', 'cash-flow', 'ledger'], true))
            <label><span>Dari tanggal</span><input class="form-control" type="date" name="date_start" id="fnDateStart" value="{{ today()->subDays(29)->toDateString() }}"></label>
            <label><span>Sampai tanggal</span><input class="form-control" type="date" name="date_end" id="fnDateEnd" value="{{ today()->toDateString() }}"></label>
        @endif

        @if ($section === 'monthly')
            <label><span>Bulan laporan</span><input class="form-control" type="month" name="monthly_period" id="fnMonthlyFilter" max="{{ today()->format('Y-m') }}" value="{{ today()->format('Y-m') }}"></label>
        @endif

        @if (in_array($section, ['cash-flow', 'ledger'], true))
            <label><span>Sumber</span><select class="form-select" name="source" id="fnSource"><option value="">Semua sumber</option>@foreach($sources as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
            <label><span>Metode</span><select class="form-select" name="payment_method" id="fnMethod"><option value="">Semua metode</option>@foreach($paymentMethods as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
        @endif

        @if ($section === 'ledger')
            <label><span>Arus</span><select class="form-select" name="type" id="fnType"><option value="">Masuk & keluar</option><option value="income">Pendapatan</option><option value="expense">Pengeluaran</option></select></label>
            <label class="fn-search"><span>Pencarian</span><div><i class="mdi mdi-magnify"></i><input class="form-control" type="search" name="search" id="fnSearch" placeholder="Nomor, kategori, keterangan"></div></label>
        @endif

        <div class="fn-filter-actions">
            <button class="btn fn-btn-secondary" type="button" id="fnReset"><i class="mdi mdi-filter-remove-outline"></i> Reset</button>
            <button class="btn fn-btn-primary" type="submit" id="fnApply"><i class="mdi mdi-check"></i> Terapkan</button>
        </div>
    </form>
</section>
