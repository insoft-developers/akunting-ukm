<!DOCTYPE html>
<html>
<head>
	<title>Export Laporan Neraca</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
 
	<div class="container">
		<center>
			<h4>Laporan Neraca</h4>
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
            <tr><td colspan="3" style="border-top:2px solid black;"><strong>Aktiva Lancar</strong></td></tr>
            @php
        

             $total_lancar = 0;
            @endphp
            @foreach($dt['aktiva_lancar'] as $i)
            @php
                $lancar = DB::table('ml_journal_list')
                    ->where('asset_data_id', $i->id)
                    ->where('account_code_id', 1)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('debet - credit'));
                $total_lancar = $total_lancar + $lancar;
            @endphp
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp; {{$i->name}} </td>
                <td style="text-align:right;"> {{number_format($lancar)}} </td>
                <td></td>
            </tr>
            @endforeach
            <tr>
                <td><strong>Total Aktiva Lancar</strong></td>
                <td></td>
                <td style="text-align:right;"> {{number_format($total_lancar)}} </td>
            </tr>
            <tr><td colspan="3"><strong>Aktiva Tetap</strong></td></tr>
            @php
            
             $total_tetap = 0;
            @endphp
            @foreach($dt['aktiva_tetap'] as $a)
            @php
                $tetap = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 2)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('debet-credit'));
                $total_tetap = $total_tetap + $tetap;
            @endphp
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp; {{$a->name}} </td>
                <td style="text-align:right;">{{number_format($tetap)}} </td>
                <td></td>
            </tr>
            @endforeach
            <tr>
                <td><strong>Total Aktiva Tetap</strong></td>
                <td></td>
                <td style="text-align:right;">{{number_format($total_tetap)}}</td>
            </tr>
            @php
               

                $total_aktiva = $total_lancar + $total_tetap;
            @endphp
            <tr>
                <td><strong>TOTAL AKTIVA</strong></td>
                <td></td>
                <td style="text-align:right;"><strong> {{number_format($total_aktiva)}} </strong></td>
            </tr>
            <tr><td colspan="3"><strong>Utang Jangka Pendek</strong></td></tr>
            @php
          

             $total_pendek = 0;
            @endphp
            @foreach($dt['utang_pendek'] as $a)
            @php
                $pendek = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 4)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('credit-debet'));
                $total_pendek = $total_pendek + $pendek;
            @endphp
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp; {{$a->name}} </td>
                <td style="text-align:right;">{{( number_format($pendek) )}}</td>
                <td></td>
            </tr>
            
            @endforeach
            <tr>
                <td><strong>Total Utang Jangka Pendek</strong></td>
                <td></td>
                <td style="text-align:right;">{{( number_format($total_pendek) )}}</td>
            </tr>
            <tr><td colspan="3"><strong>Utang Jangka Panjang</strong></td></tr>
            @php
            

            $total_panjang =0;
            @endphp
            @foreach($dt['utang_panjang'] as $a)
            @php
                $panjang = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 5)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('credit-debet'));
                $total_panjang = $total_panjang + $panjang;
            @endphp
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp; {{$a->name}} </td>
                <td style="text-align:right;">{{number_format($panjang)}}</td>
                <td></td>
            </tr>
            
            @endforeach
            <tr>
                <td><strong>Total Utang Jangka Panjang</strong></td>
                <td></td>
                <td style="text-align:right;">{{number_format($total_panjang)}}</td>
            </tr>

            <tr><td colspan="3"><strong>Modal</strong></td></tr>
            @php
           
            $total_modal = 0;
            @endphp
            @foreach($dt['modal'] as $a)
            @php
                $modal = DB::table('ml_journal_list')
                    ->where('asset_data_id', $a->id)
                    ->where('account_code_id', 6)
                    ->where('created', '>=', $awal)
                    ->where('created', '<=', $akhir)
                    ->sum(\DB::raw('credit-debet'));
                $total_modal = $total_modal + $modal;
            @endphp
            <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp; {{$a->name}} </td>
                <td style="text-align:right;"> {{number_format($modal)}} </td>
                <td></td>
            </tr>
            
            @endforeach
            <td>&nbsp;&nbsp;&nbsp;&nbsp; LABA/RUGI BERSIH </td>
            <td style="text-align:right;"> {{number_format($laba_bersih)}} </td>
            <td></td>
            <tr>
                <td><strong>Total Modal</strong></td>
                <td></td>
                <td style="text-align:right;"> {{number_format($total_modal + $laba_bersih)}} </td>
            </tr>
            <tr>
                <td><strong>TOTAL UTANG DAN MODAL</strong></td>
                <td></td>
                <td style="text-align:right;"><strong> {{number_format($total_pendek + $total_panjang + $total_modal + $laba_bersih)}}</strong></td>
            </tr>
            

            

        </table>
		
		
    </div>
 
</body>
</html>