<div class="row">
    <div class="col-md-12 text-center">
        <h2>Chào mừng đến với Hệ thống Quản lý Thư viện</h2>
        <p>Vui lòng chọn chức năng bên dưới hoặc từ menu trên:</p>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Đăng ký độc giả</h5>
                <a href="?action=dangKy" class="btn btn-primary">Đi đến</a>
            </div>
        </div>
    </div>
    <?php if (!isset($_SESSION['user'])): ?>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Đăng nhập</h5>
                    <a href="?action=dangNhap" class="btn btn-primary">Đi đến</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Quản lý sách</h5>
                    <a href="?action=quanLySach" class="btn btn-primary">Đi đến</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Thêm phiếu mượn</h5>
                    <a href="?action=themPhieuMuon" class="btn btn-primary">Đi đến</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Quản lý phiếu mượn</h5>
                    <a href="?action=quanLyPhieuMuon" class="btn btn-primary">Đi đến</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Trả sách</h5>
                    <a href="?action=traSach" class="btn btn-primary">Đi đến</a>                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Thống kê</h5>
                    <a href="?action=thongKe" class="btn btn-primary">Đi đến</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>