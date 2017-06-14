<?php

/**
 * Class com vários metodos utilizados frequentemente no projeto
 *
 * Metodos estaticos:
 * - getAccessToken
 * - getRefreshToken
 *
 */

namespace Bioliving\Custom;

use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Firebase\JWT\JWT;
use \PDO as PDO;

// Import do namespace global do PDO


class Token {
	private $token;
	private $tokenValido;
	public static $tokenPayload; // Null ate ser utilizado o metodo autenticarToken
	private static $accessCookieName = 'token';
	private static $refreshCookieName = 'token_refresh';
	private static $algorithm = 'HS256';

	public function __construct( $token ) {
		$this->tokenValido = false;
		$this->token       = $token;

		if ( $this->validarSyntax() ) {
			$this->tokenValido = true;
		} else {
			throw new \Exception( "Token com syntax inválida" );
		}

		return $this->tokenValido;
	}

	// Verificar se token tem syntax valida
	private function validarSyntax() {

		$tokenValido = false;

		if ( gettype( $this->token ) === 'string' ) {
			preg_match( '/([0-9A-Za-z]+)\.([0-9A-Za-z]+)\.([0-9A-Za-z]+)/', $this->token, $tokenMatch );
			if ( sizeof( $tokenMatch ) === 4 ) {
				$tokenValido = true;
			}
		}

		return $tokenValido;
	}

	// Obter access cookie
	public static function getAccessToken() {
		$cookieExiste = false;

		if ( ! empty( $_COOKIE[ self::$accessCookieName ] ) ) {
			$cookieExiste = true;
		}

		$token = $cookieExiste ? $_COOKIE[ self::$accessCookieName ] : false;

		return $token;
	}

	// Obter refresh cookie
	public static function getRefreshToken() {
		$cookieExiste = false;

		if ( ! empty( $_COOKIE[ self::$refreshCookieName ] ) ) {
			$cookieExiste = true;
		}

		$cookie = $cookieExiste ? $_COOKIE[ self::$refreshCookieName ] : false;

		return $cookie;
	}
	// Autenticar JWT
	// Verifica se token expirou, ou secret nao é correta
	// Se token nao for valido retorna false, caso contrario retorna token;
	public static function autenticar( $token = false ) {

		// Se tem argumento entao $token = argumento, caso contrario é por default o access token
		$token = $token ? $token : self::getAccessToken();

		$jwtAutentico = false;

		try {
			self::$tokenPayload = (array) JWT::decode( $token, getenv( 'SECRET_KEY' ), array( self::$algorithm ) );
			$jwtAutentico       = true;

		} catch ( \Exception $e ) {
			// Nao mostrar em ambiente de producao
			Errors::filtro( function ( $e ) {
				echo $e->getMessage();
			}, function () {
			}, $e );
		}

		return $jwtAutentico ? $token : false;
	}

	// Quantos SEGUNDOS  falta até expirar, caso não seja possivel retorna false (por ex se token nao tiver claim exp)
	public static function getSegRestantes( $token = false ) {

		// Se tem argumento entao $token = argumento, caso contrario é por default o access token
		$token = $token ? $token : self::getAccessToken();

		$tempoRestante = false;
		if ( self::autenticar( $token ) ) {
			$tempoRestante = self::$tokenPayload['exp'] - time();
		}

		return $tempoRestante;
	}

	// Retorna ID do utilizador ao qual o token está associado
	public static function getUtilizador( $token = false ) {

		// Se tem argumento entao $token = argumento, caso contrario é por default o access token
		$token = $token ? $token : self::getAccessToken();

		$id = false;
		if ( self::autenticar( $token ) ) {
			$id = self::$tokenPayload['idUtilizador'];
		}

		return $id;
	}

	// Retorna array SCOPE do token
	public static function getScopes( $token = false ) {

		// Se tem argumento entao $token = argumento, caso contrario é por default o access token
		$token = $token ? $token : self::getAccessToken();

		$scope = [ 'publico' ];
		if ( self::autenticar( $token ) ) {
			$scope = self::$tokenPayload['scope'];
		}

		return $scope;
	}
	// Todo falta verificar se utilizador esta ativo
	// Verificar se refresh token existe na base de dados de acordo com o utilizador que esta na payload
	public static function verificarRefresh( $refreshToken = false ) {
		$refreshTokenAtivo = false;
		$refreshToken      = $refreshToken ? $refreshToken : self::getRefreshToken();

		$idUtilizador = self::getUtilizador( $refreshToken );

		if ( $idUtilizador ) {
			try {

				$sql = "SELECT id FROM utilizadores_tokens WHERE (`utilizadores_id_utilizadores` = :id) AND (`refresh_token` = :refreshToken)";

				// iniciar ligação à base de dados
				$db = new Db();

				// conectar
				$db = $db->connect();

				// Query
				$stmt = $db->prepare( $sql );
				$stmt->bindValue( ':id', $idUtilizador, PDO::PARAM_INT );
				$stmt->bindValue( ':refreshToken', $refreshToken, PDO::PARAM_STR );
				$stmt->execute();
				$db    = null;
				$dados = $stmt->fetch( PDO::FETCH_ASSOC );

				// Refresh token encontrado = verificado
				if ( ! empty( $dados ) && sizeof( $dados ) > 0 ) {
					print_r( $dados );
					$refreshTokenAtivo = true;
				}

			} catch ( \PDOException $e ) {
				// Nao mostrar em ambiente de producao
				Errors::filtro( function ( $e ) {
					echo $e->getMessage();
				}, function () {
				}, $e );
			}
		}

		return $refreshTokenAtivo;
	}

	// Remover refresh token

	public static function apagarRefresh() {

	}

	// Gerar access token através de ID do utilizador

	// Gerar novo refresh token através de ID do utilizador


}