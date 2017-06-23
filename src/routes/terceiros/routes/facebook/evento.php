<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Helper as H;


$app->get( '/api/terceiros/facebook/evento/{id}', function ( Request $request, Response $response ) {

	$id = $request->getAttribute('id'); // ir buscar id
	// Default
	$dados        = '';
	$info = 'parametros invalidos';
	$status      = 400; // Bad request


	if(v::numeric()->length(2,50)->validate($id)){
	$fbToken = getenv('FACEBOOK_TOKEN');
	$fbToken = urlencode($fbToken);

	$facebookGetUrl = "https://graph.facebook.com/$id?fields=owner,name,start_time,end_time,place,description,attending_count,interested_count,maybe_count,cover,is_canceled&access_token=$fbToken";

		$facebookData = @file_get_contents($facebookGetUrl,true);
		if($facebookData === false){
			$info = "servico indisponivel";
			$status = 503; // Servico indisponivel
		} else {
			$info = "informacao obtida com sucesso";
			$status = 200; // Ok
			$dados = json_decode($facebookData);
		}
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
