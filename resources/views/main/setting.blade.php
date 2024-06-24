
   

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
                    <li class="breadcrumb-item">Pengaturan Perusahaan</li>
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
                            <h5 class="card-title">Pengaturan</h5>
                
                           
                        </div>    
                        <div class="card-body custom-card-action p-0">
                            <div class="container mtop30 main-box">
                                <table class="table table-hover">
                                    <tr>
                                        <td class="menu-report-row"><strong><span class="report-menu-title">Pengaturan Perusahaan</span></strong><br><span class="report-menu-subtitle">Pengaturan Profil Perusahaan</span></td>
                                    </tr>
                                    <tr>
                                        <td class="menu-report-row"><strong><span class="report-menu-title">Pengaturan Modal Awal</span></strong><br><span class="report-menu-subtitle">Atur Modal Awal Perusahaan</span></td>
                                    </tr>
                                    <tr>
                                        <td class="menu-report-row"><strong><span class="report-menu-title">Pengaturan Kode Rekening</span></strong><br><span class="report-menu-subtitle">Daftar Kode Rekening Perusahaan</span></td>
                                    </tr>
                                    <tr>
                                        <td class="menu-report-row"><a href="{{ url('generate_opening_balance') }}"><strong><span class="report-menu-title">Generate Opening Balance</span></strong><br><span class="report-menu-subtitle">Generate Opening Balance</span></a></td>
                                    </tr>
                                    
                                </table>

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
   