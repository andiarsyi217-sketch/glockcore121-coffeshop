<?php
// ============================================================
// db/config.php -- Koneksi Database Glockcore 121
// ============================================================

define('DB_HOST',    'localhost');
define('DB_USER',    'root');       // Default XAMPP
define('DB_PASS',    '');           // Default XAMPP (kosong)
define('DB_NAME',    'glockcore121');
define('DB_CHARSET', 'utf8mb4');

// Buat koneksi MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal: ' . $conn->connect_error
    ]));
}

// Set charset
$conn->set_charset(DB_CHARSET);

// Set timezone WITA
date_default_timezone_set('Asia/Makassar');

// Nomor WhatsApp Penjual Glockcore 121 (Ganti jika diperlukan, gunakan format 62xxx)
define('WA_PENJUAL', '6282298663371');

