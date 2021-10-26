<?php

class APITest {
    private $base_url = "http://localhost/api";
    private $results = [];
    private $total_tests = 0;
    private $passed_tests = 0;

    private function send_request($endpoint, $method = "GET", $data = null) {
        $curl = curl_init();
        $url = $this->base_url . $endpoint;
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
        ];

        if ($data && in_array($method, ["POST", "PUT"])) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        return [
            "status_code" => $http_code,
            "response" => json_decode($response, true)
        ];
    }

    private function assert($condition, $name) {
        $this->total_tests++;
        
        if ($condition) {
            $this->passed_tests++;
            $result = "SUCCESSFUL";
            $color = "\033[32m";
        } else {
            $result = "UNSUCCESSFUL";
            $color = "\033[31m";
        }

        $this->results[] = [
            "test" => $name,
            "result" => $result,
            "color" => $color
        ];
    }

    public function run_all_tests() {
        echo "🚀 API Tests Starting...\n";

        $this->test_product_operations();
        $this->test_stock_operations();
        $this->test_sale_operations();
        $this->show_results();
    }

    private function test_product_operations() {
        echo "📦 (1) `/products` Tests\n";

        $product_data = [
            "barcode" => "8680000000001",
            "name" => "Test Product 1",
            "price" => 29.99
        ];
        $response = $this->send_request("/products", "POST", $product_data);

        $this->assert(
            $response["status_code"] === 200 && $response["response"]["status"] === "success",
            "Add product"
        );

        $response = $this->send_request("/products");

        $this->assert(
            $response["status_code"] === 200 && isset($response["response"]["data"]),
            "List products"
        );

        $response = $this->send_request("/products/barcode/8680000000001");

        $this->assert(
            $response["status_code"] === 200 && isset($response["response"]["data"]),
            "Search product by barcode"
        );
    }

    private function test_stock_operations() {
        echo "📊 (2) `/stock` Tests\n";

        $stock_data = [
            "product_id" => 1,
            "quantity" => 100
        ];
        $response = $this->send_request("/stock/update", "POST", $stock_data);

        $this->assert(
            $response["status_code"] === 200 && $response["response"]["status"] === "success",
            "Update stock"
        );

        $response = $this->send_request("/stock/1");

        $this->assert(
            $response["status_code"] === 200 && isset($response["response"]["data"]),
            "Search stock"
        );
    }

    private function test_sale_operations() {
        echo "💰 (3) `/sales` Tests\n";

        $sale_data = [
            "items" => [
                [
                    "product_id" => 1,
                    "quantity" => 2,
                    "price" => 29.99
                ]
            ],
            "payment_type" => "cash"
        ];
        $response = $this->send_request("/sales", "POST", $sale_data);

        $this->assert(
            $response["status_code"] === 200 && $response["response"]["status"] === "success",
            "Add sales"
        );

        if (isset($response["response"]["sale_id"])) {
            $sale_id = $response["response"]["sale_id"];
            $response = $this->send_request("/sales/" . $sale_id);

            $this->assert(
                $response["status_code"] === 200 && isset($response["response"]["data"]),
                "Search sales"
            );
        }
    }

    private function show_results() {
        echo "📊 (#) Test Results:\n";
        
        foreach ($this->results as $result) {
            echo $result["color"];
            echo "[>] {$result["test"]}: {$result["result"]}\n";

            echo "\033[0m";
        }

        echo "=====================================\n";
        echo "Total Test: {$this->total_tests}\n";
        echo "Success: {$this->passed_tests}\n";
        echo "Fail: " . ($this->total_tests - $this->passed_tests) . "\n";
        echo "Success Rate: " . round(($this->passed_tests / $this->total_tests) * 100, 2) . "%\n";
    }
}

$tester = new APITest();
$tester->run_all_tests();