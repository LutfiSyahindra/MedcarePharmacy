@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    <style>
        .shift-page { --ink:#1b2b42; --muted:#718096; --line:#e1e7ef; --blue:#4f63d8; --green:#087c5b; display:grid; gap:18px; }
        .shift-hero { display:flex; justify-content:space-between; gap:20px; align-items:center; padding:24px; color:#fff; border-radius:18px; background:linear-gradient(135deg,#172942,#29496b 65%,#5367d6); box-shadow:0 16px 35px rgba(25,43,68,.18); }
        .shift-hero h2 { margin:3px 0 5px; font-weight:820; letter-spacing:-.03em; }
        .shift-hero p { margin:0; color:#c5d2df; }
        .shift-hero-label { font-size:.68rem; font-weight:800; letter-spacing:.13em; color:#76e1bd; }
        .shift-hero .btn { color:#20324c; border:0; background:#fff; font-weight:760; }
        .shift-alert { display:flex; gap:10px; align-items:center; margin:0; padding:12px 15px; border:1px solid #f0d7a1; border-radius:11px; background:#fff8e5; color:#73551e; }
        .shift-manager { display:grid; grid-template-columns:minmax(240px,.7fr) minmax(0,1.3fr); gap:16px; padding:18px; border:1px solid var(--line); border-radius:16px; background:#fff; box-shadow:0 8px 24px rgba(26,42,64,.07); }
        .shift-manager-control { display:grid; align-content:start; gap:8px; }
        .shift-manager-control h5 { margin:0; color:var(--ink); font-weight:800; }
        .shift-manager-control p { margin:0 0 7px; color:var(--muted); font-size:.78rem; }
        .shift-manager-state { min-height:140px; display:grid; align-content:center; gap:12px; padding:16px; border:1px solid #dfe6ee; border-radius:13px; background:#f8fafc; }
        .shift-state-heading { display:flex; justify-content:space-between; gap:12px; align-items:start; }
        .shift-state-heading span { display:grid; gap:3px; }
        .shift-state-heading small { color:var(--muted); }
        .shift-state-heading strong { color:var(--ink); font-size:1rem; }
        .shift-state-badge { padding:5px 9px; border-radius:999px; background:#edf1f6; color:#637187; font-size:.66rem; font-weight:800; }
        .shift-state-badge.is-open { color:var(--green); background:#e9f8f2; }
        .shift-live-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:8px; }
        .shift-live-grid article { display:grid; gap:3px; padding:10px; border:1px solid var(--line); border-radius:9px; background:#fff; }
        .shift-live-grid small { color:var(--muted); font-size:.61rem; }
        .shift-live-grid strong { color:var(--ink); font-size:.78rem; }
        .shift-state-actions { display:flex; flex-wrap:wrap; gap:7px; }
        .shift-state-actions .btn { font-size:.74rem; font-weight:740; }
        .shift-state-actions .btn-close-shift { color:#ad3e50; border-color:#e9c4cc; background:#fff7f8; }
        .shift-summary-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
        .shift-stat { display:flex; gap:12px; align-items:center; padding:15px; border:1px solid var(--line); border-radius:13px; background:#fff; }
        .shift-stat i { display:grid; width:38px; height:38px; place-items:center; color:var(--blue); border-radius:10px; background:#eef1ff; font-size:1.2rem; }
        .shift-stat div { display:grid; }
        .shift-stat strong { color:var(--ink); font-size:1.1rem; }
        .shift-stat span { color:var(--muted); font-size:.7rem; }
        .shift-table-card { padding:18px; border:1px solid var(--line); border-radius:16px; background:#fff; }
        .shift-filters { display:grid; grid-template-columns:1fr .8fr .8fr .8fr auto; gap:9px; align-items:end; margin-bottom:15px; }
        .shift-filters label { display:grid; gap:5px; color:#5c6a7e; font-size:.68rem; font-weight:720; }
        .shift-table-card table { width:100%!important; }
        .shift-table-card thead th { color:#6d798a; background:#f7f9fc; font-size:.66rem; text-transform:uppercase; white-space:nowrap; }
        .shift-number { display:grid; gap:2px; }
        .shift-number strong { color:var(--ink); font-size:.75rem; }
        .shift-number small { color:var(--muted); font-size:.65rem; }
        .shift-status { display:inline-flex; padding:4px 8px; border-radius:999px; background:#eef1f5; color:#667386; font-size:.64rem; font-weight:800; }
        .shift-status.is-open { color:var(--green); background:#e9f8f2; }
        .shift-difference.is-plus { color:#087c5b; }.shift-difference.is-minus { color:#b43d50; }
        .shift-detail-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
        .shift-detail-grid article { display:grid; padding:10px; border:1px solid var(--line); border-radius:9px; }
        .shift-detail-grid small { color:var(--muted); font-size:.63rem; }.shift-detail-grid strong { color:var(--ink); }
        .shift-movement-list { display:grid; gap:7px; max-height:250px; overflow:auto; }
        .shift-movement { display:flex; justify-content:space-between; gap:12px; padding:9px 11px; border:1px solid var(--line); border-radius:9px; }
        .shift-movement div { display:grid; }.shift-movement small { color:var(--muted); }.shift-movement strong.is-out { color:#b43d50; }.shift-movement strong.is-in { color:var(--green); }
        @media(max-width:1000px){.shift-manager{grid-template-columns:1fr}.shift-live-grid{grid-template-columns:repeat(2,1fr)}.shift-summary-grid{grid-template-columns:repeat(2,1fr)}.shift-filters{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:600px){.shift-hero{align-items:flex-start;flex-direction:column}.shift-summary-grid,.shift-detail-grid{grid-template-columns:1fr}.shift-filters{grid-template-columns:1fr}}
    </style>
@endpush

@section("content")
    <div class="shift-page">
        <header class="shift-hero">
            <div><span class="shift-hero-label">KONTROL & REKONSILIASI KAS</span><h2>Riwayat Shift Kasir</h2><p>Buka dan tutup kasir, pantau arus kas, lalu telusuri selisih setiap shift.</p></div>
            <a href="{{ route('penjualan.pos') }}" class="btn"><i class="mdi mdi-point-of-sale me-1"></i> Buka POS</a>
        </header>

        @if (session('cashier_closed'))
            <div class="shift-alert"><i class="mdi mdi-clock-alert-outline"></i><span>{{ session('cashier_closed') }}</span></div>
        @endif

        <section class="shift-manager">
            <div class="shift-manager-control">
                <h5>Kasir saya</h5><p>Shift dapat ditutup setelah jam operasional berakhir.</p>
                <label class="form-label" for="manageShiftBranch">Cabang</label>
                <select class="form-select" id="manageShiftBranch">
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->code }} — {{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="shift-manager-state" id="shiftManagerState">
                <div class="shift-state-heading"><span><small>Status shift</small><strong id="managerShiftTitle">Memuat...</strong></span><b class="shift-state-badge" id="managerShiftBadge">-</b></div>
                <div class="shift-live-grid d-none" id="managerShiftMetrics">
                    <article><small>Modal</small><strong data-shift-metric="opening_amount">Rp 0</strong></article>
                    <article><small>Tunai</small><strong data-shift-metric="cash_sales">Rp 0</strong></article>
                    <article><small>Kas masuk</small><strong data-shift-metric="cash_in_total">Rp 0</strong></article>
                    <article><small>Kas keluar</small><strong data-shift-metric="cash_out_total">Rp 0</strong></article>
                    <article><small>Seharusnya</small><strong data-shift-metric="expected_cash">Rp 0</strong></article>
                </div>
                <div class="shift-state-actions" id="managerShiftActions"></div>
            </div>
        </section>

        <section class="shift-summary-grid">
            <article class="shift-stat"><i class="mdi mdi-timeline-clock-outline"></i><div><strong id="shiftTotal">0</strong><span>Total shift</span></div></article>
            <article class="shift-stat"><i class="mdi mdi-cash-register"></i><div><strong id="shiftOpen">0</strong><span>Masih aktif</span></div></article>
            <article class="shift-stat"><i class="mdi mdi-lock-check-outline"></i><div><strong id="shiftClosed">0</strong><span>Sudah ditutup</span></div></article>
            <article class="shift-stat"><i class="mdi mdi-scale-balance"></i><div><strong id="shiftDifference">Rp 0</strong><span>Akumulasi selisih</span></div></article>
        </section>

        <section class="shift-table-card">
            <div class="shift-filters">
                <label>Cabang<select id="shiftBranchFilter" class="form-select"><option value="">Semua cabang</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></label>
                <label>Status<select id="shiftStatusFilter" class="form-select"><option value="">Semua</option><option value="open">Aktif</option><option value="closed">Ditutup</option></select></label>
                <label>Dari<input type="date" id="shiftDateStart" class="form-control"></label>
                <label>Sampai<input type="date" id="shiftDateEnd" class="form-control"></label>
                <button type="button" class="btn btn-outline-secondary" id="resetShiftFilter"><i class="mdi mdi-filter-remove-outline"></i> Reset</button>
            </div>
            <div class="table-responsive">
                <table id="cashierShiftTable" class="table align-middle">
                    <thead><tr><th>No</th><th>Shift</th><th>Waktu</th><th>Kasir</th><th>Modal</th><th>Tunai</th><th>Kas Seharusnya</th><th>Kas Fisik</th><th>Selisih</th><th>Status</th><th></th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="modal fade" id="shiftDetailModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"><div class="modal-header"><div><h5 class="modal-title" id="shiftDetailTitle">Detail shift</h5><small class="text-muted" id="shiftDetailSubtitle"></small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="shift-detail-grid" id="shiftDetailGrid"></div><h6 class="mt-4">Kas masuk & keluar</h6><div class="shift-movement-list" id="shiftMovementList"></div></div></div></div></div>
@endsection

@push("scripts")
<script>
$(function () {
    $.ajaxSetup({headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}});
    const urls={table:@json(route('penjualan.pos.shifts.table')),status:@json(route('penjualan.pos.shifts.status')),open:@json(route('penjualan.pos.shifts.open')),movement:@json(route('penjualan.pos.shifts.movement')),close:@json(route('penjualan.pos.shifts.close'))};
    let managerState=null;
    const money=value=>new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(Number(value)||0);
    const escapeHtml=value=>String(value??'').replace(/[&<>'"]/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
    const error=(xhr,fallback)=>Swal.fire('Gagal',Object.values(xhr.responseJSON?.errors||{})[0]?.[0]||xhr.responseJSON?.message||fallback,'error');

    const table=$('#cashierShiftTable').DataTable({processing:true,serverSide:true,ajax:{url:urls.table,data:d=>{d.branch_id=$('#shiftBranchFilter').val();d.status=$('#shiftStatusFilter').val();d.date_start=$('#shiftDateStart').val();d.date_end=$('#shiftDateEnd').val();}},order:[[2,'desc']],columns:[
        {data:'DT_RowIndex',orderable:false,searchable:false},{data:'shift_number',name:'shift_number',render:(v,t,r)=>`<span class="shift-number"><strong>${escapeHtml(v)}</strong><small>${escapeHtml(r.branch_name)}</small></span>`},{data:'opened_at',name:'opened_at',render:(v,t,r)=>`<span class="shift-number"><strong>${escapeHtml(r.opened_at_label)}</strong><small>${escapeHtml(r.closed_at_label)} · ${escapeHtml(r.duration_label)}</small></span>`},{data:'cashier_name',name:'user.name',render:escapeHtml},
        {data:'cash_summary.opening_amount',orderable:false,searchable:false,render:money},{data:'cash_summary.cash_sales',orderable:false,searchable:false,render:money},{data:'cash_summary.expected_cash',orderable:false,searchable:false,render:money},{data:'cash_summary.actual_cash',orderable:false,searchable:false,render:v=>v===null?'-':money(v)},{data:'cash_summary.cash_difference',orderable:false,searchable:false,render:v=>v===null?'-':`<strong class="shift-difference ${Number(v)>=0?'is-plus':'is-minus'}">${money(v)}</strong>`},{data:'status_label',name:'status',render:(v,t,r)=>`<span class="shift-status ${r.status==='open'?'is-open':''}">${v}</span>`},{data:'detail_url',orderable:false,searchable:false,render:u=>`<button class="btn btn-sm btn-light shift-detail-btn" data-url="${u}" title="Detail"><i class="mdi mdi-eye-outline"></i></button>`}
    ]});
    table.on('xhr',(_,__,json)=>{const s=json?.summary||{};$('#shiftTotal').text(s.total||0);$('#shiftOpen').text(s.open||0);$('#shiftClosed').text(s.closed||0);$('#shiftDifference').text(money(s.cash_difference));});
    $('#shiftBranchFilter,#shiftStatusFilter,#shiftDateStart,#shiftDateEnd').on('change',()=>table.ajax.reload());
    $('#resetShiftFilter').on('click',()=>{$('#shiftBranchFilter,#shiftStatusFilter,#shiftDateStart,#shiftDateEnd').val('');table.ajax.reload();});

    function loadManager(){const branchId=$('#manageShiftBranch').val();if(!branchId){$('#managerShiftTitle').text('Belum ada cabang aktif');return;}$.get(urls.status,{branch_id:branchId}).done(r=>{managerState=r;renderManager();}).fail(x=>error(x,'Status shift gagal dimuat.'));}
    function renderManager(){const shift=managerState?.shift,operational=managerState?.operational||{},cashierName=shift?.cashier_name&&shift.cashier_name!=='-'?shift.cashier_name:'User tidak tersedia';$('#managerShiftBadge').toggleClass('is-open',!!shift).text(shift?'Aktif':(operational.is_open?'Belum dibuka':'Di luar jam'));$('#managerShiftTitle').text(shift?`${shift.shift_number} · ${cashierName}`:`Jam operasional ${operational.label||'-'}`);$('#managerShiftMetrics').toggleClass('d-none',!shift);if(shift){$('[data-shift-metric]').each(function(){$(this).text(money(shift[$(this).data('shift-metric')]));});$('#managerShiftActions').html(`<button class="btn btn-outline-success" data-manager-action="cash_in"><i class="mdi mdi-cash-plus"></i> Kas masuk</button><button class="btn btn-outline-warning" data-manager-action="cash_out"><i class="mdi mdi-cash-minus"></i> Kas keluar</button><button class="btn btn-close-shift" data-manager-action="close"><i class="mdi mdi-lock-outline"></i> Tutup kasir</button>`);}else{$('#managerShiftActions').html(operational.is_open?'<button class="btn btn-success" data-manager-action="open"><i class="mdi mdi-lock-open-check-outline"></i> Buka kasir</button>':'<small class="text-muted">Shift baru hanya dapat dibuka saat jam operasional. Shift yang masih aktif tetap dapat ditutup.</small>');}}
    $('#manageShiftBranch').on('change',loadManager);
    $(document).on('click','[data-manager-action]',function(){const action=$(this).data('manager-action'),branchId=$('#manageShiftBranch').val();if(action==='open'){Swal.fire({title:'Buka kasir',html:'<input id="mgrAmount" type="number" min="0" step="100" class="swal2-input" placeholder="Modal awal"><textarea id="mgrNotes" class="swal2-textarea" placeholder="Catatan (opsional)"></textarea>',showCancelButton:true,confirmButtonText:'Buka kasir',preConfirm:()=>({opening_amount:Math.max(0,Number($('#mgrAmount').val())||0),opening_notes:$('#mgrNotes').val()})}).then(r=>{if(r.isConfirmed)$.post(urls.open,{branch_id:branchId,...r.value}).done(()=>{loadManager();table.ajax.reload();}).fail(x=>error(x,'Kasir gagal dibuka.'));});return;}if(action==='close'){const expected=managerState.shift.expected_cash;Swal.fire({title:'Tutup kasir',html:`<p>Kas seharusnya <b>${money(expected)}</b></p><input id="mgrActual" type="number" min="0" step="100" value="${expected}" class="swal2-input"><textarea id="mgrNotes" class="swal2-textarea" placeholder="Catatan penutupan"></textarea>`,showCancelButton:true,confirmButtonText:'Hitung & tutup',preConfirm:()=>({actual_cash:Math.max(0,Number($('#mgrActual').val())||0),closing_notes:$('#mgrNotes').val()})}).then(r=>{if(r.isConfirmed)$.post(urls.close,{branch_id:branchId,...r.value}).done(x=>{Swal.fire('Kasir ditutup',`Selisih kas ${money(x.shift.cash_difference)}`,Math.abs(Number(x.shift.cash_difference))<.01?'success':'warning');loadManager();table.ajax.reload();}).fail(x=>error(x,'Kasir gagal ditutup.'));});return;}const isIn=action==='cash_in';Swal.fire({title:isIn?'Kas masuk':'Kas keluar',html:'<input id="mgrAmount" type="number" min="1" step="100" class="swal2-input" placeholder="Nominal"><textarea id="mgrNotes" class="swal2-textarea" placeholder="Keterangan wajib"></textarea>',showCancelButton:true,confirmButtonText:'Simpan',preConfirm:()=>{const amount=Number($('#mgrAmount').val())||0,description=String($('#mgrNotes').val()||'').trim();if(amount<=0||!description){Swal.showValidationMessage('Nominal dan keterangan wajib diisi.');return false;}return{amount,description};}}).then(r=>{if(r.isConfirmed)$.post(urls.movement,{branch_id:branchId,type:action,...r.value}).done(()=>{loadManager();table.ajax.reload();}).fail(x=>error(x,'Mutasi kas gagal disimpan.'));});});

    $('#cashierShiftTable').on('click','.shift-detail-btn',function(){$.get($(this).data('url')).done(r=>{const s=r.shift;$('#shiftDetailTitle').text(s.shift_number);$('#shiftDetailSubtitle').text(`${s.branch_name} · ${s.cashier_name} · ${s.status==='open'?'Aktif':'Ditutup'}`);const fields=[['Modal awal',s.opening_amount],['Penjualan tunai',s.cash_sales],['Kas masuk',s.cash_in_total],['Kas keluar',s.cash_out_total],['Kas seharusnya',s.expected_cash],['Kas fisik',s.actual_cash],['Selisih',s.cash_difference]];$('#shiftDetailGrid').html(fields.map(([l,v])=>`<article><small>${l}</small><strong>${v===null?'-':money(v)}</strong></article>`).join(''));$('#shiftMovementList').html((r.movements||[]).length?(r.movements||[]).map(m=>`<div class="shift-movement"><div><b>${escapeHtml(m.type_label)}</b><small>${escapeHtml(m.description)} · ${escapeHtml(m.created_by)} · ${escapeHtml(m.occurred_at)}</small></div><strong class="${m.type==='cash_in'?'is-in':'is-out'}">${m.type==='cash_in'?'+':'-'} ${money(m.amount)}</strong></div>`).join(''):'<div class="text-muted">Tidak ada kas masuk atau kas keluar pada shift ini.</div>');bootstrap.Modal.getOrCreateInstance(document.getElementById('shiftDetailModal')).show();}).fail(x=>error(x,'Detail shift gagal dimuat.'));});
    loadManager();
});
</script>
@endpush
