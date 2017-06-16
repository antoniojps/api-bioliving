<?php

/*
 * Utilizada para gerir os erros da classe Utilizador exemplo de aplicaçao:
 *
try {
	Token::gerarRefreshToken(1);
} catch (\Bioliving\Errors\TokenException $e){
	echo $e->getMessage();
}
 *  Irá fazer echo de "Utilizador inativo/inexistente ou estatuto invalido" pois utilizador nao existe
 *
 * Ver metodo register e login da classe Utilizador.
 */

namespace Bioliving\Errors;


class TokenException extends \Exception {
}