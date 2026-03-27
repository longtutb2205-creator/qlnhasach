-- =====================================================
--  SCHEMA: quan_ly_nha_sach
--  Charset: utf8mb4  |  Engine: InnoDB
-- =====================================================

CREATE DATABASE IF NOT EXISTS quan_ly_nha_sach
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quan_ly_nha_sach;

-- ─────────────────────────────────────────────────────
--  1. PHÂN QUYỀN / TÀI KHOẢN
-- ─────────────────────────────────────────────────────
CREATE TABLE users (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ten        VARCHAR(100)  NOT NULL,
  email      VARCHAR(150)  NOT NULL UNIQUE,
  mat_khau   VARCHAR(255)  NOT NULL,
  role       ENUM('quan_ly','ban_hang','kho') NOT NULL DEFAULT 'ban_hang',
  trang_thai ENUM('hoat_dong','khoa') NOT NULL DEFAULT 'hoat_dong',
  avatar     VARCHAR(255)  NULL,
  created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────
--  2. DANH MỤC SÁCH
-- ─────────────────────────────────────────────────────
CREATE TABLE the_loai (
  id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ten   VARCHAR(100) NOT NULL,
  mo_ta TEXT         NULL
) ENGINE=InnoDB;

CREATE TABLE tac_gia (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ten        VARCHAR(150) NOT NULL,
  quoc_tich  VARCHAR(80)  NULL,
  tieu_su    TEXT         NULL
) ENGINE=InnoDB;

CREATE TABLE nha_xuat_ban (
  id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ten     VARCHAR(150) NOT NULL,
  dia_chi VARCHAR(255) NULL,
  dien_thoai VARCHAR(20) NULL
) ENGINE=InnoDB;

CREATE TABLE sach (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  isbn          VARCHAR(20)    NULL UNIQUE,
  ten_sach      VARCHAR(255)   NOT NULL,
  tac_gia_id    INT UNSIGNED   NULL,
  the_loai_id   INT UNSIGNED   NULL,
  nxb_id        INT UNSIGNED   NULL,
  nam_xuat_ban  YEAR           NULL,
  gia_ban       DECIMAL(12,0)  NOT NULL DEFAULT 0,
  gia_nhap      DECIMAL(12,0)  NOT NULL DEFAULT 0,
  so_luong_ton  INT            NOT NULL DEFAULT 0,
  mo_ta         TEXT           NULL,
  hinh_anh      VARCHAR(255)   NULL,
  trang_thai    ENUM('con_hang','het_hang','ngung_ban') NOT NULL DEFAULT 'con_hang',
  created_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (tac_gia_id)  REFERENCES tac_gia(id)      ON DELETE SET NULL,
  FOREIGN KEY (the_loai_id) REFERENCES the_loai(id)     ON DELETE SET NULL,
  FOREIGN KEY (nxb_id)      REFERENCES nha_xuat_ban(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────
--  3. KHÁCH HÀNG
-- ─────────────────────────────────────────────────────
CREATE TABLE khach_hang (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ten        VARCHAR(100)  NOT NULL,
  dien_thoai VARCHAR(15)   NULL UNIQUE,
  email      VARCHAR(150)  NULL,
  dia_chi    VARCHAR(255)  NULL,
  loai       ENUM('moi','thuong_xuyen','vip','tiem_nang') NOT NULL DEFAULT 'moi',
  diem_tich_luy INT        NOT NULL DEFAULT 0,
  created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────
--  4. NHÂN VIÊN
-- ─────────────────────────────────────────────────────
CREATE TABLE nhan_vien (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED   NULL UNIQUE,
  ho_ten       VARCHAR(100)   NOT NULL,
  ngay_sinh    DATE           NULL,
  gioi_tinh    ENUM('nam','nu','khac') NULL,
  dien_thoai   VARCHAR(15)    NULL,
  email        VARCHAR(150)   NULL,
  dia_chi      VARCHAR(255)   NULL,
  chuc_vu      VARCHAR(80)    NULL,
  ngay_vao_lam DATE           NULL,
  luong_co_ban DECIMAL(12,0)  NULL,
  created_at   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────
--  5. NHÀ CUNG CẤP
-- ─────────────────────────────────────────────────────
CREATE TABLE nha_cung_cap (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ten        VARCHAR(150)  NOT NULL,
  dia_chi    VARCHAR(255)  NULL,
  dien_thoai VARCHAR(20)   NULL,
  email      VARCHAR(150)  NULL,
  nguoi_lien_he VARCHAR(100) NULL,
  created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────
--  6. GIỎ HÀNG
-- ─────────────────────────────────────────────────────
CREATE TABLE gio_hang (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  khach_hang_id INT UNSIGNED  NULL,
  session_id    VARCHAR(100)  NULL,
  created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (khach_hang_id) REFERENCES khach_hang(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE gio_hang_chi_tiet (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  gio_hang_id INT UNSIGNED  NOT NULL,
  sach_id     INT UNSIGNED  NOT NULL,
  so_luong    INT           NOT NULL DEFAULT 1,
  gia         DECIMAL(12,0) NOT NULL,
  FOREIGN KEY (gio_hang_id) REFERENCES gio_hang(id)  ON DELETE CASCADE,
  FOREIGN KEY (sach_id)     REFERENCES sach(id)      ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────
--  7. HÓA ĐƠN & THANH TOÁN
-- ─────────────────────────────────────────────────────
CREATE TABLE hoa_don (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ma_hoa_don     VARCHAR(20)   NOT NULL UNIQUE,
  khach_hang_id  INT UNSIGNED  NULL,
  nhan_vien_id   INT UNSIGNED  NULL,
  tong_tien      DECIMAL(14,0) NOT NULL DEFAULT 0,
  giam_gia       DECIMAL(14,0) NOT NULL DEFAULT 0,
  tien_can_tra   DECIMAL(14,0) NOT NULL DEFAULT 0,
  trang_thai     ENUM('cho_thanh_toan','hoan_tat','huy') NOT NULL DEFAULT 'cho_thanh_toan',
  ghi_chu        TEXT          NULL,
  created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (khach_hang_id) REFERENCES khach_hang(id) ON DELETE SET NULL,
  FOREIGN KEY (nhan_vien_id)  REFERENCES nhan_vien(id)  ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE chi_tiet_hoa_don (
  id         INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  hoa_don_id INT UNSIGNED  NOT NULL,
  sach_id    INT UNSIGNED  NOT NULL,
  so_luong   INT           NOT NULL,
  don_gia    DECIMAL(12,0) NOT NULL,
  thanh_tien DECIMAL(14,0) NOT NULL,
  FOREIGN KEY (hoa_don_id) REFERENCES hoa_don(id) ON DELETE CASCADE,
  FOREIGN KEY (sach_id)    REFERENCES sach(id)    ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE thanh_toan (
  id              INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  hoa_don_id      INT UNSIGNED  NOT NULL UNIQUE,
  so_tien         DECIMAL(14,0) NOT NULL,
  phuong_thuc     ENUM('tien_mat','the_ngan_hang','vi_dien_tu') NOT NULL DEFAULT 'tien_mat',
  trang_thai      ENUM('khoi_tao','dang_xu_ly','thanh_cong','that_bai','huy') NOT NULL DEFAULT 'khoi_tao',
  ma_giao_dich    VARCHAR(100)  NULL,
  thoi_gian_tt    TIMESTAMP     NULL,
  created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (hoa_don_id) REFERENCES hoa_don(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────
--  8. KHO — PHIẾU NHẬP / XUẤT
-- ─────────────────────────────────────────────────────
CREATE TABLE phieu_nhap (
  id             INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  ma_phieu       VARCHAR(20)   NOT NULL UNIQUE,
  nha_cung_cap_id INT UNSIGNED NULL,
  nhan_vien_id   INT UNSIGNED  NULL,
  tong_tien      DECIMAL(14,0) NOT NULL DEFAULT 0,
  trang_thai     ENUM('khoi_tao','cho_xac_nhan','dang_van_chuyen','hoan_tat','huy') NOT NULL DEFAULT 'khoi_tao',
  ghi_chu        TEXT          NULL,
  created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (nha_cung_cap_id) REFERENCES nha_cung_cap(id) ON DELETE SET NULL,
  FOREIGN KEY (nhan_vien_id)    REFERENCES nhan_vien(id)    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE chi_tiet_phieu_nhap (
  id           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  phieu_nhap_id INT UNSIGNED NOT NULL,
  sach_id      INT UNSIGNED  NOT NULL,
  so_luong     INT           NOT NULL,
  don_gia_nhap DECIMAL(12,0) NOT NULL,
  thanh_tien   DECIMAL(14,0) NOT NULL,
  FOREIGN KEY (phieu_nhap_id) REFERENCES phieu_nhap(id) ON DELETE CASCADE,
  FOREIGN KEY (sach_id)       REFERENCES sach(id)       ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE phieu_xuat (
  id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  ma_phieu      VARCHAR(20)   NOT NULL UNIQUE,
  nhan_vien_id  INT UNSIGNED  NULL,
  ly_do         VARCHAR(255)  NULL,
  trang_thai    ENUM('khoi_tao','cho_xac_nhan','dang_xu_ly','hoan_tat','huy') NOT NULL DEFAULT 'khoi_tao',
  ghi_chu       TEXT          NULL,
  created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (nhan_vien_id) REFERENCES nhan_vien(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE chi_tiet_phieu_xuat (
  id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  phieu_xuat_id INT UNSIGNED  NOT NULL,
  sach_id       INT UNSIGNED  NOT NULL,
  so_luong      INT           NOT NULL,
  FOREIGN KEY (phieu_xuat_id) REFERENCES phieu_xuat(id) ON DELETE CASCADE,
  FOREIGN KEY (sach_id)       REFERENCES sach(id)       ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────
--  SEED DATA — Tài khoản mặc định
-- ─────────────────────────────────────────────────────
-- Mật khẩu: Admin@123 (bcrypt)
INSERT INTO users (ten, email, mat_khau, role) VALUES
('Quản Trị Viên', 'admin@nhasach.com',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'quan_ly'),
('Nhân Viên Bán', 'banhang@nhasach.com',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'ban_hang'),
('Nhân Viên Kho', 'kho@nhasach.com',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'kho');

-- Seed thể loại
INSERT INTO the_loai (ten) VALUES
('Văn học'),('Kinh tế'),('Khoa học kỹ thuật'),
('Lịch sử'),('Tâm lý - Kỹ năng sống'),('Thiếu nhi');

-- Seed nhà xuất bản
INSERT INTO nha_xuat_ban (ten) VALUES
('NXB Trẻ'),('NXB Kim Đồng'),('NXB Giáo Dục'),('NXB Tổng Hợp TP.HCM');