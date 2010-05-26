<?php

class ConnectorException extends Exception {}


class Connector {

    private $user;

    private $passwd;

    private $db;

    private $host;

    private $port = '3306'; 

    private static $instance = null;

    private $conn = null;

    private $result = null;    

    private function __construct() {
	$initFile = dirname(__FILE__) . '/../connector.ini';
	if (defined('CONNECTOR_INIT_FILE')) {
		$initFile = CONNECTOR_INIT_FILE;
	}	
        if (!is_readable($initFile)) {
		throw new ConnectorException('Connector initialization file is missing or is unreadable!');
	}
	$cfg = parse_ini_file($initFile);

	$this->user = $cfg['user'];
	$this->passwd = $cfg['passwd'];
	$this->db = $cfg['db'];
	$this->host = $cfg['host'];
	if (isset($cfg['port']) && !empty($cfg['port'])) {
		$this->port = $cfg['port'];
	}
	$this->conn = $this->openConnection();
    }

    private function __clone() {}


    public function openConnection() {
        $conn = @mysql_connect($this->host . ':' . $this->port, $this->user, $this->passwd);
        if (false === $conn) {
		throw new ConnectorException(mysql_error());
	}
        return $conn;
    }

    protected function closeConnection() {
        if ($this->conn) {
            if (is_resource($this->result)) {
                mysql_free_result($this->result);
            }
            $result = mysql_close($this->conn);
            $this->conn = null;
            return $result;
        }

    }

    public function query($query) {
        if (!is_string($query) || empty($query)) {
            throw new ConnectorException("Invalid query: $query");
        }
	if ($this->conn) {
            $db = mysql_select_db($this->db, $this->conn);
            if (!$db) {
                throw new ConnectorException(mysql_error());
            }
            $this->result = mysql_query($query);
            if (!$this->result) {
                throw new ConnectorException(mysql_error());
            }
            return $this->result;
        } else {
           throw new ConnectorException('No connection available');
        }

    }

    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new Connector();
        }
        return self::$instance;
    }

   
    public function __destruct() {
        $this->closeConnection();
    }

    public function getResultAsObjects() {
        $items = array();
        while ($item = mysql_fetch_object($this->result)) {
            $items[] = $item;
        }
        @mysql_free_result($this->result);
        return $items;
    }

    public function fetchOne() {
        if (0 == mysql_num_rows($this->result)) { return null; }
        mysql_data_seek($this->result, 0);
        $item = mysql_fetch_object($this->result);
        @mysql_free_result($this->result);
        return $item;
    }
}
?>
