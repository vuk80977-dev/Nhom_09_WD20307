<?php
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Customer.php';

class DashboardController
{
    public function index()
    {
        $tourModel     = new Tour();
        $bookingModel  = new Booking();
        $customerModel = new Customer();

        // Thống kê (dùng COUNT(*) ở DB)
        $stats = [
            'totalTours'     => $tourModel->countAll(),
            'totalBookings'  => $bookingModel->countAll(),
            'totalCustomers' => $customerModel->countAll(),
            // Đổi 'start_date' cho đúng tên cột ngày khởi hành nếu DB của bạn khác
            'todayDepart'    => $tourModel->countWhere('start_date = ?', [date('Y-m-d')]),
        ];

        // Booking gần đây (đã có trong Booking model)
        $recentBookings = $bookingModel->getRecentBookings(5);

        // Gọi view (view sẽ include layout 1 lần, đừng include lặp lại ở controller)
        include __DIR__ . '/../views/admin/dashboard/index.php';
    }
}
