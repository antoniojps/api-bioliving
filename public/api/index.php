<?php
// composer autoload
require_once '../../vendor/autoload.php';
// Configuração de erros
require_once '../../src/config/Errors/Errors.php';
// Para utilizar variaveis de environment dentro do .env
// https://github.com/vlucas/phpdotenv
$dotenv = new Dotenv\Dotenv(dirname(dirname(__DIR__)));
$dotenv->load();

/* SLIM APP */
// criar objeto slim com container (src/config/errors/errors.php) $c
$app = new \Slim\App($c);
// Adicionar um objeto vazio (StdClass no PHP) ao slim container para depois poder guardar neste os decoded JWT para utilizar na aplicação sobretudo para ler scopes através de $this->jwt->scope
// Ver callback nas configurações do jwt em config/jwt/config.php
$container = $app->getContainer();
$container["jwt"] = function ($container) {
	return new StdClass;
};
// Middleware
// JWT Autenticaçao
require_once "../../src/middleware/JwtAuthentication.php";
// Todo CSRF Token Middleware

// CORS para localhost
$app->options('/{routes:.+}', function ($request, $response, $args) {
	return $response;
});

$app->add(function ($req, $res, $next) {
	$response = $next($req, $res);
	return $response
			->withHeader('Access-Control-Allow-Origin', 'http://localhost:8080')
			->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, content-type, Accept, Origin, Authorization')
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
			->withHeader('Access-Control-Allow-Credentials', 'true');
});

// Routes:
# /token - Verificar dados, gerar token e criar cookie com o mesmo
require_once "../../src/routes/autenticacao/routes.php";
// Utilizadores
require "../../src/routes/utilizadores/routesUtilizadores.php";
// Eventos
require "../../src/routes/eventos/routesEventos.php";
// Locais
require "../../src/routes/locais/routesLocais.php";
// Tipos
require "../../src/routes/tipos/routesTipos.php";
// Global
require "../../src/routes/global/routesGlobal.php";
// Tags
require "../../src/routes/tags/routesTags.php";
// Interesses
require  "../../src/routes/interesses/routesInteresses.php";
// inscritos
require  "../../src/routes/inscritos/routesInscritos.php";

// Upload
require  "../../src/routes/upload/routes.php";

// Terceiros (Facebook, etc)
require  "../../src/routes/terceiros/routes.php";
// Terceiros (Facebook, etc)
require  "../../src/routes/certificados/routes.php";
$app->run();
