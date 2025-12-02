<?php
require_once __DIR__ . '/BaseModel.php';

class Payment extends BaseModel
{
    protected $table = 'payments';

    // Lấy danh sách thanh toán theo booking (mới nhất trước)
    public function getByBooking($bookingId)
    {
        $sql = "SELECT *
                FROM {$this->table}
                WHERE booking_id = :bid
                ORDER BY paid_at DESC, id DESC";
        $stmt = self::$conn->prepare($sql);
        $stmt->execute([':bid' => (int)$bookingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tính tổng tiền đã thanh toán theo booking
    public function sumByBooking($bookingId)
    {
        $sql = "SELECT COALESCE(SUM(amount),0)
                FROM {$this->table}
                WHERE booking_id = :bid
                  AND status = 'paid'";
        $stmt = self::$conn->prepare($sql);
        $stmt->execute([':bid' => (int)$bookingId]);
        return (float)$stmt->fetchColumn();
    }

    // (Tuỳ chọn) Lấy 1 dòng thanh toán
    public function findOne($id)
    {
        return $this->find($id);
    }
}
