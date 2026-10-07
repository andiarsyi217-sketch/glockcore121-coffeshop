<?php
// ============================================================
// api/get_pesanan.php -- Ambil daftar pesanan (JSON)
// Method: GET
// ============================================================

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db/config.php';

$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT * FROM pesanan WHERE 1=1";
$params = [];
$types = "";

if (!empty($status) && in_array($status, ['baru', 'diproses', 'selesai', 'dibatalkan'])) {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (nama LIKE ? OR no_hp LIKE ? OR menu_nama LIKE ? OR id LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "ssss";
}

$sql .= " ORDER BY created_at DESC LIMIT 200";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$orders = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = [
            'id'         => (int)$row['id'],
            'nama'       => $row['nama'],
            'no_hp'      => $row['no_hp'],
            'menu_id'    => (int)$row['menu_id'],
            'menu_nama'  => $row['menu_nama'],
            'harga'      => (int)$row['harga'],
            'jumlah'     => (int)$row['jumlah'],
            'total'      => (int)$row['total'],
            'catatan'    => $row['catatan'] ?: '-',
            'status'     => $row['status'],
            'created_at' => $row['created_at']
        ];
    }
}

// Hitung rekap statistik
$stats_res = $conn->query("
    SELECT 
        COUNT(*) as total_pesanan,
        SUM(CASE WHEN status = 'baru' THEN 1 ELSE 0 END) as total_baru,
        SUM(CASE WHEN status = 'diproses' THEN 1 ELSE 0 END) as total_diproses,
        SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as total_selesai,
        SUM(CASE WHEN status = 'dibatalkan' THEN 1 ELSE 0 END) as total_batal,
        SUM(CASE WHEN status != 'dibatalkan' THEN total ELSE 0 END) as total_omset
    FROM pesanan
");
$stats = $stats_res ? $stats_res->fetch_assoc() : [];

$conn->close();

echo json_encode([
    'success' => true,
    'total'   => count($orders),
    'stats'   => [
        'total_pesanan'  => (int)($stats['total_pesanan'] ?? 0),
        'total_baru'     => (int)($stats['total_baru'] ?? 0),
        'total_diproses' => (int)($stats['total_diproses'] ?? 0),
        'total_selesai'  => (int)($stats['total_selesai'] ?? 0),
        'total_batal'    => (int)($stats['total_batal'] ?? 0),
        'total_omset'    => (int)($stats['total_omset'] ?? 0),
    ],
    'data'    => $orders
], JSON_UNESCAPED_UNICODE);
