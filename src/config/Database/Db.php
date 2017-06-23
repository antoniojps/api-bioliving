<?php
namespace Bioliving\Database;

use PDO; // Import do namespace global do PDO

class Db {

/////////////////////
// Configurações db
	private $dbhost;
	private $dbname;
	private $dbuser;
	private $dbpass;

	public function __construct() {
		$this->dbhost = getenv( 'DB_HOST' );
		$this->dbname = getenv( 'DB_NAME' );
		$this->dbuser = getenv( 'DB_USER' );
		$this->dbpass = getenv( 'DB_PASS' );

	}

/////////////////////
// Metodo Connect
	public function connect() {
		$dbConnection = null;
		try {
			$dbConectionStr = "mysql:host=$this->dbhost;dbname=$this->dbname;";
			// ambiente de desenvolvimento -> mostrar erros
			if ( getenv( 'PHP_ENV' ) === 'desenvolvimento' ) {
				$dbConnection = new PDO( $dbConectionStr, $this->dbuser, $this->dbpass, [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ] );
			} else {
				error_reporting( E_ERROR | E_PARSE );
				$dbConnection = new PDO( $dbConectionStr, $this->dbuser, $this->dbpass );
			}

		} catch ( \PDOException $e ) {
			if ( getenv( 'PHP_ENV' ) === 'desenvolvimento' ) {
				echo 'Sera que a base de dados da Universidade foi abaixo ou tu es muita nabo e esqueceste-te de ligar o VPN? Mhmm... qual sera';
			} else {
				echo 'Servico indisponivel';
			}
			die;
		}

		return $dbConnection;
	}
}