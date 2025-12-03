<?php
require_once __DIR__ . '/BaseModel.php';

class Schedule extends BaseModel {
    protected $table = 'schedules';

    public function paginate($page = 1, $perPage = 10, $filters = []) {
        $page    = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset  = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if (!empty($filters['tour_id'])) {
            $where[] = "s.tour_id = :tour_id";
            $params[':tour_id'] = (int)$filters['tour_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = "s.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $where[] = "(t.name LIKE :q OR g.name LIKE :q OR s.meeting_point LIKE :q)";
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Đếm tổng
        $sqlCount = "
            SELECT COUNT(*)
            FROM {$this->table} s
            LEFT JOIN tours t ON t.id = s.tour_id
            LEFT JOIN guides g ON g.id = s.guide_id
            {$whereSql}
        ";
        $stmtCount = self::$conn->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        // Lấy dữ liệu trang (JOIN lấy tên tour + guide)
        $sql = "
            SELECT s.*, 
                   t.name AS tour_name,
                   g.name AS guide_name
            FROM {$this->table} s
            LEFT JOIN tours t ON t.id = s.tour_id
            LEFT JOIN guides g ON g.id = s.guide_id
            {$whereSql}
            ORDER BY s.start_date DESC, s.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $stmt = self::$conn->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'   => $items,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'pages'   => max(1, (int)ceil($total / $perPage)),
        ];
    }
    public function getAllWithTour() {
    $sql = "
        SELECT sc.*, t.name AS tour_name
        FROM schedules sc
        LEFT JOIN tours t ON t.id = sc.tour_id
        ORDER BY sc.start_date DESC, sc.id DESC
    ";
    $stmt = self::$conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function findWithTourGuide($id) {
    $sql = "
        SELECT sc.*, 
               t.name AS tour_name,
               g.name AS guide_name
        FROM schedules sc
        LEFT JOIN tours t ON t.id = sc.tour_id
        LEFT JOIN guides g ON g.id = sc.guide_id
        WHERE sc.id = ?
        LIMIT 1
    ";
    $stmt = self::$conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
    public function getOpenSchedulesGroupedByTour() {
    $sql = "
        SELECT sc.id, sc.tour_id, sc.start_date, sc.end_date,
               sc.capacity, sc.booked_count, sc.status
        FROM schedules sc
        WHERE sc.status = 'open'
        ORDER BY sc.start_date ASC, sc.id ASC
    ";
    $stmt = self::$conn->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grouped = [];
    foreach ($rows as $r) {
        $tid = $r['tour_id'];
        if (!isset($grouped[$tid])) $grouped[$tid] = [];
        $grouped[$tid][] = $r;
    }
    return $grouped;
}
public function countByTourId($tourId) {
    $stmt = self::$conn->prepare("SELECT COUNT(*) FROM schedules WHERE tour_id = ?");
    $stmt->execute([(int)$tourId]);
    return (int)$stmt->fetchColumn();
}

}
