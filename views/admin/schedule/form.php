<?php
$isEdit = !empty($schedule['id']);
ob_start();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$statuses = [
  'open' => 'Mở bán',
  'closed' => 'Đóng',
  'completed' => 'Hoàn thành',
  'cancelled' => 'Hủy'
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0"><?= h($title) ?></h5>
  <a href="index.php?c=Schedule&a=index" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form action="index.php?c=Schedule&a=<?= $isEdit?'update':'store' ?>" method="post" class="row g-3">
      <?php if($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$schedule['id'] ?>">
      <?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Tour <span class="text-danger">*</span></label>
        <select class="form-select" name="tour_id" required>
          <option value="">-- Chọn tour --</option>
          <?php foreach($tours as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= (int)($schedule['tour_id'] ?? 0)==(int)$t['id']?'selected':'' ?>>
              #<?= (int)$t['id'] ?> - <?= h($t['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Hướng dẫn viên</label>
        <select class="form-select" name="guide_id">
          <option value="">-- Chưa gán --</option>
          <?php foreach($guides as $g): ?>
            <option value="<?= (int)$g['id'] ?>" <?= (int)($schedule['guide_id'] ?? 0)==(int)$g['id']?'selected':'' ?>>
              #<?= (int)$g['id'] ?> - <?= h($g['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Ngày khởi hành <span class="text-danger">*</span></label>
        <input type="date" class="form-control" name="start_date"
               value="<?= h($schedule['start_date'] ?? date('Y-m-d')) ?>" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Ngày kết thúc</label>
        <input type="date" class="form-control" name="end_date"
               value="<?= h($schedule['end_date'] ?? '') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Điểm hẹn</label>
        <input type="text" class="form-control" name="meeting_point"
               value="<?= h($schedule['meeting_point'] ?? '') ?>" placeholder="VD: Cổng công viên 9/4">
      </div>

      <div class="col-md-3">
        <label class="form-label">Số chỗ tối đa</label>
        <input type="number" min="0" class="form-control" name="capacity"
               value="<?= (int)($schedule['capacity'] ?? 0) ?>">
      </div>

   

      <div class="col-md-3">
        <label class="form-label">Giá riêng (nếu khác tour)</label>
        <input type="number" min="0" step="0.01" class="form-control" name="price_override"
               value="<?= h($schedule['price_override'] ?? '') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Trạng thái</label>
        <?php $cur = $schedule['status'] ?? 'open'; ?>
        <select class="form-select" name="status">
          <?php foreach($statuses as $k=>$lb): ?>
            <option value="<?= h($k) ?>" <?= $cur===$k?'selected':''; ?>><?= h($lb) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-12">
        <label class="form-label">Ghi chú</label>
        <textarea class="form-control" name="note" rows="3"><?= h($schedule['note'] ?? '') ?></textarea>
      </div>

      <div class="col-12">
        <button class="btn btn-primary"><i class="bi bi-save"></i> <?= $isEdit?'Cập nhật':'Lưu' ?></button>
        <a href="index.php?c=Schedule&a=index" class="btn btn-outline-secondary">Hủy</a>
      </div>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
