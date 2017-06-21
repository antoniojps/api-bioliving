<?php

use Bioliving\Custom\Helper as H;
use Intervention\Image\ImageManagerStatic as Image;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

// import the Intervention Image Manager Class

//////////// Avatar  ////////////
/*
 * @param multi-form | imagem
 *
 * Verifica se imagem é jpg/png e tamanho maximo
 *
 * Todo Redimensionar imagem para dimensão máxima de avatares
 *
 */
$app->post( '/api/upload/avatar', function ( Request $request, Response $response ) {

	$status     = 403; // Bad request
	$info       = 'Bad request';
	$nomeImagem = 'file'; // Nome no formulário
	$tamanhoMax = 1; // 1 MB

	$files = $request->getUploadedFiles();
	if ( ! empty( $files[ $nomeImagem ] ) ) {

		$avatar   = $files[ $nomeImagem ];
		$nomeTemp = $avatar->file;

		$formato = $avatar->getClientMediaType();
		$tamanho = H::converterBitsMB( $avatar->getSize() );

		// Gerar novo nome unico
		$nomeUpload  = H::gerarIdUnico() . '.jpg';
		$pastaUpload = ( dirname( dirname( dirname( ( dirname( __DIR__ ) ) ) ) ) ) . '\\public\\imagens\avatars\\'; // public/imagens/avatars
		$dirFinal    = $pastaUpload . $nomeUpload;
		$urlFinal    = H::obterUrl( 'avatar', $nomeUpload );

		// Ver se tamanho nao excede limite
		if ( $tamanho <= $tamanhoMax ) {
			// Se PNG converter para JPG
			if ( $formato === 'image/png' ) {
				// Faz upload e converte para png
				if ( H::pngToJpg( $nomeTemp, $dirFinal, 100 ) ) {
					$status = 200;
				}
			} else if ( $formato === 'image/jpeg' ) {
				// Guardar
				if ( move_uploaded_file( $nomeTemp, $dirFinal ) ) {
					$status = 200;
				}
			}
		}

	}
	$responseData = [
			'status' => $status,
			'info'   => $info
	];

	if ( $status === 200 ) {
		$responseData = [
				'status'  => $status,
				'imagens' => [
						'url' => $urlFinal
				]
		];
	}

	return $response
			->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
} );

/*
 * Método para obter imagens de avatar
 * PARAM: {ficheiro}
 * @param  string ficheiro (id do avatar pretendido)
 *
 *
 * URI (&altura=200)
 * @param string altura (Altura da imagem pretendida)
 * @param string largura (Largura da imagem pretendida)
 * Nota: Um dos parametros é obrigatório contudo nao se podem fazer queries com os dois para nao distorcer imagens
 *
 * Exemplo: /api/imagens/avatars/{nomeImagem}?altura=100
 *
 */

$app->get( '/api/imagens/avatars/{ficheiro}', function ( Request $request, Response $response ) {

	// Valores default
	$path    = '/imagens/avatars/';
	$largura = '250';
	$altura  = '250';
	$min     = 44;
	$max     = 600;

	// Obter informaçao do query
	$ficheiro   = $request->getAttribute( 'ficheiro' ) . '.jpg';
	$parametros = $request->getQueryParams();
	$largura    = isset( $parametros['largura'] ) ? (int) $parametros['largura'] : $largura;
	$altura     = isset( $parametros['altura'] ) ? (int) $parametros['altura'] : $altura;

	// Classe nativa do PHP que fornece informações sobre ficheiros
	// Ver: http://php.net/manual/en/class.splfileinfo.php
	$info = new SplFileInfo( $path );

	// Abrir imagem utilizando os metodos de livraria Intervention/Image
	// Ver: http://image.intervention.io/getting_started/introduction
	$img = Image::make( H::obterUrl( 'avatar', $ficheiro ) );

	// Validaçoes
	// Resize para o tamanho pedido mantendo o aspect ratio
	if ( isset( $parametros['altura'] ) && v::intVal()->between( $min, $max )->validate( $altura ) ) {
		$img->heighten( $altura );
	} else if ( isset( $parametros['largura'] ) && v::intVal()->between( $min, $max )->validate( $largura ) ) {
		$img->widen( $largura );
	}
	// Codificar em JPG
	$response->write( $img->encode( 'jpg' ) );

	return $response->withHeader( 'Content-Type', FILEINFO_MIME_TYPE );

} );

