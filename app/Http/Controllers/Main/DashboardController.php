<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MlTransaction;
use DB;
use Session;
use Validator;
use App\Traits\CommonTrait;
use DataTables;
use Redirect;

class DashboardController extends Controller
{
    use CommonTrait;

    public function journal_add() {
        $view = 'journal-add';
        $akun = $this->get_account_select();
        return view('main.journal_add', compact('view', 'akun'));
    }
    

    public function save_multiple_journal(Request $request) {
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

            // Second insert data to journal list
            // $this->db->sql_insert($data_debit, 'ml_journal_list');
            
            DB::table('ml_journal_list')->insert($data_debit);
        
        
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

            // Second insert data to journal list
            // $this->db->sql_insert($data_credit, 'ml_journal_list');
            DB::table('ml_journal_list')->insert($data_credit);
            
        }
        

        // if ($this->input->post('status_submit') == 'approved')
        // {
        // 	$this->reGenerateOpeningBalance();
        // }

        // Update total saldo
        $reCalculateTotalSaldo = $this->checkTotalBalance($journal_id);

        // Second update data to journal
        // $this->db->sql_update(['total_balance' => $reCalculateTotalSaldo], 'ml_journal', ['id' => $journal_id]);
        DB::table('ml_journal')->where('id', $journal_id)->update(['total_balance'=> $reCalculateTotalSaldo]);

        return response()->json([
            "success" => true
        ]);
    }


    protected function initTransactionId($account_code_id1, $account_code_id2)
	{
		$var_rf_cid = explode("_", $account_code_id1);
		$rf_id 		= $var_rf_cid[0];
		$rf_code_id = $var_rf_cid[1];

		$var_st_cid = explode("_", $account_code_id2);
		$st_id 		= $var_st_cid[0];
		$st_code_id = $var_st_cid[1];

		// $res1 = $this->db->sql_prepare("select * from ml_transaction_subcat where account_code_id = :id and received_from_status = 0 order by id");
		// $bindParam1 = $this->db->sql_bindParam(['id' => $rf_code_id], $res1);
		// while ($row1 = $this->db->sql_fetch_single($bindParam1))
		// {
		// 	if ($row1['transaction_id'] !== 3 && $row1['transaction_id'] !== 5)
		// 	{
		// 		$output1[] = $row1['transaction_id'];
		// 	}
		// }

        $row1 = DB::table('ml_transaction_subcat')->where('account_code_id', $rf_code_id)->where('received_from_status', 0)->orderBy('id')->get();
        foreach($row1 as $rw1) {
            if ($rw1->transaction_id !== 3 && $rw1->transaction_id !== 5)
			{
				$output1[] = $rw1->transaction_id;
			}
        }


		// $res2 = $this->db->sql_prepare("select * from ml_transaction_subcat where account_code_id = :id and saved_to_status = 0 order by id");
		// $bindParam2 = $this->db->sql_bindParam(['id' => $st_code_id], $res2);
		// while ($row2 = $this->db->sql_fetch_single($bindParam2))
		// {
		// 	if ($row2['transaction_id'] !== 3 && $row2['transaction_id'] !== 5)
		// 	{
		// 		$output2[] = $row2['transaction_id'];
		// 	}
		// }

        $row2 = DB::table('ml_transaction_subcat')->where('account_code_id', $st_code_id)->where('saved_to_status', 0)->orderBy('id')->get();
        foreach($row2 as $rw2) {
            if ($rw2->transaction_id !== 3 && $rw2->transaction_id !== 5)
			{
				$output2[] = $rw2->transaction_id;
			}
        }

		$output3 = array_uintersect($output1, $output2, "strcasecmp");

		$k = array_rand($output3);
		$new_output = $output3[$k];

		return $new_output;
	}


   

    public function journal_table()
    {
        $data = DB::table('ml_journal')->where('userid', session('id'))->get();
        return Datatables::of($data)
            ->addColumn('dibuat', function($data){
                return '<center>'.date('d-m-Y', $data->created).'</center>';
            })
            ->addColumn('tanggal', function($data){
                return '<center><div class="date-box" style="background:'.$data->color_date.'">'.date('d', $data->created).'</div></center>';
            })
            ->addColumn('total_balance', function($data){
                return '<div sytle="text-align:right";>Rp. '.number_format($data->total_balance).'</div>';
            })
            ->addColumn('action', function($data){
                return '<center><a href="'.url('journal_edit/'.$data->id).'"><button style="width:70px;margin-bottom:5px;" class="btn btn-warning btn-sm">Sunting</button></a><button onclick="journal_delete('.$data->id.')" style="width:70px;" class="btn btn-danger btn-sm">Hapus</button></center>';
            })
        ->rawColumns(['action','dibuat','tanggal','total_balance'])
        ->make(true);
    }


    public function index() {
        $view = "jurnal";
        $list_transaksi = MlTransaction::orderBy('id', 'asc')->get();
        return view('main.dashboard', compact('view','list_transaksi'));
    }

    public function save_jurnal(Request $request) {
        $input = $request->all();

       
        $rules = array(
            "tanggal_transaksi" => "required",
            "jenis_transaksi" => "required",
            "receive_from" => "required",
            "save_to" => "required",
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

        $nominal 	= $input['nominal'];
        $date 		= strtotime($input['tanggal_transaksi']);

        $data_journal = [
            'userid'			=> session('id'),
            'transaction_id'	=> $input['jenis_transaksi'],
            'transaction_name'	=> $this->checkTransactionName($input['keterangan']),
            'rf_accode_id'		=> $input['receive_from'],
            'st_accode_id'		=> $input['save_to'],
            'debt_data'			=> '',
            'nominal'			=> $nominal,
            'color_date'		=> $this->get_data_transaction($input['jenis_transaksi'], 'color'),
            'created'			=> $date
        ];

        // First insert data to journal
        // $this->db->sql_insert($data_journal, 'ml_journal');

        $journal_id = DB::table('ml_journal')->insertGetId($data_journal);

        // Get last ID Journal after insert
        

        if ($input['receive_from'] !== '')
        {
            $account_code_id1 = explode("_", $input['receive_from']);
        }
        
        if ($input['save_to'] !== '')
        {
            $account_code_id2 = explode("_", $input['save_to']);
        }

        $data_journal_st_accode = [
            'journal_id'		=> $journal_id,
            'st_accode_id'		=> $input['save_to'],
            'account_code_id'	=> $account_code_id2[1],
            'asset_data_id'		=> $account_code_id2[0],
            'asset_data_name'	=> $this->getAllListAssetWithAccDataId($this->get_user('id'), $account_code_id2[0], $account_code_id2[1]),
            'debet'				=> $nominal,
            'created'			=> $date
        ];

        // First insert data to journal list
        // $this->db->sql_insert($data_journal_st_accode, 'ml_journal_list');
        DB::table('ml_journal_list')->insert($data_journal_st_accode);

        $data_journal_rf_accode = [
            'journal_id'		=> $journal_id,
            'rf_accode_id'		=> $input['receive_from'],
            'account_code_id'	=> $account_code_id1[1],
            'asset_data_id'		=> $account_code_id1[0],
            'asset_data_name'	=> $this->getAllListAssetWithAccDataId($this->get_user('id'), $account_code_id1[0], $account_code_id1[1]),
            'credit'			=> $nominal,
            'created'			=> $date
        ];

        // Second insert data to journal list
        // $this->db->sql_insert($data_journal_rf_accode, 'ml_journal_list');
        DB::table('ml_journal_list')->insert($data_journal_rf_accode);

        // Update total saldo
        $reCalculateTotalSaldo = $this->checkTotalBalance($journal_id);

        // Second update data to journal
        // $this->db->sql_update(['total_balance' => $reCalculateTotalSaldo], 'ml_journal', ['id' => $journal_id]);

        DB::table('ml_journal')->where('id', $journal_id)->update(["total_balance"=>$reCalculateTotalSaldo]);

        // if ($this->input->post('status_submit') == 'approved')
        // {
        //     $this->reGenerateOpeningBalance();
        // }


        return response()->json([
            "success" => true,
            "message" => 'success'

        ]);
    }


    public function checkTotalBalance($journal_id)
	{
		$total_all_debit 	= 0;
		$total_all_credit 	= 0;

		$i = 0;
		// $res_journal_list = $this->db->sql_prepare("select * from ml_journal_list where journal_id = :journal_id order by id");
		// $bindParam_journal_list = $this->db->sql_bindParam(['journal_id' => $journal_id], $res_journal_list);

        $bindParam_journal_list = DB::table('ml_journal_list')
            ->where('journal_id', $journal_id)->get();

		// while ($row_journal_list = $this->db->sql_fetch_single($bindParam_journal_list))
		// {
		// 	$total_all_debit += $row_journal_list['debet'];
		// 	$total_all_credit += $row_journal_list['credit'];			
		// }

        foreach($bindParam_journal_list as $key) {
            $total_all_debit += $key->debet;
			$total_all_credit += $key->credit;
        }

		$new_output['total_all_debit'] = $total_all_debit;
		$new_output['total_all_credit'] = $total_all_credit;

		if ($new_output['total_all_debit'] == $new_output['total_all_credit'])
		{
			$output = $new_output['total_all_debit'];
		}
		else
		{
			$new_total_all_dc = $new_output['total_all_debit']-$new_output['total_all_credit'];

			$output = $new_total_all_dc;
		}

		// echo $output;
		// exit;

		return $output;
	}

    public function checkTransactionName($transaction_name)
	{
		// $res = $this->db->sql_prepare("select * from ml_journal where userid = :userid and transaction_name = :transaction_name");
		// $bindParam = $this->db->sql_bindParam(['userid' => get_user('id'), 'transaction_name' => $transaction_name], $res);
		// $row = $this->db->sql_fetch_single($bindParam);

        $row = DB::table('ml_journal')
            ->where('userid', session('id'))
            ->where('transaction_name', $transaction_name)->get();
            
        
		if ($row->count() > 0)
    		{
			$total = $row->count() + 1;

			$output = $transaction_name.' ('.$total.')';
		}
		else
		{
			$output = $transaction_name;
		}

		return $output;
	}


    public function get_account_receive($id) {
        $data = [];
        $group = [];
        
        $simpan = [];
        $kelompok = [];
        
        $user_id = session('id');

        
        
        if($id == 2 || $id == 5 || $id == 8 || $id == 9 || $id == 10){
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
        }



        if($id == 2 || $id == 8 || $id == 9 || $id == 10){
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
           
        }

        if($id == 10){
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
           
        }


        if($id == 3 || $id == 10){
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
           
        }


        

        if($id == 1 || $id == 5 || $id == 10) {
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
        }
            
        

        if($id == 5 || $id == 7){
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
           
        }
        
        if($id == 10){
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
           
        }

        if($id == 10){
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
        }

        

        if($id == 10){
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
        }

        if($id == 1 || $id == 5 || $id == 10){
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
           
        }

        
        if($id == 10){
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
        }

        if($id == 3 || $id == 10){
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
           
        }
        // ================== simpan ke==================================

        if($id == 1 || $id == 2 || $id == 3 || $id == 5 || $id == 7 || $id == 9 || $id == 10){
            $q = DB::table('ml_current_assets')
                ->where('userid', $user_id);
            if($id == 5) {
                $q->where('code', 'piutang-usaha');
            }
            
            $query = $q->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Aktiva Lancar";
                $row['account_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($simpan, $row);
            }
            array_push($kelompok, "Aktiva Lancar");
        }

        if($id == 2 || $id == 3 || $id == 7 || $id == 9 || $id == 10){
            $query = DB::table('ml_fixed_assets')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Aktiva Tetap";
                $row['account_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($simpan, $row);
            }
            array_push($kelompok, "Aktiva Tetap");
        }

        if($id == 10){
            $query = DB::table('ml_accumulated_depreciation')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Akumulasi Penyusutan";
                $row['account_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($simpan, $row);
            }
            array_push($kelompok, "Akumulasi Penyusutan");
        }

        if($id == 10){
            $query = DB::table('ml_shortterm_debt')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Utang Jangka Pendek";
                $row['account_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($simpan, $row);
            }
            array_push($kelompok, "Utang Jangka Pendek");
        }


        if($id == 10){
            $query = DB::table('ml_income')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Pendapatan";
                $row['account_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($simpan, $row);
            }
            array_push($kelompok, "Pendapatan");
        }



        if($id == 1 || $id == 2 || $id == 10){
            $query = DB::table('ml_cost_good_sold')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Harga Pokok Penjualan";
                $row['account_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($simpan, $row);
            }
            array_push($kelompok, "Harga Pokok Penjualan");
        }

        if($id == 2 || $id == 3 || $id == 10){
            $query = DB::table('ml_selling_cost')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Biaya Penjualan";
                $row['account_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($simpan, $row);
            }
            array_push($kelompok, "Biaya Penjualan");
        }

        if($id == 2 || $id == 3 || $id == 10){
            $query = DB::table('ml_admin_general_fees')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Biaya Umum Admin";
                $row['account_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($simpan, $row);
            }
            array_push($kelompok, "Biaya Umum Admin");
        }

        if($id == 10){
            $query = DB::table('ml_non_business_income')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Pendapatan Diluar Usaha";
                $row['account_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($simpan, $row);
            }
            array_push($kelompok, "Pendapatan Diluar Usaha");
        }

        if($id == 2 || $id == 3 || $id == 10){
            $query = DB::table('ml_non_business_expenses')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Biaya Diluar Usaha";
                $row['account_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($simpan, $row);
            }
            array_push($kelompok, "Biaya Diluar Usaha");
        }

        if($id == 8){
            $query = DB::table('ml_capital')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Modal";
                $row['account_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($simpan, $row);
            }
            array_push($kelompok, "Modal");
        }

        if($id == 10){
            $query = DB::table('ml_longterm_debt')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Utang Jangka Panjang";
                $row['account_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($simpan, $row);
            }
            array_push($kelompok, "Utang Jangka Panjang");
        }

        return response()->json([
            "data" => $data,
            "group" => $group,
            "simpan" => $simpan, 
            "kelompok" => $kelompok
        ]);
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

    public function journal_multiple_form() {
        $data = $this->get_account_select();
        return response()->json([
            "data" => $data,
            "success" => true
        ]);
    }


    public function journal_edit($id) {
        $view = 'journal-edit';
        $akun = $this->get_account_select();
        $data = DB::table('ml_journal')->where('id', $id)->first();
        $detail = DB::table('ml_journal_list')->where('journal_id', $id)->get();
        return view('main.journal_edit', compact('view', 'akun', 'data', 'detail'));
    }


    public function confirm_journal_delete(Request $request) {
        $input = $request->all();


        // $check = $this->db->num_rows("ml_journal", "", ['id' => $id, 'userid' => get_user('id')]);

        $check = DB::table('ml_journal')->where('id', $input['id'])->where('userid', session('id'))->count();
        

		if ($check)
		{
			// $this->db->sql_delete("ml_journal", ['id' => $id, 'userid' => get_user('id')]);
            DB::table('ml_journal')->where('id', $input['id'])->where('userid', session('id'))->delete();
			
			// $res_delete_journallist = $this->db->sql_prepare("select journal_id from ml_journal_list where journal_id = :journal_id");
			// $bindParam_delete_journallist = $this->db->sql_bindParam(['journal_id' => $id], $res_delete_journallist);

            $row_delete_journallist = DB::table('ml_journal_list')->where('journal_id', $input['id'])->get();

			// while ($row_delete_journallist = $this->db->sql_fetch_single($bindParam_delete_journallist))
			// {
			// 	$this->db->sql_delete("ml_journal_list", ['journal_id' => $row_delete_journallist['journal_id']]);

			// }

            

            foreach($row_delete_journallist as $rd) {
                DB::table('ml_journal_list')->where('journal_id', $rd->journal_id)->delete();
            }

			return response()->json([
                "success" => true,
                "message" => "success"
            ]);
		}
		else
		{
			return response()->json([
                "success" => false,
                "message" => "failed, no data to delete!"
            ]);
		}

    }
}
