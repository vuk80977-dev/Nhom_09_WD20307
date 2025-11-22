<?php
$title = 'Danh mục tour';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Danh mục tour</h1>
    <a href="index.php?c=TourType&a=create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Thêm danh mục
    </a>
</div>

<?php if (!empty($types)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th style="width:60px;">ID</th>
                    <th>Tên danh mục</th>
                    <th>Mô tả</th>
                    <th style="width:140px;" class="text-end">Hành động</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($types as $t): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td><?= htmlspecialchars($t['name']) ?></td>
                        <td><?= htmlspecialchars($t['description']) ?></td>
                        <td class="text-end">
                            <a href="index.php?c=TourType&a=edit&id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="index.php?c=TourType&a=delete&id=<?= $t['id'] ?>"
                               onclick="return confirm('Xóa danh mục này?')"
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
        Chưa có danh mục nào. Hãy thêm mới một danh mục tour.
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
