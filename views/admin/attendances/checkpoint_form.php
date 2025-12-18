<?php
ob_start();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$booking    = $booking    ?? [];
$checkpoint = $checkpoint ?? ['id'=>0,'name'=>'Checkpoint'];
$travelers  = $travelers  ?? [];
$flash      = $flash      ?? null;

$stLabels = ['present'=>'Có mặt','late'=>'Đi muộn','absent'=>'Vắng'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h5 class="mb-0">Điểm danh: <?= h($checkpoint['name'] ?? 'Checkpoint') ?></h5>
    <div class="small text-muted">
      Booking #<?= (int)($booking['id'] ?? 0) ?>
      <?php if(!empty($booking['tour_name'])): ?> · Tour: <strong><?= h($booking['tour_name']) ?></strong><?php endif; ?>
    </div>
  </div>
  <a href="index.php?c=Attendance&amp;a=checkpoints&amp;booking_id=<?= (int)($booking['id'] ?? 0) ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
  </a>
</div>

<?php if(!empty($flash)): ?>
  <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<form method="post" action="index.php?c=Attendance&amp;a=attendance_checkpoint_store">
  <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">
  <input type="hidden" name="checkpoint_id" value="<?= (int)($checkpoint['id'] ?? 0) ?>">

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th style="width:56px;">#</th>
              <th>Họ tên</th>
              <th>Liên hệ</th>
              <th style="width:220px;">Trạng thái</th>
              <th>Ghi chú</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($travelers)): foreach ($travelers as $i=>$r):
              $cur = $r['status'] ?? 'absent';
            ?>
              <tr>
                <td><?= $i+1 ?>
                  <input type="hidden" name="rows[<?= $i ?>][traveler_id]" value="<?= (int)($r['traveler_id'] ?? 0) ?>">
                </td>
                <td><?= h($r['full_name'] ?? '') ?></td>
                <td>
                  <?= h($r['phone'] ?? '') ?><br>
                  <span class="text-muted small"><?= h($r['email'] ?? '') ?></span>
                </td>
                <td>
                  <select name="rows[<?= $i ?>][status]" class="form-select form-select-sm">
                    <?php foreach ($stLabels as $k=>$lb): ?>
                      <option value="<?= h($k) ?>" <?= $cur===$k?'selected':'' ?>><?= h($lb) ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td>
                  <input type="text" name="rows[<?= $i ?>][note]" class="form-control form-control-sm"
                         value="<?= h($r['note'] ?? '') ?>" placeholder="VD: đến trễ 10 phút">
                </td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="5" class="text-center text-muted py-4">Chưa có khách trong booking.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="mt-3 d-flex justify-content-end gap-2">
    <button class="btn btn-primary btn-sm">
      <i class="bi bi-save"></i> Lưu điểm danh
    </button>
  </div>
</form>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';