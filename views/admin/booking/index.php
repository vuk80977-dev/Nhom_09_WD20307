<?php
ob_start();
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$statuses = [
  ''=>'-- Trạng thái --',
  'pending'=>'Chờ xác nhận',
  'confirmed'=>'Đã xác nhận',
  'cancelled'=>'Đã hủy',
  'completed'=>'Hoàn thành'
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0"><?= h($title) ?></h5>
  <a href="index.php?c=Booking&a=create" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> Thêm booking
  </a>
</div>

<?php if(!empty($flash)): ?>
  <div class="alert alert-<?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
<?php endif; ?>

<form class="row g-2 mb-3" method="get">
  <input type="hidden" name="c" value="Booking">
  <input type="hidden" name="a" value="index">

  <div class="col-md-4">
    <input class="form-control" name="q" placeholder="Tìm khách / tour / email / phone..."
           value="<?= h($_GET['q'] ?? '') ?>">
  </div>

  <div class="col-md-2">
    <select class="form-select" name="tour_id">
      <option value="">-- Tất cả tour --</option>
      <?php $curTour=$_GET['tour_id']??''; foreach($tours as $t): ?>
        <option value="<?= (int)$t['id'] ?>" <?= $curTour==$t['id']?'selected':'' ?>>
          <?= h($t['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-2">
    <select class="form-select" name="status">
      <?php $curSt=$_GET['status']??''; foreach($statuses as $k=>$lb): ?>
        <option value="<?= h($k) ?>" <?= $curSt===$k?'selected':'' ?>><?= h($lb) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-4">
    <button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Lọc</button>
    <a class="btn btn-outline-dark" href="index.php?c=Booking&a=index"><i class="bi bi-x-circle"></i> Xóa lọc</a>
  </div>
</form>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0 table-responsive">
    <table class="table table-hover table-sm mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Khách hàng</th>
          <th>Tour</th>
          <th>Lịch KH</th>
          <th>HDV</th>
          <th>SL</th>
          <th>Tổng tiền</th>
          <th>Đã trả</th>
          <th>Ngày đặt</th>
          <th>Trạng thái</th>
          <th class="text-end">Thao tác</th>
        </tr>
      </thead>
      <tbody>
      <?php if(!empty($data['items'])): foreach($data['items'] as $r): ?>
        <tr>
          <td>#<?= (int)$r['id'] ?></td>

          <td>
            <?= h($r['customer_name'] ?? '') ?><br>
            <small class="text-muted">
              <?= h($r['customer_email'] ?? '') ?> | <?= h($r['customer_phone'] ?? '') ?>
            </small>
            <?php if(!empty($r['customer_address'])): ?>
              <div class="small text-muted">
                <i class="bi bi-geo-alt"></i> <?= h($r['customer_address']) ?>
              </div>
            <?php endif; ?>
          </td>

          <td><?= h($r['tour_name'] ?? '') ?></td>

          <td>
            <?= h($r['start_date'] ?? '') ?>
            → <?= h($r['end_date'] ?? '-') ?>
          </td>

          <td><?= h($r['guide_name'] ?? 'Chưa gán') ?></td>

          <td><?= (int)($r['quantity'] ?? 1) ?></td>

          <!-- Tổng tiền -->
          <td>
            <?php if(isset($r['total_price'])): ?>
              <?= number_format((float)$r['total_price']) ?> đ
            <?php else: ?>
              -
            <?php endif; ?>
          </td>

          <!-- Đã trả + còn thiếu -->
          <td>
            <?php if(isset($r['paid_total'])): ?>
              <span class="fw-semibold text-success">
                <?= number_format((float)$r['paid_total']) ?> đ
              </span>

              <?php if(isset($r['total_price']) && (float)$r['paid_total'] < (float)$r['total_price']): ?>
                <div class="small text-danger">
                  Còn thiếu <?= number_format((float)$r['total_price'] - (float)$r['paid_total']) ?> đ
                </div>
              <?php endif; ?>

            <?php else: ?>
              -
            <?php endif; ?>
          </td>

          <td><?= h($r['booking_date'] ?? '') ?></td>

          <td>
            <?php
              $st = $r['status'] ?? 'pending';
              $badge = match($st){
                'confirmed'=>'success',
                'pending'=>'warning',
                'cancelled'=>'secondary',
                'completed'=>'primary',
                default=>'secondary'
              };
            ?>
            <span class="badge text-bg-<?= $badge ?>">
              <?= h($statuses[$st] ?? $st) ?>
            </span>
          </td>

          <td class="text-end">
            <!-- Lịch sử thanh toán -->
            <a class="btn btn-sm btn-outline-success"
               title="Lịch sử thanh toán"
               href="index.php?c=Payment&a=index&booking_id=<?= (int)$r['id'] ?>">
              <i class="bi bi-cash-coin"></i>
            </a>

            <!-- Sửa -->
            <a class="btn btn-sm btn-outline-primary"
               href="index.php?c=Booking&a=edit&id=<?= (int)$r['id'] ?>">
              <i class="bi bi-pencil-square"></i>
            </a>

            <!-- Xóa -->
            <form action="index.php?c=Booking&a=destroy" method="post" class="d-inline"
                  onsubmit="return confirm('Xóa booking #<?= (int)$r['id'] ?>?');">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash3"></i>
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="11" class="text-center text-muted py-4">Không có dữ liệu.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if(($data['pages']??1)>1): ?>
<nav class="mt-3">
  <ul class="pagination pagination-sm mb-0">
    <?php
      $buildUrl=function($p){
        $qs=$_GET; $qs['page']=$p;
        return 'index.php?'.http_build_query($qs);
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
$content=ob_get_clean();
include __DIR__.'/../../layouts/main.php';
