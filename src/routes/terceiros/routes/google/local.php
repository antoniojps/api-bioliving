<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Helper as H;

$app->get( '/terceiros/google/local/{msg}', function ( Request $request, Response $response ) {

	$msg = $request->getAttribute('msg'); // ir buscar id
	// Default
	$dados        = '';
	$info = 'parametros invalidos';
	$status      = 400; // Bad request

		$googleGetUrl = "http://maps.google.com/maps/api/geocode/json?address=$msg";

		$googleData = @file_get_contents($googleGetUrl,true);


		if($googleData === false){
			$info = "servico indisponivel";
			$status = 503; // Servico indisponivel
		} else {
			$info = "informacao obtida com sucesso";
			$status = 200; // Ok
			$dados = json_decode($googleData);
		}

	$responseData = [
			'status' => "$status",
			'info' => "$info",
			'data' => $dados
	];

	$responseData = H::filtrarArr($responseData);


	return $response
			->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

} );
