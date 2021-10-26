<?php

namespace App\Controllers;

use App\Models\Product;

class ProductController
{
    private $product_model;

    public function __construct() {
        $this->product_model = new Product();
    }

    public function create() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);
            $errors = $this->validate($data);

            if (!empty($errors)) {
                throw new \Exception(implode(", ", $errors));
            }

            $this->product_model->create($data);

            echo json_encode([
                "status" => "success",
                "message" => "Product created"
            ]);
        }
        catch (\Exception $e) {
            $this->handle_error($e);
        }
    }

    public function read() {
        try {
            $products = $this->product_model->find_all();

            echo json_encode([
                "status" => "success",
                "data" => $products
            ]);
        }
        catch (\Exception $e) {
            $this->handle_error($e);
        }
    }

    public function read_by_id($id) {
        try {
            $product = $this->product_model->find_by_id($id);

            if (!$product) {
                throw new \Exception("Product not found", 404);
            }

            echo json_encode([
                "status" => "success",
                "data" => $product
            ]);
        }
        catch (\Exception $e) {
            $this->handle_error($e);
        }
    }

    public function read_by_barcode($barcode) {
        try {
            $product = $this->product_model->find_by_barcode($barcode);

            if (!$product) {
                throw new \Exception("Product not found", 404);
            }

            echo json_encode([
                "status" => "success",
                "data" => $product
            ]);
        }
        catch (\Exception $e) {
            $this->handle_error($e);
        }
    }

    private function validate($data) {
        $errors = [];
        
        if (empty($data["barcode"])) {
            $errors[] = "Barcode field is required";
        }
        
        if (empty($data["name"])) {
            $errors[] = "Product name is required";
        }
        
        if (!isset($data["price"]) || !is_numeric($data["price"]) || $data["price"] < 0) {
            $errors[] = "Valid price is required";
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