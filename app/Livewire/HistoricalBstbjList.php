<?php

namespace App\Livewire;

use App\Models\HistoricalBstbj;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Historical BSTBJ')]
class HistoricalBstbjList extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $filterDepartment = '';
    public $selectedTicket = null;
    public $sortField = 'confirmed_at';
    public $sortDirection = 'desc';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterDepartment()
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

    public function openDetail($id)
    {
        $this->selectedTicket = HistoricalBstbj::find($id);
        $this->dispatch('showDetailModal');
    }

    public function closeModal()
    {
        $this->selectedTicket = null;
        $this->dispatch('closeDetailModal');
    }

    protected function normalize($ticket)
    {
        if ($ticket->source === 'ticketing') {
            $quantity = $ticket->quantity;
            $qtyConfirm = $ticket->qty_confirm ?? '-';
            $uom = '-';
            $size = $ticket->kemasan ?? '-';
            $batchNumber = '-';
        } else {
            $batches = $ticket->batches ?? [];
            $quantity = collect($batches)->sum('qty');
            $qtyConfirm = collect($batches)->pluck('qty_confirm')->filter()->implode(', ') ?: '-';
            $uom = collect($batches)->pluck('uom')->filter()->unique()->implode(', ') ?: '-';
            $size = collect($batches)->pluck('size')->filter()->implode(', ') ?: '-';
            $batchNumber = collect($batches)->pluck('batch_tank')->filter()->implode(', ') ?: '-';
        }

        return (object) [
            'id' => (string) $ticket->_id,
            'source' => $ticket->source,
            'status' => $ticket->status ?? 'closed',
            'department' => $ticket->department,
            'spk_no' => $ticket->spk_no,
            'material' => $ticket->material,
            'quantity' => $quantity,
            'qty_confirm' => $qtyConfirm,
            'uom' => $uom,
            'size' => $size,
            'batch_number' => $batchNumber,
            'tgl_produksi' => $ticket->tgl_produksi,
            'machine_name' => $ticket->machine_name,
            'machine_code' => $ticket->machine_code,
            'customer' => $ticket->customer,
            'confirmed_by' => $ticket->confirmed_by,
            'confirmed_at' => $ticket->confirmed_at,
            'status_rpa' => $ticket->status_rpa ?? 'On Progress SAP',
            'rpa_execution' => $ticket->rpa_execution ?? 0,
        ];
    }

    public function render()
    {
        $all = HistoricalBstbj::query()
            ->when($this->filterDepartment, fn($q) => $q->where('department', $this->filterDepartment))
            ->get()
            ->map(fn($t) => $this->normalize($t));

        if ($this->search) {
            $search = strtolower($this->search);
            $all = $all->filter(function ($t) use ($search) {
                return str_contains(strtolower((string) $t->spk_no), $search)
                    || str_contains(strtolower((string) $t->customer), $search)
                    || str_contains(strtolower((string) $t->material), $search);
            });
        }

        $all = $all->sortBy($this->sortField, SORT_REGULAR, $this->sortDirection === 'desc')->values();

        $page = $this->getPage();
        $items = $all->slice(($page - 1) * $this->perPage, $this->perPage)->values();

        $tickets = new LengthAwarePaginator(
            $items,
            $all->count(),
            $this->perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.historical-bstbj-list', [
            'tickets' => $tickets,
        ]);
    }
}
