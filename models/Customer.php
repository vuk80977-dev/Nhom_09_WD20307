<?php
require_once __DIR__ . '/BaseModel.php';

class Customer extends BaseModel {
    protected $table = 'customers';

    // Phân trang + tìm kiếm khách hàng theo tên, email, trạng thái
    public function paginate($page = 1, $perPage = 10, $filters = []) {
        $page    = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset  = ($page - 1) * $perPage;

        $where  = [];
        $params = [];

        // Lọc theo trạng thái (active/inactive)
        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params[':status'] = $filters['status'];
        }
        // Từ khóa tìm kiếm theo tên hoặc email
        if (!empty($filters['q'])) {
            $where[] = "(name LIKE :q OR email LIKE :q)";
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Đếm tổng số kết quả để tính trang
        $sqlCount = "SELECT COUNT(*) FROM `{$this->table}` {$whereSql}";
        $stmtCount = self::$conn->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        // Truy vấn lấy danh sách khách hàng theo trang
        $sql = "SELECT * FROM `{$this->table}` {$whereSql} ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}";
        $stmt = self::$conn->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'   => $items,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'pages'   => $total > 0 ? (int)ceil($total / $perPage) : 1,
        ];
    }
}
?>
    