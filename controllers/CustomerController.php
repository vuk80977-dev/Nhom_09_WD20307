<?php
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Booking.php';

class CustomerController
{
    private $model;

    public function __construct()
    {
        $this->model = new Customer();
    }

    private function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    private function setFlash($type, $msg)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    private function takeFlash()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    public function index()
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $filters = [
            'status' => $_GET['status'] ?? '',
            'q'      => $_GET['q'] ?? '',
        ];

        $data  = $this->model->paginate($page, $perPage, $filters);
        $flash = $this->takeFlash();
        $title = 'Quản lý khách hàng';

        include __DIR__ . '/../views/admin/customers/index.php';
    }

    public function create()
    {
        $customer = [
            'id'      => 0,
            'name'    => '',
            'email'   => '',
            'phone'   => '',
            'address' => '',
            'status'  => 'active'
        ];
        $flash = $this->takeFlash();
        $title = 'Thêm khách hàng';

        include __DIR__ . '/../views/admin/customers/form.php';
    }

    public function store()
    {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status  = $_POST['status'] ?? 'active';

        if ($name === '' || ($email === '' && $phone === '')) {
            $this->setFlash('danger', 'Vui lòng nhập tên và ít nhất một phương thức liên lạc (email hoặc điện thoại).');
            $this->redirect('index.php?c=Customer&a=create');
        }

        $existing = $this->model->where('email', $email);
        if (!empty($email) && !empty($existing)) {
            $this->setFlash('danger', 'Email này đã được sử dụng.');
            $this->redirect('index.php?c=Customer&a=create');
        }

        $this->model->create([
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'address' => $address,
            'status'  => $status,
        ]);

        $this->setFlash('success', 'Thêm khách hàng mới thành công.');
        $this->redirect('index.php?c=Customer&a=index');
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('index.php?c=Customer&a=index');
        }

        $customer = $this->model->find($id);
        if (empty($customer)) {
            $this->setFlash('danger', 'Khách hàng không tồn tại.');
            $this->redirect('index.php?c=Customer&a=index');
        }

        $flash = $this->takeFlash();
        $title = 'Sửa khách hàng';

        include __DIR__ . '/../views/admin/customers/form.php';
    }

    public function update()
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('index.php?c=Customer&a=index');
        }

        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status  = $_POST['status'] ?? 'active';

        if ($name === '' || ($email === '' && $phone === '')) {
            $this->setFlash('danger', 'Vui lòng nhập tên và ít nhất một phương thức liên lạc (email hoặc điện thoại).');
            $this->redirect("index.php?c=Customer&a=edit&id={$id}");
        }

        $existing = $this->model->where('email', $email);
        if (!empty($email) && !empty($existing) && $existing[0]['id'] != $id) {
            $this->setFlash('danger', 'Email này đã được sử dụng.');
            $this->redirect("index.php?c=Customer&a=edit&id={$id}");
        }

        $this->model->update($id, [
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'address' => $address,
            'status'  => $status
        ]);

        $this->setFlash('success', 'Cập nhật thông tin khách hàng thành công.');
        $this->redirect('index.php?c=Customer&a=index');
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) return;

        $bookingModel = new Booking();
        $count = $bookingModel->countByCustomerId($id);

        if ($count > 0) {
            $this->setFlash("danger","Không thể xóa! Khách hàng đang có $count booking.");
            $this->redirect("index.php?c=Customer&a=index");
        }

        $this->model->delete($id);
        $this->setFlash("success","Đã xóa khách hàng.");
        $this->redirect("index.php?c=Customer&a=index");
    }
}
?>