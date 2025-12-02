<?php
$isEdit=!empty($log['id']);
ob_start();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES,'UTF-8'); }

$types=[
  'note'=>'Ghi chú',
  'incident'=>'Sự cố',
  'cost'=>'Chi phí phát sinh',
  'feedback'=>'Phản hồi khách'
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0"><?= h($title) ?></h5>
  <a href="index.php?c=TourLog&a=index" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
  </a>
</div>

<?php if(!empty($flash)): ?>
  <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="post" action="index.php?c=TourLog&a=<?= $isEdit?'update':'store' ?>" class="row g-3">
      <?php if($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$log['id'] ?>">
      <?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Lịch khởi hành</label>
        <select class="form-select" name="schedule_id" required>
          <option value="">-- Chọn lịch --</option>
          <?php foreach($schedules as $sc): ?>
            <option value="<?= (int)$sc['id'] ?>" <?= ($log['schedule_id']==$sc['id'])?'selected':'' ?>>
              #<?= (int)$sc['id'] ?> | <?= h($sc['start_date']) ?> → <?= h($sc['end_date']??'-') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Ngày ghi</label>
        <input type="date" class="form-control" name="log_date"
               value="<?= h($log['log_date']??date('Y-m-d')) ?>" required>
      </div>

      <div class="col-md-3">
        <label class="form-label">Loại log</label>
        <select class="form-select" name="type">
          <?php $cur=$log['type']??'note'; foreach($types as $k=>$lb): ?>
            <option value="<?= h($k) ?>" <?= $cur===$k?'selected':'' ?>><?= h($lb) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label">Tiêu đề</label>
        <input class="form-control" name="title" value="<?= h($log['title']??'') ?>" required>
      </div>

      <div class="col-12">
        <label class="form-label">Nội dung</label>
        <textarea class="form-control" name="content" rows="5" required><?= h($log['content']??'') ?></textarea>
      </div>

      <div class="col-12">
        <button class="btn btn-primary"><i class="bi bi-save"></i> <?= $isEdit?'Cập nhật':'Lưu' ?></button>
        <a href="index.php?c=TourLog&a=index" class="btn btn-outline-secondary">Hủy</a>
      </div>
    </form>
  </div>
</div>

<?php
$content=ob_get_clean();
include __DIR__.'/../../layouts/main.php';
