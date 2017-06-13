<?php

/**
 * Class com vários metodos utilizados frequentemente no projeto
 */

namespace Bioliving\Custom;

use Bioliving\Database\Db as Db;
use \PDO; // Import do namespace global do PDO


class Autenticacao {
		private $accessToken;
		private $refreshToken;

		public function __construct(){
			echo 'Hello from autenticacao';
		}
}