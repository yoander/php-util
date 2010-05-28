<?php
require_once dirname(__FILE__) . '/../classes/ImageScaler.class.php';
class ImageScalerTest extends PHPUnit_Framework_TestCase {

	private static $imageScaler;

	private static $imgPath;

	public static function setUpBeforeClass() {
		self::$imageScaler = new ImageScaler();
		self::$imgPath = dirname(__FILE__) . '/resources/destino-varadero.jpg';
	}
	
	public function testLoadFromFileSystem() {
		try {
			$this->assertTrue(self::$imageScaler->load(self::$imgPath), 'testLoadFromFileSystem');	
		} catch (ImageScalerException $e) {
			$this->fail($e->getMessage());
		}	
	}
	
	public function testScaleByWidth() {
		try {
			self::$imageScaler->scale(200, 200);
			self::$imageScaler->save(dirname(__FILE__) . '/resources/varadero200x200.jpg');
			self::$imageScaler->scale(300, 300);
			self::$imageScaler->save(dirname(__FILE__) . '/resources/varadero300x300.jpg');
			$this->assertTrue(true);	
		} catch (ImageScalerException $e) {
			$this->fail($e->getMessage());
		}	
	}
}
