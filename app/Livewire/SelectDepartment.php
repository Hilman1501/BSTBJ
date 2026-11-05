<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class SelectDepartment extends Component
{
    public function render()
    {
        return view('livewire.select-department');
    }
}