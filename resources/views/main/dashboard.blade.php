
   

@extends('master')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <!-- [ page-header ] start -->
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Jurnal</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Lihat Jurnal</a></li>
                    {{-- <li class="breadcrumb-item">Tables</li> --}}
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
                            <h5 class="card-title">Kelola Jurnal</h5>
                            
                            <a style="margin-right:-15px;" href="{{ url('journal_add') }}" class="avatar-text avatar-md bg-default text-white pull-right">
                                <i class="feather-plus bg-dark"></i>
                            </a>

                            <a href="javascript:void(0);" onclick="add_jurnal()" class="avatar-text avatar-md bg-dark text-white pull-right;" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                                <i class="feather-plus"></i>
                            </a>
                            
                            
                        </div>
                        <div class="card-body custom-card-action p-0">
                            <div class="container mtop30 main-box">
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-control cust-control">
                                        <option value="">Pilih bulan</option>
                                        <option value="01">Januari</option>
                                        <option value="02">Februari</option>
                                        <option value="03">Maret</option>
                                        <option value="04">April</option>
                                        <option value="05">Mei</option>
                                        <option value="06">Juni</option>
                                        <option value="07">Juli</option>
                                        <option value="08">Agustus</option>
                                        <option value="09">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-control cust-control">
                                        <option value="">Pilih tahun</option>
                                        <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                        <option value="{{ date('Y', strtotime('-1 year', strtotime( date('Y') ))) }}">{{ date('Y', strtotime('-1 year', strtotime( date('Y') ))) }}</option>
                                        <option value="{{ date('Y', strtotime('-2 year', strtotime( date('Y') ))) }}">{{ date('Y', strtotime('-2 year', strtotime( date('Y') ))) }}</option>
                                        <option value="{{ date('Y', strtotime('-3 year', strtotime( date('Y') ))) }}">{{ date('Y', strtotime('-3 year', strtotime( date('Y') ))) }}</option>
                                        <option value="{{ date('Y', strtotime('-4 year', strtotime( date('Y') ))) }}">{{ date('Y', strtotime('-4 year', strtotime( date('Y') ))) }}</option>
                                        
                                    </select>
                                </div>
                                <div class="mtop20"></div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input type="text" placeholder="Cari disini.." class="form-control cust-control">
                                    </div>
                                    
                                </div>
                            </div>
                             
                            
                            <div class="mtop50"></div>
                            <div class="table-responsive">
                                <table id="table-jurnal" class="table table-hover table-striped mb-0">
                                    <thead>
                                        <tr class="border-b">
                                            <th width="0%">ID</th>
                                            <th width="3%">Tanggal</th>
                                            <th width="*">Nama Transaksi</th>
                                            <th width="15%">Nominal</th>
                                            <th width="15%">Dibuat</th>
                                            <th width="15%" class="text-end">Opsi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="mtop30"></div>
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
   