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
                                HTML += '<option style="margin-top:-10px;" value="">'+data.data[a].name+'</option>';
                            }
                        }
                        HTML += '</optgroup>';
                    }
                    $("#receive_from").html(HTML);

                }
            })
        })

        $("#receive_from").select2();
    })
    

    
</script>
@endif








