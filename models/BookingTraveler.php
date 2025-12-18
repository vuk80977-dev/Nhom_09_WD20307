<?php
class BookingTraveler
{
    /** @var PDO */
    private $pdo; // bỏ typed property cho tương thích PHP 7.x/8.x

    /**
     * Constructor: truyền PDO hoặc dùng helper db() nếu có.
     */
    public function __construct(?PDO $pdo = null)
    {
        if ($pdo instanceof PDO) {
            $this->pdo = $pdo;
        } elseif (function_exists('db')) {
            $this->pdo = db(); // dùng helper chung
        } else {
            throw new RuntimeException('BookingTraveler: cần PDO (truyền vào constructor hoặc cung cấp helper db()).');
        }
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /** Lấy toàn bộ khách theo booking */
    public function allByBooking(int $bookingId): array
    {
        $st = $this->pdo->prepare(
            "SELECT *
             FROM booking_travelers
             WHERE booking_id = ?
             ORDER BY id"
        );
        $st->execute([$bookingId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Đếm số khách trong 1 booking */
    public function countByBooking(int $bookingId): int
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM booking_travelers WHERE booking_id=?");
        $st->execute([$bookingId]);
        return (int)$st->fetchColumn();
    }

    /** Tạo 1 khách */
    public function create(int $bookingId, array $data): int
    {
        $sql = "INSERT INTO booking_travelers
                (booking_id, full_name, dob, gender, cccd, cccd_issue_date, cccd_issue_place,
                 phone, email, address, type, note)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
        $st = $this->pdo->prepare($sql);
        $st->execute([
            $bookingId,
            trim($data['full_name'] ?? ''),
            $this->nullOr($data['dob'] ?? null),
            $this->nullOr($data['gender'] ?? null),
            $this->sanitizeCccd($data['cccd'] ?? null),
            $this->nullOr($data['cccd_issue_date'] ?? null),
            $this->nullOr($data['cccd_issue_place'] ?? null),
            $this->nullOr($data['phone'] ?? null),
            $this->nullOr($data['email'] ?? null),
            $this->nullOr($data['address'] ?? null),
            $this->nullOr($data['type'] ?? 'adult'),
            $this->nullOr($data['note'] ?? null),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /** Cập nhật 1 khách */
    public function update(int $id, array $data): void
    {
        $sql = "UPDATE booking_travelers
                SET full_name=?, dob=?, gender=?, cccd=?, cccd_issue_date=?, cccd_issue_place=?,
                    phone=?, email=?, address=?, type=?, note=?
                WHERE id=?";
        $st = $this->pdo->prepare($sql);
        $st->execute([
            trim($data['full_name'] ?? ''),
            $this->nullOr($data['dob'] ?? null),
            $this->nullOr($data['gender'] ?? null),
            $this->sanitizeCccd($data['cccd'] ?? null),
            $this->nullOr($data['cccd_issue_date'] ?? null),
            $this->nullOr($data['cccd_issue_place'] ?? null),
            $this->nullOr($data['phone'] ?? null),
            $this->nullOr($data['email'] ?? null),
            $this->nullOr($data['address'] ?? null),
            $this->nullOr($data['type'] ?? 'adult'),
            $this->nullOr($data['note'] ?? null),
            $id
        ]);
    }

    /** Xóa toàn bộ khách của 1 booking */
    public function deleteByBooking(int $bookingId): void
    {
        $st = $this->pdo->prepare("DELETE FROM booking_travelers WHERE booking_id=?");
        $st->execute([$bookingId]);
    }

    /** Xóa 1 khách theo id */
    public function delete(int $id): void
    {
        $st = $this->pdo->prepare("DELETE FROM booking_travelers WHERE id=?");
        $st->execute([$id]);
    }

    /**
     * Thay thế toàn bộ danh sách khách của 1 booking bằng dữ liệu mới từ form.
     * - Mỗi lần gọi:
     *   + XÓA hết khách cũ theo booking_id
     *   + INSERT lại theo $rows
     *
     * $rows: mảng mỗi phần tử là 1 khách, keys:
     *   full_name*, dob, gender, cccd, cccd_issue_date, cccd_issue_place,
     *   phone, email, address, type, note
     */
    public function bulkUpsert(int $bookingId, array $rows): void
    {
        if ($bookingId <= 0) return;

        $this->pdo->beginTransaction();
        try {
            // 1. Xóa hết danh sách khách cũ của booking
            $this->deleteByBooking($bookingId);

            // 2. Chuẩn bị câu lệnh insert
            $sql = "INSERT INTO booking_travelers
                    (booking_id, full_name, dob, gender, cccd, cccd_issue_date, cccd_issue_place,
                     phone, email, address, type, note)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
            $st = $this->pdo->prepare($sql);

            // 3. Insert lại từng dòng từ form
            foreach ($rows as $r) {
                $fullName = trim($r['full_name'] ?? '');
                if ($fullName === '') continue; // bỏ dòng trống

                $st->execute([
                    $bookingId,
                    $fullName,
                    $this->nullOr($r['dob'] ?? null),
                    $this->nullOr($r['gender'] ?? null),
                    $this->sanitizeCccd($r['cccd'] ?? null),
                    $this->nullOr($r['cccd_issue_date'] ?? null),
                    $this->nullOr($r['cccd_issue_place'] ?? null),
                    $this->nullOr($r['phone'] ?? null),
                    $this->nullOr($r['email'] ?? null),
                    $this->nullOr($r['address'] ?? null),
                    $this->nullOr($r['type'] ?? 'adult'),
                    $this->nullOr($r['note'] ?? null),
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /* ===================== Helpers ===================== */

    /** Trả về NULL nếu chuỗi rỗng */
    private function nullOr($v)
    {
        if ($v === null) return null;
        if (is_string($v)) {
            $v = trim($v);
            return ($v === '') ? null : $v;
        }
        return $v;
    }

    /** Chuẩn hoá CCCD: giữ 9/12 số, sai -> NULL */
    private function sanitizeCccd($cccd): ?string
    {
        if ($cccd === null) return null;
        $cccd = preg_replace('/\D+/', '', (string)$cccd);
        if ($cccd === '') return null;
        if (preg_match('/^(\d{9}|\d{12})$/', $cccd)) return $cccd;
        return null;
    }
}