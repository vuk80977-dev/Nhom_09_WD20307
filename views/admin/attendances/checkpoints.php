<?php
ob_start();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$booking  = $booking  ?? [];
$customer = $customer ?? [];
$stops    = $stops    ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h5 class="mb-0">Điểm lịch trình</h5>
    <div class="small text-muted">
      Booking #<?= (int)($booking['id'] ?? 0) ?>
      <?php if (!empty($customer['name'])): ?>
        · KH: <strong><?= h($customer['name']) ?></strong>
      <?php endif; ?>
    </div>
  </div>
  <a href="index.php?c=Booking&a=index" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
  </a>
</div>

<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<!-- CHECKPOINT MẶC ĐỊNH -->
<div class="card border-0 shadow-sm mb-3">
  <div class="card-header bg-white"><strong>Các checkpoint mặc định</strong></div>
  <div class="card-body">
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-outline-primary btn-sm"
         href="index.php?c=Attendance&amp;a=attendance_checkpoint&amp;booking_id=<?= (int)($booking['id'] ?? 0) ?>&amp;checkpoint_id=1">
        <i class="bi bi-check2-square"></i> Điểm danh: Lúc đi
      </a>
      <a class="btn btn-outline-primary btn-sm"
         href="index.php?c=Attendance&amp;a=attendance_checkpoint&amp;booking_id=<?= (int)($booking['id'] ?? 0) ?>&amp;checkpoint_id=2">
        <i class="bi bi-check2-square"></i> Điểm danh: Điểm cuối tour
      </a>
      <a class="btn btn-outline-primary btn-sm"
         href="index.php?c=Attendance&amp;a=attendance_checkpoint&amp;booking_id=<?= (int)($booking['id'] ?? 0) ?>&amp;checkpoint_id=3">
        <i class="bi bi-check2-square"></i> Điểm danh: Lúc về
      </a>
    </div>
  </div>
</div>

<!-- ĐIỂM DỪNG TÙY CHỌN -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <strong>Điểm dừng (tùy chọn)</strong>
    <div class="small text-muted">Thêm nhiều điểm dừng rồi lưu lại.</div>
  </div>
  <div class="card-body">
    <form method="post" action="index.php?c=Attendance&a=checkpoints_save">
      <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">

      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0" id="stop-table">
          <thead class="table-light">
            <tr>
              <th style="width:56px;">#</th>
              <th>Tên điểm dừng</th>
              <th style="width:210px;">Thời gian dự kiến</th>
              <th style="width:40px;"></th>
            </tr>
          </thead>
          <tbody id="stop-rows">
            <?php if(!empty($stops)): foreach($stops as $i=>$s): ?>
              <tr>
                <td class="row-index"><?= $i+1 ?></td>
                <td>
                  <input type="text" class="form-control form-control-sm"
                         name="stops[<?= $i ?>][name]" value="<?= h($s['name'] ?? '') ?>"
                         placeholder="VD: Điểm dừng 1" required>
                </td>
                <td>
                  <div class="input-group input-group-sm">
                    <input type="date" class="form-control" name="stops[<?= $i ?>][date]" value="<?= h($s['date'] ?? '') ?>">
                    <input type="time" class="form-control" name="stops[<?= $i ?>][time]" value="<?= h($s['time'] ?? '') ?>">
                  </div>
                </td>
                <td>
                  <button type="button" class="btn btn-link text-danger p-0 btn-remove" title="Xóa dòng">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </td>
              </tr>
            <?php endforeach; else: ?>
              <tr>
                <td class="row-index">1</td>
                <td><input type="text" class="form-control form-control-sm" name="stops[0][name]" placeholder="VD: Điểm dừng 1" required></td>
                <td>
                  <div class="input-group input-group-sm">
                    <input type="date" class="form-control" name="stops[0][date]">
                    <input type="time" class="form-control" name="stops[0][time]">
                  </div>
                </td>
                <td>
                  <button type="button" class="btn btn-link text-danger p-0 btn-remove" title="Xóa dòng">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-3 d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" id="add-stop">
          <i class="bi bi-plus-lg"></i> Thêm điểm dừng
        </button>
        <button class="btn btn-primary btn-sm">
          <i class="bi bi-save"></i> Lưu danh sách điểm
        </button>
      </div>

      <template id="tpl-stop-row">
        <tr>
          <td class="row-index"></td>
          <td><input type="text" class="form-control form-control-sm" name="stops[IDX][name]" placeholder="VD: Điểm dừng" required></td>
          <td>
            <div class="input-group input-group-sm">
              <input type="date" class="form-control" name="stops[IDX][date]">
              <input type="time" class="form-control" name="stops[IDX][time]">
            </div>
          </td>
          <td>
            <button type="button" class="btn btn-link text-danger p-0 btn-remove" title="Xóa dòng">
              <i class="bi bi-x-lg"></i>
            </button>
          </td>
        </tr>
      </template>
    </form>
  </div>
</div>

<script>
(function(){
  const tbody = document.getElementById('stop-rows');
  const tpl   = document.getElementById('tpl-stop-row').innerHTML;
  let idx = <?= !empty($stops) ? (int)count($stops) : 1 ?>;

  function renum(){
    [...tbody.querySelectorAll('tr')].forEach((tr,i)=>tr.querySelector('.row-index').textContent = i+1);
  }

  document.getElementById('add-stop').onclick = () => {
    tbody.insertAdjacentHTML('beforeend', tpl.replaceAll('IDX', idx++));
    renum();
  };

  tbody.addEventListener('click', e => {
    if (e.target.closest('.btn-remove')) {
      e.preventDefault();
      e.target.closest('tr').remove();
      renum();
    }
  });

  renum();
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';