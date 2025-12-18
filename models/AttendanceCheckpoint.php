<?php
/**
 * AttendanceCheckpoint model
 * Điểm danh khách theo từng checkpoint của 1 booking.
 */
class AttendanceCheckpoint
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
            throw new RuntimeException('AttendanceCheckpoint: cần PDO (truyền vào constructor hoặc cung cấp helper db()).');
        }
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Lấy roster khách của 1 booking + trạng thái điểm danh hiện có cho 1 checkpoint.
     * Trả về: traveler_id, full_name, cccd, status, note, marked_at
     */
    public function listForCheckpoint(int $bookingId, int $checkpointId): array
    {
        $sql = "SELECT t.id AS traveler_id,
                       t.full_name,
                       t.cccd,
                       a.status,
                       a.note,
                       a.marked_at
                FROM booking_travelers t
                LEFT JOIN attendance_checkpoints a
                  ON a.traveler_id = t.id
                 AND a.checkpoint_id = ?
                WHERE t.booking_id = ?
                ORDER BY t.id";
        $st = $this->pdo->prepare($sql);
        $st->execute([$checkpointId, $bookingId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Lưu điểm danh cho 1 checkpoint.
     */
    public function saveForCheckpoint(int $bookingId, int $checkpointId, array $rows, ?int $userId): void
    {
        if ($bookingId <= 0 || $checkpointId <= 0 || empty($rows)) return;

        $sql = "INSERT INTO attendance_checkpoints
                  (booking_id, checkpoint_id, traveler_id, status, note, marked_by, marked_at)
                VALUES (?,?,?,?,?,?,NOW())
                ON DUPLICATE KEY UPDATE
                  status=VALUES(status),
                  note=VALUES(note),
                  marked_by=VALUES(marked_by),
                  marked_at=NOW()";
        $st = $this->pdo->prepare($sql);

        foreach ($rows as $r) {
            $travelerId = (int)($r['traveler_id'] ?? 0);
            if ($travelerId <= 0) continue;

            $status = $r['status'] ?? 'present';
            if (!in_array($status, ['present','absent','late','left_early'], true)) {
                $status = 'present';
            }
            $note = trim($r['note'] ?? '');

            $st->execute([
                $bookingId,
                $checkpointId,
                $travelerId,
                $status,
                $note !== '' ? $note : null,
                $userId
            ]);
        }
    }
}