<?php
// ============================================================
// api/get_menu.php -- Ambil data menu dari database
// Method: GET
// Params: ?kategori=coffee|non-coffee|tea (opsional)
// ============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db/config.php';

$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

// Sanitasi input
$allowed = ['coffee', 'non-coffee', 'tea'];

if ($kategori && in_array($kategori, $allowed)) {
    $stmt = $conn->prepare(
        "SELECT id, nama, harga, kategori, gambar, deskripsi, tersedia 
         FROM menu 
         WHERE tersedia = 1 AND kategori = ?
         ORDER BY kategori, nama"
    );
    $stmt->bind_param('s', $kategori);
} else {
    $stmt = $conn->prepare(
        "SELECT id, nama, harga, kategori, gambar, deskripsi, tersedia 
         FROM menu 
         WHERE tersedia = 1
         ORDER BY kategori, nama"
    );
}

$stmt->execute();
$result = $stmt->get_result();

$menus = [];
while ($row = $result->fetch_assoc()) {
    $menus[] = [
        'id'        => (int) $row['id'],
        'nama'      => $row['nama'],
        'harga'     => (int) $row['harga'],
        'harga_fmt' => 'Rp ' . number_format($row['harga'], 0, ',', '.'),
        'kategori'  => $row['kategori'],
        'gambar'    => $row['gambar'],
        'deskripsi' => $row['deskripsi'],
        'tersedia'  => (bool) $row['tersedia'],
    ];
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'total'   => count($menus),
    'data'    => $menus
], JSON_UNESCAPED_UNICODE);
