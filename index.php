<?php
// Visto nao termos acesso ao servidor (ficheiros nao publicos) da universidade fizemos um workaround para manter os ficheiros seguros:
// - Redireccionamento da root para /public
// - Bloquear acesso aos ficheiros protegidos atraves de htaccess
header('Location: public/');