<div>
    <div class="panel-header bg-primary-gradient">
        <div class="page-inner py-5">
            <div>
                <h2 class="text-white pb-2 fw-bold">Create BSTBJ</h2>
                <h5 class="text-white op-7 mb-2">Pilih department terlebih dahulu</h5>
            </div>
        </div>
    </div>

    <div class="page-inner mt--5">
        <div class="row justify-content-center">
            <div class="col-md-3 mb-3">
                <a href="{{ route('create-bstbj.form', ['department' => 'specialty']) }}" class="text-decoration-none">
                    <div class="card full-height text-center py-5">
                        <i class="fas fa-flask fa-3x mb-3 text-danger"></i>
                        <h4 class="fw-bold">Specialty</h4>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('create-bstbj.form', ['department' => 'powder']) }}" class="text-decoration-none">
                    <div class="card full-height text-center py-5">
                        <i class="fas fa-mortar-pestle fa-3x mb-3 text-primary"></i>
                        <h4 class="fw-bold">Powder</h4>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('create-bstbj.form', ['department' => 'liquid']) }}" class="text-decoration-none">
                    <div class="card full-height text-center py-5">
                        <i class="fas fa-tint fa-3x mb-3 text-info"></i>
                        <h4 class="fw-bold">Liquid</h4>
                    </div>
                </a>
            </div>
            <div class="col-md-3 mb-3">
                <a href="{{ route('create-bstbj.form', ['department' => 'hinco']) }}" class="text-decoration-none">
                    <div class="card full-height text-center py-5">
                        <i class="fas fa-industry fa-3x mb-3 text-success"></i>
                        <h4 class="fw-bold">Hinco</h4>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
