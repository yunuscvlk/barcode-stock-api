<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

class Sale {
    private $db;

    public function __construct() {
        $this->db = Connection::get_instance()->get();
    }

    public function create($items, $payment_type) {
        $this->db->beginTransaction();
        
        try {
            $total_amount = array_sum(array_map(function($item) {
                return $item["quantity"] * $item["price"];
            }, $items));

            $stmt = $this->db->prepare("
                INSERT INTO sales (
                    total_amount,
                    payment_type
                )
                VALUES (
                    :total,
                    :payment
                )
            ");

            $stmt->execute([
                "total" => $total_amount,
                "payment" => $payment_type
            ]);

            $sale_id = $this->db->lastInsertId();

            foreach ($items as $item) {
                $stmt = $this->db->prepare("
                    INSERT INTO sale_details (
                        sale_id, 
                        product_id, 
                        quantity, 
                        unit_price, 
                        total_price
                    ) 
                    VALUES (
                        :sale_id, 
                        :product_id, 
                        :quantity, 
                        :unit_price, 
                        :total_price
                    )
                ");

                $stmt->execute([
                    "sale_id" => $sale_id,
                    "product_id" => $item["product_id"],
                    "quantity" => $item["quantity"],
                    "unit_price" => $item["price"],
                    "total_price" => $item["quantity"] * $item["price"]
                ]);

                $stmt = $this->db->prepare("
                    UPDATE
                        stock 
                    SET
                        quantity = quantity - :quantity 
                    WHERE
                        product_id = :product_id
                ");

                $stmt->execute([
                    "product_id" => $item["product_id"],
                    "quantity" => $item["quantity"]
                ]);
            }

            $this->db->commit();

            return $sale_id;
        }
        catch (\Exception $e) {
            $this->db->rollBack();

            throw $e;
        }
    }

    public function read_by_id($id) {
        $stmt = $this->db->prepare("
            SELECT 
                s.*, 
                sd.*, 
                p.name AS product_name, 
                p.barcode
            FROM 
                sales s
            JOIN 
                sale_details sd ON s.id = sd.sale_id
            JOIN 
                products p ON sd.product_id = p.id
            WHERE 
                s.id = :id
        ");

        $stmt->execute(["id" => $id]);

        return $stmt->fetchAll();
    }
}