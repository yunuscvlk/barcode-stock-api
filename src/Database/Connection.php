<?php

namespace App\Database;

use PDO;
use PDOException;

class Connection
{
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];

            if (defined("PDO::MYSQL_ATTR_INIT_COMMAND")) {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8";
            }

            $this->conn = new PDO(
                "mysql:host=" . $_ENV["DB_HOST"] . ";dbname=" . $_ENV["DB_NAME"],
                $_ENV["DB_USER"],
                $_ENV["DB_PASS"],
                $options
            );
        }
        catch (PDOException $e) {
            throw new \Exception("Database connection error: " . $e->getMessage());
        }
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function get() {
        return $this->conn;
    }
}