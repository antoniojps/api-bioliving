<?php

/**
 * Esta classe foi criada para verificar e gerir o sistema de validação e atribuição de tokens num sistema de autenticação com access e refresh tokens, em seguida poderá ler todos os métodos existentes para uma pequena descrição do que fazem ver o link "mais informação"
 *
 * Metodos estaticos:
 * - getAccessToken
 * - getRefreshToken
 * - adeusCookies
 * - autenticar
 * - getSegRestantes
 * - getUtilizador
 * - getScopes
 * - verificarRefresh
 * - gerarAccessToken
 * - verificarAtivoScopes
 * - gerarRefreshToken
 * - desativarRefreshToken
 * - apagarTokens
 *
 * Mais informação em: https://goo.gl/osha0i
 *
 */

namespace Bioliving\Custom;

use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Firebase\JWT\JWT;
use PDO as PDO;
use Respect\Validation\Validator as v;


// Import do namespace global do PDO


class Token {
	private $token;
	private $tokenValido;
	public static $tokenPayload; // Null ate ser utilizado o metodo autenticarToken
	public static $defaultClaims = [
			"iss" => 'Bioeventos',
			"aud" => 'http://labmm.clients.ua.pt'
	];
	public static $tempoAccessToken = 3600; // 1 hora
	public static $tempoRefreshToken = 3600 * 24 * 365 * 4; // 4 anos
	private static $accessCookieName = 'token';
	private static $refreshCookieName = 'token_refresh';
	private static $algorithm = 'HS256';


	public function __construct( $token ) {
		$this->tokenValido = false;
		$this->token       = $token;

		if ( $this->validarSyntax() ) {
			$this->tokenValido = true;
		} else {
			// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
			Errors::filtro( function ( $e ) {
				throw new \Exception( "Token com syntax inválida" );
			}, function () {
			} );
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

	/*
	 * Apaga as cookies
	 */

	public static function adeusCookies() {
		// adeus cookies gostei de vos conhecer
		setcookie( self::$accessCookieName, 'Tanto coisa so para fazer login', time() - 3600, '/', null, null, true );
		setcookie( self::$refreshCookieName, 'E vou chegar ao fim e nao vai funcionar', time() - 3600, '/', null, null, true );
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

	/*
 * Cria array das scopes a partir do estatuto
 *
 * Estatutos:
 * -publico : acessível a toda a gente (não precisa de token) apenas podem LER informação e com POSTs muito limitados.
 * -normal: conta registada, permite aceder a endpoints cujo ID da conta equivale ao parâmetro do query respectivo ao ID.
 * -socio: para possíveis informações/páginas que apenas os sócios tenham acesso
 * -colaborador: pode criar eventos
 * -admin: tem acesso à área de administração e a informações privilegiadas e seguras
 */
	private static function gerarScopes( $estatuto ) {
		$arrScopes = [
				'publico'     => [ 'publico' ],
				'normal'      => [ 'publico', 'normal' ],
				'socio'       => [ 'publico', 'normal', 'socio' ],
				'colaborador' => [ 'publico', 'normal', 'socio', 'colaborador' ],
				'admin'       => [ 'publico', 'normal', 'socio', 'colaborador', 'admin' ]
		];

		return $arrScopes[ $estatuto ];
	}


	/*
	 * Verificar se
	 */
	// Verificar se refresh token existe na base de dados de acordo com o utilizador que esta na payload e se este ainda está ativo
	public static function verificarRefresh( $refreshToken = false ) {
		$refreshTokenAtivo = false;
		$refreshToken      = $refreshToken ? $refreshToken : self::getRefreshToken();

		$idUtilizador = self::getUtilizador( $refreshToken );

		if ( $idUtilizador ) {
			try {

				$sql = "SELECT utilizadores.id_utilizadores as utilizador_id, utilizadores_tokens.ativo as token_ativo, utilizadores.ativo as utilizador_ativo FROM utilizadores_tokens
								RIGHT JOIN utilizadores
								ON utilizadores_tokens.utilizadores_id_utilizadores = utilizadores.id_utilizadores
								WHERE (`utilizadores_id_utilizadores` = :id)
								AND (`refresh_token` = :refreshToken)";

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

				// Refresh token encontrado,ativo e conta esta ativa = verificado

				if ( ! empty( $dados ) && sizeof( $dados ) > 0 && $dados['token_ativo'] === '1' && $dados['utilizador_ativo'] === '1' ) {
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


	// Gerar access token através do refresh token

	public static function gerarAccessToken( $refreshToken = false ) {
		$tokenGerado  = false;
		$refreshToken = $refreshToken ? $refreshToken : self::getRefreshToken();

		// Verifica se refresh token é valido
		if ( self::autenticar( $refreshToken ) && self::verificarRefresh( $refreshToken ) ) {

			// Payload igual ao refresh token excepto jwt id e tempo atualizado
			$accessPayload = array(
					"iss"          => self::$tokenPayload['iss'],
					"aud"          => self::$tokenPayload['aud'],
					"exp"          => time() + self::$tempoAccessToken, // 24 horas para desenvolvimento
					"iat"          => time(),
					"idUtilizador" => self::$tokenPayload['idUtilizador'],
					"scope"        => self::$tokenPayload['scope']
			);

			# Criar token
			$tokenGerado = JWT::encode( $accessPayload, getenv( 'SECRET_KEY' ) );

			# Criar cookies
			// HTTP Only Cookie para NAO poder ser lida atraves de Javascript
			// Segurança contra ataques XSS
			setcookie( self::$accessCookieName, $tokenGerado, time() + self::$tempoAccessToken, '/', null, null, true );

			// Caso o refresh token já nao seja valido entao:
			// Apagar cookies (tokens) e refresh token da base de dados
			// Client-side terá que redireccionar para página de login

		} else {
			self::desativarRefreshToken(self::$tokenPayload['jti']);
			self::adeusCookies();
		}

		return $tokenGerado;

	}

	/*  Método para verificar se user esta ativo
	 *  Retorna o id do utilizador e scopes
	 *  Retorna false se desativo
	 */
	public static function verificarUserAtivoScopes( $idUtilizador ) {
		$utilizadorAtivo = false;

		// Obter estatutos do utilizador e se está ativo
		try {
			$sql = "SELECT id_utilizadores as idUtilizador, ativo, nome_estatuto FROM `utilizadores`
							RIGHT JOIN estatutos
							ON utilizadores.estatutos_id_estatutos = estatutos.id_estatutos
							WHERE id_utilizadores = :idUtilizador";

			// iniciar ligação à base de dados
			$db = new Db();

			// conectar
			$db = $db->connect();

			// Query
			$stmt = $db->prepare( $sql );
			$stmt->bindValue( ':idUtilizador', $idUtilizador, PDO::PARAM_INT );
			$stmt->execute();
			$db    = null;
			$dados = $stmt->fetch( PDO::FETCH_ASSOC );

			// Refresh token encontrado,ativo e conta esta ativa = verificado
			if ( ! empty( $dados ) && sizeof( $dados ) > 0 && $dados['ativo'] === '1' ) {
				// Filtragem de dados e obter scopes
				$utilizadorAtivo = $dados;
				$scope           = self::gerarScopes( $utilizadorAtivo['nome_estatuto'] );
				unset( $utilizadorAtivo['nome_estatuto'] );
				unset( $utilizadorAtivo['ativo'] );
				$utilizadorAtivo['scope'] = $scope;
			}

		} catch ( \PDOException $e ) {
			// Nao mostrar em ambiente de producao
			Errors::filtro( function ( $e ) {
				echo $e->getMessage();
			}, function () {
			}, $e );
		}

		return $utilizadorAtivo;
	}

	/*
	 *  Método para gerar novo refresh token através de ID do utilizador
	 *  Guarda na base de dados e cria cookie
	 *
	 *  NOTA: Este não verifica se o id dos utilizadores é valido! Cabe ao contexto em que é utilizado fazer essa verificação
	*/
	public static function gerarRefreshToken( $idUtilizador ) {
		$tokenGerado = false;

		//  Metodo verificarUserAtivoScopes faz a verificaçoes devidas e retorna uma array com idUtilizador e o scope deste
		$claimsObtidas = self::verificarUserAtivoScopes( $idUtilizador );

		// Gerar JWT Id Unico
		if ( $claimsObtidas ) {

			// Payload igual ao refresh token excepto jwt id e tempo atualizado
			$accessPayload = array(
					"iss"          => self::$defaultClaims['iss'],
					"aud"          => self::$defaultClaims['aud'],
					"exp"          => time() + self::$tempoRefreshToken,
					"iat"          => time(),
					"jti"          => md5( uniqid( rand(), true ) ), // Numero random como id
					"idUtilizador" => $claimsObtidas['idUtilizador'],
					"scope"        => $claimsObtidas['scope']
			);

			# Guardar na base de dados
			try {

				# Criar token
				$tokenGerado = JWT::encode( $accessPayload, getenv( 'SECRET_KEY' ) );

				$sql = "INSERT INTO utilizadores_tokens (id,utilizadores_id_utilizadores,data_criacao,refresh_token, ativo) VALUES (:jti,:idUtilizadores,CURRENT_TIMESTAMP, :tokenGerado, 1)";

				// iniciar ligação à base de dados
				$db = new Db();

				// conectar
				$db = $db->connect();

				// Binds
				$idUtilizador = $accessPayload['idUtilizador'];
				$jti          = $accessPayload['jti']; // jtw id

				// Query
				$stmt = $db->prepare( $sql );
				$stmt->bindValue( ':jti', $jti, PDO::PARAM_STR );
				$stmt->bindValue( ':idUtilizadores', $idUtilizador, PDO::PARAM_INT );
				$stmt->bindValue( ':tokenGerado', $tokenGerado, PDO::PARAM_STR );
				$stmt->execute();
				$db = null;

				# Criar cookie
				setcookie( self::$refreshCookieName, $tokenGerado, time() + self::$tempoRefreshToken, '/', null, null, true );

			} catch ( \PDOException $e ) {
				// Nao mostrar em ambiente de producao
				Errors::filtro( function ( $e ) {
					echo $e->getMessage();
				}, function () {
				}, $e );
			}
		}

		return $tokenGerado;
	}


	// Desativar refresh token atraves da claim JWT ID na base de dados
	// @param jti é o json web token id ( array ou string )
	// (alfanumerico, entre 15 e 50 caracteres e sem espaços)
	public static function desativarRefreshToken( $jti ) {
		$refreshDesativado = false;
		$jtiValido         = false;
		$jtiType           = gettype( $jti );
		// Apenas 1 jti
		// Validar todos os ids dos tokens se tem o formato correto

		if ( $jtiType === 'string' && v::alnum()->noWhitespace()->length( 15, 50 )->validate( $jti ) ) {
			$jtiValido = true;
			$jti       = [ "$jti" ];
			// >1 jti
		} else if ( $jtiType === 'array' ) {

			// remover duplicados (array value para dar reset aos indexes)
			$jti = array_values( array_unique( $jti ) );

			// Validar se todos os jtis da array sao validos
			for ( $i = 0; $i < count( $jti ); $i ++ ) {
				if ( v::alnum()->noWhitespace()->length( 15, 50 )->validate( $jti[ $i ] ) ) {
					$jtiValido = true;
				} else {
					echo $jti[ $i ];
					$jtiValido = false;
					break;
				}
			}
		}
		if ( $jtiValido ) {

			// Criação da querie, infelizmente nao da para fazer bind de parametros com o PDO em queries que involvam CASE, dai terem sido feitas as validações anteriores de modo a ter a certeza que nao é feita SQL injection, o acesso a esta funcao so vai ser limitada pela api

			$sqlWhen = '';
			foreach ( $jti as $id ) {
				$sqlWhen .= " WHEN '$id' THEN '0'";
			}

			$sqlIn = "'" . implode( "','", $jti ) . "'";

			/* Exemplo do SQL pretendido:
			 * UPDATE `utilizadores_tokens`
			 * SET `ativo` = CASE id
			 * WHEN '2964b31e9925a6d296ab330dbdc4d965' THEN '1'
			 * WHEN 'c5238fd1e50a29947537f43a70946de9' THEN '1'
			 * END WHERE id IN ('2964b31e9925a6d296ab330dbdc4d965','c5238fd1e50a29947537f43a70946de9')
			 */

			$sql = "UPDATE `utilizadores_tokens` SET `ativo` = CASE id $sqlWhen END WHERE id IN ($sqlIn)";


			try {
				// iniciar ligação à base de dados
				$db = new Db();

				// conectar
				$db = $db->connect();

				// Query
				$stmt = $db->prepare( $sql );
				if ( $stmt->execute() ) {
					$refreshDesativado = true;
				}
				$db = null;

			} catch ( \PDOException $e ) {
				// Nao mostrar em ambiente de producao
				Errors::filtro( function ( $e ) {
					echo $e->getMessage();
				}, function () {
				}, $e );
			}
		}

		return $jtiValido && $refreshDesativado;
	}

	// Apagar tokens
	// Verifica se existe a  cookie com o nome ‘token_refresh’ caso exista invoca o metodo verificarRefresh para averiguar se o mesmo é valido, se não é valido apenas apaga a(s) cookie(s) ( token e token_refresh ), caso seja válido torna o mesmo inválido (na base de dados) e só de seguida é que apaga a(s) cookie(s).

	public static function apagarTokens() {
		$refreshToken = self::getRefreshToken();

		if ( $refreshToken && self::verificarRefresh( $refreshToken ) ) {

			// Refresh token é valido, apagar cookies e meter como desativado na BD

			$jti = self::$tokenPayload['jti'];
			self::desativarRefreshToken($jti);
			self::adeusCookies();

		} else {

			// Refresh token nao é valido, apagar cookies

			self::adeusCookies();
		}
	}

}