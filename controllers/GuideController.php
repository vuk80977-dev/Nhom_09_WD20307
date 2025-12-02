<?php
require_once __DIR__ . '/../models/Guide.php';

class GuideController
{
    private function redirect($url) {
        header("Location: {$url}");
        exit;
    }

    private function setFlash($type, $msg) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    private function takeFlash() {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }

    // GET /?c=Guide&a=index
    public function index() {
        $model = new Guide();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $filters = [
            'status' => $_GET['status'] ?? '',
            'q'      => $_GET['q']      ?? '',
        ];

        $data = $model->paginate($page, $perPage, $filters);
        $flash = $this->takeFlash();
        $title = 'Quản lý Hướng dẫn viên';

        include __DIR__ . '/../views/admin/guide/index.php';
    }

    // GET /?c=Guide&a=create
    public function create() {
        $guide = [
            'name'      => '',
            'phone'     => '',
            'email'     => '',
            'languages' => '',
            'status'    => 'active',
            'note'      => '',
        ];
        $title = 'Thêm Hướng dẫn viên';
        include __DIR__ . '/../views/admin/guide/form.php';
    }

    // POST /?c=Guide&a=store
    public function store() {
        $model = new Guide();
        $data = [
            'name'      => trim($_POST['name'] ?? ''),
            'phone'     => trim($_POST['phone'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'languages' => trim($_POST['languages'] ?? ''),
            'status'    => $_POST['status'] ?? 'active',
            'note'      => trim($_POST['note'] ?? ''),
        ];

        if ($data['name'] === '' || ($data['email'] === '' && $data['phone'] === '')) {
            $this->setFlash('danger', 'Vui lòng nhập tên và ít nhất một thông tin liên hệ (email hoặc điện thoại).');
            $this->redirect('index.php?c=Guide&a=create');
        }

        $model->create($data);
        $this->setFlash('success', 'Tạo hướng dẫn viên thành công.');
        $this->redirect('index.php?c=Guide&a=index');
    }

    // GET /?c=Guide&a=edit&id=...
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->redirect('index.php?c=Guide&a=index');

        $model = new Guide();
        $guide = $model->find($id);
        if (!$guide) {
            $this->setFlash('danger', 'Hướng dẫn viên không tồn tại.');
            $this->redirect('index.php?c=Guide&a=index');
        }

        $title = 'Sửa Hướng dẫn viên';
        include __DIR__ . '/../views/admin/guide/form.php';
    }

    // POST /?c=Guide&a=update
    public function update() {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) $this->redirect('index.php?c=Guide&a=index');

        $model = new Guide();
        $data = [
            'name'      => trim($_POST['name'] ?? ''),
            'phone'     => trim($_POST['phone'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'languages' => trim($_POST['languages'] ?? ''),
            'status'    => $_POST['status'] ?? 'active',
            'note'      => trim($_POST['note'] ?? ''),
        ];

        if ($data['name'] === '' || ($data['email'] === '' && $data['phone'] === '')) {
            $this->setFlash('danger', 'Vui lòng nhập tên và ít nhất một thông tin liên hệ (email hoặc điện thoại).');
            $this->redirect('index.php?c=Guide&a=edit&id='.$id);
        }

        $model->update($id, $data);
        $this->setFlash('success', 'Cập nhật hướng dẫn viên thành công.');
        $this->redirect('index.php?c=Guide&a=index');
    }

    // POST /?c=Guide&a=destroy
    public function delete()
{
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) return;

    $count = self::$conn->query("SELECT COUNT(*) FROM schedules WHERE guide_id=$id")->fetchColumn();

    if ($count > 0) {
        $this->setFlash("danger","Không thể xóa! Hướng dẫn viên đang phụ trách $count lịch khởi hành.");
        $this->redirect("index.php?c=Guide&a=index");
    }

    $this->model->delete($id);
    $this->setFlash("success","Đã xóa hướng dẫn viên.");
    $this->redirect("index.php?c=Guide&a=index");
}

}
