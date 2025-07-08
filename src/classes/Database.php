<?php

class Database {
	private $conn;
	
	public function __construct($host, $user, $pass, $db){
		try{
			$this->conn = new mysqli($host,$user,$pass,$db);
		}
		catch(mysqli_sql_exception $e){
			die("Connection error: " . $e->getMessage());
		}
	}
	
	public function getConnection(){
		return $this->conn;
	}
	
	public function closeConnection(){
		$this->conn->close();
	}
}
