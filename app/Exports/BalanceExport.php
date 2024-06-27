<?php

namespace App\Exports;

use App\Models\Journal;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BalanceExport implements FromView
{
    
    public $details;
    public $awal;
    public $akhir;
    public $laba;
    public function __construct($details, $awal, $akhir, $laba) {
        $this->details = $details;
        $this->awal = $awal;
        $this->akhir = $akhir;
        $this->laba = $laba;
    }
    
    
    public function view(): View
    {
        return view('export.balance_sheet', [
            'dt' => $this->details,
            'awal' => $this->awal,
            'akhir' => $this->akhir,
            'laba_bersih' => $this->laba
        ]);
    }
}
