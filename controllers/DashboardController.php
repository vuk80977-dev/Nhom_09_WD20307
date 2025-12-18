<?php
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../models/Schedule.php';

class DashboardController
{
    public function index()
    {
        $tourModel     = new Tour();
        $bookingModel  = new Booking();
        $customerModel = new Customer();
        $scheduleModel = new Schedule();

        $today = date('Y-m-d');

        $stats = [
            'totalTours'     => $tourModel->countAll(),
            'totalBookings'  => $bookingModel->countAll(),
            'totalCustomers' => $customerModel->countAll(),
            'todayDepart'    => $scheduleModel->countWhere('start_date = ?', [$today]),
        ];

        $recentBookings = $bookingModel->getRecentBookings(5);

        $title = 'Bảng điều khiển';
        include __DIR__ . '/../views/admin/dashboard/index.php';
    }
}