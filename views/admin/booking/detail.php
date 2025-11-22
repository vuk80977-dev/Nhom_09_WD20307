<?php
$title = 'Chi tiết booking';
ob_start();
?>
<h1 class="h5 mb-3">Chi tiết booking #<?= $booking['id'] ?></h1>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Tour:</strong>
                    <?= $tour ? htmlspecialchars($tour['name']) : ('Tour ID: ' . $booking['tour_id']) ?>
                </p>
                <p><strong>Khách hàng ID:</strong> <?= $booking['customer_id'] ?></p>
                <p><strong>Số khách:</strong> <?= $booking['num_guests'] ?></p>
                <p><strong>Tổng tiền:</strong> <?= number_format($booking['total_price']) ?> đ</p>
            </div>
            <div class="col-md-6">
                <p><strong>Ngày đặt:</strong> <?= $booking['booking_date'] ?></p>
                <p><strong>Ngày khởi hành:</strong> <?= $booking['start_date'] ?></p>
                <p><strong>Trạng thái:</strong>
                    <?php if ($booking['status'] == 'pending'): ?>
                        <span class="badge text-bg-warning">Chờ xác nhận</span>
                    <?php elseif ($booking['status'] == 'confirmed'): ?>
                        <span class="badge text-bg-success">Đã xác nhận</span>
                    <?php elseif ($booking['status'] == 'cancelled'): ?>
                        <span class="badge text-bg-danger">Đã hủy</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">Hoàn thành</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<h5 class="mb-3">Điểm danh lịch trình</h5>

<?php if (!empty($attendances)): ?>
    <?php foreach ($attendances as $a): ?>
        <form method="post" action="index.php?c=Booking&a=checkin" class="card border-0 shadow-sm mb-2">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <p class="mb-1"><strong>Ngày:</strong> <?= $a['schedule_date'] ?></p>
                    <p class="mb-1">
                        <strong>Trạng thái:</strong>
                        <?= $a['is_present'] ? 'Đã điểm danh' : 'Chưa điểm danh' ?>
                    </p>
                    <p class="mb-1"><strong>Ghi chú:</strong> <?= htmlspecialchars($a['note']) ?></p>
                </div>
                <div class="text-end" style="max-width:260px;">
                    <input type="hidden" name="attendance_id" value="<?= $a['id'] ?>">
                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox"
                               name="is_present" id="present<?= $a['id'] ?>"
                            <?= $a['is_present'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="present<?= $a['id'] ?>">Có mặt</label>
                    </div>

                    <textarea name="note" class="form-control mb-2" rows="1" placeholder="Ghi chú thêm (nếu có)..."></textarea>

                    <button class="btn btn-sm btn-primary">Cập nhật điểm danh</button>
                </div>
            </div>
        </form>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-info">
        Chưa có lịch điểm danh nào (sẽ tạo sau khi booking được xác nhận).
    </div>
<?php endif; ?>

<a href="index.php?c=Booking&a=index" class="btn btn-secondary mt-3">Quay lại danh sách</a>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
