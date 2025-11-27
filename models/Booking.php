<?php
require_once __DIR__ . '/BaseModel.php';

class Booking extends BaseModel {
    protected $table = 'bookings';

    // ===== Booking gần đây cho Dashboard =====
    public function getRecentBookings($limit = 5) {
        $sql = "SELECT 
                    b.*,
                    c.name AS customer_name,
                    c.email AS customer_email,
                    c.phone AS customer_phone,
                    c.address AS customer_address,
                    t.name AS tour_name,
                    sc.start_date,
                    sc.end_date,
                    g.name AS guide_name
                FROM bookings b
                JOIN customers c ON c.id = b.customer_id
                JOIN schedules sc ON sc.id = b.schedule_id
                JOIN tours t ON t.id = sc.tour_id
                LEFT JOIN guides g ON g.id = sc.guide_id
                ORDER BY b.id DESC
                LIMIT :limit";

        $stmt = self::$conn->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== Phân trang + lọc booking =====
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

        // Đếm tổng
        $sqlCount = "SELECT COUNT(*) 
                     FROM bookings b
                     JOIN customers c ON c.id=b.customer_id
                     JOIN schedules sc ON sc.id=b.schedule_id
                     JOIN tours t ON t.id=sc.tour_id
                     $whereSql";
        $stCount = self::$conn->prepare($sqlCount);
        $stCount->execute($params);
        $total = (int)$stCount->fetchColumn();

        // Lấy list theo trang
        $sql = "SELECT 
                    b.*,
                    c.name  AS customer_name,
                    c.email AS customer_email,
                    c.phone AS customer_phone,
                    c.address AS customer_address,
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

    // ===== cập nhật số chỗ đã đặt trong schedules =====
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
}
