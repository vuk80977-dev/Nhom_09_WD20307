<?php
ob_start();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES,'UTF-8'); }

// helper tiền VND: đảm bảo không bị lẻ 1đ
function money($v){
  return number_format((float)round($v, 0), 0, ',', '.');
}

$methods = [
  'cash'=>'Tiền mặt',
  'transfer'=>'Chuyển khoản',
  'card'=>'Thẻ',
  'other'=>'Khác'
];

$statuses = [
  'paid'=>'Đã thanh toán',
  'pending'=>'Chờ',
  'refunded'=>'Hoàn tiền'
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0"><?= h($title) ?></h5>
  <a href="index.php?c=Booking&a=index" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại booking
  </a>
</div>

<?php if(!empty($flash)): ?>
  <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <div class="fw-semibold mb-2">
      Booking #<?= (int)$booking['id'] ?> — <?= h($booking['customer_name'] ?? '') ?>
    </div>

    <form class="row g-2" method="post" action="index.php?c=Payment&a=store">
      <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">

      <div class="col-md-3">
        <input class="form-control" type="number" name="amount" min="1" step="1000"
               placeholder="Số tiền" required>
      </div>

      <div class="col-md-2">
        <select class="form-select" name="method">
          <?php foreach($methods as $k=>$lb): ?>
            <option value="<?= h($k) ?>"><?= h($lb) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <input class="form-control" type="datetime-local" name="paid_at"
               value="<?= date('Y-m-d\TH:i') ?>">
      </div>

      <div class="col-md-2">
        <select class="form-select" name="status">
          <?php foreach($statuses as $k=>$lb): ?>
            <option value="<?= h($k) ?>"><?= h($lb) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-2">
        <button class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Thêm</button>
      </div>

      <div class="col-12">
        <input class="form-control" name="note" placeholder="Ghi chú (nếu có)">
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0 table-responsive">
    <table class="table table-sm table-hover mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Số tiền</th>
          <th>Hình thức</th>
          <th>Thời gian</th>
          <th>Trạng thái</th>
          <th>Ghi chú</th>
          <th class="text-end" style="width:90px;">Xóa</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($payments)): foreach($payments as $p): ?>
          <tr>
            <td>#<?= (int)$p['id'] ?></td>
            <td><?= money($p['amount']) ?> đ</td>
            <td><?= h($methods[$p['method']] ?? $p['method']) ?></td>
            <td><?= h($p['paid_at']) ?></td>
            <td>
              <?php
                $badge = $p['status']==='paid' ? 'success' : ($p['status']==='pending' ? 'warning' : 'secondary');
              ?>
              <span class="badge text-bg-<?= $badge ?>">
                <?= h($statuses[$p['status']] ?? $p['status']) ?>
              </span>
            </td>
            <td><?= h($p['note'] ?? '') ?></td>
            <td class="text-end">
              <form method="post" action="index.php?c=Payment&a=destroy"
                    onsubmit="return confirm('Xóa thanh toán #<?= (int)$p['id'] ?>?');">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash3"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Chưa có thanh toán.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
