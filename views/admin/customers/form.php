<?php
// Sử dụng biến $title được controller thiết lập (vd: "Thêm khách hàng" hoặc "Sửa khách hàng")
ob_start();

// Hàm escape HTML
if (!function_exists('h')) {
  function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES);
  }
}
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header">
        <h1 class="h5 mb-0"><?= h($title) ?></h1>
      </div>
      <div class="card-body">
        <!-- Thông báo flash (nếu có) -->
        <?php if (!empty($flash)): ?>
          <div class="alert alert-<?= h($flash['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
            <?= h($flash['msg'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <!-- Form thông tin khách hàng -->
        <form action="index.php?c=Customer&a=<?= $customer['id'] ? 'update' : 'store' ?>" method="post">
          <?php if (!empty($customer['id'])): ?>
            <input type="hidden" name="id" value="<?= h($customer['id']) ?>">
          <?php endif; ?>
          <div class="mb-3">
            <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control" value="<?= h($customer['name']) ?>" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email <?= ($customer['id'] ? '' : '<span class="text-danger">*</span>') ?></label>
            <input type="email" id="email" name="email" class="form-control" value="<?= h($customer['email']) ?>">
          </div>
          <div class="mb-3">
            <label for="phone" class="form-label">Điện thoại <?= ($customer['id'] ? '' : '<span class="text-danger">*</span>') ?></label>
            <input type="text" id="phone" name="phone" class="form-control" value="<?= h($customer['phone']) ?>">
          </div>
          <div class="mb-3">
            <label for="address" class="form-label">Địa chỉ</label>
            <input type="text" id="address" name="address" class="form-control" value="<?= h($customer['address']) ?>">
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Trạng thái</label>
            <select id="status" name="status" class="form-select">
              <option value="active" <?= $customer['status'] === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
              <option value="inactive" <?= $customer['status'] === 'inactive' ? 'selected' : '' ?>>Ngừng hoạt động</option>
            </select>
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-success">
              <i class="bi bi-save"></i> <?= $customer['id'] ? 'Cập nhật' : 'Thêm mới' ?>
            </button>
            <a href="index.php?c=Customer&a=index" class="btn btn-secondary">Hủy</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
