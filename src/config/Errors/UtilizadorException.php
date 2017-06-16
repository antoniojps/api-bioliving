<?php

/*
 * Utilizada para gerir os erros da classe Utilizador exemplo de aplicaçao:
 *
try {
$user = new Utilizador( [
		'email'     => 'antoniojps@ua.pt',
		'nome'      => 'Antonio',
		'sobrenome' => 'Santos',
		'password'  => '123'
] );

$user->registrar();
} catch (\Bioliving\Errors\UtilizadorException $e){
	echo $e->getMessage();
}
 *  Irá fazer echo de "Password fraca"
 *
 * Ver metodo register e login da classe Utilizador.
 */

namespace Bioliving\Errors;


class UtilizadorException extends \Exception {
}