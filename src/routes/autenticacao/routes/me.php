<?php

use Bioliving\Custom\Token as Token;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

//////////// Me  ////////////
/*
 * Diz-nos se utilizador tem login feito ou nao
 * Com login: retorna array com id e scopes
 * Sem login: 401
 */

$app->get( '/api/me', function ( Request $request, Response $response ) {

	// Default
	$status = 401; // Unauthorized
	$data   = 'Unauthorized';

	if ( Token::getRefreshToken() || Token::getAccessToken() ) {
		$status = 200; // Ok
		$data   = [
				'id'    => Token::getUtilizador(),
				'scope' => Token::getScopes()
		];
	}

	$responseData = [
			'user' => $data
	];

	return $response
			->withJson( $data, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

} );