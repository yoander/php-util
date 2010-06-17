#!/usr/bin/env php
<?php
	define('CONNECTOR_INIT_FILE', dirname(__FILE__) . '/connector.ini');
	include_once dirname(__FILE__) . '/../Connector.class.php';
	class ConnectorTest {
		
		private static $connector;	
	
		private function __construct() {}

		private function __clone() {}

		public static function init() {
			try {
				self::$connector = Connector::getInstance(); 
				echo 'Instance was created successfully', "\n";
			} catch (ConnectorException $e) {
				echo $e->getMessage(), "\n";
			}
		}
			
		public static function testConnection() {
			try {
				self::$connector->openConnection();
				echo 'Connection was opened sucessfully!', "\n";
			} catch (ConnectorException $e) {
				echo $e->getMessage(), "\n";
			}
		}
		
		public static function testExecuteQuery() {
		}

		public static function testGetResultAsObjects() {
			$query = 'select login, name from user';
			self::$connector->query($query);
			$hotels = self::$connector->getResultAsObjects();
			foreach ($hotels as $hotel) {
				echo $hotel->login, ': ', $hotel->name, "\n";
			}
		}
	}
	ConnectorTest::init();
	ConnectorTest::testConnection();
	ConnectorTest::testExecuteQuery();
	ConnectorTest::testGetResultAsObjects();
