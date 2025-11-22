<?php
$title = 'Quản lý tài khoản';
ob_start();
?>
<h1 class="h5 mb-3">Quản lý tài khoản</h1>

<?php if (!empty($users)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th style="width:60px;">ID</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th style="width:200px;" class="text-end">Hành động</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <form method="post" action="index.php?c=User&a=changeRole" class="d-flex gap-2">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <select name="role" class="form-select form-select-sm">
                                    <option value="customer" <?= $u['role']=='customer'?'selected':'' ?>>Khách</option>
                                    <option value="staff" <?= $u['role']=='staff'?'selected':'' ?>>Nhân viên</option>
                                    <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                                </select>
                                <button class="btn btn-sm btn-primary">Lưu</button>
                            </form>
                        </td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span class="badge text-bg-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Đã khóa</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="index.php?c=User&a=toggleActive&id=<?= $u['id'] ?>"
                               class="btn btn-sm <?= $u['is_active'] ? 'btn-outline-warning':'btn-outline-success' ?>">
                                <?= $u['is_active'] ? 'Khoá' : 'Mở khoá' ?>
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
        Chưa có tài khoản nào.
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
