<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Validator;
use App\Exports\ProfitLossExport;
use App\Exports\BalanceExport;
use Maatwebsite\Excel\Facades\Excel;


class ReportController extends Controller
{
    public function index() {
        $view = 'report';
        return view('main.report',compact('view'));
    }


    public function list_account() 
    {
        $data['income'] = DB::table('ml_income')->where('userid', session('id'))->where('account_code_id', 7)->orderBy('id')->get();
        $data['hpp'] = DB::table('ml_cost_good_sold')->where('userid', session('id'))->where('account_code_id', 8)->orderBy('id')->get();
        $data['selling_cost'] = DB::table('ml_selling_cost')->where('userid', session('id'))->where('account_code_id', 9)->orderBy('id')->get();
        $data['general_fees'] = DB::table('ml_admin_general_fees')->where('userid', session('id'))->where('account_code_id', 10)->orderBy('id')->get();
        $data['non_business_income'] = DB::table('ml_non_business_income')->where('userid', session('id'))->where('account_code_id', 11)->orderBy('id')->get();
        $data['non_business_cost'] = DB::table('ml_non_business_expenses')->where('userid', session('id'))->where('account_code_id', 12)->orderBy('id')->get();

        return $data;
    }


    public function list_balance_account() 
    {
        $data['aktiva_lancar'] = DB::table('ml_current_assets')->where('userid', session('id'))->where('account_code_id', 1)->orderBy('id')->get();
        $data['aktiva_tetap'] = DB::table('ml_fixed_assets')->where('userid', session('id'))->where('account_code_id', 2)->orderBy('id')->get();
        $data['utang_pendek'] = DB::table('ml_shortterm_debt')->where('userid', session('id'))->where('account_code_id', 4)->orderBy('id')->get();
        $data['utang_panjang'] = DB::table('ml_longterm_debt')->where('userid', session('id'))->where('account_code_id', 5)->orderBy('id')->get();
        $data['modal'] = DB::table('ml_capital')->where('userid', session('id'))->where('account_code_id', 6)->orderBy('id')->get();
        
        return $data;
    }


    public function profit_loss()
	{
		$view = 'profit-loss';
        $data = $this->list_account();
        return view('main.profit_loss_report', compact('view', 'data'));
	}

    public function submit_profit_loss(Request $request) {
        $input = $request->all();
        
        $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, $input['month_to'], $input['year_to']);

        $start = $input['year_from'].'-'.$input['month_from'].'-01';
        $end = $input['year_to'].'-'.$input['month_to'].'-'.$tanggal_akhir;
        $awal = strtotime($start);
        $akhir = strtotime($end);
        
        if($start > $end) {
            return response()->json([
                "success" => false,
                "message" => "Period To can not bigger than Period From"
            ]);
        }
        
        $data = $this->list_account();
        $html = "";
        $html .= '<table class="table table-bordered" id="table-profit-loss">';
        $html .= '<tr>';
        $html .= '<th rowspan="2"><center>Keterangan</center></th>';
        $html .= '<th colspan="2"><center>'.date('F Y', strtotime($start)).' sd '.date('F Y', strtotime($end)).'</center></th>';    
        $html .= '</tr>'; 
        $html .= '<tr>';
        $html .= '<th>*</th>';
        $html .= '<th>*</th>';
        $html .= '</tr>';
        $html .= '<tr><td colspan="3" style="border-top:2px solid black;"><strong>Pendapatan</strong></td></tr>';
            
            $total_income = 0;
            
            foreach($data['income'] as $i) {
            
                $income = DB::table('ml_journal_list')
                    ->where('asset_data_id', $i->id)
                    ->where('account_code_id', 7)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('credit - debet'));
                $total_income = $total_income + $income;
           
                $html .= '<tr>';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;'.$i->name.'</td>';
                $html .= '<td style="text-align:right;">'.number_format($income).'</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            }

            $html .= '<tr>';
            $html .= '<td><strong>Pendapatan Bersih</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;">'.number_format($total_income).'</td>';
            $html .= '</tr>';
            $html .= '<tr><td colspan="3"><strong>Harga Pokok Penjualan</strong></td></tr>';
           
            $total_hpp = 0;
            
            foreach($data['hpp'] as $a) {
            
                $hpp = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 8)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('debet-credit'));
                $total_hpp = $total_hpp + $hpp;
            
                $html .= '<tr>';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;'.$a->name.'</td>';
                $html .= '<td style="text-align:right;">('.number_format($hpp).')</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            }

            $html .= '<tr>';
            $html .= '<td><strong>Total Harga Pokok Penjualan</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;">('.number_format($total_hpp).')</td>';
            $html .= '</tr>';
           
            $laba_rugi_kotor = $total_income - $total_hpp;
           
            $html .= '<tr>';
            $html .= '<td><strong>LABA/RUGI KOTOR</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;"><strong>'.number_format($laba_rugi_kotor) .'</strong></td>';
            $html .= '</tr>';
            $html .= '<tr><td colspan="3"><strong>Biaya Penjualan</strong></td></tr>';
            
            $total_selling_cost = 0;
            
            foreach($data['selling_cost'] as $a) {
            
                $selling_cost = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 9)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('debet-credit'));
                $total_selling_cost = $total_selling_cost + $selling_cost;
            
                $html .= '<tr>';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;'. $a->name .'</td>';
                $html .= '<td style="text-align:right;">('. number_format($selling_cost) .')</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            }
            $html .= '<tr>';
            $html .= '<td><strong>Total Biaya Penjualan</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;">('. number_format($total_selling_cost) .')</td>';
            $html .= '</tr>';
            $html .= '<tr><td colspan="3"><strong>Biaya Umum Admin</strong></td></tr>';
            $total_general_fees = 0;
            foreach($data['general_fees'] as $a) {
                $general_fees = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 10)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('debet-credit'));
                $total_general_fees = $total_general_fees + $general_fees;
            
                $html .= '<tr>';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;'. $a->name .'</td>';
                $html .= '<td style="text-align:right;">('. number_format($general_fees) .')</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            }
            
            $html .= '<tr>';
            $html .= '<td><strong>Total Biaya Admin dan Umum</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;">('. number_format($total_general_fees) .')</td>';
            $html .= '</tr>';

            $html .= '<tr><td colspan="3"><strong>Pendapatan Diluar Usaha</strong></td></tr>';
           
            $total_nb_income = 0;
           
            foreach($data['non_business_income'] as $a) {
            
                $nb_income = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 11)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('credit-debet'));
                $total_nb_income = $total_nb_income + $nb_income;
                $html .= '<tr>';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;'. $a->name .'</td>';
                $html .= '<td style="text-align:right;">'. number_format($nb_income) .'</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            
            }
            $html .= '<tr>';
            $html .= '<td><strong>Total Pendapatan Diluar Usaha</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;">'. number_format($total_nb_income) .'</td>';
            $html .= '</tr>';


            $html .= '<tr><td colspan="3"><strong>Biaya Diluar Usaha</strong></td></tr>';
            $total_nb_cost = 0;
            foreach($data['non_business_cost'] as $a) {
                $nb_cost = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 12)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('debet-credit'));
                $total_nb_cost = $total_nb_cost + $nb_cost;
                $html .= '<tr>';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp;'. $a->name .'</td>';
                $html .= '<td style="text-align:right;">('. number_format($nb_cost) .')</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            }
            $html .= '<tr>';
            $html .= '<td><strong>Total Biaya Diluar Usaha</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;">('. number_format($total_nb_cost) .')</td>';
            $html .= '</tr>';
            
                $laba_bersih = $laba_rugi_kotor - $total_selling_cost - $total_general_fees + $total_nb_income - $total_nb_cost;
            $html .= '<tr>';
            $html .= '<td><strong>LABA/RUGI BERSIH</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;"><strong>'. number_format($laba_bersih) .'</strong></td>';
            $html .= '</tr>';
        $html .= '</table>';

        return response()->json([
            "success" => true,
            "data" => $html
        ]);
    }


    public function balance() {
        $view = "balance-sheet";
        $data = $this->list_account();
        $dt = $this->list_balance_account();
        $laba_bersih = $this->count_net_profit(date('m'), date('Y'), date('m'), date('Y'));
        return view('main.balance_sheet', compact('view','data', 'dt','laba_bersih'));
    }

    public function count_net_profit($m_from, $y_from, $m_to, $y_to) {  
        $start = $y_from.'-'.$m_from.'-01';
       
        $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, $m_to, $y_to);
        $end = $y_to.'-'.$m_to.'-'.$tanggal_akhir;
        $awal = strtotime($start);
        $akhir = strtotime($end);
        
        
        $data = $this->list_account();
        $total_income = 0;
            
        foreach($data['income'] as $i) {
        
            $income = DB::table('ml_journal_list')
                ->where('asset_data_id', $i->id)
                ->where('account_code_id', 7)
                ->where('created', '>=', $awal)
                ->where('created', '<=', $akhir)
                ->sum(\DB::raw('credit - debet'));
            $total_income = $total_income + $income;
        
        }

        $total_hpp = 0;
        
        foreach($data['hpp'] as $a) {
        
            $hpp = DB::table('ml_journal_list')
                ->where('asset_data_id', $a->id)
                ->where('account_code_id', 8)
                ->where('created', '>=', $awal)
                ->where('created', '<=', $akhir)
                ->sum(\DB::raw('debet-credit'));
            $total_hpp = $total_hpp + $hpp;
        
            
        }

        $laba_rugi_kotor = $total_income - $total_hpp;
        $total_selling_cost = 0;
        
        foreach($data['selling_cost'] as $a) {
        
            $selling_cost = DB::table('ml_journal_list')
                ->where('asset_data_id', $a->id)
                ->where('account_code_id', 9)
                ->where('created', '>=', $awal)
                ->where('created', '<=', $akhir)
                ->sum(\DB::raw('debet-credit'));
            $total_selling_cost = $total_selling_cost + $selling_cost;
        }
        
        $total_general_fees = 0;
        foreach($data['general_fees'] as $a) {
            $general_fees = DB::table('ml_journal_list')
                ->where('asset_data_id', $a->id)
                ->where('account_code_id', 10)
                ->where('created', '>=', $awal)
                ->where('created', '<=', $akhir)
                ->sum(\DB::raw('debet-credit'));
            $total_general_fees = $total_general_fees + $general_fees;
        
            
        }
          
        $total_nb_income = 0;
        
        foreach($data['non_business_income'] as $a) {
        
            $nb_income = DB::table('ml_journal_list')
                ->where('asset_data_id', $a->id)
                ->where('account_code_id', 11)
                ->where('created', '>=', $awal)
                ->where('created', '<=', $akhir)
                ->sum(\DB::raw('credit-debet'));
            $total_nb_income = $total_nb_income + $nb_income;
            
        
        }
        
        $total_nb_cost = 0;
        foreach($data['non_business_cost'] as $a) {
            $nb_cost = DB::table('ml_journal_list')
                ->where('asset_data_id', $a->id)
                ->where('account_code_id', 12)
                ->where('created', '>=', $awal)
                ->where('created', '<=', $akhir)
                ->sum(\DB::raw('debet-credit'));
            $total_nb_cost = $total_nb_cost + $nb_cost;
            
        }
           
        $laba_bersih = $laba_rugi_kotor - $total_selling_cost - $total_general_fees + $total_nb_income - $total_nb_cost;
           

        return $laba_bersih;
        
    }


    public function submit_balance_sheet(Request $request) {
        $input = $request->all();
        
        $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, $input['month_to'], $input['year_to']);
        

        $start = $input['year_from'].'-'.$input['month_from'].'-01';
        $end = $input['year_to'].'-'.$input['month_to'].'-'.$tanggal_akhir;
        
        $awal = strtotime($start);
        $akhir = strtotime($end);
        
        if($start > $end) {
            return response()->json([
                "success" => false,
                "message" => "Period To can not bigger than Period From"
            ]);
        }  
        
        $dt = $this->list_balance_account();
        $laba_bersih = $this->count_net_profit($input['month_from'], $input['year_from'], $input['month_to'], $input['year_to']);

        $html = "";
        $html .= '<table class="table table-bordered" id="table-profit-loss">';
        $html .= '<tr>';
        $html .= '<th rowspan="2"><center>Keterangan</center></th>';
        $html .= '<th colspan="2"><center>'.date('F Y', strtotime($start)).' sd '.date('F Y', strtotime($end)).'</center></th>'; 
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<th>*</th>';
        $html .= '<th>*</th>';
        $html .= '</tr>';
        $html .= '<tr><td colspan="3" style="border-top:2px solid black;"><strong>Aktiva Lancar</strong></td></tr>';
            $total_lancar = 0;
            foreach($dt['aktiva_lancar'] as $i)
            {
                $lancar = DB::table('ml_journal_list')
                    ->where('asset_data_id', $i->id)
                    ->where('account_code_id', 1)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('debet - credit'));
                $total_lancar = $total_lancar + $lancar;
            
                $html .= '<tr>';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp; '.$i->name.' </td>';
                $html .= '<td style="text-align:right;">'.number_format($lancar).' </td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            }
            $html .= '<tr>';
            $html .= '<td><strong>Total Aktiva Lancar</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;"> '.number_format($total_lancar).' </td>';
            $html .= '</tr>';
            $html .= '<tr><td colspan="3"><strong>Aktiva Tetap</strong></td></tr>';
            
            $total_tetap = 0;
            foreach($dt['aktiva_tetap'] as $a)
            {
                $tetap = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 2)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('debet-credit'));
                $total_tetap = $total_tetap + $tetap;
           
                $html .= '<tr>';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp; '.$a->name.' </td>';
                $html .= '<td style="text-align:right;">'.number_format($tetap).' </td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            }
            $html .= '<tr>';
            $html .= '<td><strong>Total Aktiva Tetap</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;">'.number_format($total_tetap).'</td>';
            $html .= '</tr>';
            
            $total_aktiva = $total_lancar + $total_tetap;
            
            $html .= '<tr>';
            $html .= '<td><strong>TOTAL AKTIVA</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;"><strong> '.number_format($total_aktiva).' </strong></td>';
            $html .= '</tr>';
            $html .= '<tr><td colspan="3"><strong>Utang Jangka Pendek</strong></td></tr>';
        
            $total_pendek = 0;
            
            foreach($dt['utang_pendek'] as $a)
            {
                $pendek = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 4)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('credit-debet'));
                $total_pendek = $total_pendek + $pendek;
            
                $html .= '<tr>';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp; '.$a->name.' </td>';
                $html .= '<td style="text-align:right;">'.number_format($pendek).'</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
                
            }
            $html .= '<tr>';
            $html .= '<td><strong>Total Utang Jangka Pendek</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;">'.number_format($total_pendek).'</td>';
            $html .= '</tr>';
            $html .= '<tr><td colspan="3"><strong>Utang Jangka Panjang</strong></td></tr>';
            
            $total_panjang =0;
            foreach($dt['utang_panjang'] as $a)
            {
                $panjang = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 5)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('credit-debet'));
                $total_panjang = $total_panjang + $panjang;
            
                $html .= '<tr>';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp; '.$a->name.'</td>';
                $html .= '<td style="text-align:right;">'.number_format($panjang).'</td>';
                $html .= '<td></td>';
                $html .= '</tr>';
                    
            }
            $html .= '<tr>';
            $html .= '<td><strong>Total Utang Jangka Panjang</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;">'.number_format($total_panjang).'</td>';
            $html .= '</tr>';

            $html .= '<tr><td colspan="3"><strong>Modal</strong></td></tr>';
           
            $total_modal = 0;
            
            foreach($dt['modal'] as $a)
            {
                $modal = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 6)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('credit-debet'));
                $total_modal = $total_modal + $modal;
            
                $html .= '<tr>';
                $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp; '.$a->name.'</td>';
                $html .= '<td style="text-align:right;"> '.number_format($modal).' </td>';
                $html .= '<td></td>';
                $html .= '</tr>';
            
            }
            $html .= '<td>&nbsp;&nbsp;&nbsp;&nbsp; LABA/RUGI BERSIH </td>';
            $html .= '<td style="text-align:right;"> '.number_format($laba_bersih).' </td>';
            $html .= '<td></td>';
            $html .= '<tr>';
            $html .= '<td><strong>Total Modal</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;"> '.number_format($total_modal + $laba_bersih).' </td>';
            $html .= '</tr>';
            $html .= '<tr>';
            $html .= '<td><strong>TOTAL UTANG DAN MODAL</strong></td>';
            $html .= '<td></td>';
            $html .= '<td style="text-align:right;"><strong> '.number_format($total_pendek + $total_panjang + $total_modal + $laba_bersih).'</strong></td>';
            $html .= '</tr>';
            $html .= '</table>';


            return response()->json([
                "success" => true,
                "data" => $html
            ]);
    }

    
    public function journal_report() {
        $view = "journal-report";

        $awal_bulan = date('Y-m-01');
        $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        $akhir_bulan = date('Y-m-'.$tanggal_akhir);

        $awal = strtotime($awal_bulan);
        $akhir = strtotime($akhir_bulan);
        


        $data = DB::table('ml_journal')
            ->where('userid', session('id'))
            ->where('created', '>=', $awal)
            ->where('created', '<=', $akhir)
            ->orderBy('created','asc')
            ->get();
        return view('main.journal_report', compact('view','data'));
    }

    public function journal_report_submit(Request $request) {
        $input = $request->all();
        $month_from = $input['month_from'];
        $year_from = $input['year_from'];
        $month_to = $input['month_to'];
        $year_to = $input['year_to'];

        $awal_bulan = $year_from.'-'.$month_from.'-01';
        $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, $month_to, $year_to);
        $akhir_bulan = $year_to.'-'.$month_to.'-'.$tanggal_akhir;

        $awal = strtotime($awal_bulan);
        $akhir = strtotime($akhir_bulan);

        $data = DB::table('ml_journal')
            ->where('userid', session('id'))
            ->where('created', '>=', $awal)
            ->where('created', '<=', $akhir)
            ->orderBy('created','asc')
            ->get();

        $html = "";
        $html .='<table class="table">';
        $html .='<tr>';
        $html .='<th style="border-bottom: 2px solid black;">Tanggal</th>';
        $html .='<th style="border-bottom: 2px solid black;">Keterangan</th>';
        $html .='<th style="border-bottom: 2px solid black;">Debit</th>';
        $html .='<th style="border-bottom: 2px solid black;">Kredit</th>';
        $html .='</tr>';
            
                $total_debet = 0;
                $total_credit = 0;
            
            foreach($data as $key){
            
                $detail = \App\Models\JournalList::where('journal_id', $key->id)->get();
                
        $html .='<tr>';
        $html .='<td style="background-color:whitesmoke;"></td>';
        $html .='<td style="background-color:whitesmoke;"><strong>'. $key->transaction_name .'</strong></td>';
        $html .='<td style="background-color:whitesmoke;"></td>';
        $html .='<td style="background-color:whitesmoke;"></td>';
        $html .='</tr>';

                foreach($detail as $item){
                       $total_debet = $total_debet + $item->debet;
                       $total_credit = $total_credit + $item->credit;         
        $html .='<tr>';
        $html .='<td>'. date('d-m-Y', $item->created) .'</td>';
        $html .='<td>'. $item->asset_data_name .'</td>';
        $html .='<td>'. number_format($item->debet) .'</td>';
        $html .='<td>'. number_format($item->credit) .'</td>';
        $html .='</tr>';
                }
            }
        $html .='<tr>';
        $html .='<th style="border-top:2px solid black;">Total</th>';
        $html .='<th style="border-top:2px solid black;"></th>';
        $html .='<th style="border-top:2px solid black;">'. number_format($total_debet) .'</th>';
        $html .='<th style="border-top:2px solid black;">'. number_format($total_credit) .'</th>';
        $html .='</tr>';
        $html .='<tr>';
        $html .='<th></th>';
        $html .='<th></th>';
        $html .='<th></th>';
        $html .='<th></th>';
        $html .='</tr>';
        $html .='</table>';

        return response()->json([
            "success" => true,
            "data" => $html
        ]);
    }
    

    public function trial_balance() {
        $view = 'trial-balance';
        $awal_bulan = date('Y-m-01');
        $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        $akhir_bulan = date('Y-m-'.$tanggal_akhir);

        $awal = strtotime($awal_bulan);
        $akhir = strtotime($akhir_bulan);

        $data = DB::table('ml_journal')
            ->where('userid', session('id'))
            ->where('created', '>=', $awal)
            ->where('created', '<=', $akhir)
            ->orderBy('created','asc')
            ->get();

        $dt['current_asset'] = DB::table('ml_current_assets')
            ->where('userid', session('id'))
            ->get();

        $dt['fixed_asset'] = DB::table('ml_fixed_assets')
            ->where('userid', session('id'))
            ->get();

        $dt['short_debt'] = DB::table('ml_shortterm_debt')
        ->where('userid', session('id'))
        ->get(); 
        
        $dt['long_debt'] = DB::table('ml_longterm_debt')
        ->where('userid', session('id'))
        ->get(); 

        $dt['income'] = DB::table('ml_income')
        ->where('userid', session('id'))
        ->get(); 

        $dt['cost_good'] = DB::table('ml_cost_good_sold')
        ->where('userid', session('id'))
        ->get(); 

        $dt['capital'] = DB::table('ml_capital')
        ->where('userid', session('id'))
        ->get();

        return view('main.trial_balance', compact('view','data','dt'));
    }


    public function trial_balance_submit(Request $request) {
        $input = $request->all();
        $month_from = $input['month_from'];
        $year_from = $input['year_from'];
        $month_to = $input['month_to'];
        $year_to = $input['year_to'];

        $awal_bulan = $year_from.'-'.$month_from.'-01';
        $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, $month_to, $year_to);
        $akhir_bulan = $year_to.'-'.$month_to.'-'.$tanggal_akhir;

        $awal = strtotime($awal_bulan);
        $akhir = strtotime($akhir_bulan);

        $data = DB::table('ml_journal')
            ->where('userid', session('id'))
            ->where('created', '>=', $awal)
            ->where('created', '<=', $akhir)
            ->orderBy('created','asc')
            ->get();

        $dt['current_asset'] = DB::table('ml_current_assets')
            ->where('userid', session('id'))
            ->get();

        $dt['fixed_asset'] = DB::table('ml_fixed_assets')
            ->where('userid', session('id'))
            ->get();

        $dt['short_debt'] = DB::table('ml_shortterm_debt')
        ->where('userid', session('id'))
        ->get(); 
        
        $dt['long_debt'] = DB::table('ml_longterm_debt')
        ->where('userid', session('id'))
        ->get(); 

        $dt['income'] = DB::table('ml_income')
        ->where('userid', session('id'))
        ->get(); 

        $dt['cost_good'] = DB::table('ml_cost_good_sold')
        ->where('userid', session('id'))
        ->get(); 

        $dt['capital'] = DB::table('ml_capital')
        ->where('userid', session('id'))
        ->get();

        $html = "";
        $html .= '<table class="table" id="table-trial-balance">';
        $html .= '<tr>';
        $html .= '<th style="border-bottom: 2px solid black;">Keterangan</th>';
        $html .= '<th style="border-bottom: 2px solid black;">Debit</th>';
        $html .= '<th style="border-bottom: 2px solid black;">Kredit</th>';
        $html .= '</tr>';
        
        $total_debet = 0;
        $total_credit = 0;
        
        foreach($dt['current_asset'] as $key) {
        
            $ca = DB::table('ml_journal_list')
                ->where('asset_data_id', $key->id)
                ->where('created','>=', $awal)
                ->where('created', '<=', $akhir)
                ->sum(\DB::raw('debet - credit'));

            if($ca > 0) {
                $debit = $ca;
                $kredit = 0;
            } else {
                $debit = 0;
                $kredit = $ca;
            }
            
            $total_debet = $total_debet + $debit;
            $total_credit = $total_credit + $kredit;
        
        
            $html .= '<tr>';
            $html .= '<td>'.$key->name.'</td>';
            $html .= '<td>'.number_format($debit).'</td>';
            $html .= '<td>'.number_format($kredit).'</td>';
            $html .= '</tr>';
        }

        foreach($dt['income'] as $key){
        
            $inc = DB::table('ml_journal_list')
                ->where('asset_data_id', $key->id)
                ->where('created','>=', $awal)
                ->where('created', '<=', $akhir)
                ->sum(\DB::raw('credit - debet'));

            if($inc > 0) {
                $debit = 0;
                $kredit = $inc;
            } else {
                $debit = $inc;
                $kredit = 0;
            }
            $total_debet = $total_debet + $debit;
            $total_credit = $total_credit + $kredit;   

        
            $html .= '<tr>';  
            $html .= '<td>'.$key->name.'</td>';
            $html .= '<td>'.number_format($debit).'</td>';
            $html .= '<td>'.number_format($kredit).'</td>';
            $html .= '</tr>';
        }

        foreach($dt['fixed_asset'] as $key) {
        
        $fa = DB::table('ml_journal_list')
            ->where('asset_data_id', $key->id)
            ->where('created','>=', $awal)
            ->where('created', '<=', $akhir)
            ->sum(\DB::raw('debet - credit'));

        if($fa > 0) {
            $debit = $fa;
            $kredit = 0;
        } else {
            $debit = 0;
            $kredit = $fa;
        }  
        $total_debet = $total_debet + $debit;
        $total_credit = $total_credit + $kredit; 

    
            $html .= '<tr>';  
            $html .= '<td>'.$key->name.'</td>';
            $html .= '<td>'.number_format($debit).'</td>';
            $html .= '<td>'.number_format($kredit).'</td>';
            $html .= '</tr>';
        }

        foreach($dt['cost_good'] as $key){

            $fa = DB::table('ml_journal_list')
                ->where('asset_data_id', $key->id)
                ->where('created','>=', $awal)
                ->where('created', '<=', $akhir)
                ->sum(\DB::raw('debet - credit'));

            if($fa > 0) {
                $debit = $fa;
                $kredit = 0;
            } else {
                $debit = 0;
                $kredit = $fa;
            }   
            $total_debet = $total_debet + $debit;
            $total_credit = $total_credit + $kredit;
            
            $html .= '<tr>';
            $html .= '<td>'.$key->name.'</td>';
            $html .= '<td>'.number_format($debit).'</td>';
            $html .= '<td>'.number_format($kredit).'</td>';
            $html .= '</tr>';
        }

        foreach($dt['short_debt'] as $key){
        
            $sd = DB::table('ml_journal_list')
                ->where('asset_data_id', $key->id)
                ->where('created','>=', $awal)
                ->where('created', '<=', $akhir)
                ->sum(\DB::raw('credit - debet'));

            if($sd > 0) {
                $debit = 0;
                $kredit = $sd;
            } else {
                $debit = $sd;
                $kredit = 0;
            } 
            $total_debet = $total_debet + $debit;
            $total_credit = $total_credit + $kredit;  
        
        
            $html .= '<tr>'; 
            $html .= '<td>'.$key->name.'</td>';
            $html .= '<td>'.number_format($debit).'</td>';
            $html .= '<td>'.number_format($kredit).'</td>';
            $html .= '</tr>';
        }

        foreach($dt['long_debt'] as $key){
        
            $ld = DB::table('ml_journal_list')
                ->where('asset_data_id', $key->id)
                ->where('created','>=', $awal)
                ->where('created', '<=', $akhir)
                ->sum(\DB::raw('credit - debet'));

            if($ld > 0) {
                $debit = 0;
                $kredit = $ld;
            } else {
                $debit = $ld;
                $kredit = 0;
            }   
            $total_debet = $total_debet + $debit;
            $total_credit = $total_credit + $kredit;
        
            $html .= '<tr>';  
            $html .= '<td>'.$key->name.'</td>';
            $html .= '<td>'.number_format($debit).'</td>';
            $html .= '<td>'.number_format($kredit).'</td>';
            $html .= '</tr>';
        }


        foreach($dt['capital'] as $key){
        
            $nd = DB::table('ml_journal_list')
                ->where('asset_data_id', $key->id)
                ->where('created','>=', $awal)
                ->where('created', '<=', $akhir);

            if($key->code == 'prive') {
                $ld = $nd->sum(\DB::raw('debet - credit'));
                if($ld > 0) {
                    $debit = $ld;
                    $kredit = 0;
                } else {
                    $debit = 0;
                    $kredit = $ld;
                }  
            } else {
                $ld = $nd->sum(\DB::raw('credit - debet'));
                if($ld > 0) {
                    $debit = 0;
                    $kredit = $ld;
                } else {
                    $debit = $ld;
                    $kredit = 0;
                }  
            }   
            $total_debet = $total_debet + $debit;
            $total_credit = $total_credit + $kredit;
            $html .= '<tr>';
            $html .= '<td>'.$key->name.'</td>';
            $html .= '<td>'.number_format($debit).'</td>';
            $html .= '<td>'.number_format($kredit).'</td>';
            $html .= '</tr>';
        }
        $html .= '<tr>';
            
        $html .= '<td style="border-top:2px solid black;"></td>';
        $html .= '<td style="border-top:2px solid black;"><strong>'.number_format($total_debet).'</strong></td>';
        $html .= '<td style="border-top:2px solid black;"><strong>'.number_format($total_credit).'</strong></td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<th></th>';
        $html .= '<th></th>';
        $html .= '<th></th>';
        $html .= '</tr>';
        $html .= '</table>';

        return response()->json([
            "success" => true,
            "data" => $html
        ]);
    }


    public function general_ledger() {
        $view = 'general-ledger';
        $akun = $this->get_account_select();
        return view('main.general_ledger', compact('view','akun'));
    }

    public function get_account_select() {
        $data = [];
        $group = [];
        
        $user_id = session('id');
        $query = DB::table('ml_current_assets')
            ->where('userid', $user_id)
            ->get();

        foreach($query as $key) {
            $row['id'] = $key->id;
            $row['group'] = "Aktiva Lancar";
            $row['account_code_id'] = $key->account_code_id;
            $row['code'] = $key->code;
            $row['name'] = $key->name;
            array_push($data, $row);
        }
        array_push($group, "Aktiva Lancar");
    

        $query = DB::table('ml_fixed_assets')
            ->where('userid', $user_id)
            ->get();

        foreach($query as $key) {
            $row['id'] = $key->id;
            $row['group'] = "Aktiva Tetap";
            $row['account_code_id'] = $key->account_code_id;
            $row['code'] = $key->code;
            $row['name'] = $key->name;
            array_push($data, $row);
        }
        array_push($group, "Aktiva Tetap");
           
        $query = DB::table('ml_accumulated_depreciation')
            ->where('userid', $user_id)
            ->get();

        foreach($query as $key) {
            $row['id'] = $key->id;
            $row['group'] = "Akumulasi Penyusutan";
            $row['account_code_id'] = $key->account_code_id;
            $row['code'] = $key->code;
            $row['name'] = $key->name;
            array_push($data, $row);
        }
        array_push($group, "Akumulasi Penyusutan");
        
        
        $query = DB::table('ml_shortterm_debt')
            ->where('userid', $user_id)
            ->get();

        foreach($query as $key) {
            $row['id'] = $key->id;
            $row['group'] = "Utang Jangka Pendek";
            $row['account_code_id'] = $key->account_code_id;
            $row['code'] = $key->code;
            $row['name'] = $key->name;
            array_push($data, $row);
        }
        array_push($group, "Utang Jangka Pendek");
           
       
        $query = DB::table('ml_longterm_debt')
            ->where('userid', $user_id)
            ->get();

        foreach($query as $key) {
            $row['id'] = $key->id;
            $row['group'] = "Utang Jangka Panjang";
            $row['account_code_id'] = $key->account_code_id;
            $row['code'] = $key->code;
            $row['name'] = $key->name;
            array_push($data, $row);
        }
        array_push($group, "Utang Jangka Panjang");
           
        $query = DB::table('ml_capital')
                ->where('userid', $user_id)
                ->get();

        foreach($query as $key) {
            $row['id'] = $key->id;
            $row['group'] = "Modal";
            $row['account_code_id'] = $key->account_code_id;
            $row['code'] = $key->code;
            $row['name'] = $key->name;
            array_push($data, $row);
        }
        array_push($group, "Modal");
        
        $query = DB::table('ml_income')
            ->where('userid', $user_id)
            ->get();

        foreach($query as $key) {
            $row['id'] = $key->id;
            $row['group'] = "Pendapatan";
            $row['account_code_id'] = $key->account_code_id;
            $row['code'] = $key->code;
            $row['name'] = $key->name;
            array_push($data, $row);
        }
        array_push($group, 'Pendapatan');
        
       
        $query = DB::table('ml_cost_good_sold')
            ->where('userid', $user_id)
            ->get();

        foreach($query as $key) {
            $row['id'] = $key->id;
            $row['group'] = "Harga Pokok Penjualan";
            $row['account_code_id'] = $key->account_code_id;
            $row['code'] = $key->code;
            $row['name'] = $key->name;
            array_push($data, $row);
        }
        array_push($group, "Harga Pokok Penjualan");
           
    
        $query = DB::table('ml_selling_cost')
            ->where('userid', $user_id)
            ->get();

        foreach($query as $key) {
            $row['id'] = $key->id;
            $row['group'] = "Biaya Penjualan";
            $row['account_code_id'] = $key->account_code_id;
            $row['code'] = $key->code;
            $row['name'] = $key->name;
            array_push($data, $row);
        }
        array_push($group, "Biaya Penjualan");
        
        $query = DB::table('ml_admin_general_fees')
            ->where('userid', $user_id)
            ->get();

        foreach($query as $key) {
            $row['id'] = $key->id;
            $row['group'] = "Biaya Umum Admin";
            $row['account_code_id'] = $key->account_code_id;
            $row['code'] = $key->code;
            $row['name'] = $key->name;
            array_push($data, $row);
        }
        array_push($group, "Biaya Umum Admin");
        
      
        $query = DB::table('ml_non_business_income')
            ->where('userid', $user_id)
            ->get();

        foreach($query as $key) {
            $row['id'] = $key->id;
            $row['group'] = "Pendapatan Di Luar Usaha";
            $row['account_code_id'] = $key->account_code_id;
            $row['code'] = $key->code;
            $row['name'] = $key->name;
            array_push($data, $row);
        }
        array_push($group, "Pendapatan Di Luar Usaha");
                   
      
        $query = DB::table('ml_non_business_expenses')
            ->where('userid', $user_id)
            ->get();

        foreach($query as $key) {
            $row['id'] = $key->id;
            $row['group'] = "Biaya Diluar Usaha";
            $row['account_code_id'] = $key->account_code_id;
            $row['code'] = $key->code;
            $row['name'] = $key->name;
            array_push($data, $row);
        }
        array_push($group, "Biaya Diluar Usaha");
        
        $data['data'] = $data;
        $data['group'] = $group;

        return $data;
    }


    public function general_ledger_submit(Request $request) {
        $input = $request->all();

        $month_from = $input['month_from'];
        $year_from = $input['year_from'];
        $month_to = $input['month_to'];
        $year_to = $input['year_to'];

        $awal_bulan = $year_from.'-'.$month_from.'-01';
        $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, $month_to, $year_to);
        $akhir_bulan = $year_to.'-'.$month_to.'-'.$tanggal_akhir;

        $awal = strtotime($awal_bulan);
        $akhir = strtotime($akhir_bulan);


        $rules = array(
            "estimation" => "required"
        );
        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            $pesan = $validator->errors();
            $pesanarr = explode(",", $pesan);
            $find = array("[","]","{","}");
            $html = '';
            foreach($pesanarr as $p ) {
                $n = str_replace($find,"",$p);
                $o = strstr($n, ':', false);
                $html .= str_replace(":", "", $o).'<br>';
            }

            return response()->json([
            	"success" => false,
            	"message" => $html
            ]);
        }

        $estimations = explode("_",$input['estimation']);
        $account_id = $estimations[0];
        $account_asset_id = $estimations[1];
    
        $data = DB::table('ml_journal as j')
            ->join('ml_journal_list as jl', 'jl.journal_id','=', 'j.id', 'left')
            ->select('jl.*', 'j.transaction_name')
            ->where('j.userid', session('id'))
            ->where('jl.asset_data_id', $account_id)
            ->where('jl.created', '>=', $awal)
            ->where('jl.created', '<=', $akhir)
            ->orderBy('jl.created', 'asc')
            ->get();

        
        $html = "";
        $html .= '<table class="table" id="table-general-ledger">';
        $html .= '<tr>';
        $html .= '<th style="border-bottom: 2px solid black;">Tanggal</th>';
        $html .= '<th style="border-bottom: 2px solid black;">Keterangan</th>';
        $html .= '<th style="border-bottom: 2px solid black;">Debit</th>';
        $html .= '<th style="border-bottom: 2px solid black;">Kredit</th>';
        $html .= '<th style="border-bottom: 2px solid black;">Saldo</th>';
        $html .= '</tr>';

        $saldo = 0;

        $total_debit = 0;
        $total_kredit = 0;

        foreach($data as $item) {
        $saldo = $saldo + $item->debet - $item->credit;
        $total_debit = $total_debit + $item->debet;
        $total_kredit = $total_kredit + $item->credit;

        $html .= '<tr>';
        $html .= '<td>'.date('d-m-Y', $item->created).'</td>';
        $html .= '<td>'.$item->asset_data_name.'</td>';
        $html .= '<td>'.number_format($item->debet).'</td>';
        $html .= '<td>'.number_format($item->credit).'</td>';
        $html .= '<td>'.number_format(abs($saldo)).'</td>';
        $html .= '</tr>';
                
            
        }
        $html .= '<tr>';
            
        $html .= '<td style="border-top:2px solid black;"></td>';
        $html .= '<td style="border-top:2px solid black;"><strong>Total</strong></td>';
        
        $html .= '<td style="border-top:2px solid black;"><strong>'.number_format($total_debit).'</strong></td>';
        $html .= '<td style="border-top:2px solid black;"><strong>'.number_format($total_kredit).'</strong></td>';
        $html .= '<td style="border-top:2px solid black;"><strong>'.number_format(abs($total_debit - $total_kredit)).'</strong></td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<th></th>';
        $html .= '<th></th>';
        $html .= '<th></th>';
        $html .= '<th></th>';
        $html .= '<th></th>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</table>';
        return response()->json([
            "success" => true,
            "data" => $html
        ]);
    }


    public function profit_loss_export($tanggal) 
    {
        $date = explode("_",$tanggal);

        
        $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, $date[2], $date[3]);

        $start = $date[1].'-'.$date[0].'-01';
        $end = $date[3].'-'.$date[2].'-'.$tanggal_akhir;
        $awal = strtotime($start);
        $akhir = strtotime($end);
        
        $data = $this->list_account();
        return Excel::download(new ProfitLossExport($data, $awal, $akhir), 'profit_loss.xlsx');
    }

    public function balance_sheet_export($tanggal) 
    {
        $date = explode("_",$tanggal);

        
        $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, $date[2], $date[3]);

        $start = $date[1].'-'.$date[0].'-01';
        $end = $date[3].'-'.$date[2].'-'.$tanggal_akhir;
        $awal = strtotime($start);
        $akhir = strtotime($end);
        
        $dt = $this->list_balance_account();
        $laba_bersih = $this->count_net_profit($date[0], $date[1], $date[2], $date[3]);
        return Excel::download(new BalanceExport($dt, $awal, $akhir, $laba_bersih), 'balance_sheet.xlsx');
    }


}
