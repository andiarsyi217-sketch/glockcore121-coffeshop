-- ============================================================
-- DATABASE GLOCKCORE 121 - Coffee Shop
-- Import via phpMyAdmin: pilih file ini lalu klik Import
-- Atau via MySQL CLI: mysql -u root < glockcore121.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `glockcore121`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `glockcore121`;

-- ============================================================
-- TABEL: menu
-- ============================================================
CREATE TABLE IF NOT EXISTS `menu` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `nama`       VARCHAR(100) NOT NULL,
  `harga`      INT(11)      NOT NULL COMMENT 'Harga dalam Rupiah',
  `kategori`   ENUM('coffee','non-coffee','tea') NOT NULL,
  `gambar`     VARCHAR(255) NOT NULL,
  `deskripsi`  TEXT         NOT NULL,
  `tersedia`   TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DATA: 13 Menu Glockcore 121
-- ============================================================
INSERT INTO `menu` (`nama`, `harga`, `kategori`, `gambar`, `deskripsi`) VALUES
('BLACK MAGz Americano',  13000, 'coffee',     'americano.jpeg',        'Espresso murni dengan air mineral dingin/panas, menghasilkan cita rasa bold, bersih, dan menyegarkan.'),
('Butterscotch',          17000, 'coffee',     'butterscooth.jpeg',     'Perpaduan manis gurih sirup butterscotch berpadu dengan espresso mantap dan susu creamy racikan Glock.'),
('Caramel Machiato',      15000, 'coffee',     'caramel mathiato.jpeg', 'Lapisan espresso pekat di atas susu vanilla lembut, disempurnakan lelehan saus karamel legit yang memikat.'),
('Coffee Latte',          13000, 'coffee',     'coffe latte.jpeg',      'Kombinasi klasik single shot espresso dengan susu kukus bertekstur halus, pas untuk penikmat kopi lembut.'),
('SWEET MAGz Aren',       13000, 'coffee',     'kopi aren.jpeg',        'Kopi susu dengan manis legit gula aren asli Nusantara, rasa gurih otentik yang selalu jadi pilihan utama.'),
('Kopi Susu',             13000, 'coffee',     'kopi susu.jpeg',        'Perpaduan espresso segar dengan susu segar pilihan, harmonis dan pas untuk segala suasana.'),
('Matcha Latte',          15000, 'coffee',     'matcha.jpeg',           'Matcha Jepang premium dipadukan susu oat creamy, menghasilkan kombinasi pahit segar yang memanjakan.'),
('Pandan Latte',          14000, 'coffee',     'pandan.jpeg',           'Aroma pandan asli berpadu espresso dan susu segar, cita rasa lokal dengan sentuhan modern Glockcore.'),
('Chocolate',             12000, 'non-coffee', 'chocolate.jpeg',        'Cokelat pekat premium dipadukan susu segar creamy, sensasi rasa manis seimbang yang memanjakan lidah.'),
('Milo',                  12000, 'non-coffee', 'milo.jpeg',             'Milo dingin/panas klasik yang diracik dengan susu segar full cream, cocok untuk semua usia.'),
('Leci Tea',              12000, 'tea',        'lecitea.jpeg',          'Teh segar infusi dengan sirup leci harum, kesegaran buah tropis yang menyegarkan di setiap tegukan.'),
('Lemon Tea',             12000, 'tea',        'lemontea.jpeg',         'Teh hitam premium dipadu perasan lemon segar, perpaduan asam manis menyegarkan yang sempurna.'),
('Thai Tea',              13000, 'tea',        'thaitea.jpeg',          'Thai tea otentik dengan warna oranye cerah khas, rasa rempah kuat dibalut susu evaporasi yang kaya.');

-- ============================================================
-- TABEL: pesanan
-- ============================================================
CREATE TABLE IF NOT EXISTS `pesanan` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `nama`       VARCHAR(100) NOT NULL,
  `no_hp`      VARCHAR(20)  NOT NULL,
  `menu_id`    INT(11)      NOT NULL,
  `menu_nama`  VARCHAR(100) NOT NULL,
  `harga`      INT(11)      NOT NULL,
  `jumlah`     INT(11)      NOT NULL DEFAULT 1,
  `total`      INT(11)      NOT NULL,
  `catatan`    TEXT,
  `status`     ENUM('baru','diproses','selesai','dibatalkan') NOT NULL DEFAULT 'baru',
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABEL: pesan_kontak
-- ============================================================
CREATE TABLE IF NOT EXISTS `pesan_kontak` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `nama`         VARCHAR(100) NOT NULL,
  `email`        VARCHAR(150) NOT NULL,
  `subjek`       VARCHAR(200) NOT NULL,
  `pesan`        TEXT         NOT NULL,
  `sudah_dibaca` TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
