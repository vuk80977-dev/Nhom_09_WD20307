<?php
require_once __DIR__ . '/../models/TourLog.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../models/Tour.php';

class TourLogController {

    private function redirect($url){ header("Location: $url"); exit; }

    private function setFlash($type,$msg){
        if(session_status()!==PHP_SESSION_ACTIVE) session_start();
        $_SESSION['flash']=['type'=>$type,'msg'=>$msg];
    }
    private function takeFlash(){
        if(session_status()!==PHP_SESSION_ACTIVE) session_start();
        $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f;
    }

    public function index(){
        $model=new TourLog();
        $scheduleModel=new Schedule();

        $page=max(1,(int)($_GET['page']??1));
        $perPage=10;
        $filters=[
            'schedule_id'=>$_GET['schedule_id']??'',
            'type'=>$_GET['type']??'',
            'q'=>$_GET['q']??'',
        ];

        $data=$model->paginate($page,$perPage,$filters);
        $schedules=$scheduleModel->all('id DESC');

        $flash=$this->takeFlash();
        $title="Nhật ký tour";
        include __DIR__ . '/../views/admin/tourlog/index.php';
    }

    public function create(){
        $scheduleModel=new Schedule();
        $schedules=$scheduleModel->all('id DESC');
        $schedules = $scheduleModel->where('status', 'open');

        $log=[
            'schedule_id'=>'',
            'title'=>'',
            'content'=>'',
            'log_date'=>date('Y-m-d'),
            'type'=>'note',
        ];

        $flash=$this->takeFlash();
        $title="Thêm nhật ký tour";
        include __DIR__ . '/../views/admin/tourlog/form.php';
    }

   public function store(){
    $model = new TourLog();
    $scheduleModel = new Schedule(); // ✅ thêm dòng này

    $data = [
        'schedule_id' => (int)($_POST['schedule_id'] ?? 0),
        'user_id'     => null,
        'title'       => trim($_POST['title'] ?? ''),
        'content'     => trim($_POST['content'] ?? ''),
        'log_date'    => $_POST['log_date'] ?? date('Y-m-d'),
        'type'        => $_POST['type'] ?? 'note',
    ];

    if($data['schedule_id']<=0 || $data['title']==='' || $data['content']===''){
        $this->setFlash('danger','Vui lòng nhập đủ lịch, tiêu đề, nội dung.');
        $this->redirect('index.php?c=TourLog&a=create');
    }

    // ✅ check ngày ghi nằm trong lịch
    $sc = $scheduleModel->find($data['schedule_id']);
    if(!$sc){
        $this->setFlash('danger','Lịch khởi hành không hợp lệ.');
        $this->redirect('index.php?c=TourLog&a=create');
    }

    if($data['log_date'] < $sc['start_date'] || (!empty($sc['end_date']) && $data['log_date'] > $sc['end_date'])){
        $this->setFlash('danger','Ngày ghi phải nằm trong thời gian lịch khởi hành.');
        $this->redirect('index.php?c=TourLog&a=create');
    }

    $model->create($data);
    $this->setFlash('success','Tạo nhật ký tour thành công.');
    $this->redirect('index.php?c=TourLog&a=index');
}


    public function edit(){
        $id=(int)($_GET['id']??0);
        if($id<=0) $this->redirect('index.php?c=TourLog&a=index');

        $model=new TourLog();
        $log=$model->find($id);
        if(!$log){
            $this->setFlash('danger','Nhật ký không tồn tại.');
            $this->redirect('index.php?c=TourLog&a=index');
        }

        $scheduleModel=new Schedule();
        $schedules=$scheduleModel->all('id DESC');

        $flash=$this->takeFlash();
        $title="Sửa nhật ký tour";
        include __DIR__ . '/../views/admin/tourlog/form.php';
    }

    public function update(){
        $id=(int)($_POST['id']??0);
        if($id<=0) $this->redirect('index.php?c=TourLog&a=index');

        $model=new TourLog();

        $data=[
            'schedule_id'=>(int)($_POST['schedule_id']??0),
            'title'=>trim($_POST['title']??''),
            'content'=>trim($_POST['content']??''),
            'log_date'=>$_POST['log_date']??date('Y-m-d'),
            'type'=>$_POST['type']??'note',
        ];

        if($data['schedule_id']<=0 || $data['title']==='' || $data['content']===''){
            $this->setFlash('danger','Vui lòng nhập đủ lịch, tiêu đề, nội dung.');
            $this->redirect('index.php?c=TourLog&a=edit&id='.$id);
        }

        $model->update($id,$data);
        $this->setFlash('success','Cập nhật nhật ký tour thành công.');
        $this->redirect('index.php?c=TourLog&a=index');
    }

    public function destroy(){
        $id=(int)($_POST['id']??0);
        if($id>0){
            $model=new TourLog();
            $model->delete($id);
            $this->setFlash('success','Xóa nhật ký tour thành công.');
        }
        $this->redirect('index.php?c=TourLog&a=index');
    }
}
