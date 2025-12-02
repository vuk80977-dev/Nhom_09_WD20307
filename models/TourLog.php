<?php
require_once __DIR__ . '/BaseModel.php';

class TourLog extends BaseModel {
    protected $table = 'tour_logs';

    // phân trang + lọc theo schedule / type / keyword
    public function paginate($page=1, $perPage=10, $filters=[]) {
        $page=max(1,(int)$page);
        $perPage=max(1,(int)$perPage);
        $offset=($page-1)*$perPage;

        $where=[];
        $params=[];

        if(!empty($filters['schedule_id'])){
            $where[]="l.schedule_id = :schedule_id";
            $params[':schedule_id']=(int)$filters['schedule_id'];
        }

        if(!empty($filters['type'])){
            $where[]="l.type = :type";
            $params[':type']=$filters['type'];
        }

        if(!empty($filters['q'])){
            $where[]="(l.title LIKE :q OR l.content LIKE :q OR t.name LIKE :q)";
            $params[':q']='%'.$filters['q'].'%';
        }

        $whereSql = $where ? "WHERE ".implode(" AND ",$where) : "";

        $sqlCount="SELECT COUNT(*)
                   FROM tour_logs l
                   JOIN schedules s ON s.id=l.schedule_id
                   JOIN tours t ON t.id=s.tour_id
                   $whereSql";
        $stCount=self::$conn->prepare($sqlCount);
        $stCount->execute($params);
        $total=(int)$stCount->fetchColumn();

        $sql="SELECT 
                l.*,
                s.start_date, s.end_date,
                t.name AS tour_name
              FROM tour_logs l
              JOIN schedules s ON s.id=l.schedule_id
              JOIN tours t ON t.id=s.tour_id
              $whereSql
              ORDER BY l.log_date DESC, l.id DESC
              LIMIT $perPage OFFSET $offset";
        $stmt=self::$conn->prepare($sql);
        $stmt->execute($params);
        $items=$stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items'=>$items,
            'total'=>$total,
            'page'=>$page,
            'perPage'=>$perPage,
            'pages'=>$total>0?(int)ceil($total/$perPage):1,
        ];
    }
}
