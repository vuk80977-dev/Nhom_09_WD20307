<?php
require_once __DIR__ . '/BaseModel.php';

class Supplier extends BaseModel {
    protected $table = 'suppliers';

    // Phân trang + lọc NCC
    public function paginate($page=1, $perPage=10, $filters=[]) {
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = ($page-1)*$perPage;

        $where = [];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = "s.type = :type";
            $params[':type'] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where[] = "s.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['q'])) {
            $where[] = "(s.name LIKE :q 
                     OR s.phone LIKE :q 
                     OR s.email LIKE :q 
                     OR s.contact_person LIKE :q)";
            $params[':q'] = '%'.$filters['q'].'%';
        }

        $whereSql = $where ? "WHERE ".implode(" AND ", $where) : "";

        // count total
        $sqlCount = "SELECT COUNT(*) FROM suppliers s $whereSql";
        $stCount = self::$conn->prepare($sqlCount);
        $stCount->execute($params);
        $total = (int)$stCount->fetchColumn();

        // list
        $sql = "SELECT s.*
                FROM suppliers s
                $whereSql
                ORDER BY s.id DESC
                LIMIT $perPage OFFSET $offset";

        $stmt = self::$conn->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'   => $items,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'pages'   => $total>0 ? (int)ceil($total/$perPage) : 1
        ];
    }
}
