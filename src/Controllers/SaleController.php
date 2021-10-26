<?php

namespace App\Controllers;

use App\Models\Sale;
use App\Models\Stock;

class SaleController {
    private $sale_model;
    private $stock_model;

    public function __construct() {
        $this->sale_model = new Sale();
        $this->stock_model = new Stock();
    }

    public function create() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            $errors = $this->validate($data);

            if (!empty($errors)) {
                throw new \Exception(implode(", ", $errors));
            }

            foreach ($data["items"] as $item) {
                if (!$this->stock_model->check_quantity_by_id($item["product_id"], $item["quantity"])) {
                    throw new \Exception("Insufficient stock, Product ID (" . $item["product_id"] . ")");
                }
            }

            $sale_id = $this->sale_model->create($data["items"], $data["payment_type"]);

            echo json_encode([
                "status" => "success",
                "message" => "Sale created",
                "sale_id" => $sale_id
            ]);
        }
        catch (\Exception $e) {
            $this->handle_error($e);
        }
    }

    public function read_by_id($id) {
        try {
            $sale = $this->sale_model->read_by_id($id);

            if (!$sale) {
                throw new \Exception("Sale not found", 404);
            }

            echo json_encode([
                "status" => "success",
                "data" => $sale
            ]);
        }
        catch (\Exception $e) {
            $this->handle_error($e);
        }
    }

    private function validate($data) {
        $errors = [];
        
        if (empty($data["items"]) || !is_array($data["items"])) {
            $errors[] = "Sales items are required";
        }

        if (!isset($data["payment_type"]) || !in_array($data["payment_type"], ["cash", "credit_card"])) {
            $errors[] = "Valid payment type is required";
        }

        foreach ($data["items"] as $item) {
            if (!isset($item["product_id"]) || !is_numeric($item["product_id"])) {
                $errors[] = "Valid product id is required";
            }

            if (!isset($item["quantity"]) || !is_numeric($item["quantity"]) || $item["quantity"] <= 0) {
                $errors[] = "Valid product quantity is required";
            }

            if (!isset($item["price"]) || !is_numeric($item["price"]) || $item["price"] < 0) {
                $errors[] = "Valid product price is required";
            }
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