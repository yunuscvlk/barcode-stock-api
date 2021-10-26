<?php

namespace App\Controllers;

use App\Models\Stock;

class StockController {
    private $stock_model;

    public function __construct() {
        $this->stock_model = new Stock();
    }

    public function read_by_id($id) {
        try {
            $stock = $this->stock_model->read_by_id($id);

            if (!$stock) {
                throw new \Exception("Stock is not found", 404);
            }

            echo json_encode([
                "status" => "success",
                "data" => $stock
            ]);
        } catch (\Exception $e) {
            $this->handle_error($e);
        }
    }

    public function update() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            $errors = $this->validate($data);

            if (!empty($errors)) {
                throw new \Exception(implode(", ", $errors) . ".");
            }

            $this->stock_model->update_quantity_by_id($data["product_id"], $data["quantity"]);

            echo json_encode([
                "status" => "success",
                "message" => "Stock updated"
            ]);
        } catch (\Exception $e) {
            $this->handle_error($e);
        }
    }

    private function validate($data) {
        $errors = [];
        
        if (!isset($data["product_id"]) || !is_numeric($data["product_id"])) {
            $errors[] = "Valid product id is required";
        }
        
        if (!isset($data["quantity"]) || !is_numeric($data["quantity"])) {
            $errors[] = "Valid stock quantity is required";
        }
        
        return $errors;
    }

    private function handle_error($e) {
        $status_code = $e->getCode() ?: 500;

        http_response_code($status_code);

        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
}