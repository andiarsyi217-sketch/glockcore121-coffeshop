<?php
// ============================================================
// admin/index.php -- Panel Admin Glockcore 121
// Lihat pesanan & pesan kontak masuk
// ============================================================

require_once __DIR__ . '/../db/config.php';

// Tandai pesan kontak sebagai sudah dibaca jika diklik
if (isset($_GET['baca']) && is_numeric($_GET['baca'])) {
    $id = (int)$_GET['baca'];
    $conn->query("UPDATE pesan_kontak SET sudah_dibaca=1 WHERE id=$id");
    header("Location: index.php?tab=kontak");
    exit;
}

// Update status pesanan
if (isset($_POST['update_status'], $_POST['pesanan_id'], $_POST['status'])) {
    $allowed_status = ['baru','diproses','selesai','dibatalkan'];
    $pesanan_id = (int)$_POST['pesanan_id'];
    $status = $_POST['status'];
    if (in_array($status, $allowed_status)) {
        $stmt = $conn->prepare("UPDATE pesanan SET status=? WHERE id=?");
        $stmt->bind_param('si', $status, $pesanan_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: index.php?tab=pesanan&updated=1");
    exit;
}

$tab = $_GET['tab'] ?? 'pesanan';

// Ambil data pesanan
$pesanan_result = $conn->query(
    "SELECT * FROM pesanan ORDER BY created_at DESC LIMIT 100"
);
$pesanan_list = $pesanan_result ? $pesanan_result->fetch_all(MYSQLI_ASSOC) : [];

// Ambil data pesan kontak
$kontak_result = $conn->query(
    "SELECT * FROM pesan_kontak ORDER BY created_at DESC LIMIT 100"
);
$kontak_list = $kontak_result ? $kontak_result->fetch_all(MYSQLI_ASSOC) : [];

// Statistik ringkas
$total_pesanan  = count($pesanan_list);
$total_kontak   = count($kontak_list);
$belum_dibaca   = array_filter($kontak_list, fn($k) => !$k['sudah_dibaca']);
$pesanan_baru   = array_filter($pesanan_list, fn($p) => $p['status'] === 'baru');

$conn->close();

// Fungsi status badge
function badge($status) {
    $map = [
        'baru'        => 'background:#1d4ed8;color:#fff',
        'diproses'    => 'background:#d97706;color:#fff',
        'selesai'     => 'background:#15803d;color:#fff',
        'dibatalkan'  => 'background:#b91c1c;color:#fff',
    ];
    $style = $map[$status] ?? 'background:#555;color:#fff';
    return "<span style='$style;padding:2px 10px;border-radius:12px;font-size:0.8rem;font-weight:600;text-transform:uppercase;'>$status</span>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel | Glockcore 121</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', sans-serif; background: #0d0d0d; color: #e5e5e5; min-height: 100vh; }

    /* Header */
    .header { background: #111; border-bottom: 1px solid #222; padding: 1rem 2rem; display: flex; align-items: center; gap: 1rem; }
    .header-logo { font-size: 1.4rem; font-weight: 900; letter-spacing: 0.1em; color: #fff; }
    .header-logo span { color: #888; }
    .header-badge { background: #1f1f1f; border: 1px solid #333; padding: 4px 12px; border-radius: 20px; font-size: 0.78rem; color: #888; }

    /* Stats */
    .stats { display: flex; gap: 1rem; padding: 1.5rem 2rem; flex-wrap: wrap; }
    .stat-card { background: #161616; border: 1px solid #222; border-radius: 10px; padding: 1.2rem 1.8rem; min-width: 160px; }
    .stat-card h3 { font-size: 2rem; font-weight: 800; color: #fff; }
    .stat-card p { font-size: 0.82rem; color: #666; margin-top: 4px; }

    /* Tabs */
    .tabs { display: flex; gap: 0; padding: 0 2rem; border-bottom: 1px solid #222; margin-bottom: 0; }
    .tab-btn { padding: 0.9rem 1.5rem; background: none; border: none; color: #666; cursor: pointer; font-size: 0.95rem; border-bottom: 2px solid transparent; transition: all 0.2s; }
    .tab-btn.active { color: #fff; border-bottom-color: #fff; }
    .tab-btn:hover { color: #ccc; }

    /* Table */
    .content { padding: 2rem; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
    th { background: #1a1a1a; padding: 12px 14px; text-align: left; color: #888; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #222; }
    td { padding: 12px 14px; border-bottom: 1px solid #1a1a1a; vertical-align: top; }
    tr:hover td { background: #111; }
    .unread td { background: #0f1821; }

    /* Form update status */
    .status-form { display: flex; gap: 6px; align-items: center; }
    .status-form select { background: #1a1a1a; color: #e5e5e5; border: 1px solid #333; border-radius: 6px; padding: 4px 8px; font-size: 0.82rem; }
    .btn-sm { background: #fff; color: #000; border: none; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; cursor: pointer; }
    .btn-sm:hover { background: #ddd; }
    .btn-baca { background: #1f1f1f; color: #ccc; border: 1px solid #333; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; text-decoration: none; }
    .btn-baca:hover { background: #2a2a2a; }

    .empty { text-align: center; padding: 3rem; color: #555; }
    .alert-success { background: #052e16; border: 1px solid #166534; color: #4ade80; padding: 10px 16px; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
  </style>
</head>
<body>

<div class="header">
  <div class="header-logo">GLOCKCORE <span>121</span></div>
  <div class="header-badge">Admin Panel</div>
  <a href="../index.html" style="margin-left:auto;color:#555;font-size:0.85rem;text-decoration:none;">← Kembali ke Website</a>
</div>

<div class="stats">
  <div class="stat-card">
    <h3><?= $total_pesanan ?></h3>
    <p>Total Pesanan</p>
  </div>
  <div class="stat-card" style="border-color:#1d4ed888">
    <h3 style="color:#60a5fa"><?= count($pesanan_baru) ?></h3>
    <p>Pesanan Baru</p>
  </div>
  <div class="stat-card">
    <h3><?= $total_kontak ?></h3>
    <p>Pesan Kontak</p>
  </div>
  <div class="stat-card" style="border-color:#92400e88">
    <h3 style="color:#fbbf24"><?= count($belum_dibaca) ?></h3>
    <p>Belum Dibaca</p>
  </div>
</div>

<div class="tabs">
  <button class="tab-btn <?= $tab === 'pesanan' ? 'active' : '' ?>" onclick="location.href='?tab=pesanan'">
    📋 Pesanan (<?= $total_pesanan ?>)
  </button>
  <button class="tab-btn <?= $tab === 'kontak' ? 'active' : '' ?>" onclick="location.href='?tab=kontak'">
    ✉️ Pesan Kontak (<?= $total_kontak ?>)
  </button>
</div>

<div class="content">

  <?php if (isset($_GET['updated'])): ?>
    <div class="alert-success">✓ Status pesanan berhasil diperbarui.</div>
  <?php endif; ?>

  <?php if ($tab === 'pesanan'): ?>
  <!-- TAB: PESANAN -->
  <div class="table-wrap">
    <?php if (empty($pesanan_list)): ?>
      <div class="empty">Belum ada pesanan yang masuk.</div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Tanggal</th>
          <th>Nama Pelanggan</th>
          <th>No. HP</th>
          <th>Menu</th>
          <th>Jml</th>
          <th>Total</th>
          <th>Catatan</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pesanan_list as $p): ?>
        <tr>
          <td style="color:#555">#<?= $p['id'] ?></td>
          <td style="color:#777;font-size:0.82rem"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
          <td style="font-weight:600"><?= htmlspecialchars($p['nama']) ?></td>
          <td><?= htmlspecialchars($p['no_hp']) ?></td>
          <td><?= htmlspecialchars($p['menu_nama']) ?></td>
          <td style="text-align:center"><?= $p['jumlah'] ?></td>
          <td style="font-weight:700;color:#fff">Rp <?= number_format($p['total'], 0, ',', '.') ?></td>
          <td style="color:#888;font-size:0.85rem"><?= htmlspecialchars($p['catatan'] ?: '-') ?></td>
          <td>
            <form method="POST" class="status-form">
              <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="update_status" value="1">
              <select name="status">
                <?php foreach (['baru','diproses','selesai','dibatalkan'] as $s): ?>
                  <option value="<?= $s ?>" <?= $p['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn-sm">Simpan</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <?php else: ?>
  <!-- TAB: PESAN KONTAK -->
  <div class="table-wrap">
    <?php if (empty($kontak_list)): ?>
      <div class="empty">Belum ada pesan kontak yang masuk.</div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Tanggal</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Subjek</th>
          <th>Pesan</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($kontak_list as $k): ?>
        <tr class="<?= !$k['sudah_dibaca'] ? 'unread' : '' ?>">
          <td style="color:#555">#<?= $k['id'] ?></td>
          <td style="color:#777;font-size:0.82rem"><?= date('d/m/Y H:i', strtotime($k['created_at'])) ?></td>
          <td style="font-weight:600"><?= htmlspecialchars($k['nama']) ?></td>
          <td style="color:#888"><?= htmlspecialchars($k['email']) ?></td>
          <td><?= htmlspecialchars($k['subjek']) ?></td>
          <td style="color:#aaa;font-size:0.85rem;max-width:300px"><?= nl2br(htmlspecialchars(substr($k['pesan'], 0, 150))) ?><?= strlen($k['pesan']) > 150 ? '...' : '' ?></td>
          <td>
            <?php if (!$k['sudah_dibaca']): ?>
              <a href="?tab=kontak&baca=<?= $k['id'] ?>" class="btn-baca">Tandai Dibaca</a>
            <?php else: ?>
              <span style="color:#15803d;font-size:0.82rem">✓ Dibaca</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>

</body>
</html>
