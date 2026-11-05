<div>
        <div class="panel-header bg-primary-gradient">
            <div class="page-inner py-5">
                <div>
                    <h2 class="text-white pb-2 fw-bold">Outstanding BSTBJ</h2>
                    <h5 class="text-white op-7 mb-2">List data outstanding dengan status BSTBJ</h5>
                </div>
            </div>
        </div>

        <div class="page-inner mt--5">
            <div class="row">
                <div class="col-md-12">
                    <div class="card full-height">
                        <div class="card-body">

                            <div class="row mb-3 align-items-center">
                                <div class="col-6 col-md-3 mb-2 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <span class="mr-2">Show</span>
                                        <select wire:model.live="perPage" class="form-control form-control-sm"
                                            style="width: auto; display: inline-block;">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                        <span class="ml-2">entries</span>
                                    </div>
                                </div>
                                <div class="col-md-6 offset-md-3">
                                    <input type="text" wire:model.live.debounce.300ms="search"
                                        class="form-control form-control-sm" placeholder="Search Ticket...">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr class="bg-primary text-white">
                                            <th>Detail</th>
                                            <th style="cursor: pointer;" wire:click="sortBy('department')">Department</th>
                                            <th style="cursor: pointer;" wire:click="sortBy('status')">Status</th>
                                            <th style="cursor: pointer;" wire:click="sortBy('spk_no')">SPK No</th>
                                            <th>Material</th>
                                            <th style="cursor: pointer;" wire:click="sortBy('quantity')">Qty</th>
                                            <th>Size</th>
                                            <th>Nomor Batch</th>
                                            <th style="cursor: pointer;" wire:click="sortBy('tgl_produksi')">Tanggal
                                                Produksi</th>
                                            <th>Nama Mesin</th>
                                            <th>Customer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $deptBadge = [
                                                'specialty' => 'primary',
                                                'powder' => 'warning',
                                                'liquid' => 'info',
                                                'hinco' => 'secondary',
                                            ];
                                        @endphp
                                        @forelse ($tickets as $ticket)
                                            <tr>
                                                <td>
                                                    <button type="button" class="btn btn-info btn-sm"
                                                        wire:click="openDetail('{{ $ticket->source }}', '{{ $ticket->id }}')"
                                                        title="Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                                <td><span
                                                        class="badge badge-{{ $deptBadge[$ticket->department] ?? 'secondary' }}">{{ ucfirst($ticket->department) }}</span>
                                                </td>
                                            <td><span class="badge badge-warning">{{ ucfirst($ticket->status) }}</span></td>
                                                <td>{{ $ticket->spk_no }}</td>
                                                <td>{{ ltrim($ticket->material, '0') }}</td>
                                                <td>{{ $ticket->quantity }}</td>
                                                <td>{{ $ticket->size }}</td>
                                                <td>
                                                    @forelse ($ticket->batch_number as $batch)
                                                        {{ $batch }}@if (!$loop->last)
                                                            <br>
                                                        @endif
                                                    @empty
                                                        -
                                                    @endforelse
                                                </td>
                                                <td>{{ $ticket->tgl_produksi ? \Carbon\Carbon::parse($ticket->tgl_produksi)->format('d-m-Y') : '-' }}
                                                </td>
                                                <td>{{ $ticket->machine_name }}</td>
                                                <td>{{ $ticket->customer }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center">Tidak ada data outstanding BSTBJ.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($tickets->hasPages())
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <span class="text-muted">
                                            Showing {{ $tickets->firstItem() }} to {{ $tickets->lastItem() }} of
                                            {{ $tickets->total() }} entries
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-end">
                                            {{ $tickets->links() }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Detail -->
        @if ($selectedTicket)
            <div wire:ignore.self class="modal fade" id="detailModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <h5 class="modal-title text-white">
                                Detail BSTBJ - {{ ucfirst($selectedSource === 'ticketing_powder' ? 'powder' : ($selectedTicket->department ?? $selectedSource)) }}
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" wire:click="closeModal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label class="strong">SPK No</label>
                                    <input type="text" class="form-control" value="{{ $selectedTicket->spk_no }}"
                                        readonly>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="strong">Customer</label>
                                    <input type="text" class="form-control" value="{{ $selectedTicket->customer }}"
                                        readonly>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label class="strong">Material</label>
                                    <input type="text" class="form-control"
                                        value="{{ ltrim($selectedTicket->material_code ?? $selectedTicket->material, '0') }}"
                                        readonly>
                                </div>
                            </div>

                            @if (in_array($selectedSource, ['ticketing', 'ticketing_powder'], true))
                                <div class="row">
                                    <div class="col-md-2 form-group">
                                        <label class="strong">Qty Request</label>
                                        <input type="text" class="form-control" value="{{ $selectedTicket->quantity }}"
                                            readonly>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label class="strong">Qty Confirm</label>
                                        <input type="number" class="form-control"
                                            style="background-color: #fff3cd; border-color: #ffc107;"
                                            wire:model="qtyConfirmInputs.0" placeholder="Qty dari Gudang">
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label class="strong">Sloc</label>
                                        {{-- wire:ignore supaya Select2 tidak rusak saat Livewire re-render --}}
                                        <div wire:ignore>
                                            <select class="form-control sloc-select" data-index="0">
                                                <option value="">-- Pilih Sloc --</option>
                                                @foreach ($slocOptions as $opt)
                                                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label class="strong">Size</label>
                                        <input type="text" class="form-control" value="{{ $selectedTicket->kemasan }}"
                                            readonly>
                                    </div>
                                    <div class="col-md-3 form-group">
                                        <label class="strong">Tanggal Produksi</label>
                                        <input type="text" class="form-control"
                                            value="{{ $selectedTicket->tgl_buat ? \Carbon\Carbon::parse($selectedTicket->tgl_buat)->format('d-m-Y') : '-' }}"
                                            readonly>
                                    </div>
                                </div>

                                <!-- Progress Status Stepper (khusus specialty, mengikuti alur SPK) -->
                                <div class="form-group">
                                    <label class="strong">Progress Status</label>
                                    @php
                                        $statusSteps = [
                                            'Open',
                                            'Transfer RM',
                                            'Production Process',
                                            'QC Test',
                                            'BSTBJ',
                                            'Waiting GI',
                                            'Close',
                                        ];
                                        $currentIndex = array_search(
                                            ucfirst(strtolower($selectedTicket->status)),
                                            array_map('ucfirst', array_map('strtolower', $statusSteps)),
                                        );
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center position-relative mt-4"
                                        style="padding: 0 10px;">
                                        @foreach ($statusSteps as $stepIndex => $step)
                                            <div class="text-center" style="flex: 1;">
                                                <div class="rounded-circle mx-auto mb-2"
                                                    style="width: 14px; height: 14px; background-color: {{ $stepIndex <= $currentIndex ? '#1572e8' : '#dee2e6' }};">
                                                </div>
                                                <small
                                                    class="{{ $stepIndex <= $currentIndex ? 'text-primary font-weight-bold' : 'text-muted' }}">{{ $step }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="text-center mt-2"><strong>{{ $selectedTicket->progress }}%</strong></div>
                                </div>
                            @else
                                <div class="form-group">
                                    <label class="strong">Batch / Tank</label>
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Batch/Tank</th>
                                                <th>Qty Request</th>
                                                <th>Size</th>
                                                <th>UOM</th>
                                                <th>Qty Confirm</th>
                                                <th style="min-width: 200px;">Sloc</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($selectedTicket->batches ?? [] as $i => $batch)
                                                <tr>
                                                    <td>{{ $batch['batch_tank'] ?? '-' }}</td>
                                                    <td>{{ $batch['qty'] ?? '-' }}</td>
                                                    <td>{{ $batch['size'] ?? '-' }}</td>
                                                    <td>{{ $batch['uom'] ?? '-' }}</td>
                                                    <td>
                                                        @if ($selectedTicket->confirmed)
                                                            {{ $batch['qty_confirm'] ?? '-' }}
                                                        @else
                                                            <input type="number" class="form-control form-control-sm"
                                                                wire:model="qtyConfirmInputs.{{ $i }}"
                                                                placeholder="Qty dari Gudang">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($selectedTicket->confirmed)
                                                            {{ $batch['sloc'] ?? '-' }}
                                                        @else
                                                            <div wire:ignore>
                                                                <select class="form-control form-control-sm sloc-select"
                                                                    data-index="{{ $i }}">
                                                                    <option value="">-- Pilih Sloc --</option>
                                                                    @foreach ($slocOptions as $opt)
                                                                        <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($selectedTicket->confirmed)
                                    <div class="alert alert-success py-2">
                                        <i class="fas fa-check-circle"></i>
                                        Confirmed oleh <strong>{{ $selectedTicket->confirmed_by }}</strong>
                                        pada
                                        {{ \Carbon\Carbon::parse($selectedTicket->confirmed_at)->format('d-m-Y H:i') }}
                                    </div>
                                @endif
                            @endif

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="strong">Machine Name</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedTicket->machine_name }}" readonly>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="strong">Machine Code</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedTicket->machine_code }}" readonly>
                                </div>
                            </div>

                            {{-- Recipe Items - dinonaktifkan sementara
                            @if (!empty($selectedTicket->recipe_items))
                                ...
                            @endif
                            --}}

                            {{-- Used Stock - dinonaktifkan sementara
                            @if (!empty($selectedTicket->used_stock))
                                ...
                            @endif
                            --}}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary"
                                onclick="confirmBstbjPrompt()">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <style>
            #detailModal .select2-container { width: 100% !important; }
            #detailModal .select2-container .select2-selection--single {
                height: 38px;
                background-color: #fff3cd;
                border-color: #ffc107;
            }
            #detailModal .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; }
            #detailModal .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }

            /* versi kecil untuk di dalam tabel */
            #detailModal table .select2-container .select2-selection--single { height: 31px; }
            #detailModal table .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 29px; font-size: 12px; }
            #detailModal table .select2-container--default .select2-selection--single .select2-selection__arrow { height: 29px; }
        </style>

        <script>
            // ===== Auto-load Select2 kalau layout belum punya =====
            // Kalau server TIDAK punya akses internet, download file-nya ke public/vendor/select2/
            // lalu ganti 2 URL di bawah jadi '/vendor/select2/select2.min.css' dan '/vendor/select2/select2.min.js'
            const SELECT2_CSS = 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css';
            const SELECT2_JS  = 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js';

            function ensureSelect2(callback) {
                if ($.fn.select2) {
                    callback();
                    return;
                }

                if (!document.getElementById('select2-css')) {
                    const link = document.createElement('link');
                    link.id = 'select2-css';
                    link.rel = 'stylesheet';
                    link.href = SELECT2_CSS;
                    document.head.appendChild(link);
                }

                const script = document.createElement('script');
                script.src = SELECT2_JS;
                script.onload = callback;
                script.onerror = function() {
                    console.error('Gagal load Select2 dari: ' + SELECT2_JS);
                };
                document.head.appendChild(script);
            }

            function initSlocSelect() {
                ensureSelect2(renderSlocSelect);
            }

            // Aktifkan Select2 (searchable) untuk dropdown Sloc di modal
            function renderSlocSelect() {
                $('#detailModal .sloc-select').each(function() {
                    const $el = $(this);
                    const idx = $el.data('index');

                    if ($el.hasClass('select2-hidden-accessible')) {
                        $el.select2('destroy');
                    }

                    // PENTING: dropdownParent harus .modal-content (di dalam .modal-dialog),
                    // BUKAN #detailModal. Kalau dropdown ditempel langsung ke elemen .modal,
                    // klik pada opsi bisa dianggap klik backdrop oleh Bootstrap sehingga
                    // modal ikut tertutup saat memilih Sloc.
                    $el.select2({
                        dropdownParent: $el.closest('.modal-content'),
                        width: '100%',
                        placeholder: '-- Pilih Sloc --',
                        allowClear: true
                    });

                    // nilai awal dari Livewire
                    const current = @this.get('slocInputs.' + idx);
                    $el.val(current ?? '').trigger('change.select2');

                    // sync balik ke Livewire (tanpa request, terkirim saat confirmBstbj)
                    $el.off('change.livewire').on('change.livewire', function() {
                        @this.set('slocInputs.' + idx, $(this).val() || '', false);
                    });
                });
            }

            // Delegated, karena #detailModal baru ada di DOM setelah selectedTicket terisi
            $(document).off('shown.bs.modal.sloc', '#detailModal')
                .on('shown.bs.modal.sloc', '#detailModal', function() {
                    initSlocSelect();
                });

            document.addEventListener('livewire:initialized', function() {
                Livewire.on('showDetailModal', () => {
                    setTimeout(() => {
                        $('#detailModal').modal('show');
                    }, 100);
                });

                // Setelah modal benar-benar selesai ditutup (animasi kelar, backdrop hilang),
                // baru reset state di server. Ini mencegah backdrop nyangkut.
                $('#detailModal').on('hidden.bs.modal', function() {
                    @this.call('resetSelectedTicket');
                });

                // Dipanggil dari PHP setelah confirmBstbj() selesai
                Livewire.on('bstbjConfirmed', () => {
                    $('#detailModal').modal('hide');
                });

                // Dipanggil dari PHP kalau pengiriman ke Kartu Stok gagal.
                // Tiket tetap terbuka; tombol Confirm diaktifkan lagi.
                Livewire.on('bstbjError', ({ message }) => {
                    $('#detailModal .modal-footer .btn-primary').prop('disabled', false);
                    swal('Gagal kirim ke Kartu Stok', message, 'error');
                });
            });

            function confirmBstbjPrompt() {
                swal({
                    title: "Are you sure?",
                    text: "Data BSTBJ ini akan di-confirm dan dipindahkan ke Historical BSTBJ. Aksi ini tidak bisa dibatalkan.",
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: "Cancel",
                            value: false,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Yes, Confirm",
                            value: true,
                            visible: true,
                            closeModal: true,
                        }
                    },
                    dangerMode: false,
                }).then((result) => {
                    if (result) {
                        // Cegah klik ganda: tombol dimatikan sampai proses selesai
                        $('#detailModal .modal-footer .btn-primary').prop('disabled', true);
                        @this.call('confirmBstbj');
                    }
                });
            }
        </script>
    @endpush