<?php


/*
 * Routes para todos endpoints relativos aos tokens
 * POST /login
 * Verificar dados, gerar access e refresh token e criar cookies com os mesmos
 * Scopes: todos
 *
 * DELETE /login = logout
 * Torna refresh token inativo e apaga as cookies.
 * Scopes: id do utilizador no token
 *
 * POST /create
 * Verificar dados, insert na base de dados, gerar access e refresh token e criar cookies com os mesmos
 * Scopes: todos
 *
 */

//////////// Login e Logout  ////////////
require_once 'routes/login.php';

//////////// Registrar  ////////////
require_once 'routes/create.php';

//////////// Info sobre o utilizador  ////////////
require_once 'routes/me.php';


