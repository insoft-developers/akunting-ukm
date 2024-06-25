
   

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
                    <li class="breadcrumb-item"><a href="{{ url('setting') }}">Pengaturan</a></li>
                    <li class="breadcrumb-item">Company Setting</li>
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
                <div class="col-xxl-12"> 
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Company Setting</h5>
                
                           
                        </div>    
                        <div class="card-body custom-card-action p-0">
                            <div class="container mtop30 main-box">
                                @if ( session()->has('success') )
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="alert alert-success">
                                                {!! session('success') !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                               <form id="form-company-setting" method="POST" action="{{ url("company_setting_update") }}">
                                    @csrf

                                    <input name="id" type="hidden" value="{{ $data == null ? "" : $data->id }}">
                                    <div class="row">
                                        <div class="col-md-6 mtop20">
                                            <div class="form-group">
                                                <label>Email Perusahaan</label>
                                                <input name="company_email" value="{{ $data == null ? "" : $data->company_email }}" type="email" class="form-control cust-control" placeholder="email address">
                                                @if($errors->has('company_email'))
                                                    <span class="help-block">{{ $errors->first('company_email') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6 mtop20">
                                            <div class="form-group">
                                                <label>Nama Perusahaan</label>
                                                <input name="company_name" value="{{ $data == null ? "" : $data->company_name }}" type="text" class="form-control cust-control" placeholder="company name">
                                                @if($errors->has('company_name'))
                                                    <span class="help-block">{{ $errors->first('company_name') }}</span>
                                                @endif
                                            </div>
                                            
                                        </div>

                                        <div class="col-md-6 mtop20">
                                            <div class="form-group">
                                                <label>Domisili</label>
                                                <input name="domicile" value="{{ $data == null ? "" : $data->domicile }}" type="text" class="form-control cust-control" placeholder="domicile">
                                                
                                            </div>
                                        </div>
                                        <div class="col-md-6 mtop20">
                                            <div class="form-group">
                                                <label>Bidang Usaha</label>
                                                <input name="business_fields" value="{{$data == null ? "" : $data->business_fields }}" type="text" class="form-control cust-control" placeholder="business fields">
                                            </div>
                                            
                                        </div>
                                        <div class="col-md-6 mtop20">
                                            <div class="form-group">
                                                <label>NPWP</label>
                                                <input name="npwp" value="{{$data == null ? "" : $data->npwp }}" type="text" class="form-control cust-control" placeholder="NPWP">
                                            </div>
                                        </div>
                                        <div class="col-md-6 mtop20">
                                            <div class="form-group">
                                                <label>Phone Number</label>
                                                <input name="phone_number" value="{{$data == null ? "" : $data->phone_number }}" type="text" class="form-control cust-control" placeholder="enter your phone number">
                                                @if($errors->has('phone_number'))
                                                    <span class="help-block">{{ $errors->first('phone_number') }}</span>
                                                @endif
                                            </div>
                                            
                                        </div>
                                        <div class="col-md-12 mtop20">
                                            <div class="form-group">
                                                <label>Alamat</label>
                                                <textarea name="address" style="height: 120px;" class="form-control cust-control" placeholder="tell us your company address">{{$data == null ? "" : $data->address }}</textarea>
                                                @if($errors->has('address'))
                                                    <span class="help-block">{{ $errors->first('address') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-3 mtop20">
                                            <div class="form-group">
                                                <label>Company Tax(%)</label>
                                                <input name="tax" type="text" value="{{$data == null ? "" : $data->tax }}" class="form-control cust-control" placeholder="enter your company tax">
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button class="btn btn-primary pull-right">Simpan Perubahan</button>
                                        </div>
                                    </div>

                               </form>
                               <div class="mtop60"></div>
                            </div>
                        </div>
                        
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
   