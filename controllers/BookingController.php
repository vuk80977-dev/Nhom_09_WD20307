<?php
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Schedule.php';

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

    // Danh sách + lọc
    public function index(){
        $bookingModel = new Booking();
        $tourModel = new Tour();
        $scheduleModel = new Schedule();

        $page=max(1,(int)($_GET['page']??1));
        $perPage=10;

        $filters=[
            'status'=>$_GET['status']??'',
            'q'=>$_GET['q']??'',
            'tour_id'=>$_GET['tour_id']??'',
            'schedule_id'=>$_GET['schedule_id']??'',
        ];

        $data=$bookingModel->paginate($page,$perPage,$filters);

        $tours = $tourModel->all('id DESC');
        $schedules = $scheduleModel->all('id DESC');

        $flash=$this->takeFlash();
        $title="Quản lý booking";
        include __DIR__ . '/../views/admin/booking/index.php';
    }

    public function create(){
        $tourModel=new Tour();
        $customerModel=new Customer();
        $scheduleModel=new Schedule();

        $tours=$tourModel->all('id DESC');
        $customers=$customerModel->all('id DESC');
        $schedulesByTour = $scheduleModel->getOpenSchedulesGroupedByTour();

        $booking=[
            'tour_id'=>'',
            'schedule_id'=>'',
            'customer_id'=>'',
            'booking_date'=>date('Y-m-d'),
            'quantity'=>1,
            'status'=>'pending',
            'note'=>'',
            'rating'=>null,
            'feedback'=>'',
            'issue'=>'',
        ];

        $flash=$this->takeFlash();
        $title="Thêm booking";
        include __DIR__ . '/../views/admin/booking/form.php';
    }

    public function store(){
        $bookingModel=new Booking();
        $scheduleModel=new Schedule();

        $data=[
            'tour_id'=>(int)($_POST['tour_id']??0),
            'schedule_id'=>(int)($_POST['schedule_id']??0),
            'customer_id'=>(int)($_POST['customer_id']??0),
            'booking_date'=>$_POST['booking_date']??date('Y-m-d'),
            'quantity'=>(int)($_POST['quantity']??1),
            'status'=>$_POST['status']??'pending',
            'note'=>trim($_POST['note']??''),
            'rating'=>($_POST['rating']??'')!==''?(int)$_POST['rating']:null,
            'feedback'=>trim($_POST['feedback']??''),
            'issue'=>trim($_POST['issue']??''),
        ];

        if($data['tour_id']<=0||$data['schedule_id']<=0||$data['customer_id']<=0){
            $this->setFlash('danger','Vui lòng chọn Tour / Lịch khởi hành / Khách hàng.');
            $this->redirect('index.php?c=Booking&a=create');
        }

        if($data['quantity']<=0){
            $this->setFlash('danger','Số lượng khách phải > 0.');
            $this->redirect('index.php?c=Booking&a=create');
        }

        // check lịch khớp tour + còn chỗ
        $sc=$scheduleModel->find($data['schedule_id']);
        if(!$sc || (int)$sc['tour_id']!==$data['tour_id']){
            $this->setFlash('danger','Lịch khởi hành không hợp lệ.');
            $this->redirect('index.php?c=Booking&a=create');
        }

        $cap=(int)($sc['capacity']??0);
        $booked=(int)($sc['booked_count']??0);
        if($cap>0 && ($booked+$data['quantity'])>$cap){
            $this->setFlash('danger','Lịch này không đủ chỗ.');
            $this->redirect('index.php?c=Booking&a=create');
        }

        $bookingModel->create($data);

        if($data['status']==='confirmed'){
            $bookingModel->incBooked($data['schedule_id'],$data['quantity']);
        }

        $this->setFlash('success','Tạo booking thành công.');
        $this->redirect('index.php?c=Booking&a=index');
    }

    public function edit(){
        $id=(int)($_GET['id']??0); if($id<=0) $this->redirect('index.php?c=Booking&a=index');

        $bookingModel=new Booking();
        $tourModel=new Tour();
        $customerModel=new Customer();
        $scheduleModel=new Schedule();

        $booking=$bookingModel->find($id);
        if(!$booking){
            $this->setFlash('danger','Booking không tồn tại.');
            $this->redirect('index.php?c=Booking&a=index');
        }

        $tours=$tourModel->all('id DESC');
        $customers=$customerModel->all('id DESC');
        $schedulesByTour = $scheduleModel->getOpenSchedulesGroupedByTour();

        $flash=$this->takeFlash();
        $title="Sửa booking";
        include __DIR__ . '/../views/admin/booking/form.php';
    }

    public function update(){
        $id=(int)($_POST['id']??0); if($id<=0) $this->redirect('index.php?c=Booking&a=index');

        $bookingModel=new Booking();
        $scheduleModel=new Schedule();

        $old=$bookingModel->find($id);
        if(!$old){
            $this->setFlash('danger','Booking không tồn tại.');
            $this->redirect('index.php?c=Booking&a=index');
        }

        $data=[
            'tour_id'=>(int)($_POST['tour_id']??0),
            'schedule_id'=>(int)($_POST['schedule_id']??0),
            'customer_id'=>(int)($_POST['customer_id']??0),
            'booking_date'=>$_POST['booking_date']??date('Y-m-d'),
            'quantity'=>(int)($_POST['quantity']??1),
            'status'=>$_POST['status']??'pending',
            'note'=>trim($_POST['note']??''),
            'rating'=>($_POST['rating']??'')!==''?(int)$_POST['rating']:null,
            'feedback'=>trim($_POST['feedback']??''),
            'issue'=>trim($_POST['issue']??''),
        ];

        if($data['tour_id']<=0||$data['schedule_id']<=0||$data['customer_id']<=0){
            $this->setFlash('danger','Vui lòng chọn Tour / Lịch / Khách hàng.');
            $this->redirect("index.php?c=Booking&a=edit&id=$id");
        }

        if($data['quantity']<=0){
            $this->setFlash('danger','Số lượng khách phải > 0.');
            $this->redirect("index.php?c=Booking&a=edit&id=$id");
        }

        $sc=$scheduleModel->find($data['schedule_id']);
        if(!$sc || (int)$sc['tour_id']!==$data['tour_id']){
            $this->setFlash('danger','Lịch khởi hành không hợp lệ.');
            $this->redirect("index.php?c=Booking&a=edit&id=$id");
        }

        // xử lý booked_count khi đổi lịch / đổi status / đổi quantity
        $oldStatus=$old['status'];
        $newStatus=$data['status'];
        $oldSchedule=(int)$old['schedule_id'];
        $newSchedule=(int)$data['schedule_id'];
        $oldQty=(int)$old['quantity'];
        $newQty=(int)$data['quantity'];

        // Nếu booking cũ confirmed => trừ khỏi lịch cũ
        if($oldStatus==='confirmed'){
            $bookingModel->decBooked($oldSchedule,$oldQty);
        }

        // Check chỗ lịch mới trước khi cộng
        $cap=(int)($sc['capacity']??0);
        $booked=(int)($sc['booked_count']??0);
        if($cap>0 && ($booked + ($newStatus==='confirmed'?$newQty:0))>$cap){
            // rollback cộng lại cũ nếu cần
            if($oldStatus==='confirmed'){
                $bookingModel->incBooked($oldSchedule,$oldQty);
            }
            $this->setFlash('danger','Lịch mới không đủ chỗ.');
            $this->redirect("index.php?c=Booking&a=edit&id=$id");
        }

        // Nếu booking mới confirmed => cộng vào lịch mới
        if($newStatus==='confirmed'){
            $bookingModel->incBooked($newSchedule,$newQty);
        }

        $bookingModel->update($id,$data);
        $this->setFlash('success','Cập nhật booking thành công.');
        $this->redirect('index.php?c=Booking&a=index');
    }

    public function destroy(){
        $id=(int)($_POST['id']??0);
        if($id>0){
            $bookingModel=new Booking();
            $old=$bookingModel->find($id);

            if($old && $old['status']==='confirmed'){
                $bookingModel->decBooked((int)$old['schedule_id'],(int)$old['quantity']);
            }

            $bookingModel->delete($id);
            $this->setFlash('success','Xóa booking thành công.');
        }
        $this->redirect('index.php?c=Booking&a=index');
    }
}
