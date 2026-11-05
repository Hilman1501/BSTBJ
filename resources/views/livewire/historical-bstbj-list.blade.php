<div>
    <div class="panel-header bg-primary-gradient">
        <div class="page-inner py-5">
            <div>
                <h2 class="text-white pb-2 fw-bold">Historical BSTBJ</h2>
                <h5 class="text-white op-7 mb-2">List data BSTBJ yang sudah dikonfirmasi</h5>
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
                            <div class="col-6 col-md-3 mb-2 mb-md-0">
                                <div class="d-flex align-items-center">
                                    <span class="mr-2">Department</span>
                                    <select wire:model.live="filterDepartment" class="form-control form-control-sm"
                                        style="width: auto; display: inline-block;">
                                        <option value="">All</option>
                                        <option value="specialty">Specialty</option>
                                        <option value="hinco">Hinco</option>
                                        <option value="liquid">Liquid</option>
                                        <option value="powder">Powder</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
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
                                        <th>Qty Production</th>
                                        <th>Qty Confirm</th>
                                        <th>UOM</th>
                                        <th>Size</th>
                                        <th>Nomor Batch</th>
                                        <th>Tanggal Produksi</th>
                                        <th>Nama Mesin</th>
                                        <th>Customer</th>
                                        <th>Confirmed By</th>
                                        <th style="cursor: pointer;" wire:click="sortBy('confirmed_at')">Confirmed At
                                        </th>
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
                                                    wire:click="openDetail('{{ $ticket->id }}')" title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                            <td><span
                                                    class="badge badge-{{ $deptBadge[$ticket->department] ?? 'secondary' }}">{{ ucfirst($ticket->department) }}</span>
                                            </td>
                                            <td><span class="badge badge-success">{{ ucfirst($ticket->status) }}</span></td>
                                            <td>{{ $ticket->spk_no }}</td>
                                            <td>{{ ltrim($ticket->material, '0') }}</td>
                                            <td>{{ $ticket->quantity }}</td>
                                            <td>{{ $ticket->qty_confirm }}</td>
                                            <td>{{ $ticket->uom }}</td>
                                            <td>{{ $ticket->size }}</td>
                                            <td>{{ $ticket->batch_number }}</td>
                                            <td>{{ $ticket->tgl_produksi ? \Carbon\Carbon::parse($ticket->tgl_produksi)->format('d-m-Y') : '-' }}
                                            </td>
                                            <td>{{ $ticket->machine_name }}</td>
                                            <td>{{ $ticket->customer }}</td>
                                            <td>{{ $ticket->confirmed_by }}</td>
                                            <td>{{ $ticket->confirmed_at ? \Carbon\Carbon::parse($ticket->confirmed_at)->format('d-m-Y H:i') : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="15" class="text-center">Belum ada data historical BSTBJ.</td>
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

    <!-- Modal Detail (read-only, tanpa tombol Confirm) -->
    @if ($selectedTicket)
        <div wire:ignore.self class="modal fade" id="detailModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">Detail Historical BSTBJ -
                            {{ ucfirst($selectedTicket->department) }}</h5>
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
                                    value="{{ ltrim($selectedTicket->material, '0') }}" readonly>
                            </div>
                        </div>

                        @if ($selectedTicket->source === 'manual')
                            <div class="form-group">
                                <label class="strong">Batch / Tank</label>
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Batch/Tank</th>
                                            <th>Qty Production</th>
                                            <th>Size</th>
                                            <th>UOM</th>
                                            <th>Qty Confirm</th>
                                            <th>Qty Selisih</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($selectedTicket->batches ?? [] as $batch)
                                            @php
                                                $selisih =
                                                    is_numeric($batch['qty'] ?? null) &&
                                                    is_numeric($batch['qty_confirm'] ?? null)
                                                        ? $batch['qty'] - $batch['qty_confirm']
                                                        : '-';
                                            @endphp
                                            <tr>
                                                <td>{{ $batch['batch_tank'] ?? '-' }}</td>
                                                <td>{{ $batch['qty'] ?? '-' }}</td>
                                                <td>{{ $batch['size'] ?? '-' }}</td>
                                                <td>{{ $batch['uom'] ?? '-' }}</td>
                                                <td>{{ $batch['qty_confirm'] ?? '-' }}</td>
                                                <td
                                                    class="{{ is_numeric($selisih) && $selisih > 0 ? 'text-danger font-weight-bold' : '' }}">
                                                    {{ $selisih }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label class="strong">Qty Request</label>
                                    <input type="text" class="form-control" value="{{ $selectedTicket->quantity }}"
                                        readonly>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label class="strong">Qty Confirm</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedTicket->qty_confirm ?? '-' }}" readonly>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label class="strong">Kemasan</label>
                                    <input type="text" class="form-control"
                                        value="{{ $selectedTicket->kemasan }}" readonly>
                                </div>
                            </div>
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

                        <div class="alert alert-success py-2 mb-0">
                            <i class="fas fa-check-circle"></i>
                            Status <strong>{{ ucfirst($selectedTicket->status ?? 'closed') }}</strong>.
                            Confirmed oleh <strong>{{ $selectedTicket->confirmed_by }}</strong>
                            pada {{ \Carbon\Carbon::parse($selectedTicket->confirmed_at)->format('d-m-Y H:i') }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                            wire:click="closeModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('showDetailModal', () => {
                setTimeout(() => {
                    $('#detailModal').modal('show');
                }, 100);
            });
            Livewire.on('closeDetailModal', () => {
                $('#detailModal').modal('hide');
            });
        });
    </script>
@endpush
