<?php
$isEdit = !empty($booking['id']);
ob_start();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
$statuses = [
  'pending'=>'Chờ xác nhận',
  'confirmed'=>'Đã xác nhận',
  'cancelled'=>'Đã hủy',
  'completed'=>'Hoàn thành'
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0"><?= h($title) ?></h5>
  <a href="index.php?c=Booking&a=index" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
  </a>
</div>

<?php if(!empty($flash)): ?>
  <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="post" action="index.php?c=Booking&a=<?= $isEdit?'update':'store' ?>" class="row g-3">
      <?php if($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$booking['id'] ?>">
      <?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Tour</label>
        <select class="form-select" name="tour_id" required>
          <option value="">-- Chọn tour --</option>
          <?php foreach($tours as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= (($booking['tour_id'] ?? '')==$t['id'])?'selected':'' ?>>
              <?= h($t['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Lịch khởi hành (mở bán)</label>
        <select class="form-select" name="schedule_id" required>
          <option value="">-- Chọn lịch --</option>
          <?php foreach($schedulesByTour as $tourId=>$list): ?>
            <optgroup label="Tour #<?= (int)$tourId ?>">
              <?php foreach($list as $sc): ?>
                <option value="<?= (int)$sc['id'] ?>"
                  <?= (($booking['schedule_id'] ?? '')==$sc['id'])?'selected':'' ?>>
                  #<?= (int)$sc['id'] ?> | <?= h($sc['start_date']) ?> → <?= h($sc['end_date']??'-') ?>
                  (<?= (int)$sc['booked_count'] ?>/<?= (int)$sc['capacity'] ?>)
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Khách hàng</label>
        <select class="form-select" name="customer_id" required>
          <?php
            // tìm khách đang chọn để show info
            $selectedCustomer = null;
            foreach($customers as $cc){
              if((int)$cc['id'] === (int)($booking['customer_id'] ?? 0)){
                $selectedCustomer = $cc; break;
              }
            }
          ?>

          <option value="">-- Chọn khách hàng --</option>
          <?php foreach($customers as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (($booking['customer_id'] ?? '')==$c['id'])?'selected':'' ?>>
              <?= h($c['name']) ?> (<?= h($c['email']??'') ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php if($selectedCustomer): ?>
        <div class="col-md-6">
          <label class="form-label d-block">&nbsp;</label>
          <div class="alert alert-light border small mb-0">
            <div><b>Thông tin khách hàng:</b></div>
            <div>• Họ tên: <?= h($selectedCustomer['name']) ?></div>
            <?php if(!empty($selectedCustomer['phone'])): ?>
              <div>• SĐT: <?= h($selectedCustomer['phone']) ?></div>
            <?php endif; ?>
            <?php if(!empty($selectedCustomer['email'])): ?>
              <div>• Email: <?= h($selectedCustomer['email']) ?></div>
            <?php endif; ?>
            <?php if(!empty($selectedCustomer['address'])): ?>
              <div>• Địa chỉ: <?= h($selectedCustomer['address']) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="col-md-3">
        <label class="form-label">Số lượng khách</label>
        <input type="number" min="1" class="form-control" name="quantity"
               value="<?= (int)($booking['quantity']??1) ?>" required>
      </div>
      <div class="col-md-3">
  <label class="form-label">Tiền cọc trước</label>
  <input type="number"
         class="form-control"
         name="deposit"
         min="0"
         step="1000"
         value="<?= h($booking['deposit'] ?? 0) ?>"
         placeholder="VD: 500000">
</div>


      <div class="col-md-3">
        <label class="form-label">Ngày đặt</label>
        <input type="date" class="form-control" name="booking_date"
               value="<?= h($booking['booking_date']??date('Y-m-d')) ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Trạng thái</label>
        <select class="form-select" name="status">
          <?php $cur=$booking['status']??'pending'; foreach($statuses as $k=>$lb): ?>
            <option value="<?= h($k) ?>" <?= $cur===$k?'selected':'' ?>><?= h($lb) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Ghi chú admin</label>
        <textarea class="form-control" name="note" rows="2"><?= h($booking['note']??'') ?></textarea>
      </div>

      <!-- ✅ ĐÃ XÓA KHỐI ĐÁNH GIÁ (rating) -->

      <div class="col-md-12">
        <label class="form-label">Phản hồi khách</label>
        <textarea class="form-control" name="feedback" rows="2"><?= h($booking['feedback']??'') ?></textarea>
      </div>

      <div class="col-12">
        <label class="form-label">Sự cố / ghi nhận vận hành</label>
        <textarea class="form-control" name="issue" rows="2"><?= h($booking['issue']??'') ?></textarea>
      </div>

      <div class="col-12">
        <button class="btn btn-primary"><i class="bi bi-save"></i> <?= $isEdit?'Cập nhật':'Lưu' ?></button>
        <a href="index.php?c=Booking&a=index" class="btn btn-outline-secondary">Hủy</a>
      </div>

      <script>
        const tourSelect = document.querySelector('select[name="tour_id"]');
        const scheduleSelect = document.querySelector('select[name="schedule_id"]');

        // lưu toàn bộ option lịch theo tour_id
        const allOptions = [...scheduleSelect.querySelectorAll('option, optgroup')];

        function filterSchedules() {
          const tourId = tourSelect.value;

          // reset
          scheduleSelect.innerHTML = '<option value="">-- Chọn lịch --</option>';

          allOptions.forEach(el => {
            if (el.tagName === 'OPTGROUP') {
              const label = el.getAttribute('label') || '';
              const match = label.includes('#' + tourId);
              if (tourId === '' || match) {
                scheduleSelect.appendChild(el.cloneNode(true));
              }
            }
          });
        }

        tourSelect.addEventListener('change', filterSchedules);

        // chạy lần đầu khi load form
        filterSchedules();
      </script>

    </form>
  </div>
</div>

<?php
$content=ob_get_clean();
include __DIR__.'/../../layouts/main.php';
