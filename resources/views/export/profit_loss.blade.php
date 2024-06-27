<!DOCTYPE html>
<html>
<head>
	<title>Export Laporan Excel</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
 
	<div class="container">
		<center>
			<h4>Laporan Laba Rugi</h4>
			<h5><a target="_blank" href="https://randu.co.id">Randu Akunting</a></h5>
		</center>
		
		
		
        <table class="table table-bordered" id="table-profit-loss">
            <tr>
                <th rowspan="2"><center>Keterangan</center></th>
                <th colspan="2"><center>{{ date('F Y', $awal) }} - {{ date('F Y', $akhir) }}</center></th>    
            </tr> 
            <tr>
                <th>*</th>
                <th>*</th>
            </tr>
            <tr><td colspan="3" style="border-top:2px solid black;"><strong>Pendapatan</strong></td></tr>
            @php
             $total_income = 0;
            @endphp
            @foreach($data['income'] as $i)
            @php
                $income = DB::table('ml_journal_list')
                    ->where('asset_data_id', $i->id)
                    ->where('account_code_id', 7)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('credit - debet'));
                $total_income = $total_income + $income;
            @endphp
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $i->name }}</td>
                <td style="text-align:right;">{{ number_format($income) }}</td>
                <td></td>
            </tr>
            @endforeach
            <tr>
                <td><strong>Pendapatan Bersih</strong></td>
                <td></td>
                <td style="text-align:right;">{{ number_format($total_income) }}</td>
            </tr>
            <tr><td colspan="3"><strong>Harga Pokok Penjualan</strong></td></tr>
            @php
             $total_hpp = 0;
            @endphp
            @foreach($data['hpp'] as $a)
            @php
                $hpp = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 8)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('debet-credit'));
                $total_hpp = $total_hpp + $hpp;
            @endphp
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;{{ $a->name }}</td>
                <td style="text-align:right;">({{ number_format($hpp) }})</td>
                <td></td>
            </tr>
            @endforeach
            <tr>
                <td><strong>Total Harga Pokok Penjualan</strong></td>
                <td></td>
                <td style="text-align:right;">({{ number_format($total_hpp) }})</td>
            </tr>
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
 
</body>
</html>