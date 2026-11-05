<?php

namespace App\Livewire;

use App\Models\ManualBstbj;
use App\Models\HistoricalBstbj;
use App\Models\Ticketing;
use App\Models\Machine;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Create BSTBJ')]
class CreateBstbj extends Component
{
    public $department;

    public $spk_no;
    public $tgl_produksi;
    public $customer;
    public $machine_code;
    public $keterangan;

    // Customer (dropdown dari pgsql_vw -> vw_customer)
    public $customerResults = [];
    public $showCustomerDropdown = false;

    // Material utama
    public $material = '';
    public $materialSearch = '';
    public $materialResults = [];
    public $showMaterialDropdown = false;

    public $batches = [
        ['batch_tank' => '', 'qty' => '', 'size' => '', 'uom' => ''],
    ];

    // Recipe Items & Used Stock - dinonaktifkan sementara, tapi struktur tetap disimpan
    public $recipeItems = [
        ['material_code' => '', 'material_desc' => '', 'qty_request' => '', 'nomor_reservasi' => ''],
    ];
    public $recipeItemSearch = [''];
    public $recipeItemResults = [[]];
    public $recipeItemShowDropdown = [false];

    public $usedStock = [
        ['material' => '', 'qty_use' => '', 'unit' => ''],
    ];
    public $usedStockSearch = [''];
    public $usedStockResults = [[]];
    public $usedStockShowDropdown = [false];

    public $machine_name = '';
    public $machineSearch = '';
    public $machineResults = [];
    public $showMachineDropdown = false;

    public $uomOptions = ['BAG', 'KG', 'PC', 'LITER', 'DRUM', 'JC'];

    public function mount($department)
    {
        if (!in_array($department, ['specialty', 'powder', 'liquid', 'hinco'])) {
            abort(404);
        }
        $this->department = $department;
    }

    // ================= MACHINE =================

    public function loadMachineDefaults()
    {
        if (empty($this->machineResults) && trim($this->machineSearch) === '') {
            $this->machineResults = $this->searchMachines();
        }
    }

    protected function getAllMachines()
    {
        return Cache::remember('bstbj:all_machines', 3600, function () {
            $machines = collect();

            // 1. Dari collection ticketing_production.ticketings (MongoDB Ticketing)
            try {
                $tList = Ticketing::raw(function ($collection) {
                    return $collection->aggregate([
                        ['$match' => ['machine_name' => ['$exists' => true, '$ne' => null, '$ne' => '']]],
                        ['$group' => ['_id' => ['machine_code' => '$machine_code', 'machine_name' => '$machine_name']]],
                    ]);
                });
                foreach ($tList as $t) {
                    $id = (array) $t->_id;
                    if (!empty($id['machine_name'])) {
                        $machines->push([
                            'machine_code' => trim((string) ($id['machine_code'] ?? '')),
                            'machine_name' => trim((string) ($id['machine_name'] ?? '')),
                        ]);
                    }
                }
            } catch (\Throwable $e) {}

            // 2. Dari manual_bstbjs
            try {
                $mList = ManualBstbj::raw(function ($collection) {
                    return $collection->aggregate([
                        ['$match' => ['machine_name' => ['$exists' => true, '$ne' => null, '$ne' => '']]],
                        ['$group' => ['_id' => ['machine_code' => '$machine_code', 'machine_name' => '$machine_name']]],
                    ]);
                });
                foreach ($mList as $m) {
                    $id = (array) $m->_id;
                    if (!empty($id['machine_name'])) {
                        $machines->push([
                            'machine_code' => trim((string) ($id['machine_code'] ?? '')),
                            'machine_name' => trim((string) ($id['machine_name'] ?? '')),
                        ]);
                    }
                }
            } catch (\Throwable $e) {}

            // 3. Dari historical_bstbjs
            try {
                $hList = HistoricalBstbj::raw(function ($collection) {
                    return $collection->aggregate([
                        ['$match' => ['machine_name' => ['$exists' => true, '$ne' => null, '$ne' => '']]],
                        ['$group' => ['_id' => ['machine_code' => '$machine_code', 'machine_name' => '$machine_name']]],
                    ]);
                });
                foreach ($hList as $h) {
                    $id = (array) $h->_id;
                    if (!empty($id['machine_name'])) {
                        $machines->push([
                            'machine_code' => trim((string) ($id['machine_code'] ?? '')),
                            'machine_name' => trim((string) ($id['machine_name'] ?? '')),
                        ]);
                    }
                }
            } catch (\Throwable $e) {}

            // 4. Fallback ke model Machine jika collection machines suatu saat diisi
            try {
                $rawMachines = Machine::query()->select('machine_code', 'machine_name')->get();
                foreach ($rawMachines as $m) {
                    if (!empty($m->machine_name)) {
                        $machines->push([
                            'machine_code' => trim((string) $m->machine_code),
                            'machine_name' => trim((string) $m->machine_name),
                        ]);
                    }
                }
            } catch (\Throwable $e) {}

            return $machines
                ->unique(fn($item) => $item['machine_code'] . '|' . $item['machine_name'])
                ->sortBy('machine_name', SORT_NATURAL | SORT_FLAG_CASE)
                ->map(fn($item) => (object) $item)
                ->values()
                ->all();
        });
    }

    protected function searchMachines($keyword = null)
    {
        $all = collect($this->getAllMachines());

        if ($keyword) {
            $kw = mb_strtolower(trim($keyword));
            $all = $all->filter(function ($m) use ($kw) {
                return str_contains(mb_strtolower($m->machine_code), $kw)
                    || str_contains(mb_strtolower($m->machine_name), $kw);
            });
        }

        return $all->values()->all();
    }

    public function openMachineDropdown()
    {
        if (trim($this->machineSearch) === '') {
            $this->machineResults = $this->searchMachines();
        }
        $this->showMachineDropdown = true;
    }

    public function updatedMachineSearch()
    {
        if (trim($this->machineSearch) === '') {
            $this->machine_name = '';
            $this->machine_code = '';
            $this->machineResults = $this->searchMachines();
            return;
        }

        $this->machineResults = $this->searchMachines($this->machineSearch);
        $this->showMachineDropdown = true;
    }

    public function selectMachine($machineCode, $machineName)
    {
        $this->machine_name = $machineName;
        $this->machine_code = $machineCode;
        $this->machineSearch = $machineCode . ' - ' . $machineName;
        $this->showMachineDropdown = false;
        $this->machineResults = [];
    }

    // ================= CUSTOMER =================

    protected function searchCustomers($keyword = null)
    {
        $keyword = trim((string) $keyword);
        $limit = $keyword !== '' ? 15 : 5;

        return Cache::remember('bstbj:cust:' . md5(mb_strtolower($keyword)), 600, function () use ($keyword, $limit) {
            // ASUMSI nama kolom: customer_number & customer_name.
            $query = DB::connection('pgsql_vw')
                ->table('vw_customer')
                ->select('customer_number', 'customer_name');

            if ($keyword !== '') {
                $like = '%' . $keyword . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('customer_name', 'ilike', $like)
                        ->orWhere('customer_number', 'ilike', $like);
                });
            }

            // TANPA DISTINCT / ORDER BY (tabel 2.3 juta baris): ambil lebih, dedupe di PHP
            return $query->limit($limit * 3)->get()
                ->unique('customer_name')
                ->take($limit)
                ->values()
                ->toArray();
        });
    }

    public function openCustomerDropdown()
    {
        if (trim((string) $this->customer) === '') {
            $this->customerResults = $this->searchCustomers();
        }
        $this->showCustomerDropdown = true;
    }

    public function updatedCustomer()
    {
        if (trim((string) $this->customer) === '') {
            $this->customerResults = $this->searchCustomers();
            $this->showCustomerDropdown = true;
            return;
        }

        if (strlen($this->customer) < 3) {
            return;
        }

        $this->customerResults = $this->searchCustomers($this->customer);
        $this->showCustomerDropdown = true;
    }

    public function selectCustomer($index)
    {
        $name = data_get($this->customerResults[$index] ?? null, 'customer_name');

        if ($name) {
            $this->customer = strtoupper(trim($name));
        }

        $this->showCustomerDropdown = false;
        $this->customerResults = [];
    }

    protected function searchMaterials($keyword = null)
    {
        $keyword = trim((string) $keyword);
        $limit = $keyword !== '' ? 15 : 10;

        return Cache::remember('bstbj:mat:' . md5(mb_strtolower($keyword)), $keyword === '' ? 3600 : 600, function () use ($keyword, $limit) {
            // Ambil dari vw_material, lalu cukup CEK ADA di BOM (EXISTS)
            // -> tidak perlu JOIN + DISTINCT + ORDER BY di 1.5 juta baris
            $query = DB::connection('pgsql_vw')
                ->table('vw_material as mat')
                ->select('mat.material_number', 'mat.material_desc', 'mat.uom', 'mat.size')
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('vw_bom_sap as bom')
                        ->whereRaw('TRIM(bom.product_name_decrypt) = TRIM(mat.material_number)');
                });

            if ($keyword !== '') {
                $like = '%' . $keyword . '%';
                $query->where(function ($q) use ($like) {
                    $q->where('mat.material_number', 'ilike', $like)
                        ->orWhere('mat.material_desc', 'ilike', $like);
                });
            }

            return $query->limit($limit * 3)->get()
                ->unique('material_number')
                ->take($limit)
                ->values()
                ->toArray();
        });
    }

    // ================= MATERIAL UTAMA =================

    // Dipanggil otomatis lewat wire:init setelah halaman tampil (tidak menahan loading halaman)
    public function loadMaterialDefaults()
    {
        if (empty($this->materialResults) && trim($this->materialSearch) === '') {
            $this->materialResults = $this->searchMaterials();
        }
    }

    public function openMaterialDropdown()
    {
        if (trim($this->materialSearch) === '') {
            $this->materialResults = $this->searchMaterials();
        }
        $this->showMaterialDropdown = true;
    }

    public function updatedMaterialSearch()
    {
        if (trim($this->materialSearch) === '') {
            $this->material = '';
            $this->materialResults = $this->searchMaterials();
            return;
        }

        if (strlen($this->materialSearch) < 2) {
            return;
        }

        $this->materialResults = $this->searchMaterials($this->materialSearch);
        $this->showMaterialDropdown = true;
    }

    public function selectMaterial($materialNumber, $materialDesc, $uom, $size)
    {
        $this->material = $materialNumber;
        $this->materialSearch = ltrim($materialNumber, '0') . ' - ' . $materialDesc;
        $this->showMaterialDropdown = false;
        $this->materialResults = [];

        if ($this->batches[0]['size'] === '' || $this->batches[0]['size'] === null) {
            $this->batches[0]['size'] = $size;
        }
        if (empty($this->batches[0]['uom'])) {
            $this->batches[0]['uom'] = $uom;
        }
    }

    // ================= RECIPE ITEMS (nonaktif sementara) =================

    public function openRecipeDropdown($index)
    {
        if (trim($this->recipeItemSearch[$index] ?? '') === '') {
            $this->recipeItemResults[$index] = $this->searchMaterials();
        }
        $this->recipeItemShowDropdown[$index] = true;
    }

    protected function searchRecipeMaterial($index)
    {
        $keyword = $this->recipeItemSearch[$index] ?? '';

        if (trim($keyword) === '') {
            $this->recipeItems[$index]['material_code'] = '';
            $this->recipeItems[$index]['material_desc'] = '';
            $this->recipeItemResults[$index] = $this->searchMaterials();
            return;
        }

        if (strlen($keyword) < 2) {
            return;
        }

        $this->recipeItemResults[$index] = $this->searchMaterials($keyword);
        $this->recipeItemShowDropdown[$index] = true;
    }

    public function selectRecipeMaterial($index, $materialNumber, $materialDesc)
    {
        $this->recipeItems[$index]['material_code'] = $materialNumber;
        $this->recipeItems[$index]['material_desc'] = $materialDesc;
        $this->recipeItemSearch[$index] = ltrim($materialNumber, '0') . ' - ' . $materialDesc;
        $this->recipeItemShowDropdown[$index] = false;
        $this->recipeItemResults[$index] = [];
    }

    public function addRecipeItem()
    {
        $this->recipeItems[] = ['material_code' => '', 'material_desc' => '', 'qty_request' => '', 'nomor_reservasi' => ''];
        $this->recipeItemSearch[] = '';
        $this->recipeItemResults[] = [];
        $this->recipeItemShowDropdown[] = false;
    }

    public function removeRecipeItem($index)
    {
        unset($this->recipeItems[$index], $this->recipeItemSearch[$index], $this->recipeItemResults[$index], $this->recipeItemShowDropdown[$index]);
        $this->recipeItems = array_values($this->recipeItems);
        $this->recipeItemSearch = array_values($this->recipeItemSearch);
        $this->recipeItemResults = array_values($this->recipeItemResults);
        $this->recipeItemShowDropdown = array_values($this->recipeItemShowDropdown);
    }

    // ================= USED STOCK (nonaktif sementara) =================

    public function openUsedStockDropdown($index)
    {
        if (trim($this->usedStockSearch[$index] ?? '') === '') {
            $this->usedStockResults[$index] = $this->searchMaterials();
        }
        $this->usedStockShowDropdown[$index] = true;
    }

    protected function searchUsedStockMaterial($index)
    {
        $keyword = $this->usedStockSearch[$index] ?? '';

        if (trim($keyword) === '') {
            $this->usedStock[$index]['material'] = '';
            $this->usedStockResults[$index] = $this->searchMaterials();
            return;
        }

        if (strlen($keyword) < 2) {
            return;
        }

        $this->usedStockResults[$index] = $this->searchMaterials($keyword);
        $this->usedStockShowDropdown[$index] = true;
    }

    public function selectUsedStockMaterial($index, $materialNumber, $materialDesc)
    {
        $this->usedStock[$index]['material'] = $materialNumber;
        $this->usedStockSearch[$index] = ltrim($materialNumber, '0') . ' - ' . $materialDesc;
        $this->usedStockShowDropdown[$index] = false;
        $this->usedStockResults[$index] = [];
    }

    public function addUsedStock()
    {
        $this->usedStock[] = ['material' => '', 'qty_use' => '', 'unit' => ''];
        $this->usedStockSearch[] = '';
        $this->usedStockResults[] = [];
        $this->usedStockShowDropdown[] = false;
    }

    public function removeUsedStock($index)
    {
        unset($this->usedStock[$index], $this->usedStockSearch[$index], $this->usedStockResults[$index], $this->usedStockShowDropdown[$index]);
        $this->usedStock = array_values($this->usedStock);
        $this->usedStockSearch = array_values($this->usedStockSearch);
        $this->usedStockResults = array_values($this->usedStockResults);
        $this->usedStockShowDropdown = array_values($this->usedStockShowDropdown);
    }

    // ================= BATCH =================

    public function addBatch()
    {
        $this->batches[] = ['batch_tank' => '', 'qty' => '', 'size' => '', 'uom' => ''];
    }

    public function removeBatch($index)
    {
        unset($this->batches[$index]);
        $this->batches = array_values($this->batches);
    }

    public function updated($property, $value)
    {
        if (str_starts_with($property, 'recipeItemSearch.')) {
            $index = (int) str_replace('recipeItemSearch.', '', $property);
            $this->searchRecipeMaterial($index);
        }

        if (str_starts_with($property, 'usedStockSearch.')) {
            $index = (int) str_replace('usedStockSearch.', '', $property);
            $this->searchUsedStockMaterial($index);
        }
    }

    protected function rules()
    {
        return [
            'spk_no' => 'required|numeric',
            'tgl_produksi' => 'required|date',
            'customer' => 'required|string',
            'material' => 'required|string',
            'machine_name' => 'required|string',
            'machine_code' => 'required|string',
            'batches.*.batch_tank' => 'required|string',
            'batches.*.qty' => 'required|numeric',
            'batches.*.size' => 'required|string',
            'batches.*.uom' => 'required|string',
        ];
    }

    public function save()
    {
        $this->validate();

        $this->customer = strtoupper($this->customer);
        $this->batches = array_map(function ($batch) {
            $batch['batch_tank'] = strtoupper($batch['batch_tank']);
            $batch['size'] = strtoupper($batch['size']);
            $batch['qty_confirm'] = null;
            return $batch;
        }, $this->batches);

        ManualBstbj::create([
            'spk_no' => $this->spk_no,
            'tgl_produksi' => $this->tgl_produksi,
            'customer' => $this->customer,
            'material' => $this->material,
            'machine_name' => $this->machine_name,
            'machine_code' => $this->machine_code,
            'keterangan' => $this->keterangan,
            'batches' => $this->batches,
            'recipe_items' => $this->recipeItems,
            'used_stock' => $this->usedStock,
            'department' => $this->department,
            'status' => 'bstbj',
            'progress' => 0,
            'confirmed' => false,
            'confirmed_by' => null,
            'confirmed_at' => null,
        ]);

        session()->flash('message', 'BSTBJ ' . strtoupper($this->department) . ' berhasil dibuat.');

        return redirect()->route('outstanding-bstbj');
    }

    public function render()
    {
        return view('livewire.create-bstbj');
    }
}