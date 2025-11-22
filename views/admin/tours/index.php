<?php
$title = 'Quản lý tour';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Quản lý tour</h1>
    <a href="index.php?c=Tour&a=create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Thêm tour
    </a>
</div>

<?php if (!empty($tours)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th style="width:60px;">ID</th>
                    <th>Tên tour</th>
                    <th>Giá</th>
                    <th>Thời lượng</th>
                    <th>Ngày khởi hành</th>
                    <th>Danh mục</th>
                    <th>Nhà cung cấp</th>
                    <th>Trạng thái</th>
                    <th style="width:140px;" class="text-end">Hành động</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tours as $tour): ?>
                    <tr>
                        <td><?= $tour['id'] ?></td>
                        <td><?= htmlspecialchars($tour['name']) ?></td>
                        <td><?= number_format($tour['price']) ?> đ</td>
                        <td><?= htmlspecialchars($tour['duration']) ?></td>
                        <td><?= $tour['start_date'] ?></td>
                        <td><?= $tour['tour_type_id'] ?></td>
                        <td><?= $tour['supplier_id'] ?></td>
                        <td>
                            <?php if ($tour['status'] == 'active'): ?>
                                <span class="badge text-bg-success">Đang mở</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Tạm dừng</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="index.php?c=Tour&a=edit&id=<?= $tour['id'] ?>" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="index.php?c=Tour&a=delete&id=<?= $tour['id'] ?>"
                               onclick="return confirm('Xóa tour này?')"
                               class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info mb-0">
        Chưa có tour nào. Hãy thêm tour mới.
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
