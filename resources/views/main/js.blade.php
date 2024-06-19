


<script>
    function show_error(pesan) {
        Swal.fire({
            icon: "error",
            title: "Notice!...",
            html: pesan,
            footer: ''
        });

    }
</script>

@if($view == 'journal-add')
<script>

    let index_item = 2;


    function set_debit(id) {
        var debit = $("#debit_"+id).val();
        if(debit != '') {
            $("#kredit_"+id).attr('readonly', true);
        } else {
            $("#kredit_"+id).removeAttr('readonly');
        }
    }


    function set_kredit(id) {
        var kredit = $("#kredit_"+id).val();
        if(kredit != '') {
            $("#debit_"+id).attr('readonly', true);
        } else {
            $("#debit_"+id).removeAttr('readonly');
        }
    }

    function add_item() {
        index_item++;
        $.ajax({
            url:"{{ url('journal_multiple_form') }}",
            type: "GET",
            dataType:"JSON",
            success: function(data) {
                console.log(data.data.data);
                var HTML= '';
                HTML += '<div class="row" id="row_'+index_item+'">';
                HTML += '<div class="col-md-4">';
                                            
                HTML += '<select class="form-control cust-control" id="akun_'+index_item+'" name="akun[]">';
                HTML += '<option value="">Pilih</option>';
                for(var i=0; i< data.data.group.length; i++) {
                    HTML += '<optgroup label="'+data.data.group[i]+'">';
                        for(var n=0; n< data.data.data.length; n++) {
                            if(data.data.group[i] == data.data.data[n]['group']) {
                                 HTML += '<option value="">'+data.data.data[n]['name']+'</option>';
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

@if($view == 'jurnal')
<script>

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
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'All'],
        ],
        ajax: "{{ route('journal.table') }}",
        order: [[ 0, "desc" ]],
        columns: [
            {data: 'id', name: 'id'},
            {data: 'tanggal', name: 'tanggal'},
            {data: 'transaction_name', name: 'transaction_name'},
            {data: 'nominal', name: 'nominal'},
            {data: 'dibuat', name: 'dibuat'},
            {data:'action', name: 'action', orderable: false, searchable: false},
        ]
    });

    

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
                    console.log(data);
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
                        console.log(data);
                        if(data.success) {
                            $("#modal-tambah").modal("hide");
                            table.ajax.reload(null, false);
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


    

    
</script>
@endif








