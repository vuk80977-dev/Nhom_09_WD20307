<?php
$title = 'Thêm tour mới';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="h5 mb-0">Thêm tour mới</h1>
            </div>
            <div class="card-body">
                <form method="post" action="index.php?c=Tour&a=store">
                    <div class="mb-3">
                        <label class="form-label">Tên tour</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Giá</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Thời lượng</label>
                        <input type="text" name="duration" class="form-control" placeholder="Ví dụ: 3N2Đ">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ngày khởi hành</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Danh mục tour</label>
                        <select name="tour_type_id" class="form-select">
                            <option value="">-- Chọn danh mục --</option>
                            <?php if (!empty($types)): ?>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= $type['id'] ?>">
                                        <?= htmlspecialchars($type['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nhà cung cấp</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">-- Chọn nhà cung cấp --</option>
                            <?php if (!empty($suppliers)): ?>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?= $s['id'] ?>">
                                        <?= htmlspecialchars($s['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="active">Đang mở bán</option>
                            <option value="inactive">Tạm dừng</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php?c=Tour&a=index" class="btn btn-secondary">Quay lại</a>
                        <button type="submit" class="btn btn-primary">Lưu tour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
