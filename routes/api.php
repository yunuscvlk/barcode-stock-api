<?php

use App\Controllers\ProductController;
use App\Controllers\StockController;
use App\Controllers\SaleController;

$request_uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$request_method = $_SERVER["REQUEST_METHOD"];

$uri_segments = array_values(array_filter(explode("/", trim($request_uri, "/"))));

$product_controller = new ProductController();
$stock_controller = new StockController();
$sale_controller = new SaleController();

try {
    if (!isset($uri_segments[0]) || $uri_segments[0] !== "api") {
        throw new Exception("Invalid endpoint", 404);
    }

    switch ($uri_segments[1] ?? "") {
        case "products": {
            if ($request_method === "POST") {
                $product_controller->create();
            }
            else if ($request_method === "GET") {
                if (!isset($uri_segments[2])) {
                    $product_controller->read();
                }
                else if ($uri_segments[2] === "barcode" && isset($uri_segments[3])) {
                    $product_controller->read_by_barcode($uri_segments[3]);
                }
                else {
                    $product_controller->read_by_id($uri_segments[2]);
                }
            }
            else {
                throw new Exception("Invalid products endpoint", 404);
            }

            break;
        }

        case "stock": {
            if ($request_method === "GET" && isset($uri_segments[2])) {
                $stock_controller->read_by_id($uri_segments[2]);
            }
            else if ($request_method === "POST" && isset($uri_segments[2]) && $uri_segments[2] === "update") {
                $stock_controller->update();
            }
            else {
                throw new Exception("Invalid stock endpoint", 404);
            }

            break;
        }

        case "sales": {
            if ($request_method === "POST") {
                $sale_controller->create();
            }
            else if ($request_method === "GET" && isset($uri_segments[2])) {
                $sale_controller->read_by_id($uri_segments[2]);
            }
            else {
                throw new Exception("Invalid sales endpoint", 404);
            }

            break;
        }
    }
}
catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}