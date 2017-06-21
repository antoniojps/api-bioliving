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

}