<?php

class db {
    // connection link
    public $pdo;

    public function __construct() {
        $this->pdo = $this->dbConnect();
    }

    protected function dbConnect() {
        try {
            $pdo = new PDO('mysql:host=' . CONFIG_DATABASE_HOST . ';port=' . CONFIG_DATABASE_PORT . ';dbname=' . CONFIG_DATABASE_NAME . ';charset=utf8', CONFIG_DATABASE_USER, CONFIG_DATABASE_PASSWORD);
        } catch (PDOException $exception) {
            new monolog('ERROR', 'Unable to connect to the database: ' . $exception->getMessage());
            die('1000');
        }
        return $pdo;
    }

    public function select($query) {
        $runQuery = $this->pdo->query($query);
        $results = $runQuery->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}