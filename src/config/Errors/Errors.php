<?php

namespace Bioliving\Errors;


$configuration = [
		'settings' => [
			// Only set this if you need access to route within middleware
				'determineRouteBeforeAppMiddleware' => true
		]
];

$c = new \Slim\Container( $configuration );

// Handler para pagina nao encontrada
$c['notFoundHandler'] = function ( $c ) {
	return function ( $request, $response ) use ( $c ) {
		return $c['response']
				->withStatus( 404 )
				->withHeader( 'Content-Type', 'text/html' )
				->write( 'Pagina nao encontrada' );
	};
};

// Handler caso seja metodo errado para a route
$c['notAllowedHandler'] = function ( $c ) {
	return function ( $request, $response, $methods ) use ( $c ) {
		return $c['response']
				->withStatus( 405 )
				->withHeader( 'Allow', implode( ', ', $methods ) )
				->withHeader( 'Content-type', 'text/html' )
				->write( 'Metodo invalido, devia ser ' . implode( ', ', $methods ) );
	};
};


/*
 * Esconder erros em ambiente de produção e fazer log em ficheiro escondido
 */

if ( getenv( 'PHP_ENV' ) === 'producao' ) {
	ini_set( "display_errors", 0 );
	ini_set( "log_errors", 1 );
	//Define where do you want the log to go, syslog or a file of your liking with
	ini_set( "error_log", "syslog" ); // or ini_set("error_log", "/path/to/syslog/file");
} else if ( getenv( 'PHP_ENV' ) === 'desenvolvimento' ) {
	error_reporting( E_ALL );
}

// Gestao dos erros, classe utiliza sempre que queremos apanhar erros que auxiliem no desenvolvimento:
// try{
//
//  } catch(\PDOException $e){
//  Errors::filtro(function($e){
//  	echo $e->getMessage();
//  },function($e){
//  	echo 'Em producao';
//  },$e);
//  }
class Errors {

	// Apenas chama a funçao de acordo com a variavel de environment
	public static function filtro( $desenvolvimento, $producao = null, $exception = null) {
		$producao = $producao ? $producao: function(){};
		if ( is_callable( $desenvolvimento ) && is_callable($producao) ) {
			if ( getenv( 'PHP_ENV' ) === 'desenvolvimento' ) {
				call_user_func($desenvolvimento,$exception);
			} else {
				call_user_func($producao,$exception);
			}
		}
	}

	// retorna valores
	public static function filtroReturn( $desenvolvimento, $producao = null, $exception = null) {

		$resultado = null;

		$producao = $producao ? $producao: function(){};
		if ( is_callable( $desenvolvimento ) && is_callable($producao) ) {
			if ( getenv( 'PHP_ENV' ) === 'desenvolvimento' ) {
				$resultado = call_user_func($desenvolvimento,$exception);
			} else {
				$resultado = call_user_func($producao,$exception);
			}
		}

		return $resultado;

	}

}