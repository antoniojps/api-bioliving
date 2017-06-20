<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


//////////// Avatar  ////////////
/*
 * @param multi-form | imagem
 *
 * Verifica se imagem é jpg/png e tamanho maximo
 *
 * Redimensiona imagem para várias versões possiveis.
 *
 * Cria ficheiro na pasta respectiva
 *
 */

$app->post('/api/upload/avatar', function ($request, $response, $args) {

	$status = 403; // Bad request
	$info   = 'Bad request';

	$files = $request->getUploadedFiles();

	if (empty($files['newfile'])) {
		throw new Exception('Expected a newfile');
	}

	$newfile = $files['newfile'];
	// do something with $newfile

	$responseData = [
			'status' => $status,
			'info'   => $info
	];

	return $response
			->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
});