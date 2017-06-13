<?php


/*
 * Routes para todos endpoints relativos aos tokens
 * POST /token
 * Verificar dados, gerar access e refresh token e criar cookies com os mesmos
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
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;
use Bioliving\Database\Db as Db;
use Bioliving\Custom\Autenticacao as Autenticacao;

//////////// Criar token ////////////

$app->post('/api/token',function(Request $request, Response $response){
	// verificacoes se user existe, se pass esta correta entre outros

	// Gerar tokens
	$key = getenv('SECRET_KEY');

	# Definir payloads

	// Access token
	$accessPayload = array(
			"iss" => "Aplicacao Bioliving",
			"aud" => "http://example.com",
			"exp" => time()+3600*24, // 24 horas para desenvolvimento
			"iat" => time(),
			"idUtilizador" => "69",
			"scope"=>['normal','socio','admin']
	);

	// Refresh token
	$refreshPayload = array(
			"iss" => "Aplicacao Bioliving",
			"aud" => "http://example.com",
			"exp" => time()+86400*30, // 86400*30 30 dias
			"iat" => time(),
			"idUtilizador" => "69",
			"scope"=>['normal','socio','admin']
	);

	# Criar tokens
	$accessJwt = JWT::encode($accessPayload, $key);
	$refreshJwt = JWT::encode($refreshPayload, $key);


	# Criar cookies
	// HTTP Only Cookie que NAO poder ser lida atraves de Javascript
	// Segurança contra ataques XSS
	setcookie('token',$accessJwt,time()+(3600*24),'/',null,null,true);
	setcookie('token_refresh',$refreshJwt,time()+(86400*7),'/',null,null,true);


	// Apagar cookie
	//setcookie('token',$accessJwt,time()-3600,'/',null,null,true);
	//setcookie('token_refresh',$refreshJwt,time()-3600,'/',null,null,true);


	$responseData = [
			'status'=>'success',
			'data'=>[
					'JWT'=>"$accessJwt",
					'JWT Refresh'=>"$refreshJwt"
			]
	];

	return $response
			->withHeader("Content-Type", "application/json")
			->write(json_encode($responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
});
