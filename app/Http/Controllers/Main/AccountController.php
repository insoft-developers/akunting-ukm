<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use Validator;
use Illuminate\Support\Str;
use DB;
use Session;

class AccountController extends Controller
{
    public function register() {
        $view = "register";
        return view('main.register');
    }

    public function signup(Request $request) {
        $input = $request->all();
        
        $rules = array(
            "email" => "required|email|unique:ml_accounts,email",
            "fullname" => "required|min:4|max:34",
            "whatsapp" => "required",
            "password" => "required|min:6|confirmed",
            "tos" => "required"
        );

        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);   
        }
        $uuid = (String) Str::uuid();
        $input['uuid'] = $uuid;
        $input['username'] = $uuid;
        $input['password'] = bcrypt($input['password']);
        $input['status'] = 0;
        $input['roles'] = 1;
        $input['role_code'] = 'general_member';
        $input['created'] = time();
        $query = Account::create($input);
        $id = $query->id;

        if($id == 1) {
            Account::where('id', $id)->update([
                "roles" => 99,
                "role_code" => "administrator",
            ]);
        }

        $this->insert_ml_current_assets($id);
        $this->insert_ml_fixed_assets($id);
        $this->insert_ml_accumulated_depreciation($id);
        $this->insert_ml_shortterm_debt($id);
        $this->insert_ml_longterm_debt($id);
        $this->insert_ml_capital($id);
        $this->insert_ml_income($id);
        $this->insert_ml_cost_good_sold($id);
        $this->insert_ml_selling_cost($id);
        $this->insert_ml_admin_general_fees($id);
        $this->insert_ml_non_business_income($id);
        $this->insert_ml_non_business_expenses($id);

        $create_user_info = ['user_id' => $id, 'phone_number' => $input['whatsapp']];
        DB::table('ml_user_information')->insert($create_user_info);

        // Automaticaly login to dashboard afte successfuly signup
        // $res = $this->db->sql_prepare("select id, username from ml_accounts where id = :id");
        // $bindParam = $this->db->sql_bindParam(['id' => $id], $res);
        // $row = $this->db->sql_fetch_single($bindParam);

        

        $generate_token = Str::random(36);

        // $set_session	= ['id' => $row['id'], 'client_id' => 0, 'username' => $row['username'], 'token' => $generate_token];
        session(["id"=> $id, "client_id"=> 0, "username" => $uuid, "token"=> $generate_token]);

        // $this->db->sql_update(['token' => $generate_token], 'ml_accounts', ['id' => $row['id']]);
        Account::where('id',$id)->update([
            "token" => $generate_token,
        ]);
        
        // Add script code for marketing subscriber
        $params = 
        [
            'first_name' 	=> $input['fullname'],
            'email' 		=> $input['email'],
            'mobile'		=> $input['whatsapp'],
            'api_token' 	=> '32eb25687322b584c9e87464dbef07fa',
            'list_id' 		=> '42898'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"https://api.mailketing.co.id/api/v1/addsubtolist");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);

        // echo json_encode(['status' => 'success', 'url' => base_url('manage_journal')]);
        // exit;


        return redirect('/');        
    }


    public function insert_ml_current_assets($userid)
	{
		$data = 
		[
			'Kas',
			'Bank BCA',
			'Bank Mandiri',
			'Bank BRI',
			'Bank BNI',
			'Piutang COD',
			'Piutang Marketplace',
			'Perlengkapan',
			'Persediaan Bahan Baku',
			'Persediaan Barang Dagang',
			'Piutang Usaha',
			'Sewa Bayar Dimuka',
			'Iklan Bayar Dimuka'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_current_assets')->insert(['userid'=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 1, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}

	public function insert_ml_fixed_assets($userid)
	{
		$data = 
		[
			'Tanah',
			'Bangunan',
			'Kendaraan',
			'Peralatan'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_fixed_assets')->insert(['userid'	=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 2, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}

	public function insert_ml_accumulated_depreciation($userid)
	{
		$data = 
		[
			'Akumulasi Penyusutan Kendaraan',
			'Akumulasi Penyusutan Peralatan',
			'Akumulasi Penyusutan Bangunan'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_accumulated_depreciation')->insert(['userid'	=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 3, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}

	public function insert_ml_shortterm_debt($userid)
	{
		$data = 
		[
			'Utang Usaha'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_shortterm_debt')->insert(['userid'	=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 4, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}

	public function insert_ml_longterm_debt($userid)
	{
		$data = 
		[
			'Utang Bank'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_longterm_debt')->insert(['userid'	=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 5, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}

	public function insert_ml_capital($userid)
	{
		$data = 
		[
			'Modal Pemilik',
			'Prive'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_capital')->insert(['userid'	=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 6, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}

	public function insert_ml_income($userid)
	{
		$data = 
		[
			'Pendapatan',
			'Penjualan Barang',
			'Ikhtisar Laba/Rugi',
			'Potongan Penjualan',
			'Retur Penjualan'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_income')->insert(['userid'	=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 7, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}

	public function insert_ml_cost_good_sold($userid)
	{
		$data = 
		[
			'Harga Pokok Penjualan',
			'Potongan Pembelian',
			'Retur Pembelian'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_cost_good_sold')->insert(['userid'	=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 8, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}

	public function insert_ml_selling_cost($userid)
	{
		$data = 
		[
			'Biaya Pengiriman',
			'Biaya Penjualan Lain-Lain',
			'Biaya Iklan',
			'Biaya Retur Penjualan',
			'Biaya Kerusakan Barang'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_selling_cost')->insert(['userid'	=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 9, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}

	public function insert_ml_admin_general_fees($userid)
	{
		$data = 
		[
			'Biaya Air',
			'Biaya Depresiasi Peralatan',
			'Biaya Gaji Karyawan',
			'Biaya Listrik',
			'Biaya Makan dan Minum',
			'Biaya Perlengkapan',
			'Biaya Sewa Tempat Usaha',
			'Biaya Telepon',
			'Biaya Umum Lain-Lain',
			'Biaya Penyusutan Bangunan',
			'Biaya Penyusutan Kendaraan',
			'Biaya Penyusutan Peralatan',
			'Beban Piutang Tak Tertagih',
			'Biaya Diluar Usaha'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_admin_general_fees')->insert(['userid'	=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 10, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}

	public function insert_ml_non_business_income($userid)
	{
		$data = 
		[
			'Pendapatan Bunga Bank',
			'Pendapatan Hasil Panen'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_non_business_income')->insert(['userid'	=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 11, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}

	public function insert_ml_non_business_expenses($userid)
	{
		$data = 
		[
			'Biaya Administrasi Bank'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_non_business_expenses')->insert(['userid'	=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 12, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}
}
