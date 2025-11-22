<?php
require_once __DIR__ . '/../configs/env.php';

class BaseModel {
    protected static $conn;
    protected $table;

    public function __construct()
    {
        if (!self::$conn) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
            self::$conn = new PDO($dsn, DB_USER, DB_PASS);
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

 public function all($orderBy = null)
{
    if (empty($this->table)) {
        throw new Exception("Model " . static::class . " chưa khai báo thuộc tính \$table.");
    }

    $sql = "SELECT * FROM {$this->table}";
    if ($orderBy) {
        $sql .= " ORDER BY $orderBy";
    }
    $stmt = self::$conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Đếm toàn bộ bản ghi trong bảng
public function countAll()
{
    if (empty($this->table)) {
        throw new Exception("Model " . static::class . " chưa khai báo thuộc tính \$table.");
    }

    $sql = "SELECT COUNT(*) FROM {$this->table}";
    return (int) self::$conn->query($sql)->fetchColumn();
}

// Đếm bản ghi theo điều kiện WHERE
public function countWhere($whereSql, $params = [])
{
    if (empty($this->table)) {
        throw new Exception("Model " . static::class . " chưa khai báo thuộc tính \$table.");
    }

    $sql = "SELECT COUNT(*) FROM {$this->table} WHERE $whereSql";
    $stmt = self::$conn->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}


    public function find($id)
    {
        $stmt = self::$conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function where($column, $value)
    {
        $stmt = self::$conn->prepare("SELECT * FROM {$this->table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $cols = array_keys($data);
        $colStr = implode(',', $cols);
        $placeholders = implode(',', array_fill(0, count($cols), '?'));

        $sql = "INSERT INTO {$this->table} ($colStr) VALUES ($placeholders)";
        $stmt = self::$conn->prepare($sql);
        $stmt->execute(array_values($data));
        return self::$conn->lastInsertId();
    }

    public function update($id, $data)
    {
        $set = [];
        foreach ($data as $col => $val) {
            $set[] = "$col = ?";
        }
        $setStr = implode(', ', $set);

        $sql = "UPDATE {$this->table} SET $setStr WHERE id = ?";
        $stmt = self::$conn->prepare($sql);

        $values = array_values($data);
        $values[] = $id;

        return $stmt->execute($values);
    }

    public function delete($id)
    {
        $stmt = self::$conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
