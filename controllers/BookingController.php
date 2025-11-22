<?php
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Customer.php';

class BookingController
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

    // GET /?c=Booking&a=index
    public function index() {
        $bookingModel = new Booking();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $filters = [
            'status' => $_GET['status'] ?? '',
            'q'      => $_GET['q']      ?? '',
        ];

        $data = $bookingModel->paginate($page, $perPage, $filters);
        $flash = $this->takeFlash();

        // Truyền danh sách tour & khách để hiển thị tên (nếu muốn mở rộng)
        $tourModel = new Tour();
        $customerModel = new Customer();
        $tours = [];
        foreach ($tourModel->all() as $t) { $tours[$t['id']] = $t; }
        $customers = [];
        foreach ($customerModel->all() as $c) { $customers[$c['id']] = $c; }

        $title = 'Quản lý Booking';
        include __DIR__ . '/../views/admin/booking/index.php';
    }

    // GET /?c=Booking&a=create
    public function create() {
        $tourModel = new Tour();
        $customerModel = new Customer();
        $tours = $tourModel->all();
        $customers = $customerModel->all();
        $booking = ['tour_id' => '', 'customer_id' => '', 'booking_date' => date('Y-m-d'), 'status' => 'pending'];
        $title = 'Thêm Booking';
        include __DIR__ . '/../views/admin/booking/form.php';
    }

    // POST /?c=Booking&a=store
    public function store() {
        $bookingModel = new Booking();

        $data = [
            'tour_id'      => (int)($_POST['tour_id'] ?? 0),
            'customer_id'  => (int)($_POST['customer_id'] ?? 0),
            'booking_date' => $_POST['booking_date'] ?? date('Y-m-d'),
            'status'       => $_POST['status'] ?? 'pending',
        ];

        // Validate đơn giản
        if ($data['tour_id'] <= 0 || $data['customer_id'] <= 0) {
            $this->setFlash('danger', 'Vui lòng chọn Tour và Khách hàng hợp lệ.');
            $this->redirect('index.php?c=Booking&a=create');
        }

        $bookingModel->create($data);
        $this->setFlash('success', 'Tạo booking thành công.');
        $this->redirect('index.php?c=Booking&a=index');
    }

    // GET /?c=Booking&a=edit&id=...
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { $this->redirect('index.php?c=Booking&a=index'); }

        $bookingModel = new Booking();
        $booking = $bookingModel->find($id);
        if (!$booking) {
            $this->setFlash('danger', 'Booking không tồn tại.');
            $this->redirect('index.php?c=Booking&a=index');
        }

        $tourModel = new Tour();
        $customerModel = new Customer();
        $tours = $tourModel->all();
        $customers = $customerModel->all();
        $title = 'Sửa Booking';
        include __DIR__ . '/../views/admin/booking/form.php';
    }

    // POST /?c=Booking&a=update
    public function update() {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { $this->redirect('index.php?c=Booking&a=index'); }

        $bookingModel = new Booking();

        $data = [
            'tour_id'      => (int)($_POST['tour_id'] ?? 0),
            'customer_id'  => (int)($_POST['customer_id'] ?? 0),
            'booking_date' => $_POST['booking_date'] ?? date('Y-m-d'),
            'status'       => $_POST['status'] ?? 'pending',
        ];

        if ($data['tour_id'] <= 0 || $data['customer_id'] <= 0) {
            $this->setFlash('danger', 'Vui lòng chọn Tour và Khách hàng hợp lệ.');
            $this->redirect('index.php?c=Booking&a=edit&id=' . $id);
        }

        $bookingModel->update($id, $data);
        $this->setFlash('success', 'Cập nhật booking thành công.');
        $this->redirect('index.php?c=Booking&a=index');
    }

    // POST /?c=Booking&a=destroy
    public function destroy() {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $bookingModel = new Booking();
            $bookingModel->delete($id);
            $this->setFlash('success', 'Xóa booking thành công.');
        }
        $this->redirect('index.php?c=Booking&a=index');
    }
}
