<?php

class SQLiteConnectorException extends Exception {}


class SQLiteConnector {

    private static $instance = null;

    private $dbh = null;

    private $sth = null;

    private $dbFile = '';

    private function __construct() {
    $this->dbFile = dirname(__FILE__) . '/../sqlite.db';
    if (defined('DB_FILE')) {
        $this->dbFile = DB_FILE;
    }
    $this->dbh = $this->connect();
    }

    private function __clone() {}


    public function connect() {
        try {
        $dbh = new PDO('sqlite:' . $this->dbFile);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $dbh;
        } catch (PDOException $e) {
            throw new SQLiteConnectorException($e->getMessage());
        }
    }

    public function query($query, $params = null, $dbh = null) {
        if (!is_string($query) || empty($query)) {
            throw new SQLiteConnectorException("Invalid query: $query");
        }
        if (!is_null($dbh)) {
            $this->dbh = $dbh;
        }
        if (is_null($this->dbh)) {
               throw new SQLiteConnectorException('No database handler available');
        }
        try {
            $this->sth = $this->dbh->prepare($query);
            $this->sth->setFetchMode(PDO::FETCH_CLASS);
            $this->sth->execute($params);
        } catch (PDOException $e) {
            throw new SQLiteConnectorException($e->getMessage());
        }
        return true;
    }

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new SQLiteConnector();
        }
        return self::$instance;
    }


    public function fetchAll() {
        $this->sth->fetchAll();
    }

    public function fetchOne() {
        return $this->sth->fetchObject();
    }

    public function dropTable($tableName) {
    $this->query("DROP TABLE IF EXISTS $tableName");
    }
}
?>
