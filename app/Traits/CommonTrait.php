<?php
  namespace App\Traits;

  use DB;
  use Session;
  
  trait CommonTrait {
        public function get_data_transaction($key = '',  $coloum = '')
        {
            // Connected to the database
           

            // $res = $db->sql_prepare("select * from ml_transaction where id = :id");
            // $bindParam = $db->sql_bindParam(['id' => $key], $res);
            // $row = $db->sql_fetch_single($bindParam);

            $row = DB::table('ml_transaction')->where('id', $key)->first();
            // Prevent from Automatic conversion of false to array is deprecated
            return $row->$coloum;
            
        }

        public function get_user($key)
        {
            
            $id 		= session('id');
            $token		= session('token');
            $username	= session('username');

            $row = DB::table('ml_accounts')->where('id', $id)->where('username', $username)->first();
            return $row->$key;
        }

        public function getAllListAssetWithAccDataId($userid, $account_data_id, $account_code_id)
        {
           
            $output = '';

            if ($account_code_id == 1)
            {
                $output = $this->getListCurrentAssetWithAccDataId($userid, $account_data_id, $account_code_id);
            }

            elseif ($account_code_id == 2)
            {
                $output = $this->getListFixedAssetWithAccDataId($userid, $account_data_id, $account_code_id);
            }

            elseif ($account_code_id == 3)
            {
                $output = $this->getListAccumulatedDepreciationWithAccDataId($userid, $account_data_id, $account_code_id);
            }

            elseif ($account_code_id == 4)
            {
                $output = $this->getListShortTermDebtWithAccDataId($userid, $account_data_id, $account_code_id);
            }

            elseif ($account_code_id == 5)
            {
                $output = $this->getListLongTermDebtWithAccDataId($userid, $account_data_id, $account_code_id);
            }

            elseif ($account_code_id == 6)
            {
                $output = $this->getListCapitalWithAccDataId($userid, $account_data_id, $account_code_id);
            }

            elseif ($account_code_id == 7)
            {
                $output = $this->getListIncomeWithAccDataId($userid, $account_data_id, $account_code_id);
            }

            elseif ($account_code_id == 8)
            {
                $output = $this->getListCostGoodSoldWithAccDataId($userid, $account_data_id, $account_code_id);
            }

            elseif ($account_code_id == 9)
            {
                $output = $this->getListSellingCostWithAccDataId($userid, $account_data_id, $account_code_id);
            }

            elseif ($account_code_id == 10)
            {
                $output = $this->getListAdminGeneralFeesWithAccDataId($userid, $account_data_id, $account_code_id);
            }

            elseif ($account_code_id == 11)
            {
                $output = $this->getListNonBusinessIncomeWithAccDataId($userid, $account_data_id, $account_code_id);
            }

            elseif ($account_code_id == 12)
            {
                $output = $this->getListNonBusinessExpensesWithAccDataId($userid, $account_data_id, $account_code_id);
            }

            return $output;
        }

        protected function getListCurrentAssetWithAccDataId($userid, $account_data_id, $account_code_id)
        {
            $row = DB::table('ml_current_assets')
                ->where('userid', $userid)
                ->where('id', $account_data_id)
                ->where('account_code_id', $account_code_id)
                ->first();

            return $row->name;
        }

        protected function getListFixedAssetWithAccDataId($userid, $account_data_id, $account_code_id)
        {
            

            $row = DB::table('ml_fixed_assets')
                ->where('userid', $userid)
                ->where('id', $account_data_id)
                ->where('account_code_id', $account_code_id)
                ->first();

            return $row->name;
        }

        protected function getListAccumulatedDepreciationWithAccDataId($userid, $account_data_id, $account_code_id)
        {
            $row = DB::table('ml_accumulated_depreciation')
                ->where('userid', $userid)
                ->where('id', $account_data_id)
                ->where('account_code_id', $account_code_id)
                ->first();

            return $row->name;
        }

        protected function getListShortTermDebtWithAccDataId($userid, $account_data_id, $account_code_id)
        {
        
            $row = DB::table('ml_shortterm_debt')
            ->where('userid', $userid)
            ->where('id', $account_data_id)
            ->where('account_code_id', $account_code_id)
            ->first();

            return $row->name;
        }

        protected function getListLongTermDebtWithAccDataId($userid, $account_data_id, $account_code_id)
        {
            
            $row = DB::table('ml_longterm_debt')
            ->where('userid', $userid)
            ->where('id', $account_data_id)
            ->where('account_code_id', $account_code_id)
            ->first();

            return $row->name;
        }

        protected function getListCapitalWithAccDataId($userid, $account_data_id, $account_code_id)
        {
           

            $row = DB::table('ml_capital')
            ->where('userid', $userid)
            ->where('id', $account_data_id)
            ->where('account_code_id', $account_code_id)
            ->first();

            return $row->name;
        }

        protected function getListIncomeWithAccDataId($userid, $account_data_id, $account_code_id)
        {
          
            $row = DB::table('ml_income')
            ->where('userid', $userid)
            ->where('id', $account_data_id)
            ->where('account_code_id', $account_code_id)
            ->first();

            return $row->name;
        }

        protected function getListCostGoodSoldWithAccDataId($userid, $account_data_id, $account_code_id)
        {
           

            $row = DB::table('ml_cost_good_sold')
            ->where('userid', $userid)
            ->where('id', $account_data_id)
            ->where('account_code_id', $account_code_id)
            ->first();

            return $row->name;
        }

        protected function getListSellingCostWithAccDataId($userid, $account_data_id, $account_code_id)
        {
            
            $row = DB::table('ml_selling_cost')
            ->where('userid', $userid)
            ->where('id', $account_data_id)
            ->where('account_code_id', $account_code_id)
            ->first();

            return $row->name;
        }

        protected function getListAdminGeneralFeesWithAccDataId($userid, $account_data_id, $account_code_id)
        {
           

            $row = DB::table('ml_admin_general_fees')
            ->where('userid', $userid)
            ->where('id', $account_data_id)
            ->where('account_code_id', $account_code_id)
            ->first();

            return $row->name;
        }

        protected function getListNonBusinessIncomeWithAccDataId($userid, $account_data_id, $account_code_id)
        {
           

            $row = DB::table('ml_non_business_income')
            ->where('userid', $userid)
            ->where('id', $account_data_id)
            ->where('account_code_id', $account_code_id)
            ->first();

            return $row->name;
        }

        protected function getListNonBusinessExpensesWithAccDataId($userid, $account_data_id, $account_code_id)
        {
            

            $row = DB::table('ml_non_business_expenses')
            ->where('userid', $userid)
            ->where('id', $account_data_id)
            ->where('account_code_id', $account_code_id)
            ->first();

            return $row->name;
        }

  }