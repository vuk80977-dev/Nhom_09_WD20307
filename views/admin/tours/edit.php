<?php
$title = 'Sửa tour';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="h5 mb-0">Sửa tour</h1>
            </div>
            <div class="card-body">
                <form method="post" action="index.php?c=Tour&a=update">
                    <input type="hidden" name="id" value="<?= $tour['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Tên tour</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= htmlspecialchars($tour['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Giá</label>
                        <input type="number" name="price" class="form-control"
                               value="<?= $tour['price'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Thời lượng</label>
                        <input type="text" name="duration" class="form-control"
                               value="<?= htmlspecialchars($tour['duration']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ngày khởi hành</label>
                        <input type="date" name="start_date" class="form-control"
                               value="<?= $tour['start_date'] ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Danh mục tour</label>
                        <select name="tour_type_id" class="form-select">
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= $type['id'] ?>"
                                    <?= $tour['tour_type_id'] == $type['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nhà cung cấp</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">-- Chọn nhà cung cấp --</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"
                                    <?= $tour['supplier_id'] == $s['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= $tour['status']=='active'?'selected':'' ?>>Đang mở bán</option>
                            <option value="inactive" <?= $tour['status']=='inactive'?'selected':'' ?>>Tạm dừng</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($tour['description']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php?c=Tour&a=index" class="btn btn-secondary">Quay lại</a>
                        <button type="submit" class="btn btn-primary">Cập nhật tour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
