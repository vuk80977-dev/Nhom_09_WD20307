<?php
require_once __DIR__ . '/BaseModel.php';

class Booking extends BaseModel {
    protected $table = 'bookings';

    public function getRecentBookings($limit = 5) {
        $sql = "SELECT * FROM `{$this->table}` ORDER BY `booking_date` DESC, `id` DESC LIMIT :lim";
        $stmt = self::$conn->prepare($sql);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginate($page = 1, $perPage = 10, $filters = []) {
        $where = [];
        $params = [];

        // Lọc theo trạng thái (optional)
        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params[':status'] = $filters['status'];
        }
        // Lọc theo từ khóa ID / customer_id / tour_id (optional, demo)
        if (!empty($filters['q'])) {
            $where[] = "(CAST(id AS CHAR) LIKE :q OR CAST(customer_id AS CHAR) LIKE :q OR CAST(tour_id AS CHAR) LIKE :q)";
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Đếm tổng
        $sqlCount = "SELECT COUNT(*) FROM `{$this->table}` {$whereSql}";
        $stmtCount = self::$conn->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        // Lấy dữ liệu trang
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM `{$this->table}` {$whereSql} ORDER BY `booking_date` DESC, `id` DESC LIMIT :limit OFFSET :offset";
        $stmt = self::$conn->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'    => $items,
            'total'    => $total,
            'page'     => (int)$page,
            'perPage'  => (int)$perPage,
            'pages'    => max(1, (int)ceil($total / $perPage)),
        ];
    }
}
