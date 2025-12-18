<?php
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Booking.php';

class PaymentController
{
    private function redirect($url){
        header("Location: $url");
        exit;
    }

    private function setFlash($type,$msg){
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION['flash'] = ['type'=>$type,'msg'=>$msg];
    }

    private function takeFlash(){
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }

    public function index(){
        $bookingId = (int)($_GET['booking_id'] ?? 0);
        if ($bookingId <= 0) {
            $this->redirect('index.php?c=Booking&a=index');
        }

        $bookingModel = new Booking();
        $paymentModel = new Payment();

        $booking = $bookingModel->findWithCalc($bookingId);
        if (!$booking) {
            $this->setFlash('danger', 'Booking không tồn tại.');
            $this->redirect('index.php?c=Booking&a=index');
        }

        $payments  = $paymentModel->getByBooking($bookingId);

        $paidTotal = $paymentModel->sumByBooking($bookingId);
        $bookingModel->update($bookingId, ['paid_total' => $paidTotal]);

        $flash = $this->takeFlash();
        $title = "Lịch sử thanh toán Booking #{$bookingId}";
        include __DIR__ . '/../views/admin/payments/index.php';
    }

    public function store(){
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $amount    = (float)($_POST['amount'] ?? 0);

        if ($bookingId <= 0 || $amount <= 0) {
            $this->setFlash('danger', 'Số tiền không hợp lệ.');
            $this->redirect('index.php?c=Payment&a=index&booking_id='.$bookingId);
        }

        $bookingModel = new Booking();
        $paymentModel = new Payment();

        $booking = $bookingModel->find($bookingId);
        if (!$booking) {
            $this->setFlash('danger', 'Booking không tồn tại.');
            $this->redirect('index.php?c=Booking&a=index');
        }

        $total  = (float)($booking['total_price'] ?? 0);
        $paid   = (float)($booking['paid_total'] ?? 0);
        $remain = $total > 0 ? max(0, $total - $paid) : 0;

        if ($total > 0 && $amount > $remain) {
            $this->setFlash(
                'danger',
                'Số tiền vượt quá số còn lại ('.number_format($remain).'đ).'
            );
            $this->redirect('index.php?c=Payment&a=index&booking_id='.$bookingId);
        }

        $paidAt = $_POST['paid_at'] ?? $_POST['pay_date'] ?? date('Y-m-d H:i:s');
        if (strlen($paidAt) === 10) {
            $paidAt .= ' 00:00:00';
        }

        $paymentModel->create([
            'booking_id' => $bookingId,
            'paid_at'    => $paidAt,
            'amount'     => $amount,
            'method'     => $_POST['method'] ?? 'cash',
            'note'       => trim($_POST['note'] ?? '')
        ]);

        $newPaidTotal = $paid + $amount;
        $bookingModel->update($bookingId, ['paid_total' => $newPaidTotal]);

        $this->setFlash('success','Ghi nhận thanh toán thành công.');
        $this->redirect('index.php?c=Payment&a=index&booking_id='.$bookingId);
    }

    public function destroy(){
        $id        = (int)($_POST['id'] ?? 0);
        $bookingId = (int)($_POST['booking_id'] ?? 0);

        if ($id > 0) {
            $paymentModel = new Payment();
            $bookingModel = new Booking();

            $payment = $paymentModel->find($id);
            $amount  = $payment ? (float)$payment['amount'] : 0;

            $paymentModel->delete($id);

            if ($bookingId > 0 && $amount > 0) {
                $booking = $bookingModel->find($bookingId);
                if ($booking) {
                    $newPaid = max(0, (float)$booking['paid_total'] - $amount);
                    $bookingModel->update($bookingId, ['paid_total' => $newPaid]);
                }
            }
        }

        $this->setFlash('success','Đã xóa dòng thanh toán.');
        $this->redirect('index.php?c=Payment&a=index&booking_id='.$bookingId);
    }
}