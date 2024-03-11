<?php

class DatabaseConnection{

	private static $dbInstance = null;
	
	private function __construct(){

	}
	
	private function __clone(){

	}

	public static function getInstance() {

		if ( self::$dbInstance === null  ) {
			
			try {
				self::$dbInstance = new mysqli('localhost', 'root', '', 'user_db');
			} catch (Exception $e) {
				echo $e->getMessage();			
			}
		}
		return self::$dbInstance;
	}
}

$db = DatabaseConnection::getInstance();