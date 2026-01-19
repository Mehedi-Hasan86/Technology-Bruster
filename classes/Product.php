<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;
    private $table = 'products';

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function getAll($limit = 12, $offset = 0, $filters = []) {
        $query = "SELECT p.* FROM {$this->table} p WHERE p.is_active = 1";

        if (!empty($filters['category_id'])) {
            $category_id = $filters['category_id'];
            $query .= " AND p.category_id = $category_id";
        }

        if (!empty($filters['search'])) {
            $search = '%' . $this->conn->real_escape_string($filters['search']) . '%';
            $query .= " AND (p.title LIKE '$search' OR p.description LIKE '$search')";
        }

        if (!empty($filters['min_price'])) {
            $min = $filters['min_price'];
            $query .= " AND p.price >= $min";
        }

        if (!empty($filters['max_price'])) {
            $max = $filters['max_price'];
            $query .= " AND p.price <= $max";
        }

        if (!empty($filters['sort'])) {
            if ($filters['sort'] == 'popular') {
                $query .= " ORDER BY p.downloads_count DESC";
            } elseif ($filters['sort'] == 'price_low') {
                $query .= " ORDER BY p.price ASC";
            } elseif ($filters['sort'] == 'price_high') {
                $query .= " ORDER BY p.price DESC";
            } else {
                $query .= " ORDER BY p.created_at DESC";
            }
        } else {
            $query .= " ORDER BY p.created_at DESC";
        }

        $query .= " LIMIT $limit OFFSET $offset";
        return $this->conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ? AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getScreenshots($product_id) {
        $query = "SELECT * FROM product_screenshots WHERE product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getRelated($category_id, $product_id, $limit = 4) {
        $query = "SELECT * FROM {$this->table} WHERE category_id = ? AND id != ? AND is_active = 1 LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $category_id, $product_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
