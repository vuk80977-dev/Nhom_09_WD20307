<?php
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../models/Payment.php';

class BookingController {

    private function redirect($url){
        header("Location: $url"); exit;
    }

    private function setFlash($type,$msg){
        if(session_status()!==PHP_SESSION_ACTIVE) session_start();
        $_SESSION['flash']=['type'=>$type,'msg'=>$msg];
    }

    private function takeFlash(){
        if(session_status()!==PHP_SESSION_ACTIVE) session_start();
        $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f;
    }

    // =============================
    //  LIST + FILTER
    // =============================
    public function index(){
        $bookingModel = new Booking();
        $tourModel = new Tour();
        $scheduleModel = new Schedule();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $filters = [
            'status' => $_GET['status'] ?? '',
            'q' => $_GET['q'] ?? '',
            'tour_id' => $_GET['tour_id'] ?? '',
            'schedule_id' => $_GET['schedule_id'] ?? '',
        ];

        $data = $bookingModel->paginate($page, $perPage, $filters);
        $tours = $tourModel->all('id DESC');
        $schedules = $scheduleModel->all('id DESC');

        $flash = $this->takeFlash();
        $title = "Quản lý booking";
        include __DIR__ . '/../views/admin/booking/index.php';
    }

    // =============================
    //  CREATE FORM
    // =============================
    public function create(){
        $tourModel = new Tour();
        $customerModel = new Customer();
        $scheduleModel = new Schedule();

        $tours = $tourModel->all('id DESC');
        $customers = $customerModel->all('id DESC');
        $schedulesByTour = $scheduleModel->getOpenSchedulesGroupedByTour();

        $booking = [
            'tour_id' => '',
            'schedule_id' => '',
            'customer_id' => '',
            'booking_date' => date('Y-m-d'),
            'quantity' => 1,
            'status' => 'pending',
            'note' => '',
            'feedback' => '',
            'issue' => '',
        ];

        $flash = $this->takeFlash();
        $title = "Thêm booking";
        include __DIR__ . '/../views/admin/booking/form.php';
    }

    // =============================
    //  STORE BOOKING
    // =============================
    public function store() {

        $bookingModel = new Booking();
        $scheduleModel = new Schedule();
        $tourModel = new Tour();
        $paymentModel = new Payment();

        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        $schedule = $scheduleModel->find($scheduleId);

        if (!$schedule || $schedule['status'] !== 'open') {
            $this->setFlash('danger', 'Lịch khởi hành không hợp lệ.');
            $this->redirect('index.php?c=Booking&a=create');
        }

        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        // check chỗ
        if ($schedule['capacity'] > 0 && $schedule['booked_count'] + $qty > $schedule['capacity']) {
            $this->setFlash('danger', 'Không đủ chỗ cho lịch này.');
            $this->redirect('index.php?c=Booking&a=create');
        }

        $tour = $tourModel->find($schedule['tour_id']);
        $pricePerPerson = $schedule['price_override'] ?? $tour['price'];
        $totalPrice = $pricePerPerson * $qty;

        $deposit = (float)($_POST['deposit'] ?? 0);
        if ($deposit < 0) $deposit = 0;
        if ($deposit > $totalPrice) $deposit = $totalPrice;

        $data = [
            'tour_id' => $schedule['tour_id'],
            'schedule_id' => $scheduleId,
            'customer_id' => (int)($_POST['customer_id'] ?? 0),
            'quantity' => $qty,
            'booking_date' => $_POST['booking_date'] ?? date('Y-m-d'),
            'status' => $_POST['status'] ?? 'pending',
            'note' => trim($_POST['note'] ?? ''),
            'feedback' => trim($_POST['feedback'] ?? ''),
            'issue' => trim($_POST['issue'] ?? ''),
            'price_per_person' => $pricePerPerson,
            'total_price' => $totalPrice,
            'deposit' => $deposit,
            'paid_total' => $deposit,
        ];

        if ($data['customer_id'] <= 0) {
            $this->setFlash('danger','Vui lòng chọn khách hàng.');
            $this->redirect('index.php?c=Booking&a=create');
        }

        // gọi logic chuẩn trong Model
        $id = $bookingModel->createBooking($data);

        // thêm payment cọc
        if ($deposit > 0) {
            $paymentModel->create([
                'booking_id' => $id,
                'paid_at' => date('Y-m-d H:i:s'),
                'amount' => $deposit,
                'method' => 'cash',
                'status' => 'paid',
                'note' => 'Tiền cọc ban đầu'
            ]);
        }

        $this->setFlash('success','Tạo booking thành công.');
        $this->redirect('index.php?c=Booking&a=index');
    }

    // =============================
    //  EDIT FORM
    // =============================
    public function edit(){
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->redirect('index.php?c=Booking&a=index');

        $bookingModel = new Booking();
        $tourModel = new Tour();
        $customerModel = new Customer();
        $scheduleModel = new Schedule();

        $booking = $bookingModel->find($id);
        if (!$booking) {
            $this->setFlash('danger', 'Booking không tồn tại.');
            $this->redirect('index.php?c=Booking&a=index');
        }

        $tours = $tourModel->all('id DESC');
        $customers = $customerModel->all('id DESC');
        $schedulesByTour = $scheduleModel->getOpenSchedulesGroupedByTour();

        $flash = $this->takeFlash();
        $title = "Sửa booking";
        include __DIR__ . '/../views/admin/booking/form.php';
    }

    // =============================
    //  UPDATE BOOKING
    // =============================
    public function update() {

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) $this->redirect('index.php?c=Booking&a=index');

        $bookingModel = new Booking();
        $scheduleModel = new Schedule();
        $tourModel = new Tour();

        $old = $bookingModel->find($id);
        if (!$old) {
            $this->setFlash('danger','Booking không tồn tại.');
            $this->redirect('index.php?c=Booking&a=index');
        }

        $scheduleId = (int)($_POST['schedule_id']);
        $schedule = $scheduleModel->find($scheduleId);

        if (!$schedule) {
            $this->setFlash('danger','Lịch khởi hành không tồn tại.');
            $this->redirect('index.php?c=Booking&a=edit&id=' . $id);
        }

        $qty = max(1, (int)$_POST['quantity']);

        $tour = $tourModel->find($schedule['tour_id']);
        $pricePerPerson = $schedule['price_override'] ?? $tour['price'];
        $totalPrice = $pricePerPerson * $qty;

        $deposit = $old['deposit']; // giữ nguyên cọc cũ

        $data = [
            'tour_id' => $schedule['tour_id'],
            'schedule_id' => $scheduleId,
            'customer_id' => (int)$_POST['customer_id'],
            'quantity' => $qty,
            'booking_date' => $_POST['booking_date'],
            'status' => $_POST['status'],
            'note' => trim($_POST['note']),
            'feedback' => trim($_POST['feedback']),
            'issue' => trim($_POST['issue']),
            'price_per_person' => $pricePerPerson,
            'total_price' => $totalPrice,
            'deposit' => $deposit,
        ];

        // gọi logic chuẩn trong Model
        try {
            $bookingModel->updateBooking($id, $data);
        } catch (Exception $e) {
            $this->setFlash('danger', $e->getMessage());
            $this->redirect('index.php?c=Booking&a=edit&id='.$id);
        }

        $this->setFlash('success','Cập nhật booking thành công.');
        $this->redirect('index.php?c=Booking&a=index');
    }

    // =============================
    //  DELETE BOOKING
    // =============================
    public function destroy(){
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $bookingModel = new Booking();
            try {
                $bookingModel->deleteBooking($id);
            } catch (Exception $e) {
                $this->setFlash('danger', $e->getMessage());
                $this->redirect('index.php?c=Booking&a=index');
            }

            $this->setFlash('success','Xóa booking thành công.');
        }

        $this->redirect('index.php?c=Booking&a=index');
    }
}
