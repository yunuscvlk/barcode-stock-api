<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

class Product
{
    private $db;

    public function __construct() {
        $this->db = Connection::get_instance()->get();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO products (
                barcode, 
                name, 
                price
            ) 
            VALUES (
                :barcode, 
                :name, 
                :price
            )
        ");

        return $stmt->execute([
            "barcode" => $data["barcode"],
            "name" => $data["name"],
            "price" => $data["price"]
        ]);
    }

    public function find_all() {
        $stmt = $this->db->query("
            SELECT 
                * 
            FROM 
                products
        ");

        return $stmt->fetchAll();
    }

    public function find_by_id($id) {
        $stmt = $this->db->prepare("
            SELECT 
                * 
            FROM 
                products 
            WHERE 
                id = :id
        ");

        $stmt->execute(["id" => $id]);

        return $stmt->fetch();
    }

    public function find_by_barcode($barcode) {
        $stmt = $this->db->prepare("
            SELECT 
                * 
            FROM 
                products 
            WHERE 
                barcode = :barcode
        ");
        
        $stmt->execute(["barcode" => $barcode]);
        
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE
                products 
            SET 
                barcode = :barcode, 
                name = :name, 
                price = :price 
            WHERE 
                id = :id
        ");

        return $stmt->execute([
            "id" => $id,
            "barcode" => $data["barcode"],
            "name" => $data["name"],
            "price" => $data["price"]
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("
            DELETE FROM
                products
            WHERE
                id = :id
        ");

        return $stmt->execute(["id" => $id]);
    }
}