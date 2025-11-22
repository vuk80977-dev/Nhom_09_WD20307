<?php
$title = 'Sửa nhà cung cấp';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="h5 mb-0">Sửa nhà cung cấp</h1>
            </div>
            <div class="card-body">
                <form action="index.php?c=Supplier&a=update" method="post">
                    <input type="hidden" name="id" value="<?= $supplier['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Tên nhà cung cấp</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= htmlspecialchars($supplier['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Người liên hệ</label>
                        <input type="text" name="contact_name" class="form-control"
                               value="<?= htmlspecialchars($supplier['contact_name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Điện thoại</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= htmlspecialchars($supplier['phone']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($supplier['email']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <input type="text" name="address" class="form-control"
                               value="<?= htmlspecialchars($supplier['address']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="3"><?= htmlspecialchars($supplier['note']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php?c=Supplier&a=index" class="btn btn-secondary">
                            Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/main.php';
