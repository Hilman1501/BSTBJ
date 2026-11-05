<?php

namespace App\Livewire;

use App\Models\Ticketing;
use App\Models\TicketingPowder;
use App\Models\ManualBstbj;
use App\Models\HistoricalBstbj;
use App\Services\KartuStokService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Outstanding BSTBJ')]
class OutstandingBstbj extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $selectedTicket = null;
    public $selectedSource = null;
    public $qtyConfirmInputs = [];
    public $slocInputs = [];
    public $slocOptions = [];
    public $sortField = 'spk_no';
    public $sortDirection = 'asc';

    public function mount()
    {
        $this->loadSlocOptions();
    }

    protected function loadSlocOptions()
    {
        // Hanya sloc dengan sloc_grp INTERNAL.
        // distinct() supaya tidak dobel kalau sloc yang sama ada di beberapa plant.
        $this->slocOptions = DB::connection('pgsql_vw')
            ->table('vw_sloc')
            ->where('sloc_grp', 'INTERNAL')
            ->select('sloc', 'sloc_name')
            ->distinct()
            ->orderBy('sloc')
            ->get()
            ->map(fn($r) => [
                'value' => $r->sloc,
                'label' => "{$r->sloc} - {$r->sloc_name}"
            ])
            ->toArray();
    }

    public function resetSelectedTicket()
    {
        $this->selectedTicket = null;
        $this->selectedSource = null;
        $this->qtyConfirmInputs = [];
        $this->slocInputs = [];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Source yang datanya berasal dari collection ticketing
     * (specialty = ticketings, powder = ticketings_powder).
     * Keduanya memakai struktur dokumen dan form detail yang sama.
     */
    protected function isTicketingSource($source): bool
    {
        return in_array($source, ['ticketing', 'ticketing_powder'], true);
    }

    public function openDetail($source, $id)
    {
        $ticket = match ($source) {
            'manual' => ManualBstbj::find($id),
            'ticketing_powder' => TicketingPowder::find($id),
            default => Ticketing::find($id),
        };

        if (!$ticket) {
            return;
        }

        $this->selectedTicket = $ticket;
        $this->selectedSource = $source;

        if ($source === 'manual') {
            $this->qtyConfirmInputs = collect($this->selectedTicket->batches ?? [])
                ->map(fn($b) => $b['qty_confirm'] ?? $b['qty'] ?? '')
                ->toArray();

            $this->slocInputs = collect($this->selectedTicket->batches ?? [])
                ->map(fn($b) => $b['sloc'] ?? '')
                ->toArray();
        } else {
            $this->qtyConfirmInputs = [$this->selectedTicket->quantity ?? ''];
            $this->slocInputs = [$this->selectedTicket->sloc ?? ''];
        }

        $this->dispatch('showDetailModal');
    }

    public function closeModal()
    {
        $this->selectedTicket = null;
        $this->selectedSource = null;
        $this->qtyConfirmInputs = [];
        $this->slocInputs = [];
        $this->dispatch('closeDetailModal');
    }

    /**
     * Kirim penambahan stok Barang Jadi ke Kartu Stok (KS) lewat API.
     * Melempar exception kalau gagal, supaya confirmBstbj() berhenti
     * dan tiket TIDAK ikut ditutup.
     */
    protected function sendToKartuStok(): void
    {
        $t = $this->selectedTicket;
        $date = now()->toDateString();

        if ($this->selectedSource === 'manual') {
            // Satu item per batch (tiap batch punya qty dan sloc sendiri).
            $items = [];
            foreach ($t->batches ?? [] as $i => $batch) {
                $items[] = [
                    'spk_number' => (string) $t->spk_no,
                    'material_code' => (string) $t->material,
                    's_loc' => $this->slocInputs[$i] ?? null,
                    'qty' => $this->qtyConfirmInputs[$i] ?? null,
                    'unit' => strtolower((string) $t->department),
                    'uom' => $batch['uom'] ?? null,
                    'packing' => $batch['size'] ?? null,
                    'actual_transaction_date' => $date,
                ];
            }
        } else {
            // 'ticketing' (specialty) atau 'ticketing_powder' (powder)
            $items = [[
                'spk_number' => (string) $t->spk_no,
                'material_code' => (string) $t->material_code,
                's_loc' => $this->slocInputs[0] ?? null,
                'qty' => $this->qtyConfirmInputs[0] ?? null,
                'unit' => $this->selectedSource === 'ticketing_powder' ? 'powder' : 'specialty',
                'packing' => $t->kemasan ?? null,
                'actual_transaction_date' => $date,
            ]];
        }

        app(KartuStokService::class)->addMany($items);
    }

    public function confirmBstbj()
    {
        if (!$this->selectedTicket) {
            return;
        }

        // Langkah 1: tambah stok di Kartu Stok. Kalau gagal, berhenti di sini
        // (tiket tetap terbuka dan tidak masuk Historical).
        try {
            $this->sendToKartuStok();
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('bstbjError', message: $e->getMessage());
            return;
        }

        $confirmedBy = auth()->user()->name ?? 'Unknown';
        $confirmedAt = now();
        $newStatus = 'closed';

        if ($this->selectedSource === 'manual') {
            $batches = $this->selectedTicket->batches ?? [];

            foreach ($batches as $i => $batch) {
                // Data hasil konfirmasi
                $batches[$i]['qty_confirm'] = $this->qtyConfirmInputs[$i] ?? null;
                $batches[$i]['sloc'] = $this->slocInputs[$i] ?? null;

                // Status RPA per batch
                $batches[$i]['status_rpa'] = 'On Progress SAP';

                // Default execution RPA per batch
                $batches[$i]['rpa_execution'] = 0;
            }

            // Update data Manual BSTBJ
            $this->selectedTicket->batches = $batches;
            $this->selectedTicket->status = $newStatus;
            $this->selectedTicket->confirmed = true;
            $this->selectedTicket->confirmed_by = $confirmedBy;
            $this->selectedTicket->confirmed_at = $confirmedAt;
            $this->selectedTicket->save();

            // Simpan ke Historical BSTBJ
            // status_rpa dan rpa_execution berada di masing-masing batch,
            // bukan di level luar dokumen.
            HistoricalBstbj::create([
                'source' => 'manual',
                'original_id' => (string) $this->selectedTicket->_id,
                'department' => $this->selectedTicket->department,
                'spk_no' => $this->selectedTicket->spk_no,
                'customer' => $this->selectedTicket->customer,
                'material' => $this->selectedTicket->material,
                'machine_name' => $this->selectedTicket->machine_name,
                'machine_code' => $this->selectedTicket->machine_code,
                'keterangan' => $this->selectedTicket->keterangan,
                'batches' => $batches,
                'tgl_produksi' => $this->selectedTicket->tgl_produksi,
                'confirmed_by' => $confirmedBy,
                'confirmed_at' => $confirmedAt,
                'status' => $newStatus,
            ]);
        } else {
            // 'ticketing' (specialty) atau 'ticketing_powder' (powder)
            HistoricalBstbj::create([
                'source' => $this->selectedSource,
                'original_id' => (string) $this->selectedTicket->_id,
                'department' => $this->selectedSource === 'ticketing_powder' ? 'powder' : 'specialty',
                'spk_no' => $this->selectedTicket->spk_no,
                'customer' => $this->selectedTicket->customer,
                'material' => $this->selectedTicket->material_code,
                'machine_name' => $this->selectedTicket->machine_name,
                'machine_code' => $this->selectedTicket->machine_code,
                'quantity' => $this->selectedTicket->quantity,
                'qty_confirm' => $this->qtyConfirmInputs[0] ?? null,
                'sloc' => $this->slocInputs[0] ?? null,
                'kemasan' => $this->selectedTicket->kemasan,
                'tgl_produksi' => $this->selectedTicket->tgl_buat,
                'confirmed_by' => $confirmedBy,
                'confirmed_at' => $confirmedAt,
                'status' => $newStatus,
            ]);
        }

        session()->flash(
            'message',
            'BSTBJ berhasil di-confirm dan dipindahkan ke Historical BSTBJ.'
        );

        $this->dispatch('bstbjConfirmed');
    }

    protected function normalizeTicketing($ticket, string $source = 'ticketing', string $department = 'specialty')
    {
        return (object) [
            'id' => (string) $ticket->_id,
            'source' => $source,
            'department' => $department,
            'spk_no' => $ticket->spk_no,
            'material' => $ticket->material_code,
            'quantity' => $ticket->quantity,
            'uom' => '-',
            'size' => $ticket->kemasan ?? '-',
            'batch_number' => $ticket->batch_number ? [$ticket->batch_number] : [],
            'tgl_produksi' => $ticket->tgl_buat ?? null,
            'machine_name' => $ticket->machine_name,
            'machine_code' => $ticket->machine_code,
            'customer' => $ticket->customer,
            'status' => 'waiting for approval logistic',
        ];
    }

    protected function normalizeManual($ticket)
    {
        $batches = $ticket->batches ?? [];

        return (object) [
            'id' => (string) $ticket->_id,
            'source' => 'manual',
            'department' => $ticket->department,
            'spk_no' => $ticket->spk_no,
            'material' => $ticket->material,
            'quantity' => collect($batches)->sum('qty'),
            'uom' => collect($batches)
                ->pluck('uom')
                ->filter()
                ->unique()
                ->implode(', ') ?: '-',
            'size' => collect($batches)
                ->pluck('size')
                ->filter()
                ->implode(', ') ?: '-',
            'batch_number' => collect($batches)
                ->pluck('batch_tank')
                ->filter()
                ->values()
                ->toArray(),
            'tgl_produksi' => $ticket->tgl_produksi ?? null,
            'machine_name' => $ticket->machine_name,
            'machine_code' => $ticket->machine_code,
            'customer' => $ticket->customer,
            'status' => 'waiting for approval logistic',
        ];
    }

    /**
     * Ambil tiket yang menunggu BSTBJ dari satu collection ticketing.
     * Dipakai bersama oleh specialty (ticketings) dan powder (ticketings_powder).
     */
    protected function getWaitingTickets(
        string $modelClass,
        string $source,
        string $department,
        ?\Closure $filter = null
    ) {
        // Yang sudah dikonfirmasi (tercatat di historical) perlu di-exclude,
        // karena datanya di collection ticketing sendiri tidak bisa kita ubah.
        $confirmedIds = HistoricalBstbj::where('source', $source)
            ->pluck('original_id')
            ->toArray();

        // Default (specialty): batch berstatus waiting_gr_gi.
        $filter ??= fn($q) => $q->where(
            'partial_productions.batch_status',
            'waiting_gr_gi'
        );

        return $filter($modelClass::query())
            ->whereNotIn('_id', $confirmedIds)
            ->get()
            ->map(fn($t) => $this->normalizeTicketing($t, $source, $department));
    }

    /**
     * Filter khusus powder. Di ticketings_powder, status batch ada di
     * partial_productions.status (bukan batch_status seperti specialty),
     * dan status dokumen berubah jadi 'bstbj' saat sampai tahap BSTBJ.
     * Salah satu kondisi di bawah cukup untuk menampilkan tiketnya.
     */
    protected function powderFilter(): \Closure
    {
        return fn($q) => $q->where(function ($w) {
            $w->where('status', 'bstbj')
                ->orWhere('partial_productions.batch_status', 'waiting_gr_gi')
                ->orWhere('partial_productions.status', 'waiting_gr_gi');
        });
    }

    protected function getAllTickets()
    {
        $ticketing = $this->getWaitingTickets(Ticketing::class, 'ticketing', 'specialty');
        $powder = $this->getWaitingTickets(
            TicketingPowder::class,
            'ticketing_powder',
            'powder',
            $this->powderFilter()
        );

        $manual = ManualBstbj::where('status', 'bstbj')
            ->get()
            ->map(fn($t) => $this->normalizeManual($t));

        return $ticketing->concat($powder)->concat($manual);
    }

    public function render()
    {
        $all = $this->getAllTickets();

        if ($this->search) {
            $search = strtolower($this->search);

            $all = $all->filter(function ($t) use ($search) {
                return str_contains(
                    strtolower((string) $t->spk_no),
                    $search
                )
                || str_contains(
                    strtolower((string) $t->customer),
                    $search
                )
                || str_contains(
                    strtolower((string) $t->material),
                    $search
                );
            });
        }

        $all = $all
            ->sortBy(
                $this->sortField,
                SORT_REGULAR,
                $this->sortDirection === 'desc'
            )
            ->values();

        $page = $this->getPage();

        $items = $all
            ->slice(
                ($page - 1) * $this->perPage,
                $this->perPage
            )
            ->values();

        $tickets = new LengthAwarePaginator(
            $items,
            $all->count(),
            $this->perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query()
            ]
        );

        return view('livewire.outstanding-bstbj', [
            'tickets' => $tickets,
        ]);
    }
}