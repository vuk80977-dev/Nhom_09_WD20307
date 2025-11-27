<?php
require_once __DIR__ . '/../models/Customer.php';

class CustomerController
{
    private $model;

    public function __construct()
    {
        $this->model = new Customer();
    }

    // Hàm tiện ích điều hướng trang
    private function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    // Thiết lập thông báo flash (type: 'success' hoặc 'danger', msg: nội dung)
    private function setFlash($type, $msg)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    // Lấy và xóa thông báo flash trong session
    private function takeFlash()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    // Hiển thị danh sách khách hàng với phân trang và lọc tìm kiếm
    public function index()
    {
        // Lấy tham số trang và bộ lọc từ URL (GET parameters)
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $filters = [
            'status' => $_GET['status'] ?? '',
            'q'      => $_GET['q'] ?? '',
        ];

        // Lấy dữ liệu khách hàng kèm tổng số trang theo bộ lọc
        $data  = $this->model->paginate($page, $perPage, $filters);
        $flash = $this->takeFlash();                     // Thông báo (nếu có) sau thao tác
        $title = 'Quản lý khách hàng';                   // Tiêu đề cho view

        include __DIR__ . '/../views/admin/customers/index.php';
    }

    // Hiển thị form thêm khách hàng mới
    public function create()
    {
        // Khởi tạo mảng thông tin khách hàng mặc định (trống)
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

    // Xử lý lưu khách hàng mới (POST)
    public function store()
    {
        // Lấy và trim dữ liệu từ form
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status  = $_POST['status'] ?? 'active';

        // Ràng buộc: Tên không được rỗng và phải có ít nhất email hoặc điện thoại
        if ($name === '' || ($email === '' && $phone === '')) {
            $this->setFlash('danger', 'Vui lòng nhập tên và ít nhất một phương thức liên lạc (email hoặc điện thoại).');
            $this->redirect('index.php?c=Customer&a=create');
        }

        // Kiểm tra email đã tồn tại chưa
        $existing = $this->model->where('email', $email);
        if (!empty($email) && !empty($existing)) {
            $this->setFlash('danger', 'Email này đã được sử dụng.');
            $this->redirect('index.php?c=Customer&a=create');
        }

        // Dữ liệu hợp lệ, tiến hành lưu vào CSDL
        $this->model->create([
            'name'    => $name,
            'email'   => $email,
            'phone'   => $phone,
            'address' => $address,
            'status'  => $status,
            // 'created_at' => date('Y-m-d H:i:s') // (Nếu cần thiết, thường CSDL tự thêm)
        ]);

        $this->setFlash('success', 'Thêm khách hàng mới thành công.');
        $this->redirect('index.php?c=Customer&a=index');
    }

    // Hiển thị form chỉnh sửa thông tin khách hàng
    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('index.php?c=Customer&a=index');
        }

        // Tìm khách hàng theo ID
        $customer = $this->model->find($id);
        if (empty($customer)) {
            $this->setFlash('danger', 'Khách hàng không tồn tại.');
            $this->redirect('index.php?c=Customer&a=index');
        }

        $flash = $this->takeFlash();
        $title = 'Sửa khách hàng';

        include __DIR__ . '/../views/admin/customers/form.php';
    }

    // Xử lý cập nhật khách hàng (POST)
    public function update()
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('index.php?c=Customer&a=index');
        }

        // Lấy dữ liệu từ form
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status  = $_POST['status'] ?? 'active';

        // Ràng buộc dữ liệu (tương tự store)
        if ($name === '' || ($email === '' && $phone === '')) {
            $this->setFlash('danger', 'Vui lòng nhập tên và ít nhất một phương thức liên lạc (email hoặc điện thoại).');
            $this->redirect("index.php?c=Customer&a=edit&id={$id}");
        }
        // Kiểm tra trùng email với người khác
        $existing = $this->model->where('email', $email);
        if (!empty($email) && !empty($existing) && $existing[0]['id'] != $id) {
            $this->setFlash('danger', 'Email này đã được sử dụng.');
            $this->redirect("index.php?c=Customer&a=edit&id={$id}");
        }

        // Cập nhật bản ghi
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

    // Xóa khách hàng
    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->model->delete($id);
            $this->setFlash('success', 'Xóa khách hàng thành công.');
        }
        $this->redirect('index.php?c=Customer&a=index');
    }
}
?>
