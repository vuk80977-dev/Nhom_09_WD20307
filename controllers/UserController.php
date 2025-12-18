<?php

class UserController
{
    private $userModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
    }

    // ===== Helper: check admin =====
    private function isAdmin(): bool
    {
        // Project bạn đang dùng $_SESSION['user']
        $role = $_SESSION['user']['role'] ?? null;

        // Chấp nhận nhiều kiểu lưu role (tránh lỗi Admin/admin/1)
        return in_array($role, ['admin', 'Admin', 1, '1'], true);
    }

    private function requireLogin()
    {
        if (empty($_SESSION['user'])) {
            header('Location: index.php?c=User&a=login');
            exit;
        }
    }

    private function requireAdmin()
    {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            $_SESSION['error'] = 'Bạn không có quyền.';
            header('Location: index.php?c=Dashboard&a=index');
            exit;
        }
    }

    // ===== Admin: Danh sách người dùng =====
    public function index()
    {
        $this->requireAdmin();
        $users = $this->userModel->all();
        include __DIR__ . '/../views/admin/users/index.php';
    }

    // ===== Register =====
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = (string)($_POST['password'] ?? '');
            $confirm_password = (string)($_POST['confirm_password'] ?? '');

            if ($name === '' || $email === '' || $password === '') {
                $_SESSION['error'] = 'Tên, email và mật khẩu không được để trống';
                header('Location: index.php?c=User&a=register');
                exit;
            }

            if ($password !== $confirm_password) {
                $_SESSION['error'] = 'Mật khẩu xác nhận không khớp';
                header('Location: index.php?c=User&a=register');
                exit;
            }

            $existingUser = $this->userModel->where('email', $email);
            if (!empty($existingUser)) {
                $_SESSION['error'] = 'Email này đã được sử dụng';
                header('Location: index.php?c=User&a=register');
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $this->userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => 'customer',
                'is_active' => 1,
            ]);

            $_SESSION['success'] = 'Đăng ký thành công, vui lòng đăng nhập';
            header('Location: index.php?c=User&a=login');
            exit;
        }

        include __DIR__ . '/../views/admin/users/register.php';
    }

    // ===== Login =====
   public function login()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $_SESSION['error'] = 'Email và mật khẩu không được để trống';
            header('Location: index.php?c=User&a=login');
            exit;
        }

        $user = $this->userModel->where('email', $email);
        if (empty($user)) {
            $_SESSION['error'] = 'Email không tồn tại';
            header('Location: index.php?c=User&a=login');
            exit;
        }

        $u = $user[0];

        if (isset($u['is_active']) && (int)$u['is_active'] !== 1) {
            $_SESSION['error'] = 'Tài khoản đã bị khóa';
            header('Location: index.php?c=User&a=login');
            exit;
        }

        if (!password_verify($password, $u['password'])) {
            $_SESSION['error'] = 'Mật khẩu không chính xác';
            header('Location: index.php?c=User&a=login');
            exit;
        }

        // ✅ CHẶN ROLE KHÔNG PHẢI ADMIN
        if (!in_array($u['role'] ?? '', ['admin', 'Admin', 1, '1'], true)) {
            $_SESSION['error'] = 'Chỉ tài khoản admin mới được đăng nhập.';
            header('Location: index.php?c=User&a=login');
            exit;
        }

        $_SESSION['user'] = $u;
        header('Location: index.php?c=Dashboard&a=index');
        exit;
    }

    include __DIR__ . '/../views/admin/users/login.php';
}

    // ===== Edit profile (user) =====
    public function edit()
    {
        $this->requireLogin();

        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $user = $this->userModel->find($userId);

        if (!$user) {
            die('Người dùng không tồn tại');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = (string)($_POST['password'] ?? '');

            if ($name === '' || $email === '') {
                $_SESSION['error'] = 'Tên và email không được để trống';
                header('Location: index.php?c=User&a=edit');
                exit;
            }

            $data = [
                'name' => $name,
                'email' => $email
            ];

            if ($password !== '') {
                $data['password'] = password_hash($password, PASSWORD_BCRYPT);
            }

            $this->userModel->update($userId, $data);

            $_SESSION['user'] = array_merge($_SESSION['user'], $data);
            $_SESSION['success'] = 'Thông tin tài khoản đã được cập nhật';
            header('Location: index.php?c=User&a=edit');
            exit;
        }

        include __DIR__ . '/../views/admin/users/edit.php';
    }

    // ===== Logout =====
    public function logout()
    {
        session_destroy();
        header('Location: index.php?c=User&a=login');
        exit;
    }

    // ===== Admin: đổi vai trò (nút Lưu) =====
    public function changeRole()
    {
        $this->requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        $role = trim($_POST['role'] ?? '');

        // UI có thể gửi Admin/Khách hoặc admin/customer
        $allowed = ['admin', 'customer', 'Admin', 'Khách'];

        if ($id <= 0 || !in_array($role, $allowed, true)) {
            $_SESSION['error'] = 'Dữ liệu không hợp lệ';
            header('Location: index.php?c=User&a=index');
            exit;
        }

        // Không cho tự đổi quyền chính mình (tùy chọn nhưng nên có)
        if ($id === (int)($_SESSION['user']['id'] ?? -1)) {
            $_SESSION['error'] = 'Không thể tự đổi quyền của chính bạn';
            header('Location: index.php?c=User&a=index');
            exit;
        }

        // Chuẩn hoá để lưu DB
        $map = ['Admin' => 'admin', 'Khách' => 'customer'];
        $role = $map[$role] ?? $role;

        $this->userModel->update($id, ['role' => $role]);

        $_SESSION['success'] = 'Đổi quyền thành công';
        header('Location: index.php?c=User&a=index');
        exit;
    }

    // ===== Admin: khóa/mở khóa (nút Khóa/Mở) =====
    public function toggleStatus()
    {
        $this->requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['error'] = 'ID không hợp lệ';
            header('Location: index.php?c=User&a=index');
            exit;
        }

        // Không cho tự khóa mình
        if ($id === (int)($_SESSION['user']['id'] ?? -1)) {
            $_SESSION['error'] = 'Không thể tự khóa tài khoản của chính bạn';
            header('Location: index.php?c=User&a=index');
            exit;
        }

        $u = $this->userModel->find($id);
        if (!$u) {
            $_SESSION['error'] = 'Không tìm thấy người dùng';
            header('Location: index.php?c=User&a=index');
            exit;
        }

        $cur = (int)($u['is_active'] ?? 1);
        $new = $cur === 1 ? 0 : 1;

        $this->userModel->update($id, ['is_active' => $new]);

        $_SESSION['success'] = $new === 1 ? 'Đã mở khóa tài khoản' : 'Đã khóa tài khoản';
        header('Location: index.php?c=User&a=index');
        exit;
    }
    public function toggleActive()
{
    // gọi lại hàm toggleStatus để khỏi lệch tên action
    return $this->toggleStatus();
}

}
