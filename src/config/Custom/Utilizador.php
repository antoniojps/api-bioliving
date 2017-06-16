<?php

/*
 * Desenvolvido no âmbito da cadeira de LabMM4 2016 por:
 * António Santos, Diana Rocha, Joao Silva e Ruben Ferreira
 *
 * Sistema de registro e login para Gestor de Eventos da Bioliving
 *
 * Ao instanciar a class Utilizador definir parametro que é uma array com as chaves e valores seguintes:
 * string email (tem de ser um email valido)
 * string nome (apenas caracteres)
 * string sobrenome (apenas caracteres)
 * string password
		 * Maior que 8 caracteres
		 * Não pode ser igual ao email, nome, sobrenome, nome do site
		 * Nao pode ter caracteres que se repetem mais de 3 vezes
		 * Diferente das x (guardadas na bd) passwords mais utilizadas em Portugal
 *
 * Exemplo utilização:
 *
try {
	$user = new Utilizador( [
			'email'     => 'antoniojps@ua.pt',
			'nome'      => 'Antonio',
			'sobrenome' => 'Santos',
			'password'  => 'password123'
	] );

	$user->registrar();

} catch ( \Bioliving\Errors\UtilizadorException $e ) {
	echo $e->getMessage();
}
 *
 */

namespace Bioliving\Custom;

use Bioliving\Database\Db as Db;
use Bioliving\Errors\UtilizadorException as UtilizadorException;
use \PDO; // Import do namespace global do PDO


class Utilizador {
	private $nome;
	private $sobrenome;
	private $email;
	private $password;
	private $argumentos;

	public function __construct( $arr ) {

		if ( gettype( $arr ) !== 'array' ) {
			throw new UtilizadorException( "Parametro dos utilizadores nao e uma array" );
		}

		$this->argumentos     = array_keys( $arr );
		$this->nome           = isset( $arr['nome'] ) ? $arr['nome']: null;
		$this->sobrenome      = isset( $arr['sobrenome'] ) ? $arr['sobrenome'] : null;
		$this->email          = isset( $arr['email'] ) ? strtolower( $arr['email'] ) : null;
		$this->password       = isset( $arr['password'] ) ? $arr['password'] : null;

		print_r( $arr );
	}

// Permite verificar se foi instanciado com todos os argumentos necessarios para metodos que necessitem destes
// Exemplo: Metodo registrar necessita de ter nome,sobrenome,email,password, caso nao tenham retorna false e mostra um erro
	private function validArgNecessarios( $argArr ) {

		// Todo Fazer com que veja se existem os argumentos necessarios, neste momento iguala em vez de verificar se existem ou nao
		// Todo adicionar de seguida ao metodo getId
		// ver array_intersect
		sort( $this->argumentos );
		sort( $argArr );
		if ( $this->argumentos === $argArr ) {
			return true;
		} else {
			return false;
		}
	}

/*
 * Retorna true se email é valido, false se nao
 */
	private function validarEmail() {
		if ( filter_var( $this->email, FILTER_VALIDATE_EMAIL ) ) {
			return true;
		} else {
			return false;
		}
	}

/*
* Caso exista uma conta com o email retorna true, caso contrario retorna false
*/
	private function emailExiste() {
		// Verificar se email já existe
		$sql         = "SELECT id_utilizadores,email,ativo FROM utilizadores WHERE email = :email";
		$emailExiste = false;


		try {
			$db = new Db();

			// Conectar
			$db   = $db->connect();
			$stmt = $db->prepare( $sql );
			$stmt->bindParam( ':email', $this->email );
			if ( $stmt->execute() ) {
				$emailInput = $stmt->fetch( PDO::FETCH_ASSOC )['email'];
				// Retornou resultados
				if ( ! empty( $emailInput ) ) {
					$emailExiste = true;
				}
			}
			$db = null;

		} catch ( \PDOException $e ) {
			echo $e->getMessage();
			echo $e->getCode();
		}

		return (bool) $emailExiste;
	}

/*
* Verificaçoes da password:
* Maior que 8 caracteres
* Não pode ser igual ao email, nome, sobrenome, nome do site
* Nao pode ter caracteres que se repetem mais de 6 vezes
* Diferente das x passwords mais utilizadas em Portugal
*/

// Verifica que caracteres da password nao se repetem mais de 4 vezes
	private function validarPwCaracteres() {
		$maxRepeticoes     = 6;
		$caracteresValidos = false;
		// Ver quantas vezes sao utilizados caracteres
		$charCount = array_count_values( str_split( $this->password ) );
		// Encontrar algum caracter que é repetido $maxRepetidos ou mais
		foreach ( $charCount as $charRepetidos ) {
			if ( $charRepetidos >= $maxRepeticoes ) {
				$caracteresValidos = false;
				break;
			} else {
				$caracteresValidos = true;
			}
		}

		return $caracteresValidos;
	}

// Verifica se password esta na lista negra de passwords
// Retorna TRUE se ESTÁ e FALSE se NÃO ESTÁ
	private function verificarPwListNegra() {

		// Ver se password está na lista negra
		$naListaNegra = true;
		try {

			$sql = "SELECT * FROM `passwords_blacklist` WHERE `passwords` = :password";
			$db  = new Db();

			// Conectar
			$db   = $db->connect();
			$stmt = $db->prepare( $sql );
			$stmt->bindParam( ':password', $this->password );

			if ( $stmt->execute() ) {
				$db         = null;
				$resultados = $stmt->fetch( PDO::FETCH_ASSOC )['passwords'];
				if ( empty( $resultados ) ) {
					$naListaNegra = false;
				} else {
					$naListaNegra = true;
				}
			}
		} catch ( \PDOException $e ) {
			echo $e->getMessage();
			echo $e->getCode();
		}

		return (bool) $naListaNegra;

	}

	private function validarPassword() {
		$minCaracteres  = 8;
		$passwordValida = false;
		if ( strlen( $this->password ) >= $minCaracteres && $this->password !== strtolower( $this->nome ) && $this->password !== strtolower( $this->sobrenome ) && $this->password !== strtolower( $this->email ) && $this->password !== $_SERVER['SERVER_NAME'] && $this->validarPwCaracteres() ) {
			if ( $this->verificarPwListNegra() ) {
				$passwordValida = false;
			} else {
				$passwordValida = true;
			}
		} else {
			$passwordValida = false;
		}

		return (bool) $passwordValida;
	}

// Validar nome,sobrenome
	private function validarNomes(){

		$nomesValidos = false;

		$validarArr = [
			'nome' => $this->nome,
			'sobrenome' => $this->sobrenome
		];

	// RegEx para todos os caracteres inclusive latins e outros com minimo de uma letra e maximo de 50 (50 é o que o Facebook utiliza e é limite da base de dados)
	// https://stackoverflow.com/a/5429984/7663060

		$matches  = preg_grep ('/^[\p{L}\s]{1,50}$/u', $validarArr);

		if(sizeof($matches)==2){
			$nomesValidos = true;
		}

		return (bool) $nomesValidos;
	}

/*
* Registrar
 *
 * retorna user id se registrado com sucesso
 * retorna false caso nao tenha registado com sucesso, podendo se ler as exceptions com um catch \Bioliving\Errors\UtilizadorException
 *
 *
*/
	public function registrar() {

		$userRegistado = false;

		// Validações
		if ( $this->validArgNecessarios( [ 'email', 'nome', 'sobrenome', 'password'] ) ) {
			if ( $this->validarEmail() ) {
				if ( ! $this->emailExiste() ) {
					if ( $this->validarPassword() ) {
						if( $this->validarNomes()){

						// Insert na base de dados
						try {

							$password = password_hash($this->password,PASSWORD_DEFAULT);

							$db = new Db();
							$db = $db->connect();

							// Penultimo 1 = ativo, Ultimo 3 = Estatuto Normal
							$sql = "INSERT INTO utilizadores (id_utilizadores,nome, apelido, genero, data_nascimento, data_registo_user, email, password, foto, sobre, sobre_mini, telemovel, localizacao_id_localizacao, ativo, estatutos_id_estatutos) VALUES (NULL, :nome, :sobrenome, NULL, NULL, CURRENT_TIMESTAMP, :email, :password, NULL, NULL, NULL, NULL, NULL, '1', '3')";

							$stmt = $db->prepare($sql);

							$stmt->bindValue(':nome',$this->nome,  PDO::PARAM_STR);
							$stmt->bindValue(':sobrenome',$this->sobrenome, PDO::PARAM_STR);
							$stmt->bindValue(':email',$this->email, PDO::PARAM_STR);
							$stmt->bindValue(':password',$password, PDO::PARAM_STR);
							$stmt->execute();
							$db = null;

							$userRegistado = true;

						} catch (\PDOException $e){
							throw new UtilizadorException("Erro DB : " . $e->getMessage());
						}

						// return true;
						} else {
							throw new UtilizadorException( "Nomes nao sao validos" );
						}
					} else {
						throw new UtilizadorException( "Password fraca" );
					}
					// Email ja existe
				} else {
					throw new UtilizadorException( "Email existente" );
				}
			} else {
				throw new UtilizadorException( "Email invalido" );
			}
		} else {
			throw new UtilizadorException( "Parametros em falta para utilizador o metodo registrar" );
		}

		return $userRegistado;

	}

	// Login
	// Retorna id do utilizador se funcionou
	// Retorna false caso contrário
	public function login() {

		$userLogado = false;

		// Verificar se utilizou email e password
		if ( $this->validArgNecessarios( [ 'email','password'] ) ) {
			if ( $this->validarEmail() ) {

				// Obter hashed password do utilizador com o email utilizado
				try {


					$db = new Db();
					$db = $db->connect();

					// Na criaçao do token verifica-se se usar está ativo.
					$sql = "SELECT id_utilizadores,email,password FROM utilizadores WHERE email = :email";

					$stmt = $db->prepare($sql);

					$stmt->bindValue(':email',$this->email, PDO::PARAM_STR);
					$stmt->execute();
					$db = null;
					$utilizador = $stmt->fetch(PDO::FETCH_ASSOC);
						// Verificações se user está logado
					if($utilizador){
						if(password_verify($this->password,$utilizador['password'])){
							$userLogado = $utilizador['id_utilizadores'];
						} else {
							throw new UtilizadorException("Password incorreta");
						}
					} else {
						throw new UtilizadorException("Utilizador inexistente");
					}
				} catch (\PDOException $e){
					throw new UtilizadorException("Erro DB : " . $e->getMessage());
				}

			} else {
				throw new UtilizadorException( "Email invalido" );
			}
		} else {
			throw new UtilizadorException( "Parametros em falta para utilizador no metodo login" );
		}

		return $userLogado;

	}

	/*
	 * Metodo obtem o id do utilizador através de email
	 */

	public function getId(){

		$idUtilizador = false;

			try {
				$db = new Db();
				$db = $db->connect();

				// Na criaçao do token verifica-se se usar está ativo.
				$sql = "SELECT id_utilizadores,email,password FROM utilizadores WHERE email = :email";

				$stmt = $db->prepare( $sql );

				$stmt->bindValue( ':email', $this->email, PDO::PARAM_STR );
				$stmt->execute();
				$db         = null;
				$utilizador = $stmt->fetch( PDO::FETCH_ASSOC );
				// Verificações se user está logado
				if ( $utilizador ) {
					$idUtilizador = $utilizador['id_utilizadores'];
				} else {
					throw new UtilizadorException( "Utilizador inexistente no Metodo obterId" );
				}
			} catch ( \PDOException $e ) {
				throw new UtilizadorException( "Erro DB : " . $e->getMessage() );
			}
		return $idUtilizador;

	}


}
