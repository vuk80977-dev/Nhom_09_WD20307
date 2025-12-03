<?php
class TourController {
    private $tourModel;
    private $tourTypeModel;
    private $supplierModel;

    public function __construct()
    {
        $this->tourModel = new Tour();
        $this->tourTypeModel = new TourType();
        $this->supplierModel = new Supplier();
    }

    public function index()
    {
        $tours = $this->tourModel->all();
        include __DIR__ . '/../views/admin/tours/index.php';
    }

    public function create()
    {
        $types = $this->tourTypeModel->all();
        $suppliers = $this->supplierModel->all();

        include __DIR__ . '/../views/admin/tours/create.php';
    }

    public function store()
    {
        $data = [
            'name'        => $_POST['name'],
            'price'       => $_POST['price'],
            'duration'    => $_POST['duration'],
          
            'description' => $_POST['description'],
            'tour_type_id'=> $_POST['tour_type_id'],
            'supplier_id' => $_POST['supplier_id'],
            'status'      => $_POST['status']
        ];

        $this->tourModel->create($data);
        header('Location: index.php?c=Tour&a=index');
    }

    public function edit()
    {
        $id = $_GET['id'];
        $tour = $this->tourModel->find($id);
        $types = $this->tourTypeModel->all();
        $suppliers = $this->supplierModel->all();

        include __DIR__ . '/../views/admin/tours/edit.php';
    }

    public function update()
    {
        $id = $_POST['id'];

        $data = [
            'name'        => $_POST['name'],
            'price'       => $_POST['price'],
            'duration'    => $_POST['duration'],
           
            'description' => $_POST['description'],
            'tour_type_id'=> $_POST['tour_type_id'],
            'supplier_id' => $_POST['supplier_id'],
            'status'      => $_POST['status']
        ];

        $this->tourModel->update($id, $data);

        header('Location: index.php?c=Tour&a=index');
    }

public function delete()
{
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        header('Location: index.php?c=Tour&a=index');
        exit;
    }

    // dùng Schedule model để kiểm tra ràng buộc
    $scheduleModel = new Schedule();
    $count = $scheduleModel->countByTourId($id);

    if ($count > 0) {
        $_SESSION['flash'] = [
            'type' => 'danger',
            'msg'  => "Không thể xóa! Tour còn $count lịch khởi hành."
        ];
        header('Location: index.php?c=Tour&a=index');
        exit;
    }

    // bạn đang dùng tourModel chứ không phải model
    $this->tourModel->delete($id);

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => "Đã xóa tour."
    ];
    header('Location: index.php?c=Tour&a=index');
    exit;
}


}
