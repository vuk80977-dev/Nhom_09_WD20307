<?php
$title = 'Đăng ký';
ob_start();
?>

<div class="mx-auto auth-card">
    <div class="card shadow-sm">
        <div class="card-header">
            <h1 class="h5 mb-0">Đăng ký tài khoản</h1>
        </div>
        <div class="card-body">
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form action="index.php?c=Auth&a=register" method="post">
                <div class="mb-3">
                    <label class="form-label">Họ tên</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nhập lại mật khẩu</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="index.php?c=Auth&a=login" class="btn btn-outline-secondary">Đã có tài khoản</a>
                    <button type="submit" class="btn btn-primary">Đăng ký</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
