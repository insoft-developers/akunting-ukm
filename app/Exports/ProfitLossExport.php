<?php

namespace App\Exports;

use App\Models\Journal;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProfitLossExport implements FromView
{
    
    public $details;
    public $awal;
    public $akhir;
    public function __construct($details, $awal, $akhir) {
        $this->details = $details;
        $this->awal = $awal;
        $this->akhir = $akhir;
    }
    
    
    public function view(): View
    {
        return view('export.profit_loss', [
            'data' => $this->details,
            'awal' => $this->awal,
            'akhir' => $this->akhir
        ]);
    }
}
