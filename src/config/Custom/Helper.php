<?php
/**
 * Class Helper
 *
 * Tem funcoes utilizadas repetitivamente ao longo do projeto
 *
 * Para utilizar basta meter no topo:
 * use Bioliving\Custom\Helper as H;
 *
 * Depois para invocar uma funcao/metodo basta:
 * H::nomeDaFuncao();
 */

namespace Bioliving\Custom;

class Helper {

	// Utilizada para verificar se um parametro obrigatorio foi enviado no request
	static public function obrigatorio($param){
		return (! is_null( $param ) && ! empty( $param ));
	}

	/*
	 * Verifica scopes, se as scopes estao de acordo com o requisito entao retorna true, caso contrario retorna false
	 * $scopeToken = scopes do utilizador que estao no token
	 * $scopeRoute = scopes que podem aceder a route
	 */

 	static public function scopes($scopeToken, $scopeRoute){

 	}


}