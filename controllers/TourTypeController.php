<?php
class TourTypeController {
    private $model;

    public function __construct()
    {
        $this->model = new TourType();
    }

    public function index()
    {
        $types = $this->model->all();
        include __DIR__ . '/../views/admin/tour_types/index.php';
    }

    public function create()
    {
        include __DIR__ . '/../views/admin/tour_types/create.php';
    }

    public function store()
    {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);

        $this->model->create([
            'name' => $name,
            'description' => $description
        ]);

        header('Location: index.php?c=TourType&a=index');
    }

    public function edit()
    {
        $id = $_GET['id'];
        $type = $this->model->find($id);
        include __DIR__ . '/../views/admin/tour_types/edit.php';
    }

    public function update()
    {
        $id = $_POST['id'];

        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description']
        ];

        $this->model->update($id, $data);

        header('Location: index.php?c=TourType&a=index');
    }

    public function delete()
    {
        $id = $_GET['id'];
        $this->model->delete($id);

        header('Location: index.php?c=TourType&a=index');
    }
}
