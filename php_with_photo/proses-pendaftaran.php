<?php

include("config.php");

// cek apakah tombol daftar sudah diklik atau blum?
if(isset($_POST['daftar'])){

    // ambil data dari formulir
    $nama = mysqli_real_escape_string($db, $_POST['nama']);
    $alamat = mysqli_real_escape_string($db, $_POST['alamat']);
    $jk = mysqli_real_escape_string($db, $_POST['jenis_kelamin']);
    $agama = mysqli_real_escape_string($db, $_POST['agama']);
    $sekolah = mysqli_real_escape_string($db, $_POST['sekolah_asal']);

    // proses upload foto (opsional)
    $fotoPath = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

        $file = $_FILES['foto'];

        // tangani error upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            header('Location: index.php?status=gagal&msg=foto');
            exit;
        }

        // batas ukuran 2MB
        $maxSize = 2 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            header('Location: index.php?status=gagal&msg=filesize');
            exit;
        }

        $tmp = $file['tmp_name'];
        if (!is_uploaded_file($tmp)) {
            header('Location: index.php?status=gagal&msg=invalid');
            exit;
        }

        // validasi mime
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: 'application/octet-stream';
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

        if (!array_key_exists($mime, $allowed)) {
            header('Location: index.php?status=gagal&msg=mime');
            exit;
        }

        // nama file unik
        $ext = $allowed[$mime];
        $random = bin2hex(random_bytes(6));
        $filename = date('Ymd_His') . '_' . $random . '.' . $ext;

        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $dest = $uploadDir . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($tmp, $dest)) {
            header('Location: index.php?status=gagal&msg=save');
            exit;
        }

        // simpan path relatif (untuk digunakan di list)
        $fotoPath = 'uploads/' . $filename;
    }

    // buat query
    $sql = "INSERT INTO calon_siswa (nama, alamat, jenis_kelamin, agama, sekolah_asal, foto) VALUE ('$nama', '$alamat', '$jk', '$agama', '$sekolah', " . ($fotoPath ? "'$fotoPath'" : "NULL") . ")";
    $query = mysqli_query($db, $sql);

    // apakah query simpan berhasil?
    if( $query ) {
        // kalau berhasil alihkan ke halaman index.php dengan status=sukses
        header('Location: index.php?status=sukses');
    } else {
        // kalau gagal alihkan ke halaman indek.php dengan status=gagal
        header('Location: index.php?status=gagal');
    }


} else {
    die("Akses dilarang...");
}

?>