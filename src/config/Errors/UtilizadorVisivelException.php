<?php

/*
 * Utilizada enviar respostas ao utilizador sobre o erro ocorrido, esta será visivel para o mesmo em ambiente de producao
 *
try {
$user = new Utilizador( [
		'email'     => 'antoniojps@ua.pt',
		'nome'      => 'Antonio',
		'sobrenome' => 'Santos',
		'password'  => '123'
] );

$user->registrar();
} catch (\Bioliving\Errors\UtilizadorVisivelException $e){
	echo $e->getMessage();
}
 *  Irá fazer echo de "Password fraca"
 *
 * Ver metodo register e login da classe Utilizador.
 */

namespace Bioliving\Errors;


class UtilizadorVisivelException extends \Exception {
}