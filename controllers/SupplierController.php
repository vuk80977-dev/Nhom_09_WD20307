<?php
require_once __DIR__ . '/../models/Supplier.php';

class SupplierController {
    private $model;

    public function __construct()
    {
        $this->model = new Supplier();
    }

    private function redirect($url){
        header("Location: $url"); exit;
    }

    private function setFlash($type,$msg){
        if(session_status()!==PHP_SESSION_ACTIVE) session_start();
        $_SESSION['flash'] = ['type'=>$type,'msg'=>$msg];
    }

    private function takeFlash(){
        if(session_status()!==PHP_SESSION_ACTIVE) session_start();
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }

    // GET: index.php?c=Supplier&a=index
    public function index()
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $filters = [
            'q'      => $_GET['q'] ?? '',
            'type'   => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];

        // dùng paginate thay vì all()
        $data  = $this->model->paginate($page, $perPage, $filters);
        $flash = $this->takeFlash();
        $title = "Nhà cung cấp";

        include __DIR__ . '/../views/admin/suppliers/index.php';
    }

    // GET: create
    public function create()
    {
        $flash = $this->takeFlash();
        $title = "Thêm nhà cung cấp";

        // set default để form không bị undefined
        $supplier = [
            'name'         => '',
            'type'         => 'other',
            'contact_name' => '',
            'phone'        => '',
            'email'        => '',
            'address'      => '',
            'status'       => 'active',
            'note'         => '',
        ];

        include __DIR__ . '/../views/admin/suppliers/create.php';
    }

    // POST: store
    public function store()
    {
        $data = [
            'name'         => trim($_POST['name'] ?? ''),
            'type'         => $_POST['type'] ?? 'other',
            'contact_name' => trim($_POST['contact_name'] ?? ''),
            'phone'        => trim($_POST['phone'] ?? ''),
            'email'        => trim($_POST['email'] ?? ''),
            'address'      => trim($_POST['address'] ?? ''),
            'status'       => $_POST['status'] ?? 'active',
            'note'         => trim($_POST['note'] ?? '')
        ];

        if($data['name']===''){
            $this->setFlash('danger','Tên nhà cung cấp không được để trống.');
            $this->redirect('index.php?c=Supplier&a=create');
        }

        $this->model->create($data);
        $this->setFlash('success','Thêm nhà cung cấp thành công.');
        $this->redirect('index.php?c=Supplier&a=index');
    }

    // GET: edit&id=
    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        if($id<=0) $this->redirect('index.php?c=Supplier&a=index');

        $supplier = $this->model->find($id);
        if(!$supplier){
            $this->setFlash('danger','Nhà cung cấp không tồn tại.');
            $this->redirect('index.php?c=Supplier&a=index');
        }

        $flash = $this->takeFlash();
        $title = "Sửa nhà cung cấp";

        include __DIR__ . '/../views/admin/suppliers/edit.php';
    }

    // POST: update
    public function update()
    {
        $id = (int)($_POST['id'] ?? 0);
        if($id<=0) $this->redirect('index.php?c=Supplier&a=index');

        $data = [
            'name'         => trim($_POST['name'] ?? ''),
            'type'         => $_POST['type'] ?? 'other',
            'contact_name' => trim($_POST['contact_name'] ?? ''),
            'phone'        => trim($_POST['phone'] ?? ''),
            'email'        => trim($_POST['email'] ?? ''),
            'address'      => trim($_POST['address'] ?? ''),
            'status'       => $_POST['status'] ?? 'active',
            'note'         => trim($_POST['note'] ?? '')
        ];

        if($data['name']===''){
            $this->setFlash('danger','Tên nhà cung cấp không được để trống.');
            $this->redirect('index.php?c=Supplier&a=edit&id='.$id);
        }

        $this->model->update($id, $data);
        $this->setFlash('success','Cập nhật nhà cung cấp thành công.');
        $this->redirect('index.php?c=Supplier&a=index');
    }

    // GET/POST: delete 
    public function delete()
{
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) return;

    // check tour liên quan
    $count = self::$conn->query("SELECT COUNT(*) FROM tours WHERE supplier_id=$id")->fetchColumn();

    if ($count > 0) {
        $this->setFlash("danger", "Không thể xóa! Nhà cung cấp đang được sử dụng cho $count tour.");
        $this->redirect("index.php?c=Supplier&a=index");
    }

    $this->model->delete($id);
    $this->setFlash("success", "Đã xóa nhà cung cấp.");
    $this->redirect("index.php?c=Supplier&a=index");
}

}
