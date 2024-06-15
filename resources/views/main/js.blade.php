


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


@if($view == 'jurnal')
<script>
    function add_jurnal() {
        $("#modal-tambah").modal("show");
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

                        } else{
                            show_error(data.message);
                        }
                    }
                })
        })
        // $("#receive_from").select2();
    })
    

    
</script>
@endif








