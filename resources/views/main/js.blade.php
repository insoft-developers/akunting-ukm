


<script>
    function show_error(pesan) {
        Swal.fire({
            icon: "error",
            title: "Notice!...",
            html: pesan,
            footer: ''
        });

    }

    function formatAngka(angka, prefix){
        var number_string = angka.toString().replace(/[^,\d]/g, '').toString(),
        split   		= number_string.split(','),
        sisa     		= split[0].length % 3,
        rupiah     		= split[0].substr(0, sisa),
        ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

        // tambahkan titik jika yang di input sudah menjadi angka ribuan
        if(ribuan){
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }
</script>

@if($view == 'journal-add')
<script>

    let index_item = 2;
    let total_debit = 0;
    let total_kredit = 0;

    $("#form-tambah-jurnal").submit(function(e){
        e.preventDefault();
        $.ajax({
            url: "{{ url('save_multiple_journal') }}",
            type: "POST",
            dataType: "JSON",
            data: $(this).serialize(),
            success: function(data) {
            
                if(data.success) {
                    window.location = "{{ url('/') }}";
                } else {
                    show_error(data.message);
                }
            }
        })
    })


    function set_debit(id) {
        
        var debit = $("#debit_"+id).val();
        if(debit != '') {
            $("#kredit_"+id).attr('readonly', true);      
        } else {
            $("#kredit_"+id).removeAttr('readonly');
        }
        count_total_debit();
        
    }

    function count_total_debit() {
        total_debit = 0;
        for(var i=1; i<= index_item; i++) {
            var debit = $("#debit_"+i).val() != '' ? $("#debit_"+i).val() : 0;
            if(debit === undefined) {
                debit = 0;
            }

            total_debit = +total_debit + +debit;
            
        }
        count_total();
    }

    function count_total_kredit() {
        total_kredit = 0;
        for(var i=1; i<= index_item; i++) {
            var kredit = $("#kredit_"+i).val() != '' ?  $("#kredit_"+i).val() : 0;
            if(kredit === undefined) {
                kredit =0;
            }
            total_kredit = +total_kredit + +kredit;
        }
        count_total();
    }

    function count_total() {
        $(".label-debit").text(formatAngka(total_debit, "Rp."));
        $(".label-kredit").text(formatAngka(total_kredit, "Rp."));
    }

    function set_kredit(id) {
        var kredit = $("#kredit_"+id).val();
        if(kredit != '') {
            $("#debit_"+id).attr('readonly', true);
        } else {
            $("#debit_"+id).removeAttr('readonly');
        }
        count_total_kredit();
    }


    function delete_item(id) {
        $("#row_"+id).remove();
        count_total_debit();
        count_total_kredit();
    }

    function add_item() {
        index_item++;
        $.ajax({
            url:"{{ url('journal_multiple_form') }}",
            type: "GET",
            dataType:"JSON",
            success: function(data) {
                
                var HTML= '';
                HTML += '<div class="row" id="row_'+index_item+'">';
                HTML += '<div class="col-md-4">';
                                            
                HTML += '<select class="form-control cust-control" id="akun_'+index_item+'" name="akun[]">';
                HTML += '<option value="">Pilih</option>';
                for(var i=0; i< data.data.group.length; i++) {
                    HTML += '<optgroup label="'+data.data.group[i]+'">';
                        for(var n=0; n< data.data.data.length; n++) {
                            if(data.data.group[i] == data.data.data[n]['group']) {
                                 HTML += '<option value="'+data.data.data[n]['id']+'_'+data.data.data[n]['account_code_id']+'">'+data.data.data[n]['name']+'</option>';
                            }
                        }
                    HTML += '</optgroup>';
                }
               

                HTML += '</select>';
                
                HTML += '</div>';

                HTML += '<div class="col-md-4">';
                                                
                HTML += '<input type="number" onkeyup="set_debit('+index_item+')" class="form-control cust-control" placeholder="0" id="debit_'+index_item+'" name="debit[]">';
                HTML += '</div>';
                                        
                HTML += '<div class="col-md-4">';
                                                
                HTML += '<input type="number" onkeyup="set_kredit('+index_item+')" class="form-control cust-control" placeholder="0" id="kredit_'+index_item+'" name="kredit[]">';
                HTML += '<a href="javascript:void(0);" onclick="delete_item('+index_item+')" type="button" class="btn btn-sm del-item"><i class="fa fa-remove"></i></a>';
                HTML += '</div>';

                $("#input_add_container").append(HTML);
            }
        })

        
    }
</script>
@endif


@if($view == 'journal-edit')
<script>

    let index_item = "{{ $total_item }}";
    let total_debit = 0;
    let total_kredit = 0;


    
    
    // addition_item();

    count_grandtotal();

    function count_grandtotal() {
        count_total_debit();
        count_total_kredit();
    }
    
    function addition_item() {
        var n = "{{ $total_item }}";
        
        for(var i=index_item; i < n; i++) {
            add_item_init(i);
    
        }
        count_total_debit();
        count_total_kredit();
    }
    

    $("#form-update-jurnal").submit(function(e){
        e.preventDefault();
        $.ajax({
            url: "{{ url('journal_update') }}",
            type: "POST",
            dataType: "JSON",
            data: $(this).serialize(),
            success: function(data) {
                
                if(data.success) {
                    window.location = "{{ url('/') }}";
                } else {
                    show_error(data.message);
                }
            }
        })
    })


    function set_debit(id) {
        
        var debit = $("#debit_"+id).val();
        if(debit != '') {
            $("#kredit_"+id).attr('readonly', true);      
        } else {
            $("#kredit_"+id).removeAttr('readonly');
        }
        count_total_debit();
        
    }

    function count_total_debit() {
        total_debit = 0;
        for(var i=1; i<= index_item; i++) {
            var debit = $("#debit_"+i).val() != '' ? $("#debit_"+i).val() : 0;
            if(debit === undefined) {
                debit = 0;
            }

            total_debit = +total_debit + +debit;
            
        }
        count_total();
    }

    function count_total_kredit() {
        total_kredit = 0;
        for(var i=1; i<= index_item; i++) {
            var kredit = $("#kredit_"+i).val() != '' ?  $("#kredit_"+i).val() : 0;
            if(kredit === undefined) {
                kredit =0;
            }
            total_kredit = +total_kredit + +kredit;
        }
        count_total();
    }

    function count_total() {
        $(".label-debit").text(formatAngka(total_debit, "Rp."));
        $(".label-kredit").text(formatAngka(total_kredit, "Rp."));
    }

    function set_kredit(id) {
        var kredit = $("#kredit_"+id).val();
        if(kredit != '') {
            $("#debit_"+id).attr('readonly', true);
        } else {
            $("#debit_"+id).removeAttr('readonly');
        }
        count_total_kredit();
    }


    function delete_item(id) {
        $("#row_"+id).remove();
        count_total_debit();
        count_total_kredit();
        
    }

    function add_item() {
        index_item++;
        $.ajax({
            url:"{{ url('journal_multiple_form') }}",
            type: "GET",
            dataType:"JSON",
            success: function(data) {
                
                var HTML= '';
                HTML += '<div class="row" id="row_'+index_item+'">';
                HTML += '<div class="col-md-4">';
                                            
                HTML += '<select class="form-control cust-control" id="akun_'+index_item+'" name="akun[]">';
                HTML += '<option value="">Pilih</option>';
                for(var i=0; i< data.data.group.length; i++) {
                    HTML += '<optgroup label="'+data.data.group[i]+'">';
                        for(var n=0; n< data.data.data.length; n++) {
                            if(data.data.group[i] == data.data.data[n]['group']) {
                                 HTML += '<option value="'+data.data.data[n]['id']+'_'+data.data.data[n]['account_code_id']+'">'+data.data.data[n]['name']+'</option>';
                            }
                        }
                    HTML += '</optgroup>';
                }
               

                HTML += '</select>';
                
                HTML += '</div>';

                HTML += '<div class="col-md-4">';
                                                
                HTML += '<input type="number" onkeyup="set_debit('+index_item+')" class="form-control cust-control" placeholder="0" id="debit_'+index_item+'" name="debit[]">';
                HTML += '</div>';
                                        
                HTML += '<div class="col-md-4">';
                                                
                HTML += '<input type="number" onkeyup="set_kredit('+index_item+')" class="form-control cust-control" placeholder="0" id="kredit_'+index_item+'" name="kredit[]">';
                HTML += '<a href="javascript:void(0);" onclick="delete_item('+index_item+')" type="button" class="btn btn-sm del-item"><i class="fa fa-remove"></i></a>';
                HTML += '</div>';

                $("#input_add_container").append(HTML);
            }
        })

        
    }


    function add_item_init(n) {
        
        var index_detail = +n + +1;
        
        index_item++;
        $.ajax({
            url:"{{ url('journal_multiple_form') }}",
            type: "GET",
            dataType:"JSON",
            async:true,
            success: function(data) {
               
                var HTML= '';
                HTML += '<div class="row" id="row_'+index_item+'">';
                HTML += '<div class="col-md-4">';
                                            
                HTML += '<select class="form-control cust-control" id="akun_'+index_item+'" name="akun[]">';
                HTML += '<option value="">Pilih</option>';
                for(var i=0; i< data.data.group.length; i++) {
                    HTML += '<optgroup label="'+data.data.group[i]+'">';
                        for(var n=0; n< data.data.data.length; n++) {
                            if(data.data.group[i] == data.data.data[n]['group']) {
                                 HTML += '<option value="'+data.data.data[n]['id']+'_'+data.data.data[n]['account_code_id']+'">'+data.data.data[n]['name']+'</option>';
                            }
                        }
                    HTML += '</optgroup>';
                }
               

                HTML += '</select>';
                
                HTML += '</div>';

                HTML += '<div class="col-md-4">';
                                                
                HTML += '<input type="number" onkeyup="set_debit('+index_item+')" class="form-control cust-control" placeholder="0" id="debit_'+index_item+'" name="debit[]">';
                HTML += '</div>';
                                        
                HTML += '<div class="col-md-4">';
                                                
                HTML += '<input type="number" onkeyup="set_kredit('+index_item+')" class="form-control cust-control" placeholder="0" id="kredit_'+index_item+'" name="kredit[]">';
                HTML += '<a href="javascript:void(0);" onclick="delete_item('+index_item+')" type="button" class="btn btn-sm del-item"><i class="fa fa-remove"></i></a>';
                HTML += '</div>';

                $("#input_add_container").append(HTML);                
            },
            
        })


    }
    
    
    

</script>
@endif


@if($view == 'jurnal')
<script>
    var bulan = $("#bulan").val();
    var tahun = $("#tahun").val();
    var cari = $("#cari").val();


    init_journal_table(bulan,tahun,cari);

    function init_journal_table(bulan,tahun,cari) {
        var csrf_token = $('meta[name="csrf-token"]').attr('content');
        var table = new DataTable('#table-jurnal');
        table.destroy();
 
       
        var table = $('#table-jurnal').DataTable({
            processing:true,
            serverSide:true,
            dom: 'Blfrtip',
            columnDefs: [
                {
                    target: 0,
                    visible: false,
                    searchable: false
                },
            ],
            
            ajax: {type: "POST", url: "{{ route('journal.table') }}", data:{'bulan':bulan, 'tahun':tahun, 'cari':cari, '_token':csrf_token}},
            order: [[ 0, "desc" ]],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'tanggal', name: 'tanggal'},
                {data: 'transaction_name', name: 'transaction_name'},
                {data: 'total_balance', name: 'total_balance'},
                {data: 'dibuat', name: 'dibuat'},
                {data:'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    }

    
    $("#bulan").change(function(){
        refresh_table();
    });

    $("#tahun").change(function(){
        refresh_table();
    });

    $("#cari").keyup(function(){
        var bulan = $("#bulan").val();
        var tahun = $("#tahun").val();
        var cari = $(this).val();
        init_journal_table(bulan,tahun,cari); 
    })


    function refresh_table() {
        var bulan = $("#bulan").val();
        var tahun = $("#tahun").val();
        var cari = $("#cari").val();
        init_journal_table(bulan,tahun,cari); 
    }
    

    function add_jurnal() {
        $("#modal-tambah").modal("show");
        reset_form();
    }




    $(document).ready(function(){
        $("#jenis_transaksi").change(function(){
            var jenis = $(this).val();
            $.ajax({
                url:"{{ url('get_account_receive') }}"+"/"+jenis,
                type: "GET",
                dataType:"JSON",
                success:function(data) {
                 
                    var HTML = '';
                    HTML += '<option value="">Pilih diterima dari</option>';
                    for(var i=0; i<data.group.length;i++) {
                        
                        HTML += '<optgroup style="margin-top:-10px;" label="'+data.group[i]+'">';
                        for(var a=0; a<data.data.length; a++) {
                            if(data.group[i] == data.data[a].group) {
                                HTML += '<option style="margin-top:-10px;" value="'+data.data[a].id+'_'+data.data[a].account_code_id+'">'+data.data[a].name+'</option>';
                            }
                        }
                        HTML += '</optgroup>';
                    }
                    $("#receive_from").html(HTML);


                    var HTM = '';
                    HTM += '<option value="">Pilih disimpan ke</option>';
                    for(var i=0; i<data.kelompok.length;i++) {
                        
                        HTM += '<optgroup style="margin-top:-10px;" label="'+data.kelompok[i]+'">';
                        for(var a=0; a<data.simpan.length; a++) {
                            if(data.kelompok[i] == data.simpan[a].group) {
                                HTM += '<option style="margin-top:-10px;" value="'+data.simpan[a].id+'_'+data.simpan[a].account_code_id+'">'+data.simpan[a].name+'</option>';
                            }
                        }
                        HTM += '</optgroup>';
                    }
                    $("#save_to").html(HTM);

                }
            })

            
        })

        $("#form-tambah-jurnal").submit(function(e){
                e.preventDefault();
                $.ajax({
                    url: "{{ url('save_jurnal') }}",
                    type: "POST",
                    dataType: "JSON",
                    data: $(this).serialize(),
                    success: function(data){
                        
                        if(data.success) {
                            $("#modal-tambah").modal("hide");
                            refresh_table();
                        } else{
                            show_error(data.message);
                        }
                    }
                })
        })
        // $("#receive_from").select2();

        
        function reset_form() {
                var sekarang = "{{ date('Y-m-d') }}";
                $("#tanggal_transaksi").val(sekarang);
                $("#jenis_transaksi").val("");
                $("#receive_from").val("");
                $("#save_to").val("");
                $("#keterangan").val("");
                $("#nominal").val("");
            }
        })
        
        function journal_delete(id) {
            Swal.fire({
                title: "Delete Data?",
                text: "Are you sure, do you want to delete this item?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                if (result.isConfirmed) {
                    confirm_delete_data(id);
                    
                }
            });
        }

        function confirm_delete_data(id) {
            var csrf_token = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url:"{{ url('confirm_journal_delete') }}",
                type: "POST",
                dataType:"JSON",
                data: {'id':id,'_token':csrf_token},
                success: function(data) {
                    
                    if(data.success) {
                        Swal.fire({
                            title: "Deleted!",
                            text: "Your file has been deleted.",
                            icon: "success"
                        });
                        refresh_table();
                    } else {
                        Swal.fire({
                            title: "Failed!",
                            text: data.message,
                            icon: "error"
                        });
                    }
                }
            })
        }

</script>
@endif

@if($view == 'setting')
<script>
    function on_opening_balance_click() {
        window.location="{{ url('generate_opening_balance') }}";
    }

    function on_company_setting_click() {
        window.location ="{{ url('company_setting') }}";
    }

    function on_initial_capital() {
        window.location = "{{ url('initial_capital') }}";
    }

    function on_account_setting_click() {
        window.location = "{{ url('account_setting') }}";
    }
</script>
@endif

@if($view == 'opening-balance')
<script>

    $("#form-opening-balance").submit(function(e){
        e.preventDefault();
        $.ajax({
            url :"{{ url('submit_opening_balance') }}",
            dataType: "JSON",
            type: "POST",
            data: $(this).serialize(),
            success: function(data) {
                console.log(data);
                Swal.fire({
                    title: "Success!",
                    text: data.message,
                    icon: "success"
                });
            }
        });
    })
</script>
@endif

@if($view == 'profit-loss')
<script>


    function export_excel() {
        var monthfrom = $("#month_from").val();
        var yearfrom = $("#year_from").val();
        var monthto = $("#month_to").val();
        var yearto = $("#year_to").val();
        var tanggal = monthfrom+'_'+yearfrom+'_'+monthto+'_'+yearto;
        window.location = "{{ url('profit_loss_export') }}"+"/"+tanggal;
    }



    $("#form-profit-loss-submit").submit(function(e){
        e.preventDefault();
        $.ajax({
            url: "{{ url('submit_profit_loss') }}",
            type: "POST",
            dataType: "JSON",
            data: $(this).serialize(),
            success: function(data) {
                console.log(data);
                if(data.success) {
                    $(".table-responsive").html(data.data);
                } else {
                    Swal.fire({
                        title: "Failed!",
                        text: data.message,
                        icon: "error"
                    });
                }
            }
        })
    }); 

</script>
@endif


@if($view == 'balance-sheet')
<script>

    function export_excel() {
        var monthfrom = $("#month_from").val();
        var yearfrom = $("#year_from").val();
        var monthto = $("#month_to").val();
        var yearto = $("#year_to").val();
        var tanggal = monthfrom+'_'+yearfrom+'_'+monthto+'_'+yearto;
        window.location = "{{ url('balance_sheet_export') }}"+"/"+tanggal;
    }


    $("#form-balance-sheet-submit").submit(function(e){
        e.preventDefault();
        $.ajax({
            url: "{{ url('submit_balance_sheet') }}",
            type: "POST",
            dataType: "JSON",
            data: $(this).serialize(),
            success: function(data) {
                console.log(data);
                if(data.success) {
                    $(".table-responsive").html(data.data);
                } else {
                    Swal.fire({
                        title: "Failed!",
                        text: data.message,
                        icon: "error"
                    });
                }
            }
        })
    }); 

</script>
@endif

@if($view == 'initial-capital')
<script>

    let index_item = "{{ $total_item }}" + +2;
    let total_debit = 0;
    let total_kredit = 0;


    
    
    // addition_item();

    count_grandtotal();

    function count_grandtotal() {
        count_total_debit();
        count_total_kredit();
    }
    
    function addition_item() {
        var n = "{{ $total_item }}";
        
        for(var i=index_item; i < n; i++) {
            add_item_init(i);
    
        }
        count_total_debit();
        count_total_kredit();
    }
    

    $("#form-update-jurnal").submit(function(e){
        e.preventDefault();
        $.ajax({
            url: "{{ url('save_initial_capital') }}",
            type: "POST",
            dataType: "JSON",
            data: $(this).serialize(),
            success: function(data) {
                
                if(data.success) {
                    Swal.fire({
                        title: "Success!",
                        text: data.message,
                        icon: "success"
                    });
                } else {
                    show_error(data.message);
                }
            }
        })
    })


    function set_debit(id) {
        
        var debit = $("#debit_"+id).val();
        if(debit != '') {
            $("#kredit_"+id).attr('readonly', true);      
        } else {
            $("#kredit_"+id).removeAttr('readonly');
        }

        
        count_total_debit();
        
    }

    function count_total_debit() {
        total_debit = 0;
        for(var i=1; i<= index_item; i++) {
            var debit = $("#debit_"+i).val() != '' ? $("#debit_"+i).val() : 0;
            if(debit === undefined) {
                debit = 0;
            }

            total_debit = +total_debit + +debit;
            
        }
        count_total();
    }

    function count_total_kredit() {
        total_kredit = 0;
        for(var i=1; i<= index_item; i++) {
            var kredit = $("#kredit_"+i).val() != '' ?  $("#kredit_"+i).val() : 0;
            if(kredit === undefined) {
                kredit =0;
            }
            total_kredit = +total_kredit + +kredit;
        }
        count_total();
    }

    function count_total() {
        $(".label-debit").text(formatAngka(total_debit, "Rp."));
        $(".label-kredit").text(formatAngka(total_kredit, "Rp."));
    }

    function set_kredit(id) {
        var kredit = $("#kredit_"+id).val();
        if(kredit != '') {
            $("#debit_"+id).attr('readonly', true);
        } else {
            $("#debit_"+id).removeAttr('readonly');
        }
        count_total_kredit();
    }


    function delete_item(id) {
        $("#row_"+id).remove();
        count_total_debit();
        count_total_kredit();
        
    }

    function add_item() {
        index_item++;
        $.ajax({
            url:"{{ url('journal_multiple_form') }}",
            type: "GET",
            dataType:"JSON",
            success: function(data) {
                
                var HTML= '';
                HTML += '<div class="row" id="row_'+index_item+'">';
                HTML += '<div class="col-md-4">';
                                            
                HTML += '<select class="form-control cust-control" id="akun_'+index_item+'" name="akun[]">';
                HTML += '<option value="">Pilih</option>';
                for(var i=0; i< data.data.group.length; i++) {
                    HTML += '<optgroup label="'+data.data.group[i]+'">';
                        for(var n=0; n< data.data.data.length; n++) {
                            if(data.data.group[i] == data.data.data[n]['group']) {
                                 HTML += '<option value="'+data.data.data[n]['id']+'_'+data.data.data[n]['account_code_id']+'">'+data.data.data[n]['name']+'</option>';
                            }
                        }
                    HTML += '</optgroup>';
                }
               

                HTML += '</select>';
                
                HTML += '</div>';

                HTML += '<div class="col-md-4">';
                                                
                HTML += '<input type="number" onkeyup="set_debit('+index_item+')" class="form-control cust-control" placeholder="0" id="debit_'+index_item+'" name="debit[]">';
                HTML += '</div>';
                                        
                HTML += '<div class="col-md-4">';
                                                
                HTML += '<input type="number" onkeyup="set_kredit('+index_item+')" class="form-control cust-control" placeholder="0" id="kredit_'+index_item+'" name="kredit[]">';
                HTML += '<a href="javascript:void(0);" onclick="delete_item('+index_item+')" type="button" class="btn btn-sm del-item"><i class="fa fa-remove"></i></a>';
                HTML += '</div>';

                $("#input_add_container").append(HTML);
            }
        })

        
    }


    function add_item_init(n) {
        
        var index_detail = +n + +1;
        
        index_item++;
        $.ajax({
            url:"{{ url('journal_multiple_form') }}",
            type: "GET",
            dataType:"JSON",
            async:true,
            success: function(data) {
               
                var HTML= '';
                HTML += '<div class="row" id="row_'+index_item+'">';
                HTML += '<div class="col-md-4">';
                                            
                HTML += '<select class="form-control cust-control" id="akun_'+index_item+'" name="akun[]">';
                HTML += '<option value="">Pilih</option>';
                for(var i=0; i< data.data.group.length; i++) {
                    HTML += '<optgroup label="'+data.data.group[i]+'">';
                        for(var n=0; n< data.data.data.length; n++) {
                            if(data.data.group[i] == data.data.data[n]['group']) {
                                 HTML += '<option value="'+data.data.data[n]['id']+'_'+data.data.data[n]['account_code_id']+'">'+data.data.data[n]['name']+'</option>';
                            }
                        }
                    HTML += '</optgroup>';
                }
               

                HTML += '</select>';
                
                HTML += '</div>';

                HTML += '<div class="col-md-4">';
                                                
                HTML += '<input type="number" onkeyup="set_debit('+index_item+')" class="form-control cust-control" placeholder="0" id="debit_'+index_item+'" name="debit[]">';
                HTML += '</div>';
                                        
                HTML += '<div class="col-md-4">';
                                                
                HTML += '<input type="number" onkeyup="set_kredit('+index_item+')" class="form-control cust-control" placeholder="0" id="kredit_'+index_item+'" name="kredit[]">';
                HTML += '<a href="javascript:void(0);" onclick="delete_item('+index_item+')" type="button" class="btn btn-sm del-item"><i class="fa fa-remove"></i></a>';
                HTML += '</div>';

                $("#input_add_container").append(HTML);                
            },
            
        })


    }
</script>
@endif

@if($view == 'account-setting')
<script>
    function on_account_item_click(id) {
        var akun = '';
        if(id == 0) 
        {
            akun = "current_assets"
        } 
        else if(id== 1) 
        {
            akun = "fixed_assets";
        } 
        else if(id== 2) 
        {
            akun = "accumulated_depreciation";
        }
        else if(id== 3) 
        {
            akun = "short_term_debt";
        }
        else if(id== 4) 
        {
            akun = "long_term_debt";
        }
        else if(id== 5) 
        {
            akun = "capital";
        }
        else if(id== 6) 
        {
            akun = "income";
        }
        else if(id== 7) 
        {
            akun = "cost_good_sold";
        }
        else if(id== 8) 
        {
            akun = "selling_cost";
        }
        else if(id== 9) 
        {
            akun = "admin_general_fees";
        }
        else if(id== 10) 
        {
            akun = "non_business_income";
        }
        else if(id== 11) 
        {
            akun = "non_business_expenses";
        }
        window.location = "{{ url('account_setting') }}"+"/"+akun;
    }
</script>
@endif

@if($view == 'account-setting-detail')
<script>

    let nomor = "{{ $data->count() }}";
    let account_code_id = "{{ $data[0]->account_code_id }}";

    function add_item() {

        var html ='';
        html += '<div class="form-group mtop20">';
        html += '<input name="account_item[]" id="account_item_'+nomor+'" type="text" class="form-control cust-control">';
        html += '</div>';
        html += '<input type="hidden" name="id[]">';
        html += '<input type="hidden" name="account_code_id[]" value="'+account_code_id+'">';

        $("#setting-input-container").append(html);
        nomor++;
        
    }

    $("#form-setting-account").submit(function(e){
        e.preventDefault();
        $.ajax({
            url: "{{ url('save_setting_account') }}",
            type: "POST",
            dataType: "JSON",
            data: $(this).serialize(),
            success: function(data) {
                console.log(data);
                if(data.success) {
                    Swal.fire({
                        title: "Success!",
                        text: data.message,
                        icon: "success"
                    });
                } else {
                    Swal.fire({
                        title: "Error!",
                        html: data.message,
                        icon: "error"
                    });
                }
            }
        })
    })
</script>
@endif

@if($view == 'report')
<script>
    function on_profit_loss_click() {
        window.location = "{{ url('profit_loss') }}";
    }

    function on_balance_click() {
        window.location = "{{ url('balance') }}";
    }

    function on_journal_report_click() {
        window.location = "{{ url('journal_report') }}";
    }

    function on_trial_balance_click() {
        window.location = "{{ url('trial_balance') }}";
    }

    function on_general_ledger_click() {
        window.location = "{{ url('general_ledger') }}";
    }

    function download_file_spt() {
        window.location = "{{ asset('template/main/files/file_spt.xls') }}";
    }

</script>
@endif
@if($view == 'journal-report')
<script>
    
    $("#form-journal-report-submit").submit(function(e){
        e.preventDefault();

        $.ajax({
            url: "{{ url('journal_report_submit') }}",
            type: "POST",
            dataType: "JSON",
            data: $(this).serialize(),
            success: function(data) {
                console.log(data);
                $(".table-responsive").html(data.data);
            }
        })
    })

</script>
@endif

@if($view == 'trial-balance')
<script>

    function export_excel() {
        var monthfrom = $("#month_from").val();
        var yearfrom = $("#year_from").val();
        var monthto = $("#month_to").val();
        var yearto = $("#year_to").val();
        var tanggal = monthfrom+'_'+yearfrom+'_'+monthto+'_'+yearto;
        window.location = "{{ url('trial_balance_export') }}"+"/"+tanggal;
    }



     $("#form-trial-balance-submit").submit(function(e){
        e.preventDefault();

        $.ajax({
            url: "{{ url('trial_balance_submit') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(data) {
                console.log(data);
                $(".table-responsive").html(data.data);
            }
        })
    })
</script>
@endif

@if($view == 'general-ledger')
<script>
    
    $("#form-general-ledger-submit").submit(function(e){
        e.preventDefault();
        $.ajax({
            url: "{{ url('general_ledger_submit') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(data) {
                console.log(data);

                if(data.success) {
                    $(".table-responsive").html(data.data);
                } else {
                    Swal.fire({
                        title: "Error!",
                        html: data.message,
                        icon: "error"
                    });
                }
            }
        })
    });

</script>
@endif

