<?php
ob_start();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES,'UTF-8'); }

$types = [
  ''=>'-- Loại NCC --',
  'hotel'=>'Khách sạn/Resort',
  'transport'=>'Xe/Di chuyển',
  'restaurant'=>'Nhà hàng',
  'ticket'=>'Vé tham quan',
  'other'=>'Khác'
];

$statuses = [
  ''=>'-- Trạng thái --',
  'active'=>'Đang hợp tác',
  'inactive'=>'Ngừng hợp tác'
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0"><?= h($title) ?></h5>
  <a href="index.php?c=Supplier&a=create" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Thêm nhà cung cấp
  </a>
</div>

<?php if(!empty($flash)): ?>
  <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get">
  <input type="hidden" name="c" value="Supplier">
  <input type="hidden" name="a" value="index">

  <div class="col-md-4">
    <input class="form-control" name="q" 
           placeholder="Tìm theo tên, liên hệ, sđt, email..."
           value="<?= h($_GET['q'] ?? '') ?>">
  </div>

  <div class="col-md-3">
    <select class="form-select" name="type">
      <?php $curT=$_GET['type']??''; foreach($types as $k=>$lb): ?>
        <option value="<?= h($k) ?>" <?= $curT===$k?'selected':''; ?>><?= h($lb) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-3">
    <select class="form-select" name="status">
      <?php $curS=$_GET['status']??''; foreach($statuses as $k=>$lb): ?>
        <option value="<?= h($k) ?>" <?= $curS===$k?'selected':''; ?>><?= h($lb) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-2">
    <button class="btn btn-outline-secondary w-100"><i class="bi bi-funnel"></i> Lọc</button>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0 table-responsive">
    <table class="table table-hover table-sm mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th style="width:70px;">ID</th>
          <th>Tên nhà cung cấp</th>
          <th>Loại</th>
          <th>Liên hệ</th>
          <th>Điện thoại</th>
          <th>Email</th>
          <th>Trạng thái</th>
          <th class="text-end" style="width:130px;">Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($data['items'])): foreach($data['items'] as $r): ?>
          <tr>
            <td>#<?= (int)$r['id'] ?></td>
            <td>
              <div class="fw-semibold"><?= h($r['name']) ?></div>
              <?php if(!empty($r['address'])): ?>
                <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= h($r['address']) ?></div>
              <?php endif; ?>
            </td>
            <td><?= h($types[$r['type']] ?? $r['type']) ?></td>
            <td><?= h($r['contact_person'] ?? '') ?></td>
            <td><?= h($r['phone'] ?? '') ?></td>
            <td><?= h($r['email'] ?? '') ?></td>
            <td>
              <?php
                $st = $r['status'] ?? 'active';
                $badge = $st==='active' ? 'success' : 'secondary';
              ?>
              <span class="badge text-bg-<?= $badge ?>">
                <?= h($statuses[$st] ?? $st) ?>
              </span>
            </td>

            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary"
                 href="index.php?c=Supplier&a=edit&id=<?= (int)$r['id'] ?>">
                <i class="bi bi-pencil-square"></i>
              </a>
              <form action="index.php?c=Supplier&a=destroy" method="post" class="d-inline"
                    onsubmit="return confirm('Xóa nhà cung cấp #<?= (int)$r['id'] ?>?');">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash3"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="8" class="text-center text-muted py-4">Chưa có nhà cung cấp.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if(($data['pages'] ?? 1) > 1): ?>
<nav class="mt-3">
  <ul class="pagination pagination-sm mb-0">
    <?php
      $buildUrl=function($p){
        $qs=$_GET; $qs['page']=$p; return 'index.php?'.http_build_query($qs);
      };
      $page=(int)$data['page']; $pages=(int)$data['pages'];
    ?>
    <li class="page-item <?= $page<=1?'disabled':'' ?>">
      <a class="page-link" href="<?= h($buildUrl($page-1)) ?>">«</a>
    </li>
    <?php for($i=1;$i<=$pages;$i++): ?>
      <li class="page-item <?= $i==$page?'active':'' ?>">
        <a class="page-link" href="<?= h($buildUrl($i)) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item <?= $page>=$pages?'disabled':'' ?>">
      <a class="page-link" href="<?= h($buildUrl($page+1)) ?>">»</a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
