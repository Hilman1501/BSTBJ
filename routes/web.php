<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Monitoring;
use App\Livewire\OutstandingBstbj;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Livewire\SelectDepartment;
use App\Livewire\CreateBstbj;
use App\Livewire\HistoricalBstbjList;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware('web')->group(function () {
    Route::get('/login-by-hmac', [AuthController::class, 'loginByHmac']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/monitoring', Monitoring::class)->name('monitoring');

Route::get('/outstanding-bstbj', OutstandingBstbj::class)->name('outstanding-bstbj');

Route::get('/create-bstbj', SelectDepartment::class)->name('create-bstbj.select');

Route::get('/historical-bstbj', HistoricalBstbjList::class)->name('historical-bstbj');

Route::get('/create-bstbj/{department}', CreateBstbj::class)
    ->where('department', 'specialty|powder|liquid|hinco')
    ->name('create-bstbj.form');

Route::get('/dashboard', function () {
    return view('index', ['title' => 'Dashboard']);
})->name('dashboard');
