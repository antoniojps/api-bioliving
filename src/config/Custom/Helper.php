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
	 * Criar nome de imagem unico, retorna esse nome
	 */
 	static public function gerarIdUnico(){
		return  md5( uniqid( rand(), true ) );
 	}

	/*
	 * Conversão de imagem PNG em JPG
	 */

 	static public function pngToJpg($originalFile, $outputFile, $quality){
		$imagemOriginal = imagecreatefrompng($originalFile);
		$imagemJpg = imagejpeg($imagemOriginal, $outputFile, $quality);
		imagedestroy($imagemOriginal);

		return $imagemJpg;
 	}

 	static public function converterBitsMB($bits){
 		return round($bits  / 1024 / 1024,2);
 	}

	/*
	 * Converte caminhos de recursos locais em urls pois o browser nao aceita mostrar ficheiros locais
	 * Exemplo:
	 *
	 * Converte: file:///C:/xampp/htdocs/lab/public/imagens/avatars/e6ffb82712f49443f3649bb61232f577.jpg
	 * Em: http://localhost/lab/public/imagens/avatars/e6ffb82712f49443f3649bb61232f577.jpg
	 *
	 */

 	static public function obterUrl($tipoImagem,$idImagem){
		$pastaUpload = 'learning-SLIM/public/imagens/';

		if($tipoImagem === 'avatar'){
			$pastaUpload .= 'avatars/';
		}

		$url = 'http://' . $_SERVER['SERVER_NAME'] .'/'. $pastaUpload . $idImagem;

		return $url;
 	}

 	/*
 	 *	 Filtrar array: remover indices com valores nulls ou strings vazias ''
 	 */
 	 static public function filtrarArray($arr){
		 $arr = array_filter(array_map(function ($valor1) {
			 return $valor1 = array_filter($valor1, function ($valor2) {
				 return $valor2 !== null && $valor2 !== '';
			 });
		 }, $arr));

		 return $arr;
 	 }




}