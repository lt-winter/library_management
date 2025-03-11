<?php

namespace App\Controllers;

use App\Models\PhieuMuon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDO;
use Exception;

class PhieuMuonController
{
    private $conn;
    public $phieuMuon;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->phieuMuon = new PhieuMuon($conn);
    }

    public function quanLyPhieuMuon() {
        if (!isset($_SESSION['user'])) {
            header("Location: /ct467-project/public/?action=dangNhap");
            exit;
        }

        $errors = [];
        $thong_bao = '';
        $phieuMuonList = $this->phieuMuon->danhSachPhieuMuon();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['xoa'])) {
                $maPhieuMuon = $_POST['ma_phieu_muon'] ?? '';
                if ($this->phieuMuon->xoaPhieuMuon($maPhieuMuon)) {
                    $thong_bao = "Xóa phiếu mượn thành công!";
                    $phieuMuonList = $this->phieuMuon->danhSachPhieuMuon();
                } else {
                    $errors[] = "Lỗi khi xóa phiếu mượn!";
                }
            } elseif (isset($_POST['xuat_excel'])) {
                $this->xuatExcelPhieuMuon($phieuMuonList); // Gọi hàm riêng
                exit;
            }
        }

        $data = [
            'action' => 'quanLyPhieuMuon',
            'errors' => $errors,
            'thong_bao' => $thong_bao,
            'phieuMuonList' => $phieuMuonList
        ];
        extract($data);
        require_once __DIR__ . '/../views/layouts/main.php';
    }

    public function xuatExcelPhieuMuon($phieuMuonList) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Mã Phiếu Mượn');
        $sheet->setCellValue('B1', 'Tên Độc Giả');
        $sheet->setCellValue('C1', 'Ngày Mượn');
        $sheet->setCellValue('D1', 'Ngày Trả');
        $sheet->setCellValue('E1', 'Trạng Thái');
        $sheet->setCellValue('F1', 'Sách Mượn');

        $row = 2;
        foreach ($phieuMuonList as $pm) {
            $sheet->setCellValue("A$row", $pm['MaPhieuMuon']);
            $sheet->setCellValue("B$row", $pm['TenDocGia']);
            $sheet->setCellValue("C$row", $pm['NgayMuon']);
            $sheet->setCellValue("D$row", $pm['NgayTra']);
            $sheet->setCellValue("E$row", $pm['TrangThai']);
            $sheet->setCellValue("F$row", $pm['SachMuon']);
            $row++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="danh_sach_phieu_muon_' . date('YmdHis') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    public function danhSachPhieuMuon()
    {
        $sql = "CALL DanhSachPhieuMuonChuaTra()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function traSach()
    {
        if (!isset($_SESSION['user'])) {
            header("Location: /ct467-project/public/?action=dangNhap");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $maChiTietPM = $_POST['ma_chi_tiet_pm'] ?? '';
            $ngayTraSach = $_POST['ngay_tra_sach'] ?? '';

            $this->conn->beginTransaction();
            try {
                // Thêm vào PhieuTra (Trigger sẽ tính tiền phạt)
                $sql = "INSERT INTO PhieuTra (MaChiTietPM, NgayTraSach) VALUES (:maChiTietPM, :ngayTraSach)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':maChiTietPM' => $maChiTietPM, ':ngayTraSach' => $ngayTraSach]);

                // Cập nhật trạng thái PhieuMuon nếu tất cả sách trong phiếu đã trả
                $sql = "SELECT COUNT(*) as chuaTra 
                        FROM ChiTietPhieuMuon ctpm 
                        LEFT JOIN PhieuTra pt ON ctpm.MaChiTietPM = pt.MaChiTietPM 
                        WHERE ctpm.MaPhieuMuon = (
                            SELECT MaPhieuMuon FROM ChiTietPhieuMuon WHERE MaChiTietPM = :maChiTietPM
                        ) AND pt.MaChiTietPM IS NULL";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':maChiTietPM' => $maChiTietPM]);
                $chuaTra = $stmt->fetch(PDO::FETCH_ASSOC)['chuaTra'];

                if ($chuaTra == 0) { // Nếu không còn sách nào chưa trả
                    $sql = "UPDATE PhieuMuon 
                            SET TrangThai = 'Đã trả' 
                            WHERE MaPhieuMuon = (
                                SELECT MaPhieuMuon FROM ChiTietPhieuMuon WHERE MaChiTietPM = :maChiTietPM
                            )";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([':maChiTietPM' => $maChiTietPM]);
                }

                // Cập nhật số lượng sách
                $sql = "UPDATE Sach s 
                        JOIN ChiTietPhieuMuon ctpm ON s.MaSach = ctpm.MaSach 
                        SET s.SoLuong = s.SoLuong + ctpm.SoLuongMuon 
                        WHERE ctpm.MaChiTietPM = :maChiTietPM";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([':maChiTietPM' => $maChiTietPM]);

                $this->conn->commit();
                $thong_bao = "Trả sách thành công!";
            } catch (Exception $e) {
                $this->conn->rollBack();
                $errors[] = "Lỗi khi trả sách: " . $e->getMessage();
            }
        }

        $data = [
            'action' => 'traSach',
            'errors' => $errors ?? [],
            'thong_bao' => $thong_bao ?? ''
        ];
        extract($data);
        require_once __DIR__ . '/../views/layouts/main.php';
    }
}
