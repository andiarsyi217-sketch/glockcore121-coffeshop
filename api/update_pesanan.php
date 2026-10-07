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

if ($action === 'update_full') {
    $nama     = trim($data['nama']      ?? '');
    $no_hp    = trim($data['no_hp']     ?? '');
    $menu_id  = (int)($data['menu_id']  ?? 0);
    $menu_nama = trim($data['menu_nama'] ?? '');
    $harga    = (int)($data['harga']    ?? 0);
    $jumlah   = (int)($data['jumlah']   ?? 1);
    $total    = (int)($data['total']    ?? 0);
    $catatan  = trim($data['catatan']   ?? '');
    $status   = trim($data['status']    ?? 'baru');

    $allowed = ['baru', 'diproses', 'selesai', 'dibatalkan'];
    if (!$nama) {
        http_response_code(422);
        die(json_encode(['success' => false, 'message' => 'Nama pemesan wajib diisi.']));
    }
    if (!in_array($status, $allowed)) $status = 'baru';

    $stmt = $conn->prepare(
        "UPDATE pesanan SET nama=?, no_hp=?, menu_id=?, menu_nama=?, harga=?, jumlah=?, total=?, catatan=?, status=? WHERE id=?"
    );
    $stmt->bind_param('ssississsi', $nama, $no_hp, $menu_id, $menu_nama, $harga, $jumlah, $total, $catatan, $status, $id);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true, 'message' => "Pesanan #$id berhasil diperbarui."]);
        exit;
    } else {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'Gagal memperbarui pesanan: ' . $conn->error]));
    }
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenali.']);
