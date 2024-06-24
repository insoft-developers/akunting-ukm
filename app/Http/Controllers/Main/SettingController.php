<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Traits\CommonTrait;
use App\Models\Journal;
use App\Models\JournalList;

class SettingController extends Controller
{
    use CommonTrait;
	
	public function index() {
        $view = 'setting';
        return view('main.setting', compact('view'));
    }

    public function generate_opening_balance() {
        $view = 'opening-balance';
        return view('main.generate_open_balance', compact('view'));
    }

    public function submit_opening_balance(Request $request)
	{
		$input = $request->all();
		$custom_date = $input['month'].'-'.$input['year'];
		$this_month = $input['year'].'-'.$input['month'].'-01';
		$tanggal = date('Y-m-d', strtotime($this_month));
		$u_tanggal = strtotime($tanggal);



		$capital = DB::table('ml_capital')
			->where('userid',session('id'))
			->where('code', 'modal-pemilik')
			->first();

		$c_code = $capital->id.'_'.$capital->account_code_id;


		
		

		$get_first_day_of_prev_month = date("Y-m-d", strtotime($custom_date." first day of previous month"));
		$get_last_day_of_prev_month = date("Y-m-d", strtotime($custom_date." last day of previous month"));

		$u_from = strtotime($get_first_day_of_prev_month);
		$u_to = strtotime($get_last_day_of_prev_month);

		$prive = DB::table('ml_capital')
			->where('userid',session('id'))
			->where('code', 'prive')
			->first();

		$total_prive = JournalList::where('asset_data_id', $prive->id)
			->whereBetween('created', [$u_from, $u_to])
			->sum(\DB::raw('debet-credit'));
		
		

		$laba = $this->count_net_profit($u_from, $u_to);
		


		$journal_delete = Journal::where('userid', session('id'))
			->where('is_opening_balance', 1)
			->where('created', $u_tanggal);

		foreach($journal_delete->get() as $jd) {
			JournalList::where('journal_id', $jd->id)->delete();
		}

		$journal_delete->delete();

		

		$journals = Journal::where('userid', session('id'))
			->whereBetween('created', [$u_from, $u_to])
			->get();

		foreach($journals as $journal) {
			$j = new Journal;
			$j->userid = session('id');
			$j->journal_id = $journal->journal_id;
			$j->transaction_id = $journal->transaction_id;
			$j->transaction_name = 'Saldo-Awal';
			$j->rf_accode_id = $journal->rf_accode_id;
			$j->st_accode_id = $journal->st_accode_id;
			$j->debt_data = $journal->debt_data;
			$j->nominal = $journal->nominal;
			$j->total_balance = $journal->total_balance;
			$j->is_opening_balance = 1;
			$j->color_date = $journal->color_date;
			$j->created = $u_tanggal;
			$j->save();

			$id = $j->id;


			$lists = JournalList::where('journal_id', $journal->id)->get();

			
			foreach($lists as $list) {
				if($list->account_code_id == 1 || $list->account_code_id == 2 || $list->account_code_id == 4 || $list->account_code_id == 5) {
					$jl = new JournalList;
					$jl->journal_id = $id;
					$jl->rf_accode_id = $list->rf_accode_id;
					$jl->st_accode_id = $list->st_accode_id;
					$jl->account_code_id = $list->account_code_id;
					$jl->asset_data_id = $list->asset_data_id;
					$jl->asset_data_name = $list->asset_data_name;
					$jl->debet = $list->debet;
					$jl->credit = $list->credit;
					$jl->is_debt = $list->is_debt;
					$jl->is_receivables = $list->is_receivables;
					$jl->created = $u_tanggal;
					$jl->relasi_trx = $list->relasi_trx;
					$jl->save();
				} else if($list->account_code_id == 6) {
					if($list->asset_data_id == $capital->id) {
						// dd($capital->id);
						$jl = new JournalList;
						$jl->journal_id = $id;
						$jl->rf_accode_id = $list->rf_accode_id;
						$jl->st_accode_id = $list->st_accode_id;
						$jl->account_code_id = $list->account_code_id;
						$jl->asset_data_id = $list->asset_data_id;
						$jl->asset_data_name = $list->asset_data_name;
						$jl->debet = $list->debet;
						$jl->credit = $list->credit + $laba - $total_prive;
						$jl->is_debt = $list->is_debt;
						$jl->is_receivables = $list->is_receivables;
						$jl->created = $u_tanggal;
						$jl->relasi_trx = $list->relasi_trx;
						$jl->save();
					} 
				}
			}

		}

		
		return response()->json([
			"success" =>true,
			"message" => "Generate Opening Balance Success"
		]);
		
	}

	public function count_net_profit($from, $to) {  
      
        $awal = $from;
        $akhir =$to;
        
        
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
}