<?php
use \Firebase\JWT\JWT;
use Bioliving\Custom\Token as Token;
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

$app->get( '/certificados/{token}', function ( Request $request, Response $response ) {







});