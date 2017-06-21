<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Bioliving\Custom\Helper as H;

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

$app->post('/api/upload/avatar', function (Request $request, Response $response) {

	$status = 403; // Bad request
	$info   = 'Bad request';
	$nomeImagem = 'file'; // Nome no formulário
	$tamanhoMax = 1; // 1 MB

	$files = $request->getUploadedFiles();
	if (!empty($files[$nomeImagem])) {

		$avatar = $files[$nomeImagem];
		$nomeTemp = $avatar->file;

		$formato = $avatar->getClientMediaType();
		$tamanho = H::converterBitsMB($avatar->getSize());

		// Gerar novo nome unico
		$nomeUpload = H::gerarIdUnico().'.jpg';
		$pastaUpload = (dirname(dirname(dirname((dirname(__DIR__)))))).'\\public\\imagens\avatars\\'; // public/imagens/avatars
		$urlFinal = $pastaUpload.$nomeUpload;

		// Ver se tamanho nao excede limite
		if($tamanho <= $tamanhoMax){
			// Se PNG converter para JPG
			if($formato === 'image/png'){
			// Faz upload e converte para png
			if(H::pngToJpg($nomeTemp,$urlFinal,100)){
				$status = 200;
				$info = $urlFinal;
			}
		} else if($formato==='image/jpeg'){
				// Guardar
				move_uploaded_file($nomeTemp,$urlFinal);
			$status = 200;
			$info = $urlFinal;
		}
		}

	}
	$responseData = [
			'status' => $status,
			'info'   => $info
	];

	if($status===200){
		$responseData = [
				'status' => $status,
				'imagens'   => [
					'url' => $urlFinal
				]
		];
	}

	return $response
			->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
});