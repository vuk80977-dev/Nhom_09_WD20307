<?php
class UserController {
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // Hiển thị danh sách người dùng (admin)
    public function index()
    {
        $users = $this->userModel->all();
        include __DIR__ . '/../views/admin/users/index.php';
    }

    // Đăng ký tài khoản
    public function register()
    {
        // Kiểm tra xem form đã gửi hay chưa
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Kiểm tra validation
            if (empty($name) || empty($email) || empty($password)) {
                $_SESSION['error'] = 'Tên, email và mật khẩu không được để trống';
                header('Location: index.php?c=User&a=register');
                exit;
            }

            if ($password !== $confirm_password) {
                $_SESSION['error'] = 'Mật khẩu xác nhận không khớp';
                header('Location: index.php?c=User&a=register');
                exit;
            }

            // Kiểm tra xem email đã tồn tại chưa
            $existingUser = $this->userModel->where('email', $email);
            if (!empty($existingUser)) {
                $_SESSION['error'] = 'Email này đã được sử dụng';
                header('Location: index.php?c=User&a=register');
                exit;
            }

            // Mã hóa mật khẩu
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Lưu người dùng mới
            $this->userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => 'customer', // role mặc định là customer
                'is_active' => 1, // mặc định là active
            ]);

            $_SESSION['success'] = 'Đăng ký thành công, vui lòng đăng nhập';
            header('Location: index.php?c=User&a=login');
            exit;
        }

        include __DIR__ . '/../views/admin/users/register.php';
    }

    // Đăng nhập
    public function login()
    {
        // Kiểm tra xem form đăng nhập đã gửi chưa
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Kiểm tra email và mật khẩu
            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Email và mật khẩu không được để trống';
                header('Location: index.php?c=User&a=login');
                exit;
            }

            // Tìm người dùng theo email
            $user = $this->userModel->where('email', $email);
            if (empty($user)) {
                $_SESSION['error'] = 'Email không tồn tại';
                header('Location: index.php?c=User&a=login');
                exit;
            }

            // Kiểm tra mật khẩu
            if (!password_verify($password, $user[0]['password'])) {
                $_SESSION['error'] = 'Mật khẩu không chính xác';
                header('Location: index.php?c=User&a=login');
                exit;
            }

            // Đăng nhập thành công
            $_SESSION['user'] = $user[0]; // Lưu thông tin người dùng vào session
            header('Location: index.php?c=Dashboard&a=index'); // Chuyển đến bảng điều khiển
            exit;
        }

        include __DIR__ . '/../views/admin/users/login.php';
    }

    // Sửa thông tin tài khoản
    public function edit()
    {
        $userId = $_SESSION['user']['id'];
        $user = $this->userModel->find($userId);

        // Kiểm tra nếu người dùng không tồn tại
        if (!$user) {
            die('Người dùng không tồn tại');
        }

        // Cập nhật thông tin tài khoản nếu form được gửi
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Kiểm tra validation
            if (empty($name) || empty($email)) {
                $_SESSION['error'] = 'Tên và email không được để trống';
                header('Location: index.php?c=User&a=edit');
                exit;
            }

            if (!empty($password)) {
                $password = password_hash($password, PASSWORD_BCRYPT);
                $data['password'] = $password;
            }

            // Cập nhật thông tin
            $data['name'] = $name;
            $data['email'] = $email;

            $this->userModel->update($userId, $data);

            $_SESSION['user'] = array_merge($_SESSION['user'], $data); // Cập nhật session
            $_SESSION['success'] = 'Thông tin tài khoản đã được cập nhật';
            header('Location: index.php?c=User&a=edit');
            exit;
        }

        include __DIR__ . '/../views/admin/users/edit.php';
    }

    // Đăng xuất
    public function logout()
    {
        session_destroy(); // Xóa tất cả session
        header('Location: index.php?c=User&a=login');
        exit;
    }
}openssl_free_key
