<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

class Stock {
    private $db;

    public function __construct() {
        $this->db = Connection::get_instance()->get();
    }

    public function read_by_id($id) {
        $stmt = $this->db->prepare("
            SELECT 
                s.*, 
                p.name AS product_name, 
                p.barcode 
            FROM 
                stock s 
            JOIN 
                products p ON s.product_id = p.id 
            WHERE 
                s.product_id = :id
        ");

        $stmt->execute(["id" => $id]);        

        return $stmt->fetch();
    }

    public function check_quantity_by_id($id, $quantity) {
        $stmt = $this->db->prepare("
            SELECT 
                quantity 
            FROM 
                stock 
            WHERE 
                product_id = :id
        ");

        $stmt->execute(["id" => $id]);
        
        $current_stock = $stmt->fetch();
        
        return $current_stock && $current_stock["quantity"] >= $quantity;
    }

    public function update_quantity_by_id($id, $quantity) {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO stock (
                    product_id, 
                    quantity
                ) 
                VALUES (
                    :id, 
                    :quantity
                ) 
                ON DUPLICATE KEY UPDATE 
                    quantity = quantity + :id
            ");

            $stmt->execute([
                "id" => $id,
                "quantity" => $quantity
            ]);

            $this->db->commit();

            return true;
        }
        catch (\Exception $e) {
            $this->db->rollBack();

            throw $e;
        }
    }
}