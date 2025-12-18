<?php
/**
 * Checkpoint model
 * Quản lý các điểm trong lịch trình của 1 booking:
 * - depart  : Lúc đi
 * - stop    : Các điểm dừng giữa hành trình (do bạn thêm)
 * - final   : Điểm cuối tour
 * - return  : Lúc về
 *
 * Bảng: tour_checkpoints
 *  - id BIGINT PK
 *  - booking_id INT
 *  - name VARCHAR(160)
 *  - type ENUM('depart','stop','final','return')
 *  - plan_time DATETIME
 *  - seq INT
 *  - created_at DATETIME
 */
class Checkpoint
{
    /** @var PDO */
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo instanceof PDO) {
            $this->pdo = $pdo;
        } elseif (function_exists('db')) {
            $this->pdo = db();
        } else {
            throw new RuntimeException('Checkpoint: cần PDO (truyền vào constructor hoặc cung cấp helper db()).');
        }
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /** Tạo 3 checkpoint mặc định (nếu booking chưa có) */
    public function ensureDefaults(int $bookingId): void
    {
        if ($bookingId <= 0) return;

        $st = $this->pdo->prepare("SELECT COUNT(*) FROM tour_checkpoints WHERE booking_id=?");
        $st->execute([$bookingId]);
        if ((int)$st->fetchColumn() > 0) return;

        $ins = $this->pdo->prepare(
            "INSERT INTO tour_checkpoints (booking_id, name, type, seq)
             VALUES (?,?,?,?)"
        );
        // depart (seq 0), final (9998), return (9999)
        $ins->execute([$bookingId, 'Lúc đi', 'depart', 0]);
        $ins->execute([$bookingId, 'Điểm cuối tour', 'final', 9998]);
        $ins->execute([$bookingId, 'Lúc về', 'return', 9999]);
    }

    /** Lấy toàn bộ checkpoint theo booking (sắp theo seq, id) */
    public function listByBooking(int $bookingId): array
    {
        $st = $this->pdo->prepare(
            "SELECT * FROM tour_checkpoints
             WHERE booking_id=?
             ORDER BY seq, id"
        );
        $st->execute([$bookingId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * ✅ Trả riêng danh sách các điểm dừng (type='stop') cho 1 booking,
     * kèm trường tách sẵn cho UI: date (Y-m-d), time (H:i)
     */
    public function listStopsByBooking(int $bookingId): array
    {
        $st = $this->pdo->prepare(
            "SELECT id, name, plan_time, seq
             FROM tour_checkpoints
             WHERE booking_id=? AND type='stop'
             ORDER BY seq, id"
        );
        $st->execute([$bookingId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$r) {
            $r['date'] = $r['time'] = '';
            if (!empty($r['plan_time'])) {
                $ts = strtotime($r['plan_time']);
                if ($ts) {
                    $r['date'] = date('Y-m-d', $ts);
                    $r['time'] = date('H:i',   $ts);
                }
            }
        }
        return $rows;
    }

    /**
     * Lưu lại toàn bộ danh sách các điểm dừng (type='stop') cho 1 booking:
     * - Xoá hết stop cũ
     * - Insert danh sách stop mới với seq 20,30,40...
     * - $stops: mỗi phần tử có keys: name, plan_time (từ <input type="datetime-local">) HOẶC name, date, time
     */
    public function saveStops(int $bookingId, array $stops): void
    {
        if ($bookingId <= 0) return;

        $this->pdo->beginTransaction();
        try {
            $del = $this->pdo->prepare("DELETE FROM tour_checkpoints WHERE booking_id=? AND type='stop'");
            $del->execute([$bookingId]);

            $ins = $this->pdo->prepare(
                "INSERT INTO tour_checkpoints (booking_id, name, type, plan_time, seq)
                 VALUES (?,?,?,?,?)"
            );
            $seq = 10;
            foreach ($stops as $s) {
                $name = trim($s['name'] ?? '');
                if ($name === '') continue;

                // chấp nhận 2 định dạng đầu vào:
                // 1) plan_time: 'YYYY-MM-DDTHH:MM'
                // 2) date + time: 'YYYY-MM-DD' + 'HH:MM'
                $planTime = $s['plan_time'] ?? null;
                if (!$planTime) {
                    $date = trim($s['date'] ?? '');
                    $time = trim($s['time'] ?? '');
                    if ($date !== '') {
                        $planTime = $date . ' ' . ($time !== '' ? $time : '00:00');
                    }
                } elseif (strpos($planTime, 'T') !== false) {
                    $planTime = str_replace('T', ' ', $planTime);
                }

                $seq += 10;
                $ins->execute([$bookingId, $name, 'stop', $planTime ?: null, $seq]);
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}