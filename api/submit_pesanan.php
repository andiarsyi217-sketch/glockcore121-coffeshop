<?php
// ============================================================
// api/submit_pesanan.php -- Simpan pesanan pelanggan
// Method: POST
// Body (form-data atau JSON): nama, no_hp, menu_id, jumlah, catatan
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

// Ambil data POST (JSON atau form-data)
$body = json_decode(file_get_contents('php://input'), true);
$data = $body ?: $_POST;

$nama    = trim($data['nama']    ?? '');
$no_hp   = trim($data['no_hp']  ?? '');
$menu_id = (int)($data['menu_id'] ?? 0);
$jumlah  = max(1, (int)($data['jumlah'] ?? 1));
$catatan = trim($data['catatan'] ?? '');

// Validasi
$errors = [];
if (empty($nama))                        $errors[] = 'Nama tidak boleh kosong.';
if (empty($no_hp))                       $errors[] = 'Nomor HP tidak boleh kosong.';
if ($menu_id <= 0)                       $errors[] = 'Menu tidak valid.';
if ($jumlah < 1 || $jumlah > 20)        $errors[] = 'Jumlah harus antara 1-20.';

if (!empty($errors)) {
    http_response_code(422);
    die(json_encode(['success' => false, 'message' => implode(' ', $errors)]));
}

// Cek menu di database
$stmt = $conn->prepare("SELECT id, nama, harga FROM menu WHERE id = ? AND tersedia = 1");
$stmt->bind_param('i', $menu_id);
$stmt->execute();
$menu = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$menu) {
    http_response_code(404);
    die(json_encode(['success' => false, 'message' => 'Menu tidak ditemukan atau sudah habis.']));
}

$total = $menu['harga'] * $jumlah;

// Simpan pesanan
$stmt = $conn->prepare(
    "INSERT INTO pesanan (nama, no_hp, menu_id, menu_nama, harga, jumlah, total, catatan)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('ssissiis', $nama, $no_hp, $menu_id, $menu['nama'], $menu['harga'], $jumlah, $total, $catatan);

if ($stmt->execute()) {
    $pesanan_id = $conn->insert_id;
    $stmt->close();
    $conn->close();

    $harga_fmt = 'Rp ' . number_format($menu['harga'], 0, ',', '.');
    $total_fmt = 'Rp ' . number_format($total, 0, ',', '.');
    $catatan_text = !empty($catatan) ? $catatan : '-';
    $waktu = date('d/m/Y H:i') . ' WITA';

    // Template chat siap kirim ke WhatsApp Penjual
    $wa_message = "*PESANAN BARU GLOCKCORE 121*\n"
                . "------------------------------------\n"
                . "*No. Pesanan :* #{$pesanan_id}\n"
                . "*Waktu :* {$waktu}\n"
                . "------------------------------------\n"
                . "*Nama Pemesan :* {$nama}\n"
                . "*No. HP/WA :* {$no_hp}\n"
                . "------------------------------------\n"
                . "*Detail Pesanan :*\n"
                . "- {$menu['nama']} ({$jumlah}x)\n"
                . "- Harga Satuan : {$harga_fmt}\n"
                . "- Catatan : {$catatan_text}\n"
                . "------------------------------------\n"
                . "*TOTAL TAGIHAN : {$total_fmt}*\n"
                . "------------------------------------\n"
                . "Halo kak, saya ingin konfirmasi pesanan ini. Mohon segera diproses ya, terima kasih!";

    $wa_number = defined('WA_PENJUAL') ? WA_PENJUAL : '6282298663371';
    $wa_url = "https://api.whatsapp.com/send?phone=" . urlencode($wa_number) . "&text=" . urlencode($wa_message);

    echo json_encode([
        'success'    => true,
        'message'    => "Pesanan berhasil disimpan (#$pesanan_id)! Menghubungkan ke WhatsApp penjual...",
        'pesanan_id' => $pesanan_id,
        'wa_url'     => $wa_url,
        'wa_message' => $wa_message,
        'detail'     => [
            'nama'      => $nama,
            'menu'      => $menu['nama'],
            'jumlah'    => $jumlah,
            'total'     => $total_fmt,
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    $stmt->close();
    $conn->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pesanan. Coba lagi.']);
}
