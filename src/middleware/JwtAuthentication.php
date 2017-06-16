<?php

/*
 * Middleware: https://github.com/tuupola/slim-jwt-auth
 * parses and authenticates a token when passed via header or cookie.
 */

// Middleware para autenticação de token
$app->add(new \Slim\Middleware\JwtAuthentication([

		'secret' => getenv('SECRET_KEY'),

		"path" => "/api", // caminho onde irá ser feita autenticação

		"passthrough" => ["/api/token"], // Routes onde nao é necessário autenticação

		"secure" => true, // apenas funciona com https

		"relaxed" => ["localhost", "slimapp"], // hosts para obter requests sem https

		"callback" => function ($request, $response, $arguments) use ($container) {
			// Guarda informação da payload do JWT em $container['jwt'] através de closure
			// Podem ser posteriormente acedidos com $this->jwt->propriedade
			$container["jwt"] = $arguments["decoded"];
		},

		"error" => function ($request, $response, $arguments) { // resposta no erro

			// Todo se já expirou verificar se refresh token é valido, se sim, redirect para a route requisitada caso contrário mostrar não autorizado com codigo 401

			$route = $request->getUri()->getPath();

			// Verificar se refresh token é valido



			// Em desevolvimento dizer qual o erro, em producao mostrar apenas nao autorizado
			$data['route'] = $route;
			$data["status"] = "error";
			$data["message"] = getenv('PHP_ENV') === 'desenvolvimento' ? $arguments["message"] : 'Nao autorizado';
			return $response
					->withHeader("Content-Type", "application/json")
					->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
		}
]));