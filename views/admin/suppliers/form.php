<?php
$isEdit = !empty($supplier['id']);
ob_start();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES,'UTF-8'); }

$types = [
  'hotel'=>'Khách sạn/Resort',
  'transport'=>'Xe/Di chuyển',
  'restaurant'=>'Nhà hàng',
  'ticket'=>'Vé tham quan',
  'other'=>'Khác'
];

$statuses = [
  'active'=>'Đang hợp tác',
  'inactive'=>'Ngừng hợp tác'
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0"><?= h($title) ?></h5>
  <a href="index.php?c=Supplier&a=index" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Quay lại
  </a>
</div>

<?php if(!empty($flash)): ?>
  <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="post" action="index.php?c=Supplier&a=<?= $isEdit?'update':'store' ?>" class="row g-3">

      <?php if($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$supplier['id'] ?>">
      <?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Tên nhà cung cấp *</label>
        <input class="form-control" name="name" required
               value="<?= h($supplier['name'] ?? '') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Loại nhà cung cấp</label>
        <?php $curType=$supplier['type'] ?? 'other'; ?>
        <select class="form-select" name="type">
          <?php foreach($types as $k=>$lb): ?>
            <option value="<?= h($k) ?>" <?= $curType===$k?'selected':''; ?>><?= h($lb) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Trạng thái</label>
        <?php $curSt=$supplier['status'] ?? 'active'; ?>
        <select class="form-select" name="status">
          <?php foreach($statuses as $k=>$lb): ?>
            <option value="<?= h($k) ?>" <?= $curSt===$k?'selected':''; ?>><?= h($lb) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Người liên hệ</label>
        <input class="form-control" name="contact_person"
               value="<?= h($supplier['contact_person'] ?? '') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Điện thoại</label>
        <input class="form-control" name="phone"
               value="<?= h($supplier['phone'] ?? '') ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Email</label>
        <input class="form-control" name="email"
               value="<?= h($supplier['email'] ?? '') ?>">
      </div>

      <div class="col-12">
        <label class="form-label">Địa chỉ</label>
        <input class="form-control" name="address"
               value="<?= h($supplier['address'] ?? '') ?>">
      </div>

      <div class="col-12">
        <label class="form-label">Ghi chú</label>
        <textarea class="form-control" rows="3" name="note"><?= h($supplier['note'] ?? '') ?></textarea>
      </div>

      <div class="col-12">
        <button class="btn btn-primary"><i class="bi bi-save"></i> <?= $isEdit?'Cập nhật':'Lưu' ?></button>
        <a href="index.php?c=Supplier&a=index" class="btn btn-outline-secondary">Hủy</a>
      </div>

    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
