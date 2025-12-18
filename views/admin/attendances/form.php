<?php
ob_start();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$stLabels = [
  'present'    => 'Có mặt',
  'late'       => 'Đi muộn',
  'absent'     => 'Vắng',
  'left_early' => 'Về sớm',
];

$selectedBookingId    = (int)($_GET['booking_id'] ?? 0);
$selectedCheckpointId = (int)($_GET['checkpoint_id'] ?? 0);
/** @var string $mode  'schedule' | 'checkpoint' — được set từ Controller */
$mode = $mode ?? 'schedule';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h5 class="mb-0"><?= h($title) ?></h5>
    <div class="small text-muted">
      Tour: <strong><?= h($schedule['tour_name'] ?? '') ?></strong>
      | Khởi hành: <?= h($schedule['start_date']) ?>
      | HDV: <?= h($schedule['guide_name'] ?? 'Chưa gán') ?>
    </div>
  </div>
  <a href="index.php?c=Attendance&a=index" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
  </a>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= h($flash['type'] ?? 'info') ?>"><?= h($flash['msg'] ?? '') ?></div>
<?php endif; ?>

<!-- Bộ lọc: booking + checkpoint -->
<form class="row g-2 mb-3" method="get" action="index.php">
  <input type="hidden" name="c" value="Attendance">
  <input type="hidden" name="a" value="show">
  <input type="hidden" name="schedule_id" value="<?= (int)$schedule['id'] ?>">

  <div class="col-auto">
    <label class="form-label mb-0 small text-muted">Chọn booking</label>
    <select name="booking_id" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="0">— Tất cả booking —</option>
      <?php foreach (($bookingList ?? []) as $bid): ?>
        <option value="<?= (int)$bid ?>" <?= $selectedBookingId===$bid?'selected':'' ?>>#<?= (int)$bid ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <?php if ($selectedBookingId > 0): ?>
    <div class="col-auto">
      <label class="form-label mb-0 small text-muted">Chọn checkpoint</label>
      <select name="checkpoint_id" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="0">— Tổng hợp theo lịch —</option>
        <?php foreach (($checkpointList ?? []) as $cp): ?>
          <option value="<?= (int)$cp['id'] ?>" <?= $selectedCheckpointId===(int)$cp['id']?'selected':'' ?>>
            <?= h($cp['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  <?php endif; ?>
</form>

<div class="mb-2 small">
  <?php if ($mode==='checkpoint'): ?>
    Thống kê (booking #<?= (int)$selectedBookingId ?>, checkpoint #<?= (int)$selectedCheckpointId ?>):
  <?php else: ?>
    Thống kê (<?= $selectedBookingId ? ('booking #'.(int)$selectedBookingId) : 'toàn lịch' ?>):
  <?php endif; ?>
  Có mặt <?= (int)($stats['present'] ?? 0) ?> |
  Đi muộn <?= (int)($stats['late'] ?? 0) ?> |
  Vắng <?= (int)($stats['absent'] ?? 0) ?> |
  Về sớm <?= (int)($stats['left_early'] ?? 0) ?>
</div>

<?php
// Chọn action form theo nguồn dữ liệu
$formAction = ($mode === 'checkpoint')
  ? "index.php?c=Attendance&a=attendance_checkpoint_store"
  : "index.php?c=Attendance&a=store";
?>
<form method="post" action="<?= h($formAction) ?>">
  <input type="hidden" name="schedule_id" value="<?= (int)$schedule['id'] ?>">
  <?php if ($mode === 'checkpoint'): ?>
    <input type="hidden" name="booking_id" value="<?= (int)$selectedBookingId ?>">
    <input type="hidden" name="checkpoint_id" value="<?= (int)$selectedCheckpointId ?>">
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>#Booking</th>
              <th>Hành khách</th>
              <th>Liên hệ</th>
              <th style="width:240px;">Trạng thái</th>
              <th>Ghi chú</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($roster)): foreach ($roster as $r):
              $cur = $r['attendance_status'] ?? 'absent';
            ?>
              <tr>
                <td>
                  <?php if ($mode==='checkpoint'): ?>
                    #<?= (int)$selectedBookingId ?>
                    <input type="hidden" name="rows[<?= (int)$r['traveler_id'] ?>][traveler_id]"
                           value="<?= (int)$r['traveler_id'] ?>">
                  <?php else: ?>
                    <a class="text-decoration-none"
                       href="index.php?c=Attendance&a=show&schedule_id=<?= (int)$schedule['id'] ?>&booking_id=<?= (int)$r['booking_id'] ?>">
                      #<?= (int)$r['booking_id'] ?>
                    </a>
                    <input type="hidden" name="booking_id[]"  value="<?= (int)$r['booking_id'] ?>">
                    <input type="hidden" name="traveler_id[]" value="<?= (int)$r['traveler_id'] ?>">
                  <?php endif; ?>
                </td>

                <td><?= h($r['traveler_name']) ?></td>
                <td>
                  <?= h($r['traveler_phone'] ?? '') ?><br>
                  <span class="text-muted small"><?= h($r['traveler_email'] ?? '') ?></span>
                </td>

                <td>
                  <?php if ($mode==='checkpoint'): ?>
                    <select name="rows[<?= (int)$r['traveler_id'] ?>][status]" class="form-select form-select-sm">
                      <?php foreach ($stLabels as $k=>$lb): ?>
                        <option value="<?= h($k) ?>" <?= $cur===$k?'selected':'' ?>><?= h($lb) ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php else: ?>
                    <select name="status[]" class="form-select form-select-sm">
                      <?php foreach ($stLabels as $k=>$lb): ?>
                        <option value="<?= h($k) ?>" <?= $cur===$k?'selected':'' ?>><?= h($lb) ?></option>
                      <?php endforeach; ?>
                    </select>
                  <?php endif; ?>
                </td>

                <td>
                  <?php if ($mode==='checkpoint'): ?>
                    <input type="text"
                           name="rows[<?= (int)$r['traveler_id'] ?>][note]"
                           class="form-control form-control-sm"
                           value="<?= h($r['attendance_note'] ?? '') ?>"
                           placeholder="VD: đến trễ 10 phút">
                  <?php else: ?>
                    <input type="text" name="note[]" class="form-control form-control-sm"
                           value="<?= h($r['attendance_note'] ?? '') ?>"
                           placeholder="VD: đến trễ 10 phút">
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  <?php
                    if ($mode==='checkpoint') {
                      echo $selectedCheckpointId ? 'Chưa có hành khách cho checkpoint này.' : 'Hãy chọn checkpoint để điểm danh theo mốc.';
                    } else {
                      echo $selectedBookingId ? 'Booking này chưa có hành khách.' : 'Chưa có hành khách cho lịch này.';
                    }
                  ?>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3 d-flex justify-content-end gap-2">
    <button class="btn btn-primary btn-sm">
      <i class="bi bi-save"></i>
      <?= $mode==='checkpoint' ? 'Lưu điểm danh (checkpoint)' : 'Lưu điểm danh (theo lịch)' ?>
    </button>
  </div>
</form>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';