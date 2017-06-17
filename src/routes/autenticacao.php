<?php


/*
 * Routes para todos endpoints relativos aos tokens
 * POST /login
 * Verificar dados, gerar access e refresh token e criar cookies com os mesmos
 * Scopes: todos
 *
 * POST /create
 * Verificar dados, insert na base de dados, gerar access e refresh token e criar cookies com os mesmos
 * Scopes: todos
 *
 * DELETE /login
 * Torna refresh token inativo e apaga as cookies.
 * Scopes: id do utilizador no token
 */

// importar classes para scope
use Bioliving\Custom\Helper as H;
use Bioliving\Custom\Token as Token;
use Bioliving\Custom\Utilizador as Utilizador;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

//////////// Login  ////////////
/*
 * @param string | email
 * @param string | password
 *
 * Nao tendo refresh token ativo, valida password e cria tokens.
 */
$app->post( '/api/login', function ( Request $request, Response $response ) {
// Valores default de resposta
	$status = 401; // Unauthorized
	$info   = 'Unauthorized';

// Obter dados
	$parsedBody = $request->getParsedBody();
	$email      = $parsedBody['email'];
	$password   = $parsedBody['password'];

	if ( H::obrigatorio( $email ) && H::obrigatorio( $password ) ) {
// Verificar se nao esta logged in
		if ( ! Token::verificarRefresh() ) {
			try {
// Verificar informações
				$user = new Utilizador( [
						'email'    => $email,
						'password' => $password
				] );

// Metodo login retorna id do utilizador ou false (se nao fez login)
				$idUtilizador = $user->login();

// Criar tokens
				if ( $idUtilizador ) {
					Token::gerarAccessToken( Token::gerarRefreshToken( $idUtilizador ) );
					$status = 200; // Ok
					$info   = 'Sucesso';
				}
			} catch ( \Bioliving\Errors\TokenException $e ) {
				$status = 401;

// Em ambiente de desenvolvimento mostra info sobre o erro, caso contrario apenas Unauthorized
				$info = Errors::filtroReturn( function ( $e ) {
					return $e->getMessage();
				}, function () {
					return 'Unauthorized';
				}, $e );

			} catch ( \Bioliving\Errors\UtilizadorException $e ) {
				$status = 401;
				$info   = Errors::filtroReturn( function ( $e ) {
					return $e->getMessage();
				}, function () {
					return 'Unauthorized';
				}, $e );
			}
		} else {
			$status = 401;
			$info   = Errors::filtroReturn( function () {
				return 'Refresh token ativo';
			}, function () {
				return 'Unauthorized';
			} );
		}
	} else {
		$info = Errors::filtroReturn( function () {
			return 'Parametros em falta';
		}, function () {
			return 'Unauthorized';
		} );
	}

	$responseData = [
			'status' => $status,
			'info'   => $info
	];

	return $response
			->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

} );

//////////// Registrar  ////////////
/*
 * @param string | email
 * @param string | nome
 * @param string | sobrenome
 * @param string | password
 *
 * Nao tendo refresh token ativo, faz insert das informaçoes na base de dados com password encriptada.
 */
$app->post( '/api/create', function ( Request $request, Response $response ) {
	$status = 401; // Unauthorized
	$info   = 'Unauthorized';
	// Obter dados
	$parsedBody = $request->getParsedBody();
	$email      = $parsedBody['email'];
	$nome       = $parsedBody['nome'];
	$sobrenome  = $parsedBody['sobrenome'];
	$password   = $parsedBody['password'];

	if ( H::obrigatorio( $email ) && H::obrigatorio( $password ) && H::obrigatorio( $nome ) && H::obrigatorio( $sobrenome ) ) {
// So pode registar se nao tiver sessao iniciada
		if ( ! Token::verificarRefresh() ) {
			try {
// Instanciar utilizador com parametros
				$user = new Utilizador( [
						'email'     => $email,
						'nome'      => $nome,
						'sobrenome' => $sobrenome,
						'password'  => $password
				] );
// Registar
				if ( $user->registrar() ) {
// Obter id
					$idUtilizador = $user->getId();

// Criar tokens (fazer login assim que regista)
					if ( $idUtilizador ) {
						Token::gerarAccessToken( Token::gerarRefreshToken( $idUtilizador ) );
						$status = 200; // Ok
						$info   = 'Sucesso';
					}
				}
			} catch ( \Bioliving\Errors\TokenException $e ) {
				$status = 401;

// Em ambiente de desenvolvimento mostra info sobre o erro, caso contrario apenas Unauthorized
				$info = Errors::filtroReturn( function ( $e ) {
					return $e->getMessage();
				}, function () {
					return 'Unauthorized';
				}, $e );

			} catch ( \Bioliving\Errors\UtilizadorException $e ) {
				$status = 401;
				$info   = Errors::filtroReturn( function ( $e ) {
					return $e->getMessage();
				}, function () {
					return 'Unauthorized';
				}, $e );
			}
		} else {
			$status = 401;
			$info   = Errors::filtroReturn( function () {
				return 'Refresh token ativo';
			}, function () {
				return 'Unauthorized';
			} );
		}
	} else {
		$info = Errors::filtroReturn( function () {
			return 'Parametros em falta';
		}, function () {
			return 'Unauthorized';
		} );
	}

	$responseData = [
			'status' => $status,
			'info'   => $info
	];

	return $response
			->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
} );

//////////// Logout  ////////////
/*
 * Apaga tokens caso exista pelo menos um.
 */
$app->delete( '/api/login', function ( Request $request, Response $response ) {

	$status = 503; // Service unavailable
	$info   = 'Serviço Indisponivel';

	if ( Token::getRefreshToken() || Token::getAccessToken() ) {
		if ( Token::apagarTokens() ) {
			$status = 200; // Ok
			$info   = 'Logout realizado';
		}
	} else {
		$status = 401; // Unauthorized
		$info   = 'Unauthorized';
	}

	$responseData = [
			'status' => $status,
			'info'   => $info
	];


	return $response
			->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
} );

