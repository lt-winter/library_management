<?php
namespace App\Models;

use PDO;

class Sach {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function danhSachSach() {
        $sql = "SELECT s.*, tg.TenTacGia, tl.TenTheLoai 
                FROM Sach s 
                JOIN TacGia tg ON s.MaTacGia = tg.MaTacGia 
                JOIN TheLoai tl ON s.MaTheLoai = tl.MaTheLoai";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function themSach($tenSach, $maTacGia, $maTheLoai, $namXuatBan, $nhaXuatBan, $soLuong) {
        $sql = "INSERT INTO Sach (TenSach, MaTacGia, MaTheLoai, NamXuatBan, NhaXuatBan, SoLuong) 
                VALUES (:tenSach, :maTacGia, :maTheLoai, :namXuatBan, :nhaXuatBan, :soLuong)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':tenSach' => $tenSach,
            ':maTacGia' => $maTacGia,
            ':maTheLoai' => $maTheLoai,
            ':namXuatBan' => $namXuatBan,
            ':nhaXuatBan' => $nhaXuatBan,
            ':soLuong' => $soLuong
        ]);
    }

    public function suaSach($maSach, $tenSach, $maTacGia, $maTheLoai, $namXuatBan, $nhaXuatBan, $soLuong) {
        $sql = "UPDATE Sach SET TenSach = :tenSach, MaTacGia = :maTacGia, MaTheLoai = :maTheLoai, 
                NamXuatBan = :namXuatBan, NhaXuatBan = :nhaXuatBan, SoLuong = :soLuong 
                WHERE MaSach = :maSach";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':maSach' => $maSach,
            ':tenSach' => $tenSach,
            ':maTacGia' => $maTacGia,
            ':maTheLoai' => $maTheLoai,
            ':namXuatBan' => $namXuatBan,
            ':nhaXuatBan' => $nhaXuatBan,
            ':soLuong' => $soLuong
        ]);
    }

    public function xoaSach($maSach) {
        $sql = "DELETE FROM Sach WHERE MaSach = :maSach";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':maSach' => $maSach]);
    }

    public function timKiemSach($tuKhoa) {
        $sql = "SELECT s.*, tg.TenTacGia, tl.TenTheLoai 
                FROM Sach s 
                JOIN TacGia tg ON s.MaTacGia = tg.MaTacGia 
                JOIN TheLoai tl ON s.MaTheLoai = tl.MaTheLoai 
                WHERE s.TenSach LIKE :tuKhoa OR tg.TenTacGia LIKE :tuKhoa";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':tuKhoa' => "%$tuKhoa%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}