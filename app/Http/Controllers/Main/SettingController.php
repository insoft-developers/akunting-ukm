<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Traits\CommonTrait;
use App\Models\Journal;
use App\Models\JournalList;
use Validator;
use Redirect;

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
			$j->transaction_name = 'Saldo Awal';
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

	public function company_setting() {
		$view = "company-setting";
		$data = DB::table('ml_company')->where('userid', session('id'))->first();
		return view('main.company_setting', compact('view','data'));
	}

	public function company_setting_update(Request $request) {
		$input = $request->all();
		
		$rules = array(
			"company_email" => "required|email",
			"company_name" => "required|min:3",
			"phone_number" => "required",
			"address" => "required"
		);

		$validator = Validator::make($input, $rules);
		if($validator->fails()) {
			return redirect()->back()->withInput()->withErrors($validator); 
		}

		$cek = DB::table('ml_company')->where('userid', session('id'));
		if($cek->count() > 0) {
			DB::table('ml_company')
				->where('id', $input['id'])
				->update([
					"company_email" => $input['company_email'],
					"company_name" => $input['company_name'],
					"phone_number" => $input['phone_number'],
					"address" => $input['address'],
					"domicile" => $input['domicile'],
					"business_fields" => $input['business_fields'],
					"npwp" => $input['npwp'],
					"tax" => $input['tax'],
					"updated" => time()
				]);
		} else {
			DB::table('ml_company')
				->insert([
					'userid'			=> session('id'),
					'company_email'		=> $input['company_email'],
					'company_name'		=> $input['company_name'],
					'phone_number'		=> $input['phone_number'],
					'address'			=> $input['address'],
					'domicile'			=> $input['domicile'],
					'business_fields'	=> $input['business_fields'],
					'npwp'				=> $input['npwp'],
					'tax'				=> $input['tax'],
					'updated'			=> time(),
					'created'			=> time()
				]);
		}

		return Redirect::back()->with('success', "Company Setting Successfully Updated !"); 

	}

	public function initial_capital() {
		$view = "initial-capital";
		$akun = $this->get_account_select();
		return view('main.initial_capital', compact('view','akun'));
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


	public function save_initial_capital(Request $request) {
        $input = $request->all();
        $rules = array(
            "akun.*" => "required",
            "debit.*" => "required_without:kredit.*",
            "kredit.*" => "required_without:debit.*",
            "transaction_date" => "required",
            "transaction_name" => "required",
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

        $nominal = '';
        if(empty($input['debit'][0])) {
            $nominal = $input['kredit'][0];
            $input['debit'][0] = 0;

        }
        if(empty($input['kredit'][0])) {
            $nominal = $input['debit'][0];
            $input['kredit'][0] = 0;
        } 

        $date = strtotime($input['transaction_date']);

        $get_id_transaction = $this->initTransactionId($input['akun'][0], $input['akun'][1]);

    
        $data_journal = [
            'userid'			=> session('id'),
            'transaction_id'	=> $get_id_transaction,
            'transaction_name'	=> $this->checkTransactionName($input['transaction_name']),
            'rf_accode_id'		=> $input['akun'][0],
            'st_accode_id'		=> $input['akun'][1],
            'nominal'			=> $nominal,
            'color_date'		=> $this->get_data_transaction($get_id_transaction, 'color'),
            'created'			=> $date
        ];

        // First insert data to journal
        // $this->db->sql_insert($data_journal, 'ml_journal');
        $journal_id = DB::table('ml_journal')->insertGetId($data_journal);

        for ($i = 0; $i < count($input['akun']); $i++) 
        { 
            if(! empty($input['debit'][$i])) {
                $debit = $input['debit'][$i] == null ? 0 : $input['debit'][$i];

                if ($input['akun'][$i] !== '')
                {
                    $account_code_id = explode("_", $input['akun'][$i]);
                    $asset_data_name = $this->getAllListAssetWithAccDataId(session('id'), $account_code_id[0], $account_code_id[1]);
                }

                $data_debit = [
                    'journal_id'		=> $journal_id,
                    'rf_accode_id'		=> $input['akun'][$i],
                    'account_code_id'	=> $account_code_id[1],
                    'asset_data_id'		=> $account_code_id[0],
                    'asset_data_name'	=> $asset_data_name,
                    'debet'				=> $debit,
                    'created'			=> $date
                ];                
                DB::table('ml_journal_list')->insert($data_debit);
            }

            
            if(! empty($input['kredit'][$i])) {
                $credit = $input['kredit'][$i] == null ? 0 : $input['kredit'][$i];

                if ($input['akun'][$i] !== '')
                {
                    $account_code_id = explode("_", $input['akun'][$i]);
                    $asset_data_name = $this->getAllListAssetWithAccDataId(session('id'), $account_code_id[0], $account_code_id[1]);
                }

                $data_credit = [
                    'journal_id'		=> $journal_id,
                    'st_accode_id'		=> $input['akun'][$i],
                    'account_code_id'	=> $account_code_id[1],
                    'asset_data_id'		=> $account_code_id[0],
                    'asset_data_name'	=> $asset_data_name,
                    'credit'			=> $credit,
                    'created'			=> $date
                ];
                DB::table('ml_journal_list')->insert($data_credit);
            }
        
        }
     

        // Update total saldo
        $reCalculateTotalSaldo = $this->checkTotalBalance($journal_id);

        // Second update data to journal
        // $this->db->sql_update(['total_balance' => $reCalculateTotalSaldo], 'ml_journal', ['id' => $journal_id]);
        DB::table('ml_journal')->where('id', $journal_id)->update(['total_balance'=> $reCalculateTotalSaldo]);

        return response()->json([
            "success" => true
        ]);
    }
    
}