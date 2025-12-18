<?php
require_once __DIR__ . '/BaseModel.php';

class Attendance extends BaseModel {
    protected $table = 'attendances';

    /* ========================================================
     *  A. THEO LỊCH (schedule) — cấp độ TỪNG HÀNH KHÁCH
     * ====================================================== */

    /** Lấy roster theo lịch; có thể lọc theo 1 booking cụ thể */
    public function getRosterBySchedule(int $scheduleId, int $bookingId = 0): array {
        $sql = "
            SELECT 
                b.id                    AS booking_id,
                b.status                AS booking_status,
                t.id                    AS traveler_id,
                t.full_name             AS traveler_name,
                t.phone                 AS traveler_phone,
                t.email                 AS traveler_email,
                COALESCE(a.status,'absent') AS attendance_status,
                COALESCE(a.note,'')        AS attendance_note
            FROM bookings b
            JOIN booking_travelers t ON t.booking_id = b.id
            LEFT JOIN attendances a
                   ON a.schedule_id = :sid
                  AND a.traveler_id = t.id
            WHERE b.schedule_id = :sid
        ";
        $params = [':sid' => $scheduleId];

        if ($bookingId > 0) {
            $sql .= " AND b.id = :bid";
            $params[':bid'] = $bookingId;
        }

        $sql .= " ORDER BY b.id DESC, t.id";
        $st = self::$conn->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Thống kê trạng thái theo lịch; có thể lọc theo booking */
    public function countStatusBySchedule(int $scheduleId, int $bookingId = 0): array {
        $sql = "SELECT status, COUNT(*) AS total
                FROM attendances
                WHERE schedule_id = :sid";
        $params = [':sid' => $scheduleId];

        if ($bookingId > 0) {
            $sql .= " AND booking_id = :bid";
            $params[':bid'] = $bookingId;
        }

        $sql .= " GROUP BY status";
        $st = self::$conn->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }

    /**
     * Lưu (upsert) theo lịch, cấp traveler.
     * $rows[] = ['booking_id','traveler_id','status','note']
     * Dùng alias tránh cảnh báo VALUES() (MySQL 8+).
     */
    public function saveForSchedule(int $scheduleId, array $rows): void {
        if (empty($rows)) return;

        $sql = "
            INSERT INTO attendances
                (schedule_id, booking_id, traveler_id, status, note, checked_at)
            VALUES
                (:schedule_id, :booking_id, :traveler_id, :status, :note, NOW())
            AS newvals
            ON DUPLICATE KEY UPDATE
                status     = newvals.status,
                note       = newvals.note,
                checked_at = NOW()
        ";
        $st = self::$conn->prepare($sql);

        foreach ($rows as $r) {
            $st->execute([
                ':schedule_id' => $scheduleId,
                ':booking_id'  => (int)$r['booking_id'],
                ':traveler_id' => (int)$r['traveler_id'],
                ':status'      => $r['status'] ?? 'absent',
                ':note'        => $r['note']   ?? '',
            ]);
        }
    }

    /* ========================================================
     *  B. THEO CHECKPOINT (booking + checkpoint)
     * ====================================================== */

    /** Lấy roster theo CHECKPOINT của 1 booking */
    public function getRosterByCheckpoint(int $bookingId, int $checkpointId): array {
        $sql = "
            SELECT 
                t.id        AS traveler_id,
                t.full_name AS traveler_name,
                t.phone     AS traveler_phone,
                t.email     AS traveler_email,
                COALESCE(ac.status,'present') AS attendance_status,
                COALESCE(ac.note,'')          AS attendance_note
            FROM booking_travelers t
            LEFT JOIN attendance_checkpoints ac
                   ON ac.traveler_id = t.id
                  AND ac.checkpoint_id = :cpid
            WHERE t.booking_id = :bid
            ORDER BY t.id
        ";
        $st = self::$conn->prepare($sql);
        $st->execute([':cpid' => $checkpointId, ':bid' => $bookingId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Thống kê theo CHECKPOINT của 1 booking */
    public function countStatusByCheckpoint(int $bookingId, int $checkpointId): array {
        $sql = "
            SELECT ac.status, COUNT(*) AS total
            FROM booking_travelers t
            LEFT JOIN attendance_checkpoints ac
                   ON ac.traveler_id = t.id
                  AND ac.checkpoint_id = :cpid
            WHERE t.booking_id = :bid
            GROUP BY ac.status
        ";
        $st = self::$conn->prepare($sql);
        $st->execute([':cpid' => $checkpointId, ':bid' => $bookingId]);
        return $st->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }
}