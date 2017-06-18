<?php
// composer autoload
require_once '../vendor/autoload.php';
// Configuração de erros
require_once '../src/config/Errors/Errors.php';
// Para utilizar variaveis de environment dentro do .env
// https://github.com/vlucas/phpdotenv
$dotenv = new Dotenv\Dotenv(dirname(__DIR__));
$dotenv->load();
/* CLOUDINARY Configuraçao */
\Cloudinary::config(array(
		"cloud_name" => getenv('CLOUD_NAME'),
		"api_key" => getenv('API_KEY'),
		"api_secret" => getenv('API_SECRET')
));
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
require_once "../src/middleware/JwtAuthentication.php";
// Todo CSRF Token Middleware
// Routes:
# /token - Verificar dados, gerar token e criar cookie com o mesmo
require_once "../src/routes/autenticacao.php";
// Utilizadores
require "../src/routes/utilizadores.php";
// Eventos
require "../src/routes/eventos/getEventos.php";
require "../src/routes/eventos/putEventos.php";
require "../src/routes/eventos/postEventos.php";
require "../src/routes/eventos/deleteEventos.php";
// Locais
require "../src/routes/locais/postLocais.php";
// Tipos
require "../src/routes/tipos/postTipoEventos.php";
// Global
require "../src/routes/global/getGlobal.php";
//Tags
require "../src/routes/tags/postTags.php";
$app->run();