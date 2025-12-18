<?php
$title = 'Đăng nhập';
ob_start();
?>

<div class="mx-auto auth-card">
    <div class="card shadow-sm">
        <div class="card-header">
            <h1 class="h5 mb-0">Đăng nhập</h1>
        </div>
        <div class="card-body">
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form action="index.php?c=Auth&a=login" method="post">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="index.php?c=Auth&a=register" class="btn btn-outline-secondary">Tạo tài khoản</a>
                    <button type="submit" class="btn btn-primary">Đăng nhập</button>
                </div>
            </form>

            <hr>
            <div class="small text-muted">
                Nếu bạn là quản trị viên: <a href="index.php?c=User&a=login">Đăng nhập Admin</a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
