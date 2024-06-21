<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

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

    public function profit_loss()
	{
		$view = 'profit-loss';
        $data = $this->list_account();
        return view('main.profit_loss_report', compact('view', 'data'));
	}

    public function submit_profit_loss(Request $request) {
        $input = $request->all();
        
        $start = $input['year_from'].'-'.$input['month_from'].'-01';
        $end = $input['year_to'].'-'.$input['month_to'].'-31';
        
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
        $html .= '<th colspan="2"><center>'.$start.'</center></th>';    
        $html .= '</tr>'; 
        $html .= '<tr>';
        $html .= '<th>*</th>';
        $html .= '<th>*</th>';
        $html .= '</tr>';
        $html .= '<tr><td colspan="3"><strong>Pendapatan</strong></td></tr>';
            
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
            @php
                $laba_rugi_kotor = $total_income - $total_hpp;
            @endphp
            <tr>
                <td><strong>LABA/RUGI KOTOR</strong></td>
                <td></td>
                <td style="text-align:right;"><strong>{{ number_format($laba_rugi_kotor) }}</strong></td>
            </tr>
            <tr><td colspan="3"><strong>Biaya Penjualan</strong></td></tr>
            @php
            $total_selling_cost = 0;
            @endphp
            @foreach($data['selling_cost'] as $a)
            @php
                $selling_cost = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 9)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('debet-credit'));
                $total_selling_cost = $total_selling_cost + $selling_cost;
            @endphp
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $a->name }}</td>
                <td style="text-align:right;">({{ number_format($selling_cost) }})</td>
                <td></td>
            </tr>
            
            @endforeach
            <tr>
                <td><strong>Total Biaya Penjualan</strong></td>
                <td></td>
                <td style="text-align:right;">({{ number_format($total_selling_cost) }})</td>
            </tr>
            <tr><td colspan="3"><strong>Biaya Umum Admin</strong></td></tr>
            @php
            $total_general_fees = 0;
            @endphp
            @foreach($data['general_fees'] as $a)
            @php
                $general_fees = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 10)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('debet-credit'));
                $total_general_fees = $total_general_fees + $general_fees;
            @endphp
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $a->name }}</td>
                <td style="text-align:right;">({{ number_format($general_fees) }})</td>
                <td></td>
            </tr>
            
            @endforeach
            <tr>
                <td><strong>Total Biaya Admin dan Umum</strong></td>
                <td></td>
                <td style="text-align:right;">({{ number_format($total_general_fees) }})</td>
            </tr>

            <tr><td colspan="3"><strong>Pendapatan Diluar Usaha</strong></td></tr>
            @php
            $total_nb_income = 0;
            @endphp
            @foreach($data['non_business_income'] as $a)
            @php
                $nb_income = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 11)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('credit-debet'));
                $total_nb_income = $total_nb_income + $nb_income;
            @endphp
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $a->name }}</td>
                <td style="text-align:right;">{{ number_format($nb_income) }}</td>
                <td></td>
            </tr>
            
            @endforeach
            <tr>
                <td><strong>Total Pendapatan Diluar Usaha</strong></td>
                <td></td>
                <td style="text-align:right;">{{ number_format($total_nb_income) }}</td>
            </tr>


            <tr><td colspan="3"><strong>Biaya Diluar Usaha</strong></td></tr>
            @php
            $total_nb_cost = 0;
            @endphp
            @foreach($data['non_business_cost'] as $a)
            @php
                $nb_cost = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 12)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('debet-credit'));
                $total_nb_cost = $total_nb_cost + $nb_cost;
            @endphp
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $a->name }}</td>
                <td style="text-align:right;">({{ number_format($nb_cost) }})</td>
                <td></td>
            </tr>
            
            @endforeach
            <tr>
                <td><strong>Total Biaya Diluar Usaha</strong></td>
                <td></td>
                <td style="text-align:right;">({{ number_format($total_nb_cost) }})</td>
            </tr>
            @php
                $laba_bersih = $laba_rugi_kotor - $total_selling_cost - $total_general_fees + $total_nb_income - $total_nb_cost;
            @endphp
            <tr>
                <td><strong>LABA/RUGI BERSIH</strong></td>
                <td></td>
                <td style="text-align:right;"><strong>{{ number_format($laba_bersih) }}</strong></td>
            </tr>
        </table>

        return response()->json([
            "success" => true
        ]);
    }


    


  

}
