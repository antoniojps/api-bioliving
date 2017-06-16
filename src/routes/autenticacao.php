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
 * PUT /token
 * Verificar que access token está prestes a expirar, verificar refresh token na base de dados, gerar novo access token e atualziar cookie
 * Scopes: todos
 *
 * DELETE /token
 * Adiciona refresh token à black list
 * Scopes: admin
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
	$status = 401; // Unauthorized
	$info   = 'Unauthorized';
	// Obter dados
	$parsedBody = $request->getParsedBody();
	$email      = $parsedBody['email'];
	$password   = $parsedBody['password'];

	if ( H::obrigatorio( $email ) && H::obrigatorio( $password ) ) {
		if ( ! Token::verificarRefresh() ) {
			try {
				// Verificar informações
				$user         = new Utilizador( [
						'email'    => $email,
						'password' => $password
				] );
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
				// Verificar informações
				$user = new Utilizador( [
						'email'     => $email,
						'nome'      => $nome,
						'sobrenome' => $sobrenome,
						'password'  => $password
				] );
				if ( $user->registrar() ) {

					$idUtilizador = $user->getId();

					// Criar tokens
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

