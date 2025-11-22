<?php
$title = 'Sửa danh mục tour';
ob_start();
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h1 class="h5 mb-0">Sửa danh mục tour</h1>
            </div>
            <div class="card-body">
                <form action="index.php?c=TourType&a=update" method="post">
                    <input type="hidden" name="id" value="<?= $type['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Tên danh mục</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= htmlspecialchars($type['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($type['description']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="index.php?c=TourType&a=index" class="btn btn-secondary">
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
