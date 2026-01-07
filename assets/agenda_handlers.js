$(document).ready(function() {
    
    // --------------- Select2 ---------------
    // Select2 untuk modal Tambah
    $( '#multiple-select-field' ).select2( {
        theme: "bootstrap-5",
        width: '100%',
        placeholder: 'Pilih Peserta',
        dropdownParent: $('#exampleModal'), 
        closeOnSelect: false,
    } );
    
    // Select2 untuk modal Edit
    $( '.select2-edit' ).select2( {
        theme: "bootstrap-5",
        width: '100%',
        placeholder: 'Pilih Peserta',
        dropdownParent: $('#editModal'), 
        closeOnSelect: false,
    } );
    // --------------- End Select2 ---------------


    // --------------- Pilih Peserta ---------------
    // Pilih Semua di Modal Tambah
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

    // Pilih Semua di Modal Edit
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
    
    // Pilih Berdasarkan Unit di Modal Tambah
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

    // Pilih Berdasarkan Unit Modal Edit
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
    // --------------- End Pilih Peserta ---------------

    // --------------- Handler Modal ---------------
    // Modal Delete
    $(document).on('click', 'button[data-bs-target="#deletemodal"]', function (event) {
        var id_rapat = $(this).data('id'); 
        $('#hapus_id_rapat_modal').val(id_rapat); 
    });

    // Modal Edit
    $(document).on('click', 'button[data-bs-target="#editModal"]', function (event) {
        var button = $(this);
        
        var id_rapat_terpilih = button.data('id'); 
        var tanggal = button.data('tanggal');
        var jam = button.data('jam');
        var judul = button.data('judul');
        var ruangan = button.data('ruangan');
        var keterangan = button.data('keterangan');
        var unit_id = button.data('unitid');
        var notulen_file = button.data('notulen');
        var peserta_data = button.data('peserta'); 
        
        $('#edit-multiple-select-field').val(null).trigger('change');
        $('#current_file_info').html(''); 

        $('#edit_rapat_id_unik').val(id_rapat_terpilih); 
        $('#edit_judul').val(judul);
        $('#edit_ruangan').val(ruangan);
        $('#edit_keterangan').val(keterangan);
        $('#edit_unit').val(unit_id).trigger('change');

        $('#edit_date').val(tanggal);
        $('#edit_time').val(jam);

        var dateInput = document.querySelector("#edit_date");
        var timeInput = document.querySelector("#edit_time");

        if (dateInput && dateInput._flatpickr) {
            dateInput._flatpickr.setDate(tanggal);
        }
        
        if (timeInput && timeInput._flatpickr) {
            timeInput._flatpickr.setDate(jam);
        }

        if (peserta_data && peserta_data.length > 0) {
            $('#edit-multiple-select-field').val(peserta_data).trigger('change');
        }

        $('#notulen_file_lama').val(notulen_file);
        
        var notulenHtml = 'Tidak ada file notulen saat ini. ';
        if (notulen_file) {
            var fileUrl = '../notulen_files/' + notulen_file;
            notulenHtml = 'File: <strong>' + notulen_file + '</strong>. (<a href="' + fileUrl + '" target="_blank">Lihat</a>) <br>Centang untuk menghapus: <input type="checkbox" name="hapus_file_lama" value="yes">';
        }
        $('#current_file_info').html(notulenHtml);
    });

    // Modal Notifikasi
    $(document).on('click', 'button[data-bs-target="#notifmodal"]', function (event) {
        var id_rapat = $(this).data('id'); 
        $('#notif_id_rapat').val(id_rapat); 
    });
    // --------------- End Handler Modal ---------------

});

// --------------- Modifikasi Modal View ---------------
$(document).ready(function() {
    
    $('.view-rapat-btn').on('click', function() {
        var id_rapat = $(this).data('id');
        
        // Reset tampilan peserta
        $('#view_peserta_list_box').html('<div class="text-center text-muted mt-5"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>');
        
        $.ajax({
            url: 'agenda.php',
            type: 'GET',
            data: { action: 'get_rapat_detail', id: id_rapat },
            dataType: 'json',
            success: function(data) {
                if(data) {
                    $('#view_judul').text(data.judul_rapat);
                    var jamClean = data.jam_rapat ? data.jam_rapat.substring(0, 5) : '--:--';
                    $('#view_jam').text(jamClean + ' WIB');
                    $('#view_ruangan').text(data.ruang_rapat ? data.ruang_rapat : '-');
                    $('#view_unit').text(data.nama_unit);
                    $('#view_keterangan').text(data.keterangan ? data.keterangan : '-');
                    
                    if (data.tanggal_rapat) {
                        const dateObj = new Date(data.tanggal_rapat);
                        const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
                        const months = ["JAN", "FEB", "MAR", "APR", "MEI", "JUN", "JUL", "AGU", "SEP", "OKT", "NOV", "DES"];
                        
                        $('#view_tanggal_day').text(dateObj.getDate());
                        $('#view_tanggal_month').text(months[dateObj.getMonth()]);
                        
                        const fullDate = days[dateObj.getDay()] + ', ' + dateObj.getDate() + ' ' + months[dateObj.getMonth()] + ' ' + dateObj.getFullYear();
                        $('#view_tanggal_full').text(fullDate);
                    }

                    if (data.notulen_file) {
                        var fileName = data.notulen_file;
                        var fileExt = fileName.split('.').pop().toLowerCase();
                        
                        var iconClass = 'fa-file';
                        var colorClass = 'text-secondary';

                        if (fileExt === 'pdf') {
                            iconClass = 'fa-file-pdf';
                            colorClass = 'text-danger';
                        } else if (fileExt === 'doc' || fileExt === 'docx') {
                            iconClass = 'fa-file-word';
                            colorClass = 'text-primary';
                        } else if (fileExt === 'jpg' || fileExt === 'jpeg' || fileExt === 'png') {
                            iconClass = 'fa-file-image';
                            colorClass = 'text-success';
                        }

                        $('#view_notulen_container').html(`
                            <div class="file-attachment-box">
                                <div class="d-flex align-items-center" style="overflow: hidden;">
                                    <i class="fa-solid ${iconClass} ${colorClass} fa-xl me-3"></i>
                                    <div class="d-flex flex-column" style="overflow: hidden;">
                                        <span class="fw-bold small text-truncate" style="max-width: 100%;" title="${fileName}">${fileName}</span>
                                        <span class="text-muted" style="font-size: 0.7rem;">Format: ${fileExt.toUpperCase()}</span>
                                    </div>
                                </div>
                                <a href="../notulen_files/${fileName}" target="_blank" class="btn btn-sm btn-outline-dark ms-2" title="Download File">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            </div>
                        `);
                    } else {
                        $('#view_notulen_container').html('<span class="text-muted small"><i>Tidak ada file notulen yang diunggah.</i></span>');
                    }

                    var pesertaHtml = '';
                    if (data.peserta_details && data.peserta_details.length > 0) {
                        $('#view_peserta_count').text(data.peserta_details.length + ' Orang');
                        
                        $.each(data.peserta_details, function(index, value) {
                            pesertaHtml += `
                                <div class="participant-item">
                                    <i class="fa-solid fa-user-circle fa-lg"></i>
                                    <div>${value}</div>
                                </div>
                            `;
                        });
                    } else {
                        $('#view_peserta_count').text('0 Orang');
                        pesertaHtml = '<div class="text-center text-muted mt-4"><small>Belum ada peserta yang ditambahkan.</small></div>';
                    }
                    $('#view_peserta_list_box').html(pesertaHtml);
                }
            }
        });
    });
});