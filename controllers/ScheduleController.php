<?php
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Guide.php';

class ScheduleController
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

    public function index() {
        $model = new Schedule();

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $filters = [
            'tour_id' => $_GET['tour_id'] ?? '',
            'status'  => $_GET['status'] ?? '',
            'q'       => $_GET['q'] ?? '',
        ];

        $data  = $model->paginate($page, $perPage, $filters);
        $flash = $this->takeFlash();

        $tourModel  = new Tour();
        $guideModel = new Guide();
        $tours  = $tourModel->all('id DESC');
        $guides = $guideModel->all('id DESC');

        $title = 'Lịch khởi hành';
        include __DIR__ . '/../views/admin/schedule/index.php';
    }

    public function create() {
        $tourModel  = new Tour();
        $guideModel = new Guide();
        $tours  = $tourModel->all('id DESC');
        $guides = $guideModel->all('id DESC');

        $schedule = [
            'tour_id' => '',
            'guide_id' => '',
            'start_date' => date('Y-m-d'),
            'end_date' => '',
            'meeting_point' => '',
            'capacity' => 0,
            'booked_count' => 0,
            'price_override' => '',
            'status' => 'open',
            'note' => '',
        ];

        $title = 'Thêm lịch khởi hành';
        include __DIR__ . '/../views/admin/schedule/form.php';
    }

    public function store() {
        $model = new Schedule();

        $data = [
            'tour_id'        => (int)($_POST['tour_id'] ?? 0),
            'guide_id'       => (($_POST['guide_id'] ?? '') !== '') ? (int)$_POST['guide_id'] : null,
            'start_date'     => $_POST['start_date'] ?? date('Y-m-d'),
            'end_date'       => (($_POST['end_date'] ?? '') !== '') ? $_POST['end_date'] : null,
            'meeting_point'  => trim($_POST['meeting_point'] ?? ''),
            'capacity'       => (int)($_POST['capacity'] ?? 0),
            'booked_count'   => (int)($_POST['booked_count'] ?? 0),
            'price_override' => (($_POST['price_override'] ?? '') !== '') ? (float)$_POST['price_override'] : null,
            'status'         => $_POST['status'] ?? 'open',
            'note'           => trim($_POST['note'] ?? ''),
        ];

        if ($data['tour_id'] <= 0) {
            $this->setFlash('danger', 'Vui lòng chọn tour.');
            $this->redirect('index.php?c=Schedule&a=create');
        }

        if ($data['capacity'] < 0 || $data['booked_count'] < 0) {
            $this->setFlash('danger', 'Số chỗ / số đã đặt không hợp lệ.');
            $this->redirect('index.php?c=Schedule&a=create');
        }

        // ✅ booked không được vượt capacity
        if ($data['capacity'] > 0 && $data['booked_count'] > $data['capacity']) {
            $this->setFlash('danger', 'Số đã đặt không được vượt quá số chỗ.');
            $this->redirect('index.php?c=Schedule&a=create');
        }

        // ✅ end_date không được nhỏ hơn start_date
        if (!empty($data['end_date']) && $data['end_date'] < $data['start_date']) {
            $this->setFlash('danger', 'Ngày kết thúc không được nhỏ hơn ngày khởi hành.');
            $this->redirect('index.php?c=Schedule&a=create');
        }

        // ✅ Auto-closed nếu full chỗ (chỉ khi status đang open/closed)
        if (in_array($data['status'], ['open','closed'], true)
            && $data['capacity'] > 0
            && $data['booked_count'] >= $data['capacity']) {
            $data['status'] = 'closed';
        }

        $model->create($data);
        $this->setFlash('success', 'Tạo lịch khởi hành thành công.');
        $this->redirect('index.php?c=Schedule&a=index');
    }

    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->redirect('index.php?c=Schedule&a=index');

        $model = new Schedule();
        $schedule = $model->find($id);
        if (!$schedule) {
            $this->setFlash('danger', 'Lịch khởi hành không tồn tại.');
            $this->redirect('index.php?c=Schedule&a=index');
        }

        $tourModel  = new Tour();
        $guideModel = new Guide();
        $tours  = $tourModel->all('id DESC');
        $guides = $guideModel->all('id DESC');

        $title = 'Sửa lịch khởi hành';
        include __DIR__ . '/../views/admin/schedule/form.php';
    }

    public function update() {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) $this->redirect('index.php?c=Schedule&a=index');

        $model = new Schedule();

        $data = [
            'tour_id'        => (int)($_POST['tour_id'] ?? 0),
            'guide_id'       => (($_POST['guide_id'] ?? '') !== '') ? (int)$_POST['guide_id'] : null,
            'start_date'     => $_POST['start_date'] ?? date('Y-m-d'),
            'end_date'       => (($_POST['end_date'] ?? '') !== '') ? $_POST['end_date'] : null,
            'meeting_point'  => trim($_POST['meeting_point'] ?? ''),
            'capacity'       => (int)($_POST['capacity'] ?? 0),
            'booked_count'   => (int)($_POST['booked_count'] ?? 0),
            'price_override' => (($_POST['price_override'] ?? '') !== '') ? (float)$_POST['price_override'] : null,
            'status'         => $_POST['status'] ?? 'open',
            'note'           => trim($_POST['note'] ?? ''),
        ];

        if ($data['tour_id'] <= 0) {
            $this->setFlash('danger', 'Vui lòng chọn tour.');
            $this->redirect('index.php?c=Schedule&a=edit&id='.$id);
        }

        if ($data['capacity'] < 0 || $data['booked_count'] < 0) {
            $this->setFlash('danger', 'Số chỗ / số đã đặt không hợp lệ.');
            $this->redirect('index.php?c=Schedule&a=edit&id='.$id);
        }

        // ✅ booked không được vượt capacity
        if ($data['capacity'] > 0 && $data['booked_count'] > $data['capacity']) {
            $this->setFlash('danger', 'Số đã đặt không được vượt quá số chỗ.');
            $this->redirect('index.php?c=Schedule&a=edit&id='.$id);
        }

        // ✅ end_date không được nhỏ hơn start_date
        if (!empty($data['end_date']) && $data['end_date'] < $data['start_date']) {
            $this->setFlash('danger', 'Ngày kết thúc không được nhỏ hơn ngày khởi hành.');
            $this->redirect('index.php?c=Schedule&a=edit&id='.$id);
        }

        // ✅ Auto-closed nếu full chỗ (không đè completed/cancelled)
        if (in_array($data['status'], ['open','closed'], true)
            && $data['capacity'] > 0
            && $data['booked_count'] >= $data['capacity']) {
            $data['status'] = 'closed';
        }

        $model->update($id, $data);
        $this->setFlash('success', 'Cập nhật lịch khởi hành thành công.');
        $this->redirect('index.php?c=Schedule&a=index');
    }

   public function destroy()
{
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) return;

    $count = self::$conn->query("SELECT COUNT(*) FROM bookings WHERE schedule_id=$id")->fetchColumn();

    if ($count > 0) {
        $this->setFlash("danger","Không thể xóa! Lịch này có $count booking.");
        $this->redirect("index.php?c=Schedule&a=index");
    }

    $model = new Schedule();
    $model->delete($id);

    $this->setFlash("success","Đã xóa lịch khởi hành.");
    $this->redirect("index.php?c=Schedule&a=index");
}

}
