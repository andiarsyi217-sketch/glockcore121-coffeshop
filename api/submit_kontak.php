<?php
// ============================================================
// api/submit_kontak.php -- Simpan pesan dari form kontak
// Method: POST
// Body: nama, email, subjek, pesan
// ============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']));
}

require_once __DIR__ . '/../db/config.php';

// Ambil data POST
$body = json_decode(file_get_contents('php://input'), true);
$data = $body ?: $_POST;

$nama   = trim($data['nama']   ?? '');
$email  = trim($data['email']  ?? '');
$subjek = trim($data['subjek'] ?? '');
$pesan  = trim($data['pesan']  ?? '');

// Validasi
$errors = [];
if (empty($nama))                     $errors[] = 'Nama tidak boleh kosong.';
if (empty($email))                    $errors[] = 'Email tidak boleh kosong.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
if (empty($subjek))                   $errors[] = 'Subjek tidak boleh kosong.';
if (empty($pesan))                    $errors[] = 'Pesan tidak boleh kosong.';
if (strlen($pesan) < 10)              $errors[] = 'Pesan terlalu singkat (min. 10 karakter).';

if (!empty($errors)) {
    http_response_code(422);
    die(json_encode(['success' => false, 'message' => implode(' ', $errors)]));
}

// Simpan ke database
$stmt = $conn->prepare(
    "INSERT INTO pesan_kontak (nama, email, subjek, pesan) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param('ssss', $nama, $email, $subjek, $pesan);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => true,
        'message' => "Terima kasih $nama! Pesan Anda sudah kami terima dan akan segera dibalas."
    ], JSON_UNESCAPED_UNICODE);
} else {
    $stmt->close();
    $conn->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal mengirim pesan. Coba lagi.']);
}
