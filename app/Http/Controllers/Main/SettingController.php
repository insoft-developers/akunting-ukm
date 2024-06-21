<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class SettingController extends Controller
{
    public function index() {
        $view = 'setting';
        return view('main.setting', compact('view'));
    }

    public function generate_opening_balance() {
        $view = 'opening-balance';
        return view('main.generate_open_balance', compact('view'));
    }

    public function submit_opening_balance(Request $request) {
        $input = $request->all();


        $custom_date = $input['month'].'-'.$input['year'];
        // $custom_date = 'April-2023';
        
        $get_first_day_of_prev_month = date("M-d-Y", strtotime($custom_date." first day of previous month"));
        $get_last_day_of_prev_month = date("M-d-Y", strtotime($custom_date." last day of previous month"));

        $timezone 		= +7;
        $from_date		= strtotime($get_first_day_of_prev_month.' 00:00:00');
        $for_to_date	= strtotime($get_last_day_of_prev_month.' 23:59:59');
        
        $time[] = $from_date;
        $time[] = $for_to_date;
        
        $init_to_date	= $for_to_date+$timezone*3600;
        $to_date		= strtotime(gmdate('M t Y', $init_to_date));

        $new_output = [];
        
        

        $output[session('id')] = $this->init_balance_sheet($from_date, $to_date, $input['month'], $input['year'], session('id'));
// 			$output[session('id')] = $this->init_balance_sheet($from_date, $to_date, 'April', '2023', session('id'));
        
        

        foreach ($output as $key1 => $value1) 
        {
            foreach ($value1 as $key2 => $value2) 
            {
                if (is_array($value2))
                {
                    foreach ($value2 as $key3 => $value3) 
                    {
                        $get_each_estimation = preg_split("#_#", $value3['estimation']);
                        
                        if(isset($get_each_estimation[1])){
                            if ($get_each_estimation[1] == 6)
                            {
                                if ($this->getIsCapital(session('id'), $get_each_estimation[1], $get_each_estimation[0]) == 'modal-pemilik')
                                {
                                    $new_output[$key1]['estimation'][] 	= $value3['estimation'];
                                    
                                    if ($value3['calculate_total_dc'] < 0)
                                    {
                                        $new_output[$key1]['debit_real'][]	= abs($value3['calculate_total_dc']);
                                        $new_output[$key1]['credit_real'][] = 0;
                                    }
                                    else
                                    {
                                        $new_output[$key1]['debit_real'][]	= $value3['debit_real'];
                                        $new_output[$key1]['credit_real'][] = $value3['calculate_total_dc'];
                                    }
                                }
                                else
                                {
                                    if ($this->getIsCapital(session('id'), $get_each_estimation[1], $get_each_estimation[0]) != 'prive')
                                    {
                                        $new_output[$key1]['estimation'][] 	= $value3['estimation'];
                                        $new_output[$key1]['debit_real'][]	= $value3['debit_real'];
                                        $new_output[$key1]['credit_real'][] = $value3['credit_real'];
                                    }
                                }
                            }
                            else
                            {
                                $new_output[$key1]['estimation'][] 	= $value3['estimation'];
                                $new_output[$key1]['debit_real'][]	= $value3['debit_real'];
                                $new_output[$key1]['credit_real'][] = $value3['credit_real'];
                            }
                        }else{
                            $new_output[$key1]['estimation'][] 	= $value3['estimation'];
                            $new_output[$key1]['debit_real'][]	= $value3['debit_real'];
                            $new_output[$key1]['credit_real'][] = $value3['credit_real'];
                        }
                    }
                }
            }
        }

        if (isset($new_output[session('id')]))
        {
            $this->insert_opening_balance_rc($new_output[session('id')]['estimation'], $new_output[session('id')]['debit_real'], $new_output[session('id')]['credit_real'], session('id'), $custom_date);
        }

        echo json_encode(['status' => 'success', 'msg' => 'Success']);

		

        return response()->json($input);
    }

    public function get_net_profit_loss($from_date, $to_date, $month_to, $year_to)
	{
		while ($from_date < $to_date)
		{
			$group_by_month = date('F Y', $from_date);
			
			$convert_month 	= date_parse($month_to);
			$get_last_day 	= cal_days_in_month(CAL_GREGORIAN, $convert_month['month'], $year_to);

			$new_from_group_by_month = strtotime(date("01-m-Y", strtotime($group_by_month)).' 00:00:00');
			$new_to_group_by_month = strtotime(date("t-m-Y", strtotime($group_by_month)).' 23:59:59');

			$from_date = strtotime("+1 month", $from_date);

			$output['group_by_month'][$group_by_month] 	= $group_by_month;
			$output[$group_by_month] 					= $this->init_gross_net_profit($group_by_month, $new_from_group_by_month, $new_to_group_by_month);
			$output['total_all_dc'][] 					= $output[$group_by_month]['total_net_profit'];
		}

		return $output['total_all_dc'];	
	}

	public function insert_opening_balance_rc($estimation = array(), $debit_real =  array(), $credit_real = array(), $userid = 0, $custom_date = 0)
	{
		// Delete and replace Saldo Awal with new one above
		$this->deleteOpeningBalancePerUser($custom_date);
		
		$nominal = str_replace(',', '', $debit_real[0]);
		$get_id_transaction = (isset($estimation[1])) ? $this->initTransactionId($estimation[0], $estimation[1]) : 0;
		$get_estimation_1 	= (isset($estimation[1])) ? $estimation[1] : 0;

		$get_first_day_of_this_month 		= date("M-d-Y", strtotime($custom_date." first day of this month"));
		$get_first_day_of_prev_1month 		= date("M-d-Y", strtotime("first day of -1 month"));

		$convert_first_day_of_this_month	= strtotime($get_first_day_of_this_month.' 00:00:00');

		$data_journal = [
			'userid'				=> $userid,
			'transaction_id'		=> $get_id_transaction,
			'transaction_name'		=> 'Saldo Awal',
			'rf_accode_id'			=> $estimation[0],
			'st_accode_id'			=> $get_estimation_1,
			'nominal'				=> $nominal,
			'is_opening_balance' 	=> 1,
			'color_date'			=> get_data_transaction($get_id_transaction, 'color'),
			'created'				=> $convert_first_day_of_this_month
		];

		// First insert data to journal
		$this->db->sql_insert($data_journal, 'ml_journal');

		// Get last ID Journal after insert
		$journal_id = $this->db->insert_id();

		if (is_array($estimation))
		{
			for ($i = 0; $i < count($estimation); $i++) 
			{ 
				if ($debit_real[$i] !== 0)
				{
					$debit = str_replace(',', '', $debit_real[$i]);

					if ($estimation[$i] !== '')
					{
						$account_code_id = preg_split("#_#", $estimation[$i]);
					}
					
					if(isset($account_code_id) && count($account_code_id) >= 2){
					    
					    
    					$data_debit = [
    						'journal_id'		=> $journal_id,
    						'rf_accode_id'		=> $estimation[$i],
    						'account_code_id'	=> (isset($account_code_id[1])) ? $account_code_id[1] : 0,
    						'asset_data_id'		=> (isset($account_code_id[0])) ? $account_code_id[0] : 0,
    						'asset_data_name'	=> getAllListAssetWithAccDataId($userid, ((isset($account_code_id[0])) ? $account_code_id[0] : 0), ((isset($account_code_id[1])) ? $account_code_id[1] : 0)),
    						'debet'				=> $debit,
    						'created'			=> $convert_first_day_of_this_month
    					];
    
    					// Second insert data to journal list
    					$this->db->sql_insert($data_debit, 'ml_journal_list');
					}
				}
				
				if ($credit_real[$i] !== 0)
				{
					$credit = str_replace(',', '', $credit_real[$i]);

					if ($estimation[$i] !== '')
					{
						$account_code_id = preg_split("#_#", $estimation[$i]);
					}
					
					if(isset($account_code_id) && count($account_code_id) >= 2){

    					$data_credit = [
    						'journal_id'		=> $journal_id,
    						'st_accode_id'		=> $estimation[$i],
    						'account_code_id'	=> $account_code_id[1],
    						'asset_data_id'		=> $account_code_id[0],
    						'asset_data_name'	=> getAllListAssetWithAccDataId($userid, $account_code_id[0], $account_code_id[1]),
    						'credit'			=> $credit,
    						'created'			=> $convert_first_day_of_this_month
    					];
    
    					// Second insert data to journal list
    					$this->db->sql_insert($data_credit, 'ml_journal_list');
					}
				}
			}
		}

		// Update total saldo
		$reCalculateTotalSaldo = $this->checkTotalBalance($journal_id);

		// Second update data to journal
		$this->db->sql_update(['total_balance' => $reCalculateTotalSaldo], 'ml_journal', ['id' => $journal_id]);
	}

	public function initTransactionId($account_code_id1, $account_code_id2)
	{
		$new_output = '';

		$var_rf_cid = preg_split("#_#", $account_code_id1);
		$rf_id 		= $var_rf_cid[0];
		$rf_code_id = $var_rf_cid[1];

		$var_st_cid = preg_split("#_#", $account_code_id2);
		$st_id 		= $var_st_cid[0];
		$st_code_id = $var_st_cid[1];

		$res1 = $this->db->sql_prepare("select * from ml_transaction_subcat where account_code_id = :id and received_from_status = 0 order by id");
		$bindParam1 = $this->db->sql_bindParam(['id' => $rf_code_id], $res1);
		while ($row1 = $this->db->sql_fetch_single($bindParam1))
		{
			$output1[] = $row1['transaction_id'];
		}

		$res2 = $this->db->sql_prepare("select * from ml_transaction_subcat where account_code_id = :id and saved_to_status = 0 order by id");
		$bindParam2 = $this->db->sql_bindParam(['id' => $st_code_id], $res2);
		while ($row2 = $this->db->sql_fetch_single($bindParam2))
		{
			$output2[] = $row2['transaction_id'];
		}

		if (isset($output1) && isset($output2))
		{
			$output3 = array_uintersect($output1, $output2, "strcasecmp");

			$k = array_rand($output3);
			$new_output = $output3[$k];
		}

		return $new_output;
	}

	public function init_balance_sheet($from_date, $to_date, $month_to, $year_to, $userid)
	{	
		$total_assets = 0;
		$total_debt = 0;

		$total_current_assets = 0;
		$total_fixed_assets = 0;

		$total_short_term_debt = 0;
		$total_long_term_debt = 0;

		$total_capital = 0;

		$res = $this->db->sql_select("select * from ml_account_code order by id");
		while ($row = $this->db->sql_fetch_single($res))
		{
			$data_list_balance_sheet1 = 
			[
				'Current Assets', 'Fixed Assets'
			];

			$data_list_balance_sheet2 = 
			[
				'Short Term Debt', 'Long Term Debt'
			];

			$data_list_balance_sheet3 = 
			[
				'Capital'
			];

			if (in_array($row['name'], $data_list_balance_sheet1))
			{
				$output[$row['name']] = $this->assets_debt_capital($row['id'], $from_date, $to_date, $userid);

				if (is_array($this->assets_debt_capital($row['id'], $from_date, $to_date, $userid)))
				{
					foreach ($this->assets_debt_capital($row['id'], $from_date, $to_date, $userid) as $key1 => $value1) 
					{
						foreach ($value1 as $key2 => $value2) 
						{
							if ($key2 == 'calculate_total_dc')
							{
								if ($row['name'] == 'Current Assets')
								{
									$total_current_assets += $value2;
								}
								elseif ($row['name'] == 'Fixed Assets')
								{
									$total_fixed_assets += $value2;
								}
							}
						}
					}
				}
			}

			if (in_array($row['name'], $data_list_balance_sheet2))
			{
				$output[$row['name']] = $this->assets_debt_capital($row['id'], $from_date, $to_date, $userid, ['Short Term Debt', 'Long Term Debt']);

				if (is_array($this->assets_debt_capital($row['id'], $from_date, $to_date, $userid, ['Short Term Debt', 'Long Term Debt'])))
				{
					foreach ($this->assets_debt_capital($row['id'], $from_date, $to_date, $userid, ['Short Term Debt', 'Long Term Debt']) as $key1 => $value1) 
					{
						foreach ($value1 as $key2 => $value2) 
						{
							if ($key2 == 'calculate_total_dc')
							{
								if ($row['name'] == 'Short Term Debt')
								{
									$total_short_term_debt += $value2;
								}
								elseif ($row['name'] == 'Long Term Debt')
								{
									$total_long_term_debt += $value2;
								}
							}
						}
					}
				}
			}

			if (in_array($row['name'], $data_list_balance_sheet3))
			{
				// if (is_array($this->assets_debt_capital($row['id'], $from_date, $to_date, $userid, ['Capital'])))
				// {
				// 	$output[$row['name']] = $this->assets_debt_capital($row['id'], $from_date, $to_date, $userid, ['Capital']);
				// }
				// else
				// {
				// 	$output[$row['name']] = $this->getEachCapital($userid, $row['id'], 'modal-pemilik');
				// }

				$output[$row['name']] = $this->assets_debt_capital($row['id'], $from_date, $to_date, $userid, ['Capital']);

				if (isset($this->assets_debt_capital($row['id'], $from_date, $to_date, $userid, ['Capital'])['Modal Pemilik']))
				{
					// $output_owner_capital['debit_real'] = 0;
					// $output_owner_capital['credit_real'] = 0;
					// $output_owner_capital['calculate_total_dc_new'] = 0;
					$prive = 0;
					$datax = $this->assets_debt_capital($row['id'], $from_date, $to_date, $userid, ['Capital']);
					if(isset($datax['Prive'])){
    					foreach ($datax['Prive'] as $key1 => $value1) 
    					{
    						 
    						if ($key1 == 'calculate_total_dc')
    						{ 
    						    $prive += $value1;   
    						}
    						 
    					}
					}

					foreach ($this->assets_debt_capital($row['id'], $from_date, $to_date, $userid, ['Capital'])['Modal Pemilik'] as $key1 => $value1) 
					{
						// $output_owner_capital['debit_real2'] = $this->getEachCapital2($userid, $row['id'], $from_date, $to_date, $month_to, $year_to, 'modal-pemilik')['Modal Pemilik']['debit_real'];
						// $output_owner_capital['credit_real2'] = $this->getEachCapital2($userid, $row['id'], $from_date, $to_date, $month_to, $year_to, 'modal-pemilik')['Modal Pemilik']['credit_real'];
						
						// if ($key1 == 'calculate_total_dc')
						// {
						// 	$output_owner_capital['calculate_total_dc_new'] = $value1+$this->getEachCapital2($userid, $row['id'], $from_date, $to_date, $month_to, $year_to, 'modal-pemilik')['Modal Pemilik']['calculate_total_dc'];
						// }

						if ($key1 == 'calculate_total_dc')
						{
						    //total modal  
						    $totalmodal = ($total_current_assets + $total_fixed_assets) - $total_short_term_debt - $total_long_term_debt;
						  //  $totalmodal = $value1 + $prive + $labarugi;
						    $output_owner_capital[$key1] =  $totalmodal;  
				// 			$output_owner_capital[$key1] = $this->getEachCapital2($userid, $row['id'], $from_date, $to_date, $month_to, $year_to, 'modal-pemilik')['Modal Pemilik']['calculate_total_dc']+$value1;
						}
						else
						{
							$output_owner_capital[$key1] = $value1;
						}
					}

					$output[$row['name']]['Modal Pemilik'] = $output_owner_capital;

					// $output[$row['name']]['Modal Pemilik'] = $this->getEachCapital2($userid, $row['id'], $from_date, $to_date, $month_to, $year_to, 'modal-pemilik')['Modal Pemilik'];
					// $output[$row['name']]['Modal Pemilik'] = $this->assets_debt_capital($row['id'], $from_date, $to_date, $userid, ['Capital'])['Modal Pemilik'];
				}
				else
				{
				    $varx = $output[$row['name']];
				    
					$output[$row['name']] = (int) $varx + (int) $this->getEachCapital2($userid, $row['id'], $from_date, $to_date, $month_to, $year_to, 'modal-pemilik');
				}

				if (is_array($this->assets_debt_capital($row['id'], $from_date, $to_date, $userid, ['Capital'])))
				{
					foreach ($this->assets_debt_capital($row['id'], $from_date, $to_date, $userid, ['Capital']) as $key1 => $value1) 
					{
						foreach ($value1 as $key2 => $value2) 
						{
							if ($key2 == 'calculate_total_dc')
							{
								if ($row['name'] == 'Capital')
								{
									$total_capital += $value2;
								}
							}
						}
					}
				}
			}
		}

		$output['total_assets'] 		= $total_current_assets+$total_fixed_assets;
		$output['total_debt'] 			= $total_short_term_debt+$total_long_term_debt;

		foreach ($this->get_net_profit_loss($from_date, $to_date, $month_to, $year_to) as $key => $value) 
		{
			$output['total_owners_capital'] = $value;
		}

		// $output['total_owners_capital']	= $output['total_assets']-$output['total_debt'];

		return $output;
	}

	public function init_gross_net_profit($group_by_month, $from_date, $to_date)
	{
		$total_net_income = 0;
		$total_cost_good_sold = 0;

		$total_all_net_profit = 0;

		$total_gross_profit = 0;
		$total_net_profit = 0;

		$total_selling_cost = 0;
		$total_admin_general_fees = 0;

		$total_non_business_income = 0;
		$total_non_business_expenses = 0;
		
		$total_current_assets = 0;
		$total_fix_assets = 0;
		

		$res = $this->db->sql_select("select * from ml_account_code order by id");
		while ($row = $this->db->sql_fetch_single($res))
		{
			$data_list_profit_loss = 
			[
				'Income', 'Cost Good Sold', 'Selling Cost', 'Admin General Fees', 'Non Business Income', 'Non Business Expenses' ,
				'Current Assets', 'Fixed Assets'
			];

			if (in_array($row['name'], $data_list_profit_loss))
			{
				$output[$row['name']] = $this->gross_net_profit($row['id'], $from_date, $to_date);

				if (is_array($this->gross_net_profit($row['id'], $from_date, $to_date)))
				{
					foreach ($this->gross_net_profit($row['id'], $from_date, $to_date) as $key1 => $value1) 
					{
						foreach ($value1 as $key2 => $value2) 
						{
							if ($key2 == 'calculate_total_dc')
							{
								if ($row['name'] == 'Income')
								{
									$total_net_income += $value2;
								}
								elseif ($row['name'] == 'Cost Good Sold')
								{
									$total_cost_good_sold += $value2;
								}
								elseif ($row['name'] == 'Selling Cost')
								{
									$total_selling_cost += $value2;
								}
								elseif ($row['name'] == 'Admin General Fees')
								{
									$total_admin_general_fees += $value2;
								}
								elseif ($row['name'] == 'Non Business Income')
								{
									$total_non_business_income += $value2;
								}
								elseif ($row['name'] == 'Non Business Expenses')
								{
									$total_non_business_expenses += $value2;
								}
								
								elseif ($row['name'] == 'Current Assets')
								{
									$total_current_assets += $value2;
								}
								elseif ($row['name'] == 'Fixed Assets')
								{
									$total_fix_assets += $value2;
								}
								
							}
						}
					}
				}
			}
		}

		$output['total_net_income'] 			= $total_net_income;
		$output['total_cost_good_sold'] 		= $total_cost_good_sold;
		$output['total_gross_profit'] 			= $total_net_income-$total_cost_good_sold;

		$calulcate_total1 						= $total_net_income-$total_cost_good_sold;

		$output['total_selling_cost'] 			= $total_selling_cost;
		$output['total_admin_general_fees'] 	= $total_admin_general_fees;

		$calulcate_total2 						= $total_selling_cost+$total_admin_general_fees;

		$output['total_non_business_income'] 	= $total_non_business_income;
		$output['total_non_business_expenses'] 	= $total_non_business_expenses;

		$calulcate_total3 						= $total_non_business_income+$total_non_business_expenses;
		
		//get prive
		$prive = 0;
		
		//get laba rugi
		$labarugi = 0;
		

		$output['total_net_profit'] 			= $calulcate_total1-$calulcate_total2-$calulcate_total3 + $prive + $labarugi;
		
		
	
		

		return $output  ;
	}

	public function assets_debt_capital($account_code_id, $from_date, $to_date, $userid, $group_by_name = array())
	{
		$res = $this->db->sql_prepare("select j.userid, j.id as journal_id, jl.id, jl.journal_id, jl.rf_accode_id, jl.st_accode_id, jl.account_code_id, jl.asset_data_name, jl.debet, jl.credit, jl.created from ml_journal as j right join ml_journal_list as jl on jl.journal_id = j.id where jl.created between :from_date and :to_date and j.userid = :userid and jl.account_code_id = :account_code_id order by jl.id");
		$bindParam = $this->db->sql_bindParam(['userid' => $userid, 'from_date' => $from_date, 'to_date' => $to_date, 'account_code_id' => $account_code_id], $res);
		while ($row = $this->db->sql_fetch_single($res))
		{
			$row['accode_id_data'] = ($row['rf_accode_id'] !== '' && $row['rf_accode_id'] !== null) ? $row['rf_accode_id'] : (($row['st_accode_id'] !== ''&& $row['st_accode_id'] !== null ) ? $row['st_accode_id'] : $row['rf_accode_id']);

			$first_output[$row['asset_data_name']][] = $row;
		}

		if ($this->db->sql_counts($bindParam))
		{
			foreach ($first_output as $key1 => $value1) 
			{				
				$output2[$key1] = $value1;
				$output2[$key1]['total_debet'] = 0;
				$output2[$key1]['total_credit'] = 0;

				$total_new_debit = 0;
				$total_new_credit = 0;

				foreach ($value1 as $key2 => $value2) 
				{
					$output2[$key1]['total_debet'] += $output2[$key1][$key2]['debet'];
					$output2[$key1]['total_credit'] += $output2[$key1][$key2]['credit'];

					if (is_array($group_by_name) && count($group_by_name) > 0 && $group_by_name !== NULL)
					{
						foreach ($group_by_name as $key => $value) 
						{
							if ($value == 'Short Term Debt' || $value == 'Long Term Debt')
							{
								$output2[$key1]['calculate_total_dc'] = $output2[$key1]['total_credit']-$output2[$key1]['total_debet'];
							
								if ($output2[$key1]['calculate_total_dc'] < 0)
								{
									$total_new_debit = $output2[$key1]['calculate_total_dc'];
									$total_new_credit = 0;
								}
								elseif ($output2[$key1]['calculate_total_dc'] > 0)
								{
									$total_new_debit = 0;
									$total_new_credit = abs($output2[$key1]['calculate_total_dc']);
								}
								elseif ($output2[$key1]['calculate_total_dc'] == 0)
								{
									$total_new_debit = 0;
									$total_new_credit = 0;
								}
							}
							elseif ($value == 'Capital')
							{
								$output2[$key1]['calculate_total_dc'] = $output2[$key1]['total_credit']-$output2[$key1]['total_debet'];
							
								if ($output2[$key1]['calculate_total_dc'] < 0)
								{
									$total_new_debit = abs($output2[$key1]['calculate_total_dc']);
									$total_new_credit = 0;
								}
								elseif ($output2[$key1]['calculate_total_dc'] > 0)
								{
									$total_new_debit = 0;
									$total_new_credit = $output2[$key1]['calculate_total_dc'];
								}
								elseif ($output2[$key1]['calculate_total_dc'] == 0)
								{
									$total_new_debit = 0;
									$total_new_credit = 0;
								}
							}
						}
					}
					else
					{
						$output2[$key1]['calculate_total_dc'] = $output2[$key1]['total_debet']-$output2[$key1]['total_credit'];
					
						if ($output2[$key1]['calculate_total_dc'] > 0)
						{
							$total_new_debit = abs($output2[$key1]['calculate_total_dc']);
							$total_new_credit = 0;
						}
						elseif ($output2[$key1]['calculate_total_dc'] < 0)
						{
							$total_new_debit = 0;
							$total_new_credit = abs($output2[$key1]['calculate_total_dc']);
						}
						elseif ($output2[$key1]['calculate_total_dc'] == 0)
						{
							$total_new_debit = 0;
							$total_new_credit = 0;
						}
					}

					$new_output[$key1]['estimation'] 			= $output2[$key1][$key2]['accode_id_data'];
					$new_output[$key1]['debit_real'] 			= $total_new_debit;
					$new_output[$key1]['credit_real'] 			= $total_new_credit;
					$new_output[$key1]['calculate_total_dc']	= $output2[$key1]['calculate_total_dc'];
				}
			}
		}
		else
		{
			$new_output = '';						
		}

		return $new_output;
	}

	public function gross_net_profit($account_code_id, $from_date, $to_date)
	{
		$res = $this->db->sql_prepare("select j.userid, j.transaction_name, j.id as journal_id, jl.id, jl.journal_id, jl.rf_accode_id, jl.st_accode_id, jl.account_code_id, jl.asset_data_name, jl.debet, jl.credit, jl.created from ml_journal as j right join ml_journal_list as jl on jl.journal_id = j.id where jl.created between :from_date and :to_date and j.userid = :userid and jl.account_code_id = :account_code_id and j.transaction_name != :transaction_name order by jl.id");
		$bindParam = $this->db->sql_bindParam(['userid' => get_user('id'), 'from_date' => $from_date, 'to_date' => $to_date, 'account_code_id' => $account_code_id, 'transaction_name' => 'Saldo Awal'], $res);
		while ($row = $this->db->sql_fetch_single($res))
		{
			$output[$row['asset_data_name']][] = $row;
		}

		if ($this->db->sql_counts($bindParam))
		{
			foreach ($output as $key1 => $value1) 
			{
				$output2[$key1] = $value1;
				$output2[$key1]['total_debet'] = 0;
				$output2[$key1]['total_credit'] = 0;

				$total_new_debet = 0;
				$total_new_credit = 0;

				foreach ($value1 as $key2 => $value2) 
				{
					$output2[$key1]['total_debet'] += $output2[$key1][$key2]['debet'];
					$output2[$key1]['total_credit'] += $output2[$key1][$key2]['credit'];

					$new_output[$key1]['calculate_total_dc'] = $output2[$key1]['total_debet']-$output2[$key1]['total_credit'];
				}
			}	
		}
		else
		{
			$res2 = $this->db->sql_prepare("select j.userid, j.transaction_name, j.id as journal_id, jl.id, jl.journal_id, jl.account_code_id, jl.asset_data_name from ml_journal as j right join ml_journal_list as jl on jl.journal_id = j.id where j.userid = :userid and jl.account_code_id = :account_code_id and j.transaction_name != :transaction_name order by jl.id");
			$bindParam2 = $this->db->sql_bindParam(['userid' => get_user('id'), 'account_code_id' => $account_code_id, 'transaction_name' => 'Saldo Awal'], $res2);
			while ($row2 = $this->db->sql_fetch_single($bindParam2))
			{
				$output2[$row2['asset_data_name']][] = $row2;
			}
			
			if ($this->db->sql_counts($bindParam2))
			{
				foreach ($output2 as $key1 => $value1)
				{
					$new_output[$key1]['calculate_total_dc'] = 0;
				}
			}
			else
			{
				$new_output = '';
			}
		}

		return $new_output;	
	}

	protected function checkTotalBalance($journal_id)
	{
		$total_all_debit 	= 0;
		$total_all_credit 	= 0;

		$i = 0;
		$res_journal_list = $this->db->sql_prepare("select * from ml_journal_list where journal_id = :journal_id order by id");
		$bindParam_journal_list = $this->db->sql_bindParam(['journal_id' => $journal_id], $res_journal_list);
		while ($row_journal_list = $this->db->sql_fetch_single($bindParam_journal_list))
		{
			$total_all_debit += $row_journal_list['debet'];
			$total_all_credit += $row_journal_list['credit'];			
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

		return $output;
	}

	public function checkTotalBalance2($journal_id)
	{
		$total_all_debit 	= 0;
		$total_all_credit 	= 0;

		$i = 0;
		$res_journal_list = $this->db->sql_prepare("select * from ml_journal_list where journal_id = :journal_id order by id");
		$bindParam_journal_list = $this->db->sql_bindParam(['journal_id' => $journal_id], $res_journal_list);
		while ($row_journal_list = $this->db->sql_fetch_single($bindParam_journal_list))
		{
			$total_all_debit += $row_journal_list['debet'];
			$total_all_credit += $row_journal_list['credit'];			
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

		echo $output;
	}

	public function getEachCapital($userid, $account_code_id, $code)
	{
		$res = $this->db->sql_prepare("select * from ml_capital where userid = :userid and account_code_id = :account_code_id and code = :code order by id asc");
		$bindParam = $this->db->sql_bindParam(['userid' => $userid, 'account_code_id' => $account_code_id, 'code' => $code], $res);
		while ($row = $this->db->sql_fetch_single($res))
		{
			$output[$row['name']]['estimation'] 		= $row['id'].'_'.$row['account_code_id'];
			$output[$row['name']]['debit_real'] 		= 0;
			$output[$row['name']]['credit_real'] 		= 0;
			$output[$row['name']]['calculate_total_dc'] = 0;
		}

		if ( ! $this->db->sql_counts($res))
		{
			$output = '';
		}

		return $output;
	}

	public function getEachCapital2($userid, $account_code_id, $from_date, $to_date, $month_to, $year_to, $code)
	{
		$res = $this->db->sql_prepare("select * from ml_capital where userid = :userid and account_code_id = :account_code_id and code = :code order by id asc");
		$bindParam = $this->db->sql_bindParam(['userid' => $userid, 'account_code_id' => $account_code_id, 'code' => $code], $res);
		while ($row = $this->db->sql_fetch_single($res))
		{
			foreach ($this->get_net_profit_loss($from_date, $to_date, $month_to, $year_to) as $key => $value) 
			{
				/*
				if ($value < 0)
				{
					$debit_real = abs($value);
					$credit_real = 0;
				}
				elseif ($value > 0)
				{
					$debit_real = 0;
					$credit_real = abs($value);
				}
				elseif ($value == 0)
				{
					$debit_real = 0;
					$credit_real = 0;
				}
				*/

				if ($value < 0)
				{
					$total_all_net_profit = $value;
				}
				else
				{
					$total_all_net_profit = abs($value);
				}
			}

			$output[$row['name']]['estimation'] 		= $row['id'].'_'.$row['account_code_id'];
			$output[$row['name']]['debit_real'] 		= 0;
			$output[$row['name']]['credit_real'] 		= 0;
			$output[$row['name']]['calculate_total_dc'] = $total_all_net_profit;
		}

		if ( ! $this->db->sql_counts($res))
		{
			$output = '';
		}

		return $output;
	}

	public function getIsCapital($userid, $account_code_id, $id)
	{
		$res = $this->db->sql_prepare("select * from ml_capital where userid = :userid and account_code_id = :account_code_id and id = :id order by id asc");
		$bindParam = $this->db->sql_bindParam(['userid' => $userid, 'account_code_id' => $account_code_id, 'id' => $id], $res);

		if ($this->db->sql_counts($res))
		{
			$row = $this->db->sql_fetch_single($res);

			$output = $row['code'];
		}
		else
		{
			$output = '';
		}

		return $output;
	}

	public function deleteAllOpeningBalance()
	{
		// Previous 1 month
		$get_first_day_of_prev_1month = date("M-d-Y", strtotime("first day of -1 month"));

		// This month
		$get_first_day_of_this_month = date("M-d-Y", strtotime("first day of this month"));
		// $get_last_day_of_this_month = date("M-d-Y", strtotime("last day of this month"));

		$timezone 		= +7;
		$from_date		= strtotime($get_first_day_of_prev_1month.' 00:00:00');
		$for_to_date	= strtotime($get_first_day_of_prev_1month.' 23:59:59');
		
		$init_to_date	= $for_to_date+$timezone*3600;
		$to_date		= strtotime(gmdate('M t Y', $init_to_date));

		$res = $this->db->sql_prepare("select * from ml_journal where created between :from_date and :to_date and transaction_name = :transaction_name");
		$bindParam = $this->db->sql_bindParam(['from_date' => $from_date, 'to_date' => $to_date, 'transaction_name' => 'Saldo Awal'], $res);
		
		if ($this->db->sql_counts($bindParam))
		{
			// $row = $this->db->sql_fetch_single($bindParam);

			while ($row = $this->db->sql_fetch_single($bindParam))
			{
				$res_delete1 = $this->db->sql_prepare("delete from ml_journal where id = :id and created between :from_date and :to_date and transaction_name = :transaction_name");
				$bindParam_delete1 = $this->db->sql_bindParam(['id' => $row['id'], 'from_date' => $from_date, 'to_date' => $to_date, 'transaction_name' => 'Saldo Awal'], $res_delete1);

				$res_delete_journallist = $this->db->sql_prepare("select journal_id from ml_journal_list where journal_id = :journal_id and created between :from_date and :to_date");
				$bindParam_delete_journallist = $this->db->sql_bindParam(['journal_id' => $row['id'], 'from_date' => $from_date, 'to_date' => $to_date], $res_delete_journallist);
				while ($row_delete_journallist = $this->db->sql_fetch_single($bindParam_delete_journallist))
				{
					$res_delete2 = $this->db->sql_prepare("delete from ml_journal_list where journal_id = :journal_id and created between :from_date and :to_date");
					$bindParam_delete2 = $this->db->sql_bindParam(['journal_id' => $row_delete_journallist['journal_id'], 'from_date' => $from_date, 'to_date' => $to_date], $res_delete2);
				}
			}
		}
	}

	public function deleteOpeningBalancePerUser($custom_date)
	{
		$get_first_day_of_this_month 	= date("M-d-Y", strtotime($custom_date." first day of this month"));
		$get_last_day_of_this_month 	= date("M-d-Y", strtotime($custom_date." last day of this month"));
		
		$convert_first_day_of_this_month 	= strtotime($get_first_day_of_this_month.' 00:00:00');
		$convert_last_day_of_this_month		= strtotime($get_last_day_of_this_month.' 23:59:59');

		$res = $this->db->sql_prepare("select * from ml_journal where created between :from_date and :to_date and userid = :userid and transaction_name = :transaction_name limit 1");
		$bindParam = $this->db->sql_bindParam(['from_date' => $convert_first_day_of_this_month, 'to_date' => $convert_last_day_of_this_month, 'userid' => get_user('id'), 'transaction_name' => 'Saldo Awal'], $res);

		if ($this->db->sql_counts($bindParam))
		{
			$row = $this->db->sql_fetch_single($bindParam);

			$res_delete1 = $this->db->sql_prepare("delete from ml_journal where id = :id and created between :from_date and :to_date and transaction_name = :transaction_name");
			$bindParam_delete1 = $this->db->sql_bindParam(['id' => $row['id'], 'from_date' => $convert_first_day_of_this_month, 'to_date' => $convert_last_day_of_this_month, 'transaction_name' => 'Saldo Awal'], $res_delete1);

			$res_delete_journallist = $this->db->sql_prepare("select journal_id from ml_journal_list where journal_id = :journal_id and created between :from_date and :to_date");
			$bindParam_delete_journallist = $this->db->sql_bindParam(['journal_id' => $row['id'], 'from_date' => $convert_first_day_of_this_month, 'to_date' => $convert_last_day_of_this_month], $res_delete_journallist);
			while ($row_delete_journallist = $this->db->sql_fetch_single($bindParam_delete_journallist))
			{
				$res_delete2 = $this->db->sql_prepare("delete from ml_journal_list where journal_id = :journal_id and created between :from_date and :to_date");
				$bindParam_delete2 = $this->db->sql_bindParam(['journal_id' => $row_delete_journallist['journal_id'], 'from_date' => $convert_first_day_of_this_month, 'to_date' => $convert_last_day_of_this_month], $res_delete2);
			}
		}
	}
}
