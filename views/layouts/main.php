<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title><?= isset($title) ? $title . ' | Admin Tour' : 'Admin Tour' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icon (Bootstrap Icons) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f6fa;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .admin-wrapper {
            min-height: 100vh;
            display: flex;
        }

        .admin-sidebar {
            width: 250px;
            background: #0f172a;
            color: #e5e7eb;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.3);
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .sidebar-brand span.logo {
            width: 32px;
            height: 32px;
            border-radius: .75rem;
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            font-size: 1.1rem;
        }

        .sidebar-brand .brand-text {
            font-weight: 600;
            font-size: 1rem;
        }

        .sidebar-menu {
            flex: 1;
            padding: 1rem .75rem 1.5rem;
            overflow-y: auto;
        }

        .sidebar-section-title {
            font-size: .75rem;
            text-transform: uppercase;
            color: #9ca3af;
            padding: .75rem .75rem .25rem;
            letter-spacing: .08em;
        }

        .nav-link-main {
            border-radius: .5rem;
            padding: .5rem .75rem;
            font-size: .9rem;
            color: #e5e7eb;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .nav-link-main i {
            font-size: 1rem;
        }

        .nav-link-main:hover {
            background: rgba(148, 163, 184, .25);
            color: #fff;
            text-decoration: none;
        }

        .nav-link-main.active {
            background: linear-gradient(135deg, #22c55e, #0ea5e9);
            color: #fff;
        }

        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .admin-topbar {
            height: 60px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .topbar-title {
            font-weight: 600;
            font-size: 1.05rem;
        }

        .breadcrumb-custom {
            font-size: .85rem;
            color: #6b7280;
        }

        .admin-content {
            padding: 1.5rem;
        }

        .admin-footer {
            margin-top: auto;
            padding: .75rem 1.5rem;
            font-size: .8rem;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        @media (max-width: 992px) {
            .admin-sidebar {
                width: 220px;
            }
        }

        @media (max-width: 768px) {
            .admin-wrapper {
                flex-direction: column;
            }
            .admin-sidebar {
                width: 100%;
                flex-direction: row;
                overflow-x: auto;
                height: auto;
            }
            .sidebar-menu {
                display: flex;
            }
            .sidebar-section-title {
                display: none;
            }
        }
    </style>
</head>
<body>

<?php
$currentController = $_GET['c'] ?? 'Dashboard';
$currentAction     = $_GET['a'] ?? 'index';

?>

<div class="admin-wrapper">

    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <span class="logo">
                <i class="bi bi-globe2"></i>
            </span>
            <div>
                <div class="brand-text">Tour Management</div>
                <div style="font-size:.75rem;color:#9ca3af;">Admin Dashboard</div>
            </div>
        </div>

        <nav class="sidebar-menu">

            <!-- Tổng quan -->
            <div class="sidebar-section-title">Tổng quan</div>
            <a href="index.php?c=Dashboard&a=index"
               class="nav-link-main <?= isActive('Dashboard', $currentController) ?>">
                <i class="bi bi-speedometer2"></i> Bảng điều khiển
            </a>

            <!-- Tour & lịch trình -->
            <div class="sidebar-section-title">Tour & lịch trình</div>
            <a href="index.php?c=TourType&a=index"
               class="nav-link-main <?= isActive('TourType', $currentController) ?>">
                <i class="bi bi-tags"></i> Danh mục tour
            </a>
            <a href="index.php?c=Tour&a=index"
               class="nav-link-main <?= isActive('Tour', $currentController) ?>">
                <i class="bi bi-map"></i> Quản lý tour
            </a>
            <a href="index.php?c=Supplier&a=index"
               class="nav-link-main <?= isActive('Supplier', $currentController) ?>">
                <i class="bi bi-building"></i> Nhà cung cấp
            </a>
            <a href="index.php?c=Guide&a=index"
            class="nav-link-main <?= isActive('Guide', $currentController) ?>">
                <i class="bi bi-person-badge"></i> Hướng dẫn viên
            </a>

            <a href="index.php?c=Schedule&a=index"
            class="nav-link-main <?= isActive('Schedule', $currentController) ?>">
                <i class="bi bi-calendar-week"></i> Lịch khởi hành
            </a>


            <!-- Khách hàng & booking -->
            <div class="sidebar-section-title">Khách hàng & booking</div>
            <a href="index.php?c=Booking&a=index"
               class="nav-link-main <?= isActive('Booking', $currentController) ?>">
                <i class="bi bi-journal-check"></i> Quản lý booking
            </a>
                <a href="index.php?c=Customer&a=index"
              class="nav-link-main <?= isActive('Customer', $currentController) ?>">
                <i class="bi bi-people"></i> Quản lý khách hàng
            </a>
            <a href="index.php?c=Attendance&a=index"
            class="nav-link-main <?= isActive('Attendance', $currentController) ?>">
            <i class="bi bi-clipboard-check"></i> Điểm danh theo lịch trình
            </a>


            <!-- Hệ thống -->
            <a href="index.php?c=TourLog&a=index"
            class="nav-link-main <?= isActive('TourLog', $currentController) ?>">
            <i class="bi bi-journal-text"></i> Nhật ký tour
            </a>

            <div class="sidebar-section-title">Hệ thống</div>
            <a href="index.php?c=User&a=index"
               class="nav-link-main <?= isActive('User', $currentController) ?>">
                <i class="bi bi-shield-lock"></i> Quản lý tài khoản
            </a>
            <a href="#"
               class="nav-link-main">
                <i class="bi bi-diagram-3"></i> Phân quyền
            </a>
            <a href="#"
               class="nav-link-main">
                <i class="bi bi-gear"></i> Cài đặt hệ thống
            </a>
            <a href="#"
               class="nav-link-main">
                <i class="bi bi-clock-history"></i> Nhật ký hệ thống
            </a>
        </nav>
    </aside>

    <!-- MAIN -->
    <div class="admin-main">

        <!-- TOPBAR -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <div class="topbar-title">
                    <?= $title ?? 'Bảng điều khiển' ?>
                </div>
                <div class="breadcrumb-custom">
                    Admin / <?= $title ?? 'Bảng điều khiển' ?>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <form class="d-none d-md-flex" role="search">
                    <input class="form-control form-control-sm" type="search" placeholder="Tìm kiếm..." aria-label="Search">
                </form>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i> Admin
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Thông tin cá nhân</a></li>
                        <li><a class="dropdown-item" href="#">Đổi mật khẩu</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="index.php?c=Auth&a=logout">Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- CONTENT -->
        <main class="admin-content">
            <?= $content ?? '' ?>
        </main>

        <!-- FOOTER -->
        <footer class="admin-footer d-flex justify-content-between align-items-center">
            <span>© <?= date('Y') ?> Tour Management System</span>
           
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
