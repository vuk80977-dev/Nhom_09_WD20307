<?php
// Biến có sẵn: $title, $booking (mảng), $tours, $customers
$isEdit = !empty($booking['id']);
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0"><?= htmlspecialchars($title) ?></h5>
  <a href="index.php?c=Booking&a=index" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form action="index.php?c=Booking&a=<?= $isEdit ? 'update' : 'store' ?>" method="post" class="row g-3">
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$booking['id'] ?>">
      <?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Tour</label>
        <select class="form-select" name="tour_id" required>
          <option value="">-- Chọn tour --</option>
          <?php foreach ($tours as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= (isset($booking['tour_id']) && (int)$booking['tour_id']==(int)$t['id']) ? 'selected' : '' ?>>
              #<?= (int)$t['id'] ?> - <?= htmlspecialchars($t['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Khách hàng</label>
        <select class="form-select" name="customer_id" required>
          <option value="">-- Chọn khách hàng --</option>
          <?php foreach ($customers as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (isset($booking['customer_id']) && (int)$booking['customer_id']==(int)$c['id']) ? 'selected' : '' ?>>
              #<?= (int)$c['id'] ?> - <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['email'] ?? '') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Ngày đặt</label>
        <input type="date" class="form-control" name="booking_date" value="<?= htmlspecialchars($booking['booking_date'] ?? date('Y-m-d')) ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Trạng thái</label>
        <?php
          $statuses = ['pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'cancelled' => 'Đã hủy', 'completed' => 'Hoàn thành'];
          $cur = $booking['status'] ?? 'pending';
        ?>
        <select class="form-select" name="status">
          <?php foreach ($statuses as $k => $label): ?>
            <option value="<?= $k ?>" <?= $cur===$k?'selected':''; ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12">
        <button class="btn btn-primary">
          <i class="bi bi-save"></i> <?= $isEdit ? 'Cập nhật' : 'Lưu' ?>
        </button>
        <a href="index.php?c=Booking&a=index" class="btn btn-outline-secondary">Hủy</a>
      </div>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
