<?php
// $title, $data (items, total, page, pages, perPage), $flash, $tours, $customers đã được controller cung cấp
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Quản lý Booking</h5>
  <a href="index.php?c=Booking&a=create" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Thêm Booking
  </a>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get">
  <input type="hidden" name="c" value="Booking">
  <input type="hidden" name="a" value="index">
  <div class="col-sm-4">
    <input type="text" class="form-control" name="q" placeholder="Tìm theo ID/TourID/CustomerID..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
  </div>
  <div class="col-sm-3">
    <select class="form-select" name="status">
      <option value="">-- Trạng thái --</option>
      <?php
        $statuses = ['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'cancelled' => 'Đã hủy', 'completed' => 'Hoàn thành'];
        $cur = $_GET['status'] ?? '';
        foreach ($statuses as $k => $label):
      ?>
        <option value="<?= $k ?>" <?= $cur===$k?'selected':''; ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-sm-3">
    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-funnel"></i> Lọc</button>
    <a class="btn btn-outline-dark" href="index.php?c=Booking&a=index"><i class="bi bi-x-circle"></i> Xóa lọc</a>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:80px;">ID</th>
            <th>Tour</th>
            <th>Khách hàng</th>
            <th>Ngày đặt</th>
            <th>Trạng thái</th>
            <th style="width:160px;" class="text-end">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data['items'])): foreach ($data['items'] as $row): ?>
            <tr>
              <td>#<?= (int)$row['id'] ?></td>
              <td>
                <?php
                  $t = $tours[$row['tour_id']] ?? null;
                  echo $t ? htmlspecialchars($t['name']) . " (ID {$row['tour_id']})" : 'Tour #' . (int)$row['tour_id'];
                ?>
              </td>
              <td>
                <?php
                  $c = $customers[$row['customer_id']] ?? null;
                  echo $c ? htmlspecialchars($c['name']) . " (ID {$row['customer_id']})" : 'KH #' . (int)$row['customer_id'];
                ?>
              </td>
              <td><?= htmlspecialchars($row['booking_date']) ?></td>
              <td>
                <?php
                  $badgeClass = match($row['status']) {
                    'pending'   => 'warning',
                    'confirmed' => 'success',
                    'cancelled' => 'danger',
                    default     => 'secondary'
                  };
                  $label = $statuses[$row['status']] ?? ucfirst($row['status']);
                ?>
                <span class="badge text-bg-<?= $badgeClass ?>"><?= $label ?></span>
              </td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-primary" href="index.php?c=Booking&a=edit&id=<?= (int)$row['id'] ?>">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <form action="index.php?c=Booking&a=destroy" method="post" class="d-inline" onsubmit="return confirm('Xóa booking #<?= (int)$row['id'] ?>?');">
                  <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" class="text-center text-muted py-4">Không có dữ liệu.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if ($data['pages'] > 1): ?>
<nav class="mt-3">
  <ul class="pagination pagination-sm mb-0">
    <?php
      $buildUrl = function($p) {
        $qs = $_GET;
        $qs['page'] = $p;
        return 'index.php?' . http_build_query($qs);
      };
    ?>
    <li class="page-item <?= $data['page']<=1?'disabled':'' ?>">
      <a class="page-link" href="<?= $buildUrl($data['page']-1) ?>">«</a>
    </li>
    <?php for ($i=1; $i<=$data['pages']; $i++): ?>
      <li class="page-item <?= $i==$data['page']?'active':'' ?>">
        <a class="page-link" href="<?= $buildUrl($i) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item <?= $data['page']>=$data['pages']?'disabled':'' ?>">
      <a class="page-link" href="<?= $buildUrl($data['page']+1) ?>">»</a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
