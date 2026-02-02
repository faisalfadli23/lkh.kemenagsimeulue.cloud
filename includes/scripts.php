<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Fungsi untuk menangkap parameter "pesan" di URL
    const urlParams = new URLSearchParams(window.location.search);
    const pesan = urlParams.get('pesan');

    if (pesan) {
        if (pesan === 'sukses') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data berhasil disimpan.',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (pesan === 'hapus_berhasil') {
            Swal.fire({
                icon: 'success',
                title: 'Terhapus!',
                text: 'Data telah berhasil dihapus.',
                timer: 2000,
                showConfirmButton: false
            });
        } else if (pesan === 'gagal') {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Terjadi kesalahan, silakan coba lagi.',
            });
        }
        // Dan seterusnya untuk jenis pesan lainnya...
    }
</script>