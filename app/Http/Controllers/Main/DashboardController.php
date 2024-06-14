<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MlTransaction;
use DB;
use Session;

class DashboardController extends Controller
{
    public function index() {
        $view = "jurnal";
        $list_transaksi = MlTransaction::orderBy('created', 'desc')->get();
        return view('main.dashboard', compact('view','list_transaksi'));
    }

    public function get_account_receive($id) {
        $data = [];
        $group = [];
        $user_id = session('id');

        
        if($id == 1) {
            $query = DB::table('ml_income')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Pendapatan";
                $row['acount_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($data, $row);
            }
            array_push($group, 'Pendapatan');
        }
            
        if($id == 1){
            $query = DB::table('ml_non_business_income')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Pendapatan Di Luar Usaha";
                $row['acount_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($data, $row);
            }
            array_push($group, "Pendapatan Di Luar Usaha");
           
        }

        if($id == 2){
            $query = DB::table('ml_current_assets')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Aktiva Lancar";
                $row['acount_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($data, $row);
            }
            array_push($group, "Aktiva Lancar");
           
        }

        if($id == 2){
            $query = DB::table('ml_fixed_assets')
                ->where('userid', $user_id)
                ->get();

            foreach($query as $key) {
                $row['id'] = $key->id;
                $row['group'] = "Aktiva Tetap";
                $row['acount_code_id'] = $key->account_code_id;
                $row['code'] = $key->code;
                $row['name'] = $key->name;
                array_push($data, $row);
            }
            array_push($group, "Aktiva Tetap");
           
        }

        return response()->json([
            "data" => $data,
            "group" => $group
        ]);
    }
}
