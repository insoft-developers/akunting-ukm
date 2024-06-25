<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use Validator;
use Illuminate\Support\Str;
use DB;
use Session;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Redirect;
use Mail;
use App\Mail\RegisterMail;


class AccountController extends Controller
{
    
	public function logout(Request $request) {
        $request->session()->regenerate();
        $request->session()->invalidate();
        $request->session()->flush();
        return redirect('/frontend_login');
    }


	public function terimakasih() {
		return view('main.terimakasih');
	}


	public function account_activate(Request $request) {
		$input = $request->all();
		$cek = DB::table('mail_tokens')
			->where('token', $input['code']);
		
		if($cek->count() == 1) {
			$data = $cek->first();
			$enc_email = SHA1($data->email);
			if($enc_email == $input['id']) {
				$res = DB::table('ml_accounts')->where('email', $data->email)
					->update(['is_active'=>1]);

				DB::table('mail_tokens')->where('token', $input['code'])->delete();
				return Redirect::to('frontend_login')->with('success', "Congratulations your account has been activated now, Please Login!"); 
			} else {
				return Redirect::to('frontend_login')->with('error', "Failed, your email is invalid!"); 
			}
		} else {
			return  Redirect::to('frontend_login')->with('error', "Failed, Token is invalid!"); 
		}

	
	}

	
	public function login() {
		$view = 'login';
		return view('main.login', compact('view'));
	}
	
	public function login_action(Request $request) {
		$input = $request->all();
		$rules = array(
			"email" => "email|required",
			"password" => "required|min:6"
		);
		$validator = Validator::make($input, $rules);
		if($validator->fails()) {
			return redirect()->back()->withInput()->withErrors($validator);   
		} 

		try{
			$account = Account::where('email', $input['email'])->first();
			if (Hash::check($input['password'], $account->password)) {
				if($account->is_soft_delete == 1) {
					return Redirect::back()->with('error', "Account Not Found!");
				}else {
					if($account->is_active == 1) {
						$generate_token = Str::random(36);
						session(["id"=> $account->id, "username"=> $account->username, 'name'=> $account->fullname, "email"=> $account->email, "token"=> $generate_token, "is_upgraded"=> $account->is_upgraded]);

						Account::where('id', $account->id)
							->update(['token'=> $generate_token]);
						return Redirect::to('/');
					} else {
						return Redirect::back()->with('error', "Your account hasn't been activated yet!");
					}
					
				}
			} else {
				return Redirect::back()->with('error', "The email address or password you entered is incorrect, please try again!"); 
			}
		}catch(\Exception $e) {
			return redirect()->back()->with('error', "The email address or password you entered is incorrect, please try again!");  
		}
		

	}

	public function register(Request $request) {
		$input = $request->all();
		
		$referal = $request->ref == null ? '' : $request->ref;

        $view = "register";
		$category = Category::all();
		$district = DB::table('districts')
			->select('districts.name AS distrik', 'regencies.name AS kabupaten', 'provinces.name AS provinsi')
			->join('regencies', 'regencies.id', '=', 'districts.regency_id')
			->join('provinces', 'provinces.id', '=', 'regencies.province_id')
			->get();
        return view('main.register', compact('view', 'category', 'district', 'referal'));
    }

    public function signup(Request $request) {
        $input = $request->all();
        
        $rules = array(
            "email" => "required|email|unique:ml_accounts,email",
            "fullname" => "required|min:4|max:34",
			"username" => "required|unique:ml_accounts,username",
            "whatsapp" => "required",
            "password" => "required|min:6|confirmed",
            "tos" => "required",
			"category" => "required",
        );

        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);   
        }
        $uuid = (String) Str::uuid();
        $input['uuid'] = $uuid;
        $input['password'] = bcrypt($input['password']);
        $input['status'] = 0;
        $input['roles'] = 1;
        $input['role_code'] = 'general_member';
        $input['created'] = time();
		$rs = empty($input['referal_source']) ? "BERTUMBUH" : $input['referal_source'];

		$input['referal_source'] = $rs;
		$input['is_active'] = 0;
		$input['referal_code'] = uniqid();

        $query = Account::create($input);
        $id = $query->id;

        if($id == 1) {
            Account::where('id', $id)->update([
                "roles" => 99,
                "role_code" => "administrator",
            ]);
        }

		$bg = new \App\Models\BusinessGroup;
		$bg->user_id = $id;
		$bg->branch_name = empty($input['business_name']) ? $input['fullname'] : $input['business_name'];
		$bg->business_category = $input['category'];
		$bg->business_district = $input['district'];
		$bg->business_address =  $input['full_address'];
		$bg->business_phone = empty($input['business_phone']) ? $input['whatsapp'] : $input['business_phone'];
		$bg->model = "main";
		$bg->save();


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
		$this->sendMail($input['email'], $input['fullname']);

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



		

        return redirect('/terimakasih');        
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
			'Persedian Barang Setengah Jadi',
			'Persediaan Barang Dagang',
			'Piutang Usaha',
			'Sewa Bayar Dimuka',
			'Iklan Bayar Dimuka',
			'Randu Wallet',

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
			'Penjualan Produk',
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
			'Biaya Bonus Penjualan',
			'Biaya Pengiriman',
			'Biaya Penjualan Lain-Lain',
			'Biaya Pajak Penjualan',
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
			'Biaya Internet',
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
			'Biaya Administrasi Bank',
			'Biaya Lain Lain'
		];

		foreach ($data as $key => $value) 
		{
			$get_code = str_replace(" ", "-", strtolower($value));
			$get_code = str_replace("&", "-", $get_code);
			$get_code = str_replace("/", "-", $get_code);

			DB::table('ml_non_business_expenses')->insert(['userid'	=> $userid, 'name' => $value, 'code' => $get_code, 'account_code_id' => 12, 'can_be_deleted' => 1, 'created' => time()]);
		}
	}


	public function sendMail($email, $name) {
        $input['email'] = $email;
        $input['token'] = Str::random(64);
        
        $cek = DB::table('mail_tokens')->where('email', $email);
        if($cek->count() > 0) {
			DB::table('mail_tokens')->where('email', $email)->update(["token"=> $input['token']]);
        } else {
            DB::table('mail_tokens')->insert($input);
        }

        $details = [
            'nama' => $name,
            'email' => $email,
            'link' => $input['token'],
			'id' => SHA1($email)
        ];
         
        Mail::to($email)->send(new RegisterMail($details));
    }
}
