<?php
$title = 'Quản lý khách hàng';
ob_start();

// Hàm helper escape chuỗi (ngăn mã HTML/XSS)
function h($v) {
    return htmlspecialchars($v ?? '', ENT_QUOTES);
}

// Lấy các giá trị lọc hiện tại từ query (để giữ trạng thái trên form)
$curStatus = $_GET['status'] ?? '';
$q = $_GET['q'] ?? '';

// Tuỳ chọn trạng thái cho dropdown lọc
$statuses = [
    ''         => 'Tất cả', 
    'active'   => 'Đang hoạt động', 
    'inactive' => 'Ngừng hoạt động'
];
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0"><?= h($title) ?></h1>
    <a href="index.php?c=Customer&a=create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Thêm khách hàng
    </a>
</div>

<!-- Thông báo flash (nếu có) -->
<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= h($flash['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
    <?= h($flash['msg'] ?? '') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<!-- Form lọc tìm kiếm -->
<form class="row g-2 mb-3" method="get">
    <input type="hidden" name="c" value="Customer">
    <input type="hidden" name="a" value="index">
    <div class="col-sm-4">
        <input type="text" name="q" class="form-control" placeholder="Tìm theo tên hoặc email..." value="<?= h($q) ?>">
    </div>
    <div class="col-sm-3">
        <select name="status" class="form-select">
            <?php foreach ($statuses as $val => $label): ?>
                <option value="<?= h($val) ?>" <?= $curStatus === $val ? 'selected' : '' ?>><?= h($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-sm-3">
        <button type="submit" class="btn btn-secondary">
            <i class="bi bi-funnel"></i> Lọc / Tìm kiếm
        </button>
    </div>
</form>

<!-- Bảng danh sách khách hàng -->
<div class="card shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover table-sm mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th style="width: 60px;">ID</th>
          <th>Họ tên</th>
          <th>Email</th>
          <th>Điện thoại</th>
          <th>Trạng thái</th>
          <th style="width: 120px;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach (($data['items'] ?? []) as $cus): ?>
          <tr>
            <td><?= h($cus['id']) ?></td>
            <td><?= h($cus['name']) ?></td>
            <td><?= h($cus['email']) ?></td>
            <td><?= h($cus['phone']) ?></td>
            <td>
              <?php if ($cus['status'] === 'active'): ?>
                <span class="badge bg-success">Đang hoạt động</span>
              <?php else: ?>
                <span class="badge bg-secondary">Ngừng hoạt động</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="index.php?c=Customer&a=edit&id=<?= $cus['id'] ?>" class="btn btn-sm btn-outline-warning" title="Sửa khách hàng">
                <i class="bi bi-pencil-square"></i>
              </a>
              <a href="index.php?c=Customer&a=delete&id=<?= $cus['id'] ?>" class="btn btn-sm btn-outline-danger" 
                 onclick="return confirm('Bạn có chắc chắn muốn xóa khách hàng này?');" title="Xóa khách hàng">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (empty($data['items'])): // Nếu không có khách hàng nào ?>
          <tr>
            <td colspan="6" class="text-center text-muted">Không tìm thấy khách hàng phù hợp.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Phân trang -->
<?php 
  // Hàm tạo URL cho trang tương ứng (giữ nguyên các tham số lọc hiện tại)
  $buildUrl = function($p) {
      $qs = $_GET;
      $qs['page'] = $p;
      return 'index.php?' . http_build_query($qs);
  };
  $currentPage = (int)($data['page'] ?? 1);
  $totalPages = (int)($data['pages'] ?? 1);
?>
<nav class="mt-3">
  <ul class="pagination pagination-sm mb-0 justify-content-center">
    <!-- Nút "Trang trước" -->
    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
      <a class="page-link" href="<?= h($buildUrl($currentPage - 1)) ?>">«</a>
    </li>
    <!-- Liệt kê các trang -->
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
      <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
        <a class="page-link" href="<?= h($buildUrl($p)) ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>
    <!-- Nút "Trang sau" -->
    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
      <a class="page-link" href="<?= h($buildUrl($currentPage + 1)) ?>">»</a>
    </li>
  </ul>
</nav>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
?>
