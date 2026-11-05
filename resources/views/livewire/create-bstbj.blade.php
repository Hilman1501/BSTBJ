<div>
    <div class="panel-header bg-primary-gradient">
        <div class="page-inner py-5">
            <h2 class="text-white pb-2 fw-bold">Create BSTBJ - {{ ucfirst($department) }}</h2>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form onsubmit="return false;">

                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>SPK No*</label>
                                    <input type="text" wire:model="spk_no" class="form-control" inputmode="numeric"
                                        onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                    @error('spk_no')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Tanggal Produksi*</label>
                                    <input type="date" wire:model="tgl_produksi" class="form-control">
                                    @error('tgl_produksi')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- CUSTOMER (dropdown + bisa ketik, data dari vw_customer) --}}
                                <div class="col-md-4 form-group position-relative" x-data="{ open: false }"
                                    @click.outside="open = false">
                                    <label>Customer*</label>
                                    <input type="text" wire:model.live.debounce.400ms="customer"
                                        @click="open = true; if (!$wire.customer) $wire.openCustomerDropdown()"
                                        @input="open = true" class="form-control" style="text-transform: uppercase;"
                                        placeholder="Cari / ketik nama customer..." autocomplete="off">
                                    <small class="text-muted" wire:loading wire:target="customer,openCustomerDropdown">Mencari...</small>
                                    @error('customer')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                    @if (count($customerResults) > 0)
                                        <div x-show="open" class="list-group position-absolute w-100 shadow"
                                            style="z-index: 1050; max-height: 250px; overflow-y: auto;">
                                            @foreach ($customerResults as $i => $result)
                                                <button type="button" class="list-group-item list-group-item-action text-left"
                                                    wire:key="customer-{{ $i }}" @click="open = false"
                                                    wire:click="selectCustomer({{ $i }})">
                                                    <strong>{{ ltrim($result->customer_number, '0') }}</strong> —
                                                    {{ $result->customer_name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif (strlen(trim((string) $customer)) >= 3)
                                        <div x-show="open" class="list-group position-absolute w-100 shadow" style="z-index: 1050;">
                                            <div class="list-group-item text-muted">
                                                Customer tidak ditemukan (nama yang diketik tetap bisa dipakai).
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- MATERIAL --}}
                                <div class="col-md-4 form-group position-relative" x-data="{ open: false }"
                                    wire:init="loadMaterialDefaults" @click.outside="open = false">
                                    <label>Material*</label>
                                    <input type="text" wire:model.live.debounce.400ms="materialSearch"
                                        @focus="open = true"
                                        @click="open = true; if (!$wire.materialSearch && !$wire.materialResults.length) $wire.openMaterialDropdown()"
                                        @input="open = true" class="form-control"
                                        placeholder="Cari kode atau nama material..." autocomplete="off">
                                    <small class="text-muted" wire:loading wire:target="materialSearch,openMaterialDropdown">Mencari...</small>
                                    @error('material')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                    @if (count($materialResults) > 0)
                                        <div x-show="open" class="list-group position-absolute w-100 shadow"
                                            style="z-index: 1050; max-height: 250px; overflow-y: auto;">
                                            @foreach ($materialResults as $i => $result)
                                                <button type="button" class="list-group-item list-group-item-action text-left"
                                                    wire:key="material-{{ $i }}" @click="open = false"
                                                    wire:click="selectMaterial('{{ $result->material_number }}', '{{ addslashes($result->material_desc) }}', '{{ $result->uom }}', '{{ $result->size }}')">
                                                    <strong>{{ ltrim($result->material_number, '0') }}</strong> —
                                                    {{ $result->material_desc }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif (strlen(trim((string) $materialSearch)) >= 2)
                                        <div x-show="open" class="list-group position-absolute w-100 shadow" style="z-index: 1050;">
                                            <div class="list-group-item text-muted">Material tidak ditemukan.</div>
                                        </div>
                                    @endif
                                </div>

                                {{-- MACHINE --}}
                                <div class="col-md-4 form-group position-relative" x-data="{ open: false }"
                                    wire:init="loadMachineDefaults" @click.outside="open = false">
                                    <label>Machine Name*</label>
                                    <input type="text" wire:model.live.debounce.400ms="machineSearch"
                                        @focus="open = true"
                                        @click="open = true; if (!$wire.machineSearch && !$wire.machineResults.length) $wire.openMachineDropdown()"
                                        @input="open = true" class="form-control"
                                        placeholder="Cari kode atau nama mesin..." autocomplete="off">
                                    <small class="text-muted" wire:loading wire:target="machineSearch,openMachineDropdown">Mencari...</small>
                                    @error('machine_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror

                                    @if (count($machineResults) > 0)
                                        <div x-show="open" class="list-group position-absolute w-100 shadow"
                                            style="z-index: 1050; max-height: 250px; overflow-y: auto;">
                                            @foreach ($machineResults as $i => $result)
                                                <button type="button" class="list-group-item list-group-item-action text-left"
                                                    wire:key="machine-{{ $i }}" @click="open = false"
                                                    wire:click="selectMachine('{{ $result->machine_code }}', '{{ addslashes($result->machine_name) }}')">
                                                    <strong>{{ $result->machine_code }}</strong> —
                                                    {{ $result->machine_name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif (strlen(trim((string) $machineSearch)) >= 2)
                                        <div x-show="open" class="list-group position-absolute w-100 shadow" style="z-index: 1050;">
                                            <div class="list-group-item text-muted">Mesin tidak ditemukan.</div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-4 form-group">
                                    <label>Machine Code</label>
                                    <input type="text" wire:model="machine_code" class="form-control" readonly>
                                </div>

                                {{-- <div class="col-md-6 form-group">
                                    <label>Nomor GR</label>
                                    <input type="text" wire:model="nomor_gr" class="form-control">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Nomor GI</label>
                                    <input type="text" wire:model="nomor_gi" class="form-control">
                                </div> --}}

                                <div class="col-md-12 form-group">
                                    <label>Keterangan</label>
                                    <textarea wire:model="keterangan" class="form-control" rows="2"></textarea>
                                </div>
                            </div>

                            <hr>

                            <!-- Batch / Tank / QTY / Size / UOM -->
                            <h5 class="fw-bold mb-3">Batch/Tank | QTY | Size | UOM</h5>
                            <div class="row mb-2 d-none d-md-flex">
                                <div class="col-md-3"><label>Batch/Tank*</label></div>
                                <div class="col-md-2"><label>QTY*</label></div>
                                <div class="col-md-2"><label>Size</label></div>
                                <div class="col-md-3"><label>UOM*</label></div>
                            </div>
                            @foreach ($batches as $index => $batch)
                                <div class="row mb-2 align-items-center">
                                    <div class="col-md-3">
                                        <input type="text" wire:model="batches.{{ $index }}.batch_tank"
                                            class="form-control" placeholder="Batch/Tank"
                                            style="text-transform: uppercase;"
                                            oninput="this.value = this.value.toUpperCase()">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" wire:model="batches.{{ $index }}.qty"
                                            class="form-control" placeholder="QTY">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" wire:model="batches.{{ $index }}.size"
                                            class="form-control" placeholder="Size" style="text-transform: uppercase;"
                                            oninput="this.value = this.value.toUpperCase()">
                                    </div>
                                    <div class="col-md-3">
                                        <select wire:model="batches.{{ $index }}.uom" class="form-control">
                                            <option value="">-- UOM --</option>
                                            @foreach ($uomOptions as $uom)
                                                <option value="{{ $uom }}">{{ $uom }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        @if (count($batches) > 1)
                                            <button type="button" class="btn btn-danger btn-sm"
                                                wire:click="removeBatch({{ $index }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <button type="button" class="btn btn-success btn-sm mb-4" wire:click="addBatch">
                                <i class="fas fa-plus"></i> Add
                            </button>

                            {{-- <hr> --}}

                            <!-- Recipe Items -->
                            {{-- <h5 class="fw-bold mb-3">Recipe Items</h5>
                            @foreach ($recipeItems as $index => $item)
                                <div class="row mb-2 align-items-center">
                                    <div class="col-md-3 position-relative" x-data
                                        @click.outside="$wire.set('recipeItemShowDropdown.{{ $index }}', false)">
                                        <input type="text"
                                            wire:model.live.debounce.300ms="recipeItemSearch.{{ $index }}"
                                            wire:click="openRecipeDropdown({{ $index }})" class="form-control"
                                            placeholder="Cari Material Code" autocomplete="off">

                                        @if (($recipeItemShowDropdown[$index] ?? false) && count($recipeItemResults[$index] ?? []) > 0)
                                            <div class="list-group position-absolute w-100 shadow"
                                                style="z-index: 1050; max-height: 220px; overflow-y: auto;">
                                                @foreach ($recipeItemResults[$index] as $result)
                                                    <button type="button"
                                                        class="list-group-item list-group-item-action text-left"
                                                        wire:click="selectRecipeMaterial({{ $index }}, '{{ $result->material_number }}', '{{ addslashes($result->material_desc) }}')">
                                                        <strong>{{ ltrim($result->material_number, '0') }}</strong> —
                                                        {{ $result->material_desc }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" value="{{ $item['material_desc'] }}"
                                            class="form-control bg-light" placeholder="Material Desc" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text"
                                            wire:model="recipeItems.{{ $index }}.qty_request"
                                            class="form-control" placeholder="Qty Request"
                                            style="text-transform: uppercase;"
                                            oninput="this.value = this.value.toUpperCase()">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text"
                                            wire:model="recipeItems.{{ $index }}.nomor_reservasi"
                                            class="form-control" placeholder="Nomor Reservasi">
                                    </div>
                                    <div class="col-md-1">
                                        @if (count($recipeItems) > 1)
                                            <button type="button" class="btn btn-danger btn-sm"
                                                wire:click="removeRecipeItem({{ $index }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <button type="button" class="btn btn-success btn-sm mb-4" wire:click="addRecipeItem">
                                <i class="fas fa-plus"></i> Add
                            </button> --}}

                            {{-- <hr> --}}

                            <!-- Used Stock -->
                            {{-- <h5 class="fw-bold mb-3">Used Stock</h5>
                            @foreach ($usedStock as $index => $stock)
                                <div class="row mb-2 align-items-center">
                                    <div class="col-md-5 position-relative" x-data
                                        @click.outside="$wire.set('usedStockShowDropdown.{{ $index }}', false)">
                                        <input type="text"
                                            wire:model.live.debounce.300ms="usedStockSearch.{{ $index }}"
                                            wire:click="openUsedStockDropdown({{ $index }})"
                                            class="form-control" placeholder="Cari Material" autocomplete="off">

                                        @if (($usedStockShowDropdown[$index] ?? false) && count($usedStockResults[$index] ?? []) > 0)
                                            <div class="list-group position-absolute w-100 shadow"
                                                style="z-index: 1050; max-height: 220px; overflow-y: auto;">
                                                @foreach ($usedStockResults[$index] as $result)
                                                    <button type="button"
                                                        class="list-group-item list-group-item-action text-left"
                                                        wire:click="selectUsedStockMaterial({{ $index }}, '{{ $result->material_number }}', '{{ addslashes($result->material_desc) }}')">
                                                        <strong>{{ ltrim($result->material_number, '0') }}</strong> —
                                                        {{ $result->material_desc }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" wire:model="usedStock.{{ $index }}.qty_use"
                                            class="form-control" placeholder="Qty Use"
                                            style="text-transform: uppercase;"
                                            oninput="this.value = this.value.toUpperCase()">
                                    </div>
                                    <div class="col-md-3">
                                        <select wire:model="usedStock.{{ $index }}.unit" class="form-control">
                                            <option value="">-- Unit --</option>
                                            @foreach ($uomOptions as $uom)
                                                <option value="{{ $uom }}">{{ $uom }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        @if (count($usedStock) > 1)
                                            <button type="button" class="btn btn-danger btn-sm"
                                                wire:click="removeUsedStock({{ $index }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <button type="button" class="btn btn-success btn-sm mb-4" wire:click="addUsedStock">
                                <i class="fas fa-plus"></i> Add
                            </button> --}}

                            <hr>

                            <button type="button" class="btn btn-primary" onclick="confirmSaveBstbj()">Submit
                                BSTBJ</button>
                            <a href="{{ route('create-bstbj.select') }}" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        function confirmSaveBstbj() {
            swal({
                title: "Are you sure?",
                text: "BSTBJ ini akan disimpan dan masuk ke Outstanding BSTBJ.",
                icon: "warning",
                buttons: {
                    cancel: {
                        text: "Cancel",
                        value: false,
                        visible: true,
                        closeModal: true,
                    },
                    confirm: {
                        text: "Yes, Simpan",
                        value: true,
                        visible: true,
                        closeModal: true,
                    }
                },
                dangerMode: false,
            }).then((result) => {
                if (result) {
                    @this.call('save');
                }
            });
        }
    </script>
@endpush