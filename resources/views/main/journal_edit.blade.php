
   

@extends('master')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <!-- [ page-header ] start -->
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10"></h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Jurnal</a></li>
                    <li class="breadcrumb-item">Sunting Transaksi</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="javascript:void(0)" class="page-header-right-close-toggle">
                            <i class="feather-arrow-left me-2"></i>
                            <span>Back</span>
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                       
                       
                    </div>
                </div>
                <div class="d-md-none d-flex align-items-center">
                    <a href="javascript:void(0)" class="page-header-right-open-toggle">
                        <i class="feather-align-right fs-20"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- [ page-header ] end -->
        <!-- [ Main Content ] start -->
        <div class="main-content">
            <div class="row">
                <!-- [Leads] start -->
               
                <div class="col-xxl-8"> 
                    <div class="card stretch stretch-full">
                       
                        <div class="card-header">
                            <h5 class="card-title">Sunting Transaksi</h5>
                            
                
                            <a href="javascript:void(0);" onclick="add_item()" class="avatar-text avatar-md bg-default text-white pull-right;" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                <i class="feather-plus bg-dark"></i>
                            </a>
                            
                            
                        </div>
                        <form id="form-tambah-jurnal" method="POST" action="">
                            @csrf
                            <div class="card-body custom-card-action p-0">
                                <div class="container mtop30 main-box">
                                    <div class="row">
                                        @php
                                            $tanggal = date('Y-m-d', $data->created);
                                        @endphp
                                        <div class="col-md-3">
                                            <input type="date" value="{{ $tanggal }}" id="transaction_date" name="transaction_date" class="form-control cust-control">   
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" value="{{ $data->transaction_name }}" class="form-control cust-control" placeholder="Nama Transaksi" id="transaction_name" name="transaction_name">
                                        </div>
                                    </div>
                                    <div class="mtop20"></div>
                                    <div class="row" id="row_1">
                                        <div class="col-md-4">
                                            <label>Estimasi</label>
                                            <select class="form-control cust-control" id="akun_1" name="akun[]">
                                                <option value="">Pilih</option>
                                                @foreach( $akun['group'] as $a)
                                                    <optgroup label={{ $a }}>
                                                        @foreach($akun['data'] as $i)
                                                            @if($i['group'] == $a)
                                                            <option value="{{ $i['id'] }}_{{ $i['account_code_id'] }}"><?= $i['name'] ;?></option>
                                                            @endif
                                                        
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label>Debit</label>
                                            <input type="number" onkeyup="set_debit(1)" class="form-control cust-control" placeholder="0" id="debit_1" name="debit[]">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Kredit</label>
                                            <input type="number" onkeyup="set_kredit(1)" class="form-control cust-control" placeholder="0" id="kredit_1" name="kredit[]">
                                            <a href="javascript:void(0);" onclick="delete_item(1)" type="button" class="btn btn-sm del-item"><i class="fa fa-remove"></i></a>
                                        </div>
                                    </div>
                                    <div class="row" id="row_2">
                                        <div class="col-md-4">
                                        
                                            <select class="form-control cust-control" id="akun_2" name="akun[]">
                                                <option value="">Pilih</option>
                                                @foreach( $akun['group'] as $a)
                                                    <optgroup label={{ $a }}>
                                                    @foreach($akun['data'] as $i)
                                                        @if($i['group'] == $a)
                                                        <option value="{{ $i['id'] }}_{{ $i['account_code_id'] }}"><?= $i['name'] ;?></option>
                                                    @endif
                                                    
                                                    @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            
                                            <input type="number" onkeyup="set_debit(2)" class="form-control cust-control" placeholder="0" id="debit_2" name="debit[]">
                                        </div>
                                        {{-- {{ dd($akun['group']) }} --}}
                                        <div class="col-md-4">
                                            
                                            <input type="number" onkeyup="set_kredit(2)" class="form-control cust-control" placeholder="0" id="kredit_2" name="kredit[]">
                                            <a href="javascript:void(0);" onclick="delete_item(2)" type="button" class="btn btn-sm del-item"><i class="fa fa-remove"></i></a>
                                        </div>
                                    </div>
                                    <div id="input_add_container"></div>
                                    <div class="mtop20"></div>
                                    <hr />
                                    <div class="row" id="row_total">
                                        <div class="col-md-4">
                                            <label class="label-total">TOTAL</label>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="label-debit">0</label>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="label-kredit">0</label>
                                        </div>
                                    </div>
                                    <div class="mtop20"></div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            
                                            <button style="float: right;" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </div>
                                    
                                    <div class="mtop30"></div>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                
                <!-- [Recent Orders] end -->
                <!-- [] start -->
            </div>
            
        </div>
        <!-- [ Main Content ] end -->
        
    </div>
    <!-- [ Footer ] start -->
 
  
    
@endsection
   