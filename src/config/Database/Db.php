<?php
namespace Bioliving\Database;

use \PDO; // Import do namespace global do PDO

class Db {

/////////////////////
// Configurações db
	private $dbhost;
	private $dbname;
	private $dbuser;
	private $dbpass;

	public function __construct() {


		$this->dbhost = getenv('DB_HOST');
		$this->dbname = getenv('DB_NAME');
		$this->dbuser = getenv('DB_USER');
		$this->dbpass = getenv('DB_PASS');

	}

/////////////////////
// Metodo Connect
	public function connect() {
		$dbConectionStr = "mysql:host=$this->dbhost;dbname=$this->dbname;";

		$dbConnection = new PDO( $dbConectionStr, $this->dbuser, $this->dbpass );

// ambiente de desenvolvimento -> mostrar erros
		if ( getenv( 'PHP_ENV' ) === 'desenvolvimento' ) {
			$dbConnection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		}

		return $dbConnection;
	}
}