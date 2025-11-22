<?php
$title = 'Nhà cung cấp';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h5 mb-0">Nhà cung cấp</h1>
    <a href="index.php?c=Supplier&a=create" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Thêm nhà cung cấp
    </a>
</div>

<?php if (!empty($suppliers)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th style="width:60px;">ID</th>
                    <th>Tên nhà cung cấp</th>
                    <th>Liên hệ</th>
                    <th>Điện thoại</th>
                    <th>Email</th>
                    <th style="width:140px;" class="text-end">Hành động</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td><?= $s['id'] ?></td>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= htmlspecialchars($s['contact_name']) ?></td>
                        <td><?= htmlspecialchars($s['phone']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td class="text-end">
                            <a href="index.php?c=Supplier&a=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="index.php?c=Supplier&a=delete&id=<?= $s['id'] ?>"
                               onclick="return confirm('Xóa nhà cung cấp này?')"
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
        Chưa có nhà cung cấp nào. Hãy thêm mới.
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
