<?php
require_once __DIR__ . '/BaseModel.php';

class Booking extends BaseModel {
    protected $table = 'bookings';

    // Lấy booking gần đây cho dashboard
    public function getRecentBookings($limit = 5) {
        $sql = "SELECT 
            b.*,
            c.name  AS customer_name,
            c.email AS customer_email,
            c.phone AS customer_phone,
            t.name AS tour_name,
            sc.start_date,
            sc.end_date,
            sc.capacity,
            sc.booked_count,
            g.name AS guide_name
        FROM bookings b
        JOIN customers c ON c.id=b.customer_id
        JOIN schedules sc ON sc.id=b.schedule_id
        JOIN tours t ON t.id=sc.tour_id
        LEFT JOIN guides g ON g.id=sc.guide_id
        ORDER BY b.id DESC
        LIMIT :limit";

        $stmt = self::$conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Phân trang + lọc booking (có tính tiền)
    public function paginate($page=1, $perPage=10, $filters=[]) {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = ($page-1)*$perPage;

        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "b.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['tour_id'])) {
            $where[] = "t.id = :tour_id";
            $params[':tour_id'] = (int)$filters['tour_id'];
        }
        if (!empty($filters['schedule_id'])) {
            $where[] = "sc.id = :schedule_id";
            $params[':schedule_id'] = (int)$filters['schedule_id'];
        }
        if (!empty($filters['q'])) {
            $where[] = "(c.name LIKE :q OR c.email LIKE :q OR c.phone LIKE :q OR t.name LIKE :q)";
            $params[':q'] = '%'.$filters['q'].'%';
        }

        $whereSql = $where ? "WHERE ".implode(" AND ", $where) : "";

        $sqlCount = "SELECT COUNT(*) 
            FROM bookings b
            JOIN customers c ON c.id=b.customer_id
            JOIN schedules sc ON sc.id=b.schedule_id
            JOIN tours t ON t.id=sc.tour_id
            $whereSql";
        $stCount = self::$conn->prepare($sqlCount);
        $stCount->execute($params);
        $total = (int)$stCount->fetchColumn();

        // ✅ Tính tiền: ưu tiên schedule.price_override, nếu null lấy tours.price
        $sql = "SELECT 
            b.*,
            c.name AS customer_name,
            c.email AS customer_email,
            c.phone AS customer_phone,
            c.address AS customer_address,
            t.name AS tour_name,
            t.price AS tour_price,
            sc.price_override,
            sc.start_date,
            sc.end_date,
            sc.capacity,
            sc.booked_count,
            g.name AS guide_name,
            COALESCE(b.price_per_person, sc.price_override, t.price) AS calc_price_per_person,
            (COALESCE(b.price_per_person, sc.price_override, t.price) * b.quantity) AS calc_total_price
        FROM bookings b
        JOIN customers c ON c.id=b.customer_id
        JOIN schedules sc ON sc.id=b.schedule_id
        JOIN tours t ON t.id=sc.tour_id
        LEFT JOIN guides g ON g.id=sc.guide_id
        $whereSql
        ORDER BY b.id DESC
        LIMIT $perPage OFFSET $offset";

        $stmt = self::$conn->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'=>$items,
            'total'=>$total,
            'page'=>$page,
            'perPage'=>$perPage,
            'pages'=>$total>0 ? (int)ceil($total/$perPage) : 1,
        ];
    }

    public function incBooked($scheduleId, $qty) {
        $sql = "UPDATE schedules 
                SET booked_count = booked_count + :qty
                WHERE id = :id";
        $st = self::$conn->prepare($sql);
        $st->execute([':qty'=>$qty, ':id'=>$scheduleId]);
    }

    public function decBooked($scheduleId, $qty) {
        $sql = "UPDATE schedules
                SET booked_count = IF(booked_count>=:qty, booked_count-:qty, 0)
                WHERE id=:id";
        $st = self::$conn->prepare($sql);
        $st->execute([':qty'=>$qty, ':id'=>$scheduleId]);
    }

    // Lấy 1 booking kèm giá tính
    public function findWithCalc($id){
        $sql = "SELECT 
            b.*,
            sc.tour_id,
            sc.price_override,
            t.price AS tour_price,
            COALESCE(b.price_per_person, sc.price_override, t.price) AS calc_price_per_person,
            (COALESCE(b.price_per_person, sc.price_override, t.price) * b.quantity) AS calc_total_price
        FROM bookings b
        JOIN schedules sc ON sc.id=b.schedule_id
        JOIN tours t ON t.id=sc.tour_id
        WHERE b.id = ?";
        $st = self::$conn->prepare($sql);
        $st->execute([$id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }
    // =============================
//  CREATE BOOKING (Full logic)
// =============================
public function createBooking($data)
{
    // Lấy thông tin lịch khởi hành
    $sql = "SELECT * FROM schedules WHERE id = ?";
    $st = self::$conn->prepare($sql);
    $st->execute([$data['schedule_id']]);
    $schedule = $st->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        throw new Exception("Lịch khởi hành không tồn tại.");
    }

    if ($schedule['status'] !== 'open') {
        throw new Exception("Lịch khởi hành đã đóng hoặc không mở bán.");
    }

    // Kiểm tra chỗ
    $remain = $schedule['capacity'] - $schedule['booked_count'];
    if ($data['quantity'] > $remain) {
        throw new Exception("Không đủ chỗ. Chỉ còn $remain chỗ.");
    }

    // Tạo booking
    $bookingId = $this->create($data);

    // Tăng số chỗ đã đặt
    $sql = "UPDATE schedules SET booked_count = booked_count + ? WHERE id = ?";
    self::$conn->prepare($sql)->execute([$data['quantity'], $data['schedule_id']]);

    return $bookingId;
}



// =============================
//  UPDATE BOOKING (Full logic)
// =============================
public function updateBooking($id, $newData)
{
    $old = $this->find($id);
    if (!$old) throw new Exception("Booking không tồn tại!");

    $oldQty  = $old['quantity'];
    $newQty  = $newData['quantity'];
    $oldSch  = $old['schedule_id'];
    $newSch  = $newData['schedule_id'];

    // Nếu đổi lịch khởi hành
    if ($oldSch != $newSch) {

        // Trả chỗ lịch cũ
        self::$conn->prepare(
            "UPDATE schedules SET booked_count = booked_count - ? WHERE id = ?"
        )->execute([$oldQty, $oldSch]);

        // Lấy lịch mới
        $st = self::$conn->prepare("SELECT * FROM schedules WHERE id=?");
        $st->execute([$newSch]);
        $schedule = $st->fetch(PDO::FETCH_ASSOC);

        if (!$schedule) throw new Exception("Lịch mới không tồn tại.");
        if ($schedule['status'] !== 'open') throw new Exception("Lịch mới không mở bán.");

        $remain = $schedule['capacity'] - $schedule['booked_count'];
        if ($newQty > $remain) throw new Exception("Lịch mới không đủ chỗ. Còn $remain.");

        // Tăng chỗ lịch mới
        self::$conn->prepare(
            "UPDATE schedules SET booked_count = booked_count + ? WHERE id = ?"
        )->execute([$newQty, $newSch]);
    }
    else {
        // Không đổi lịch → tính chênh lệch số lượng
        $diff = $newQty - $oldQty;

        if ($diff != 0) {
            if ($diff > 0) {
                // Cần thêm chỗ → kiểm tra
                $st = self::$conn->prepare("SELECT * FROM schedules WHERE id=?");
                $st->execute([$oldSch]);
                $schedule = $st->fetch(PDO::FETCH_ASSOC);

                $remain = $schedule['capacity'] - $schedule['booked_count'];
                if ($diff > $remain) {
                    throw new Exception("Không đủ chỗ. Còn lại $remain.");
                }
            }

            // Update số chỗ
            self::$conn->prepare(
                "UPDATE schedules SET booked_count = booked_count + ? WHERE id = ?"
            )->execute([$diff, $oldSch]);
        }
    }

    // Cập nhật booking
    return $this->update($id, $newData);
}



// =============================
//  DELETE BOOKING (Full logic)
// =============================
public function deleteBooking($id)
{
    $b = $this->find($id);
    if (!$b) return;

    // Trả lại chỗ lịch khởi hành
    self::$conn->prepare(
        "UPDATE schedules SET booked_count = booked_count - ? WHERE id = ?"
    )->execute([$b['quantity'], $b['schedule_id']]);

    // Xóa booking
    return $this->delete($id);
}
public function countByScheduleId($scheduleId){
    $stmt = self::$conn->prepare("SELECT COUNT(*) FROM bookings WHERE schedule_id = ?");
    $stmt->execute([(int)$scheduleId]);
    return (int)$stmt->fetchColumn();
}
    public function countByCustomerId($customerId){
    $stmt = self::$conn->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ?");
    $stmt->execute([(int)$customerId]);
    return (int)$stmt->fetchColumn();
}


}
