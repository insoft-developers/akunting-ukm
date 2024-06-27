
   

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
                    <li class="breadcrumb-item"><a href="{{ url('report') }}">Laporan</a></li>
                    <li class="breadcrumb-item">Lihat Laporan</li>
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
                            <h5 class="card-title">Laporan</h5>
                
                           
                        </div>    
                        <div class="card-body custom-card-action p-0">
                            <div class="container mtop30 main-box">
                                <table class="table table-hover">
                                    <tr>
                                        <td class="menu-report-row"><strong><span class="report-menu-title">Sales</span></strong><br><span class="report-menu-subtitle">Laporan Penjualan</span></td>
                                    </tr>
                                    <tr onclick="on_journal_report_click()">
                                        <td class="menu-report-row"><strong><span class="report-menu-title">Jurnal</span></strong><br><span class="report-menu-subtitle">Laporan Rekapitulasi Jurnal</span></td>
                                    </tr>
                                    <tr onclick="on_general_ledger_click()">
                                        <td class="menu-report-row"><strong><span class="report-menu-title">Buku Besar</span></strong><br><span class="report-menu-subtitle">Laporan dalam bentuk Buku Besar</span></td>
                                    </tr>
                                    <tr onclick="on_trial_balance_click()">
                                        <td class="menu-report-row"><strong><span class="report-menu-title">Neraca Saldo</span></strong><br><span class="report-menu-subtitle">Laporan dalam bentuk Neraca Saldo</span></td>
                                    </tr>
                                    <tr onclick="on_profit_loss_click()">
                                        <td class="menu-report-row"><strong><span class="report-menu-title">Laba Rugi</span></strong><br><span class="report-menu-subtitle">Laporan dalam bentuk Laba Rugi</span></td>
                                    </tr>
                                    <tr onclick="on_balance_click()">
                                        <td class="menu-report-row"><strong><span class="report-menu-title">Neraca</span></strong><br><span class="report-menu-subtitle">Laporan dalam bentuk Neraca</span></td>
                                    </tr>
                                    <tr>
                                        <td class="menu-report-row"><strong><span class="report-menu-title">Periode</span></strong><br><span class="report-menu-subtitle">Melihat Laporan berdasarkan Periode</span></td>
                                    </tr>
                                    <tr>
                                        <td class="menu-report-row"><strong><span class="report-menu-title">Utang</span></strong><br><span class="report-menu-subtitle">Laporan data Utang</span></td>
                                    </tr>
                                    <tr>
                                        <td class="menu-report-row"><strong><span class="report-menu-title">Piutang</span></strong><br><span class="report-menu-subtitle">Laporan data Piutang</span></td>
                                    </tr>
                                    
                                    <tr onclick="download_file_spt()">
                                        <td class="menu-report-row"><strong><span class="report-menu-title">SPT PPh OP</span></strong><br><span class="report-menu-subtitle">Buat laporan SPT Tahunan PPh OP</span></td>
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
   