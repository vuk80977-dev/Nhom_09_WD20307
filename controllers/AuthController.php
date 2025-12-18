<?php
class AuthController {
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = (string)($_POST['password'] ?? '');

            if ($email === '' || $password === '') {
                $_SESSION['error'] = 'Email và mật khẩu không được để trống';
                header('Location: index.php?c=Auth&a=login');
                exit;
            }

            $rows = $this->userModel->where('email', $email);
            if (empty($rows)) {
                $_SESSION['error'] = 'Email không tồn tại';
                header('Location: index.php?c=Auth&a=login');
                exit;
            }

            $user = $rows[0];
            if (!empty($user['is_active']) && (int)$user['is_active'] !== 1) {
                $_SESSION['error'] = 'Tài khoản đang bị khóa';
                header('Location: index.php?c=Auth&a=login');
                exit;
            }

            if (!password_verify($password, $user['password'])) {
                $_SESSION['error'] = 'Mật khẩu không chính xác';
                header('Location: index.php?c=Auth&a=login');
                exit;
            }

            // Login success
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        }

        include __DIR__ . '/../views/auth/login.php';
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            $confirm = (string)($_POST['confirm_password'] ?? '');

            if ($name === '' || $email === '' || $password === '') {
                $_SESSION['error'] = 'Tên, email và mật khẩu không được để trống';
                header('Location: index.php?c=Auth&a=register');
                exit;
            }

            if ($password !== $confirm) {
                $_SESSION['error'] = 'Mật khẩu xác nhận không khớp';
                header('Location: index.php?c=Auth&a=register');
                exit;
            }

            $existing = $this->userModel->where('email', $email);
            if (!empty($existing)) {
                $_SESSION['error'] = 'Email này đã được sử dụng';
                header('Location: index.php?c=Auth&a=register');
                exit;
            }

            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $id = $this->userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => $hashed,
                'role' => 'customer',
                'is_active' => 1,
            ]);

            $_SESSION['success'] = 'Đăng ký thành công, vui lòng đăng nhập';
            header('Location: index.php?c=Auth&a=login');
            exit;
        }

        include __DIR__ . '/../views/auth/register.php';
    }

    public function logout()
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
        header('Location: index.php?c=Auth&a=login');
        exit;
    }
}
