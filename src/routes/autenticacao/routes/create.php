<?php

// importar classes para scope
use Bioliving\Custom\Helper as H;
use Bioliving\Custom\Token as Token;
use Bioliving\Custom\Utilizador as Utilizador;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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
// Registrar
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