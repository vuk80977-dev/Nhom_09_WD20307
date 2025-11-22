<?php
class SupplierController {
    private $model;

    public function __construct()
    {
        $this->model = new Supplier();
    }

    public function index()
    {
        $suppliers = $this->model->all();
        include __DIR__ . '/../views/admin/suppliers/index.php';
    }

    public function create()
    {
        include __DIR__ . '/../views/admin/suppliers/create.php';
    }

    public function store()
    {
        $data = [
            'name'         => trim($_POST['name']),
            'contact_name' => trim($_POST['contact_name']),
            'phone'        => trim($_POST['phone']),
            'email'        => trim($_POST['email']),
            'address'      => trim($_POST['address']),
            'note'         => trim($_POST['note'])
        ];

        $this->model->create($data);
        header('Location: index.php?c=Supplier&a=index');
    }

    public function edit()
    {
        $id = $_GET['id'];
        $supplier = $this->model->find($id);
        include __DIR__ . '/../views/admin/suppliers/edit.php';
    }

    public function update()
    {
        $id = $_POST['id'];

        $data = [
            'name'         => trim($_POST['name']),
            'contact_name' => trim($_POST['contact_name']),
            'phone'        => trim($_POST['phone']),
            'email'        => trim($_POST['email']),
            'address'      => trim($_POST['address']),
            'note'         => trim($_POST['note'])
        ];

        $this->model->update($id, $data);

        header('Location: index.php?c=Supplier&a=index');
    }

    public function delete()
    {
        $id = $_GET['id'];
        $this->model->delete($id);

        header('Location: index.php?c=Supplier&a=index');
    }
}
