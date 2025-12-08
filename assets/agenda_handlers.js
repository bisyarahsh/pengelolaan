// Memastikan kode dieksekusi setelah DOM selesai dimuat
$(document).ready(function() {
    
    // ==========================================================
    // A. Inisialisasi Select2
    // ==========================================================
    
    // Inisialisasi Select2 untuk modal Tambah
    $( '#multiple-select-field' ).select2( {
        theme: "bootstrap-5",
        width: '100%',
        placeholder: 'Pilih Peserta',
        dropdownParent: $('#exampleModal'), 
        closeOnSelect: false,
    } );
    
    // Inisialisasi Select2 untuk modal Edit
    $( '.select2-edit' ).select2( {
        theme: "bootstrap-5",
        width: '100%',
        placeholder: 'Pilih Peserta',
        dropdownParent: $('#editModal'), 
        closeOnSelect: false,
    } );

    // ==========================================================
    // B. LOGIKA PILIH SEMUA PESERTA (Tanpa Unit)
    // ==========================================================

    // Logika Pilih Semua di Modal Tambah
    $('#select_all_peserta').on('click', function() {
        var selectElement = $('#multiple-select-field');
        var allOptions = [];
        selectElement.find('option').each(function() {
            if (!$(this).prop('disabled')) {
                allOptions.push($(this).val());
            }
        });
        selectElement.val(allOptions);
        selectElement.trigger('change');
    });

    // Logika Pilih Semua di Modal Edit
    $('#select_edit_all_peserta').on('click', function() {
        var selectElement = $('#edit-multiple-select-field');
        var allOptions = [];
        selectElement.find('option').each(function() {
            if (!$(this).prop('disabled')) {
                allOptions.push($(this).val());
            }
        });
        selectElement.val(allOptions);
        selectElement.trigger('change');
    });
    
    // ==========================================================
    // C. LOGIKA PILIH SEMUA BERDASARKAN UNIT (Modal Tambah)
    // ==========================================================
    
    $('#btn_select_all_unit').on('click', function(e) {
        e.preventDefault(); 
        var selectedUnitId = $('#select_unit_peserta').val();
        
        if (!selectedUnitId) {
            Swal.fire({
                title: "Perhatian",
                text: "Mohon pilih unit terlebih dahulu untuk melakukan seleksi massal.",
                icon: "warning"
            });
            return;
        }

        var $button = $(this);
        var originalHtml = $button.html();
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memuat...');

        $.ajax({
            url: '../php/fetch_peserta_unit.php', 
            type: 'POST',
            data: { id_unit: selectedUnitId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var participants = response.peserta;
                    
                    if (participants.length > 0) {
                        var participantIds = participants.map(function(p) { 
                            return p.id_user; 
                        });

                        $('#multiple-select-field').val(participantIds).trigger('change');
                        
                        Swal.fire({
                            title: "Berhasil!",
                            text: "Berhasil memilih " + participantIds.length + " peserta dengan peran peserta dari unit yang dipilih.",
                            icon: "success"
                        });
                        
                    } else {
                        Swal.fire({
                            title: "Informasi",
                            text: "Tidak ada peserta dengan peran peserta di unit tersebut.",
                            icon: "info"
                        });
                    }
                } else {
                    Swal.fire({
                        title: "Gagal!",
                        text: "Gagal mengambil data peserta: " + (response.error || 'Terjadi kesalahan tidak diketahui.'),
                        icon: "error"
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    title: "Error!",
                    text: "Kesalahan Koneksi Server/AJAX. Periksa console untuk detail.",
                    icon: "error"
                });
                console.error('AJAX GAGAL:', textStatus, errorThrown);
            },
            complete: function() {
                $button.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // ==========================================================
    // D. LOGIKA PILIH SEMUA BERDASARKAN UNIT (Modal Edit)
    // ==========================================================

    $('#edit_btn_select_all_unit').on('click', function(e) {
        e.preventDefault(); 
        var selectedUnitId = $('#edit_select_unit_peserta').val();
        
        if (!selectedUnitId) {
            Swal.fire({
                title: "Perhatian",
                text: "Mohon pilih unit terlebih dahulu untuk melakukan seleksi massal.",
                icon: "warning"
            });
            return;
        }

        var $button = $(this);
        var originalHtml = $button.html();
        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memuat...');

        $.ajax({
            url: '../php/fetch_peserta_unit.php', 
            type: 'POST',
            data: { id_unit: selectedUnitId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var participants = response.peserta;
                    
                    if (participants.length > 0) {
                        var participantIds = participants.map(function(p) { 
                            return p.id_user; 
                        });

                        // TARGETKAN SELECT2 MODAL EDIT
                        $('#edit-multiple-select-field').val(participantIds).trigger('change');
                        
                        Swal.fire({
                            title: "Berhasil!",
                            text: "Berhasil memilih " + participantIds.length + " peserta dengan peran peserta dari unit yang dipilih.",
                            icon: "success"
                        });
                        
                    } else {
                        Swal.fire({
                            title: "Informasi",
                            text: "Tidak ada peserta dengan peran peserta di unit tersebut.",
                            icon: "info"
                        });
                    }
                } else {
                    Swal.fire({
                        title: "Gagal!",
                        text: "Gagal mengambil data peserta: " + (response.error || 'Terjadi kesalahan tidak diketahui.'),
                        icon: "error"
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    title: "Error!",
                    text: "Kesalahan Koneksi Server/AJAX. Periksa console untuk detail.",
                    icon: "error"
                });
                console.error('AJAX GAGAL:', textStatus, errorThrown);
            },
            complete: function() {
                $button.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // ==========================================================
    // E. Event Handlers Modal Lainnya
    // ==========================================================

    // Modal Delete Handler menggunakan delegasi
    $(document).on('click', 'button[data-bs-target="#deletemodal"]', function (event) {
        var id_rapat = $(this).data('id'); 
        $('#hapus_id_rapat_modal').val(id_rapat); 
    });

    // Modal Edit Handler menggunakan delegasi
    $(document).on('click', 'button[data-bs-target="#editModal"]', function (event) {
        var button = $(this);
        
        // 1. AMBIL SEMUA DATA DARI DATA ATTRIBUTE TOMBOL
        var id_rapat_terpilih = $(this).data('id'); 
        var tanggal = button.data('tanggal');
        var jam = button.data('jam');
        var judul = button.data('judul');
        var ruangan = button.data('ruangan');
        var keterangan = button.data('keterangan');
        var unit_id = button.data('unitid');
        var notulen_file = button.data('notulen');
        var peserta_data = button.data('peserta'); 
        
        // 2. RESET/BERSIHKAN FIELD UTAMA
        $('#edit-multiple-select-field').val(null).trigger('change');
        $('#current_file_info').html(''); 

        // 3. ISI DATA KE INPUT MODAL
        $('#edit_rapat_id_unik').val(id_rapat_terpilih); 
        $('#edit_date').val(tanggal);
        $('#edit_time').val(jam);
        $('#edit_judul').val(judul);
        $('#edit_ruangan').val(ruangan);
        $('#edit_keterangan').val(keterangan);
        $('#edit_unit').val(unit_id).trigger('change');

        // Peserta (Select2)
        if (peserta_data && peserta_data.length > 0) {
            $('#edit-multiple-select-field').val(peserta_data).trigger('change');
        }

        // File Notulen
        $('#notulen_file_lama').val(notulen_file);
        
        var notulenHtml = 'Tidak ada file notulen saat ini. ';
        if (notulen_file) {
            var fileUrl = '../notulen_files/' + notulen_file;
            notulenHtml = 'File: <strong>' + notulen_file + '</strong>. (<a href="' + fileUrl + '" target="_blank">Lihat</a>) <br>Centang untuk menghapus: <input type="checkbox" name="hapus_file_lama" value="yes">';
        }
        $('#current_file_info').html(notulenHtml);
    });

    // Modal View Handler menggunakan delegasi dan AJAX
    $(document).on('click', '.view-rapat-btn', function (event) { 
        var id_rapat = $(this).data('id');
        $('#view_rapat_modal').val(id_rapat);
        
        // Reset/loading state
        $('#view_tanggal').html('Memuat...');
        $('#view_jam').html('Memuat...');
        $('#view_judul').html('Memuat...');
        $('#view_ruangan').html('Memuat...');
        $('#view_unit').html('Memuat...');
        $('#view_keterangan').html('Memuat...');
        $('#view_peserta').html('Memuat...');
        $('#view_notulen_file').html('Memuat...');
        
        // Panggil AJAX untuk mengambil detail lengkap
        $.ajax({
            url: '../php/ajax_detail.php?id=' + id_rapat,
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data && !data.error) {
                    var tanggalFormatted = data.tanggal_rapat ? new Date(data.tanggal_rapat + 'T00:00:00').toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'}) : '-';
                    var jamFormatted = data.jam_rapat ? data.jam_rapat.substring(0, 5) + ' WIB' : '-';
                    
                    $('#view_tanggal').html(tanggalFormatted);
                    $('#view_jam').html(jamFormatted);
                    $('#view_judul').html(data.judul_rapat || '-');
                    $('#view_ruangan').html(data.ruang_rapat || '-'); 
                    $('#view_unit').html(data.nama_unit || '-'); 
                    $('#view_keterangan').html(data.keterangan || '-');

                    var pesertaHtml = 'Tidak ada peserta.';
                    if (data.peserta_details && data.peserta_details.length > 0) {
                        pesertaHtml = data.peserta_details.join(', ');
                    }
                    $('#view_peserta').html(pesertaHtml);

                    var fileHtml = 'Tidak ada file notulen.';
                    if (data.notulen_file) {
                        var fileUrl = '../notulen_files/' + data.notulen_file;
                        fileHtml = '<a href="' + fileUrl + '" target="_blank" class="btn btn-sm btn-info"><i class="fa-solid fa-file-alt"></i> Lihat File Notulen</a>';
                    }
                    $('#view_notulen_file').html(fileHtml);

                } else {
                    $('#view_tanggal').html('ERROR: ' + (data.error || 'Data rapat tidak ditemukan.'));
                    console.error("Respon Server Error:", data.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#view_tanggal').html('Kesalahan Server/Koneksi. Status: ' + jqXHR.status);
                console.error("AJAX GAGAL:", textStatus, errorThrown);
            }
        });
    });

    // Handler Notifikasi
    $(document).on('click', 'button[data-bs-target="#notifmodal"]', function (event) {
        var id_rapat = $(this).data('id'); 
        $('#notif_id_rapat').val(id_rapat); 
    });

});