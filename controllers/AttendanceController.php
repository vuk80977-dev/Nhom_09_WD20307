<?php

require_once __DIR__ . '/../models/Attendance.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../models/Tour.php';
require_once __DIR__ . '/../models/Checkpoint.php';
require_once __DIR__ . '/../models/AttendanceCheckpoint.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Customer.php';

class AttendanceController
{
    private function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    private function setFlash(string $type, string $msg): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }

    private function takeFlash(): ?array {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }

    private function getPdo(): PDO {
        if (function_exists('db')) {
            /** @var PDO $pdo */
            $pdo = db();
            return $pdo;
        }
        $host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $name = defined('DB_NAME') ? DB_NAME : 'mvc_tour';
        $user = defined('DB_USER') ? DB_USER : 'root';
        $pass = defined('DB_PASS') ? DB_PASS : '';
        $port = defined('DB_PORT') ? DB_PORT : 3306;

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }

    public function index(): void {
        $scheduleModel = new Schedule();
        $schedules = $scheduleModel->getAllWithTour();

        $flash = $this->takeFlash();
        $title = "Điểm danh theo lịch trình";
        include __DIR__ . '/../views/admin/attendances/index.php';
    }

    public function show(): void {
        $scheduleId = (int)($_GET['schedule_id'] ?? 0);
        if ($scheduleId <= 0) $this->redirect('index.php?c=Attendance&a=index');

        $scheduleModel   = new Schedule();
        $attendanceModel = new Attendance();
        $pdo = $this->getPdo();

        $schedule = $scheduleModel->findWithTourGuide($scheduleId);
        if (!$schedule) {
            $this->setFlash('danger', 'Lịch khởi hành không tồn tại.');
            $this->redirect('index.php?c=Attendance&a=index');
        }

        $bookingId    = (int)($_GET['booking_id'] ?? 0);
        $checkpointId = (int)($_GET['checkpoint_id'] ?? 0);

        $bkStmt = $pdo->prepare("SELECT id FROM bookings WHERE schedule_id = ? ORDER BY id DESC");
        $bkStmt->execute([$scheduleId]);
        $bookingList = $bkStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $checkpointList = [];
        if ($bookingId > 0) {
            $st = $pdo->prepare("
                SELECT id, name 
                FROM tour_checkpoints 
                WHERE booking_id = ? 
                ORDER BY id
            ");
            $st->execute([$bookingId]);
            $checkpointList = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        if ($bookingId > 0 && $checkpointId > 0) {
            $roster = $attendanceModel->getRosterByCheckpoint($bookingId, $checkpointId);
            $stats  = $attendanceModel->countStatusByCheckpoint($bookingId, $checkpointId);
            $mode   = 'checkpoint';
        } else {
            $roster = $attendanceModel->getRosterBySchedule($scheduleId, $bookingId);
            $stats  = $attendanceModel->countStatusBySchedule($scheduleId, $bookingId);
            $mode   = 'schedule';
        }

        $flash = $this->takeFlash();
        $title = "Điểm danh lịch #{$scheduleId}";
        include __DIR__ . '/../views/admin/attendances/form.php';
    }

    public function store(): void {
        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        if ($scheduleId <= 0) $this->redirect('index.php?c=Attendance&a=index');

        $rows         = [];
        $bookingIds   = $_POST['booking_id']   ?? [];
        $travelerIds  = $_POST['traveler_id']  ?? [];
        $statuses     = $_POST['status']       ?? [];
        $notes        = $_POST['note']         ?? [];

        foreach ($bookingIds as $i => $bid) {
            $rows[] = [
                'booking_id'  => (int)$bid,
                'traveler_id' => (int)($travelerIds[$i] ?? 0),
                'status'      => $statuses[$i] ?? 'absent',
                'note'        => trim($notes[$i] ?? ''),
            ];
        }

        (new Attendance())->saveForSchedule($scheduleId, $rows);
        $this->setFlash('success', 'Lưu điểm danh (theo lịch) thành công.');
        $this->redirect('index.php?c=Attendance&a=show&schedule_id=' . $scheduleId);
    }

    public function checkpoints(): void {
        $bookingId = (int)($_GET['booking_id'] ?? 0);
        if ($bookingId <= 0) {
            $this->setFlash('danger', 'Thiếu booking_id.');
            $this->redirect('index.php?c=Booking&a=index');
        }

        $pdo        = $this->getPdo();
        $cpModel    = new Checkpoint($pdo);
        $bookingM   = new Booking();
        $customerM  = new Customer();

        $booking  = $bookingM->find($bookingId) ?: [];
        $customer = [];
        if (!empty($booking['customer_id'])) {
            $customer = $customerM->find((int)$booking['customer_id']) ?: [];
        }

        $cpModel->ensureDefaults($bookingId);

        $stops = $cpModel->listStopsByBooking($bookingId) ?? [];

        $flash = $this->takeFlash();
        $title = 'Điểm lịch trình';
        include __DIR__ . '/../views/admin/attendances/checkpoints.php';
    }

    public function checkpoints_save(): void {
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        if ($bookingId <= 0) {
            $this->setFlash('danger','Thiếu booking_id.');
            $this->redirect('index.php?c=Booking&a=index');
        }

        $stops   = $_POST['stops'] ?? [];
        $cpModel = new Checkpoint($this->getPdo());
        try {
            $cpModel->saveStops($bookingId, $stops);
            $this->setFlash('success', 'Đã lưu danh sách điểm dừng.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }
        $this->redirect('index.php?c=Attendance&a=checkpoints&booking_id=' . $bookingId);
    }

    public function attendance_checkpoint(): void {
        $bookingId    = (int)($_GET['booking_id'] ?? 0);
        $checkpointId = (int)($_GET['checkpoint_id'] ?? 0);
        if ($bookingId <= 0 || $checkpointId <= 0) {
            $this->setFlash('danger', 'Thiếu booking_id hoặc checkpoint_id.');
            $this->redirect('index.php?c=Booking&a=index');
        }

        $pdo  = $this->getPdo();
        $acM  = new AttendanceCheckpoint($pdo);

        $rows = $acM->listForCheckpoint($bookingId, $checkpointId) ?? [];

        $booking = ['id' => $bookingId];
        $stmt = $pdo->prepare("
            SELECT b.id, t.name AS tour_name
            FROM bookings b
            LEFT JOIN tours t ON t.id = b.tour_id
            WHERE b.id = ?
        ");
        $stmt->execute([$bookingId]);
        if ($bk = $stmt->fetch(PDO::FETCH_ASSOC)) $booking = $bk;

        $checkpoint = ['id' => $checkpointId, 'name' => 'Checkpoint'];
        $st = $pdo->prepare("SELECT id, name FROM tour_checkpoints WHERE id = ?");
        $st->execute([$checkpointId]);
        if ($cp = $st->fetch(PDO::FETCH_ASSOC)) $checkpoint = $cp;

        $flash = $this->takeFlash();
        $title = 'Điểm danh checkpoint';
        $travelers = $rows;
        include __DIR__ . '/../views/admin/attendances/checkpoint_form.php';
    }

    public function attendance_checkpoint_store(): void {
        $bookingId    = (int)($_POST['booking_id'] ?? 0);
        $checkpointId = (int)($_POST['checkpoint_id'] ?? 0);
        if ($bookingId <= 0 || $checkpointId <= 0) {
            $this->setFlash('danger','Thiếu booking_id hoặc checkpoint_id.');
            $this->redirect('index.php?c=Booking&a=index');
        }

        $rows   = $_POST['rows'] ?? [];
        $userId = $_SESSION['user_id'] ?? null;

        $pdo = $this->getPdo();
        $acM = new AttendanceCheckpoint($pdo);

        try {
            $acM->saveForCheckpoint($bookingId, $checkpointId, $rows, $userId);

            $stmt = $pdo->prepare("SELECT schedule_id FROM bookings WHERE id = ?");
            $stmt->execute([$bookingId]);
            $scheduleId = (int)$stmt->fetchColumn();

            if ($scheduleId > 0 && !empty($rows)) {
                $map = [
                    'Đúng giờ'   => 'present',
                    'Có mặt'     => 'present',
                    'Đi muộn'    => 'late',
                    'Vắng'       => 'absent',
                    'Về sớm'     => 'left_early',
                    'present'    => 'present',
                    'late'       => 'late',
                    'absent'     => 'absent',
                    'left_early' => 'left_early',
                ];

                $scheduleRows = [];
                foreach ($rows as $r) {
                    $scheduleRows[] = [
                        'booking_id'  => $bookingId,
                        'traveler_id' => (int)($r['traveler_id'] ?? 0),
                        'status'      => $map[$r['status'] ?? 'present'] ?? 'present',
                        'note'        => trim($r['note'] ?? ''),
                    ];
                }
                (new Attendance())->saveForSchedule($scheduleId, $scheduleRows);
            }

            $this->setFlash('success', 'Đã lưu điểm danh.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Lỗi: ' . $e->getMessage());
        }

        $this->redirect(
            'index.php?c=Attendance&a=attendance_checkpoint&booking_id='
            . $bookingId . '&checkpoint_id=' . $checkpointId
        );
    }
}