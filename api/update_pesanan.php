<?php
// ============================================================
// api/update_pesanan.php -- Update status atau hapus pesanan
// Method: POST
// ============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../db/config.php';

$body = json_decode(file_get_contents('php://input'), true);
$data = $body ?: $_POST;

$action = $data['action'] ?? 'update_status';
$id = (int)($data['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'ID pesanan tidak valid.']));
}

if ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM pesanan WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true, 'message' => 'Pesanan berhasil dihapus.']);
        exit;
    } else {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'Gagal menghapus pesanan.']));
    }
}

if ($action === 'update_status') {
    $status = trim($data['status'] ?? '');
    $allowed = ['baru', 'diproses', 'selesai', 'dibatalkan'];
    if (!in_array($status, $allowed)) {
        http_response_code(422);
        die(json_encode(['success' => false, 'message' => 'Status tidak valid.']));
    }

    $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true, 'message' => "Status pesanan #$id berhasil diubah menjadi " . ucfirst($status) . "."]);
        exit;
    } else {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'Gagal memperbarui status pesanan.']));
    }
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenali.']);
