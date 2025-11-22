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
            'start_date'  => $_POST['start_date'],
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
            'start_date'  => $_POST['start_date'],
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
        $id = $_GET['id'];
        $this->tourModel->delete($id);

        header('Location: index.php?c=Tour&a=index');
    }
}
