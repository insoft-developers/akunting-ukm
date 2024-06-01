<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use Validator;

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

        

    }
}
