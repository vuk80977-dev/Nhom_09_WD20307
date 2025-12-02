<?php
ob_start();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES,'UTF-8'); }

$types=[
  ''=>'-- Loại log --',
  'note'=>'Ghi chú',
  'incident'=>'Sự cố',
  'cost'=>'Chi phí phát sinh',
  'feedback'=>'Phản hồi khách'
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0"><?= h($title) ?></h5>
  <a href="index.php?c=TourLog&a=create" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Thêm nhật ký
  </a>
</div>

<?php if(!empty($flash)): ?>
  <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get">
  <input type="hidden" name="c" value="TourLog">
  <input type="hidden" name="a" value="index">

  <div class="col-md-4">
    <input class="form-control" name="q" placeholder="Tìm theo tiêu đề / nội dung / tour..."
           value="<?= h($_GET['q']??'') ?>">
  </div>

  <div class="col-md-3">
    <select class="form-select" name="schedule_id">
      <option value="">-- Tất cả lịch --</option>
      <?php $curSc=$_GET['schedule_id']??''; foreach($schedules as $sc): ?>
        <option value="<?= (int)$sc['id'] ?>" <?= $curSc==$sc['id']?'selected':'' ?>>
          #<?= (int)$sc['id'] ?> | <?= h($sc['start_date']) ?> → <?= h($sc['end_date']??'-') ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-2">
    <select class="form-select" name="type">
      <?php $curT=$_GET['type']??''; foreach($types as $k=>$lb): ?>
        <option value="<?= h($k) ?>" <?= $curT===$k?'selected':'' ?>><?= h($lb) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-3">
    <button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Lọc</button>
    <a class="btn btn-outline-dark" href="index.php?c=TourLog&a=index"><i class="bi bi-x-circle"></i> Xóa lọc</a>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0 table-responsive">
    <table class="table table-hover table-sm mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Tour / Lịch</th>
          <th>Tiêu đề</th>
          <th>Loại</th>
          <th>Ngày ghi</th>
          <th class="text-end">Thao tác</th>
        </tr>
      </thead>
      <tbody>
      <?php if(!empty($data['items'])): foreach($data['items'] as $r): ?>
        <tr>
          <td>#<?= (int)$r['id'] ?></td>
          <td>
            <?= h($r['tour_name']) ?><br>
            <small class="text-muted">
              Lịch #<?= (int)$r['schedule_id'] ?> | <?= h($r['start_date']) ?> → <?= h($r['end_date']??'-') ?>
            </small>
          </td>
          <td>
            <div class="fw-semibold"><?= h($r['title']) ?></div>
            <div class="small text-muted text-truncate" style="max-width:360px;">
              <?= h($r['content']) ?>
            </div>
          </td>
          <td><?= h($types[$r['type']] ?? $r['type']) ?></td>
          <td><?= h($r['log_date']) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary"
               href="index.php?c=TourLog&a=edit&id=<?= (int)$r['id'] ?>">
              <i class="bi bi-pencil-square"></i>
            </a>
            <form action="index.php?c=TourLog&a=destroy" method="post" class="d-inline"
                  onsubmit="return confirm('Xóa log #<?= (int)$r['id'] ?>?');">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="6" class="text-center text-muted py-4">Chưa có nhật ký.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if(($data['pages']??1)>1): ?>
<nav class="mt-3">
  <ul class="pagination pagination-sm mb-0">
    <?php
      $buildUrl=function($p){ $qs=$_GET; $qs['page']=$p; return 'index.php?'.http_build_query($qs); };
      $page=(int)$data['page']; $pages=(int)$data['pages'];
    ?>
    <li class="page-item <?= $page<=1?'disabled':'' ?>"><a class="page-link" href="<?= h($buildUrl($page-1)) ?>">«</a></li>
    <?php for($i=1;$i<=$pages;$i++): ?>
      <li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="<?= h($buildUrl($i)) ?>"><?= $i ?></a></li>
    <?php endfor; ?>
    <li class="page-item <?= $page>=$pages?'disabled':'' ?>"><a class="page-link" href="<?= h($buildUrl($page+1)) ?>">»</a></li>
  </ul>
</nav>
<?php endif; ?>

<?php
$content=ob_get_clean();
include __DIR__.'/../../layouts/main.php';
