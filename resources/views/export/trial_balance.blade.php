<!DOCTYPE html>
<html>
<head>
	<title>Export Neraca Saldo</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
 
	<div class="container">
		<center>
			<h4>Neraca Saldo</h4>
			<h5><a target="_blank" href="https://randu.co.id">Randu Akunting</a></h5>
		</center>
		

        <table class="table" id="table-trial-balance">
            <tr>
                
                <th style="border-bottom: 2px solid black;">Keterangan</th>
                <th style="border-bottom: 2px solid black;">Debit</th>
                <th style="border-bottom: 2px solid black;">Kredit</th>
            </tr>
            @php
                $total_debet = 0;
                $total_credit = 0;
            @endphp
            @foreach($dt['current_asset'] as $key)
            @php
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
            
            @endphp
            <tr>  
                <td>{{ $key->name }}</td>
                <td>{{ number_format($debit) }}</td>
                <td>{{ number_format($kredit) }}</td>
            </tr>
            @endforeach
            @foreach($dt['income'] as $key)
            @php
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

            @endphp
            <tr>  
                <td>{{ $key->name }}</td>
                <td>{{ number_format($debit) }}</td>
                <td>{{ number_format($kredit) }}</td>
            </tr>
            @endforeach
            @foreach($dt['fixed_asset'] as $key)
            @php
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
        
        @endphp
        <tr>  
            <td>{{ $key->name }}</td>
            <td>{{ number_format($debit) }}</td>
            <td>{{ number_format($kredit) }}</td>
        </tr>
            @endforeach

            @foreach($dt['cost_good'] as $key)
            @php
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
            @endphp
            <tr>  
                <td>{{ $key->name }}</td>
                <td>{{ number_format($debit) }}</td>
                <td>{{ number_format($kredit) }}</td>
            </tr>
            @endforeach

            @foreach($dt['short_debt'] as $key)
            @php
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
            
            @endphp
            <tr>  
                <td>{{ $key->name }}</td>
                <td>{{ number_format($debit) }}</td>
                <td>{{ number_format($kredit) }}</td>
            </tr>
            @endforeach
            @foreach($dt['long_debt'] as $key)
            @php
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
            
            @endphp
            <tr>  
                <td>{{ $key->name }}</td>
                <td>{{ number_format($debit) }}</td>
                <td>{{ number_format($kredit) }}</td>
            </tr>
            @endforeach
            @foreach($dt['capital'] as $key)
            @php
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
            
            @endphp
            <tr>  
                <td>{{ $key->name }}</td>
                <td>{{ number_format($debit) }}</td>
                <td>{{ number_format($kredit) }}</td>
            </tr>
            @endforeach
            <tr>
                
                <td style="border-top:2px solid black;"></td>
                <td style="border-top:2px solid black;"><strong>{{ number_format($total_debet) }}</strong></td>
                <td style="border-top:2px solid black;"><strong>{{ number_format($total_credit) }}</strong></td>
            </tr>
            <tr>
                
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </table>
		
		
    </div>
 
</body>
</html>