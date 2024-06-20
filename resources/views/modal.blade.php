@if($view == 'jurnal')
    <div class="modal fade" id="modal-tambah">
        <div class="modal-dialog">
        <form id="form-tambah-jurnal">
          <div class="modal-content">
           
            @csrf
            <div class="modal-header" style="background-color: #2f467a;">
              <h5 class="modal-title" style="color:white;">Transaksi Baru</h5>
              <a style="margin-right:-15px;" href="{{ url('journal_add') }}" class="avatar-text avatar-md bg-default text-white">
                <i class="feather-plus bg-dark"></i>
              </a>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <input type="date" id="tanggal_transaksi" value="{{ date('Y-m-d') }}" name="tanggal_transaksi" class="form-control cust-control">
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <select class="form-control cust-control" id="jenis_transaksi" name="jenis_transaksi">
                            <option value="">Pilih transaksi</option>
                            @foreach ($list_transaksi as $item)
                            <option value="{{ $item->id }}">{{ $item->transaction_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
              </div>
              <div class="mtop20"></div>
              <div class="form-group">
                <label>Diterima Dari:</label>
                <select class="form-control cust-control" id="receive_from" name="receive_from">
                    <option value="">Pilih diterima dari</option>
                 </select>
              </div>
              <div class="mtop20"></div>
              <div class="form-group">
                <label>Disimpan Ke:</label>
                <select class="form-control cust-control" id="save_to" name="save_to">
                    <option value="">Pilih disimpan ke</option>
                </select>
              </div>
              <div class="mtop20"></div>
              <div class="form-group">
                <label>Keterangan:</label>
                <input type="text" class="form-control cust-control" id="keterangan" name="keterangan" placeholder="Nama Transaksi">
                   
              </div>
              <div class="mtop20"></div>
              <div class="form-group">
                <label>Nominal:</label>
                <input type="text" class="form-control cust-control" id="nominal" name="nominal" placeholder="0">
                   
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </div>
        </form>
        </div>
    </div>
    @endif