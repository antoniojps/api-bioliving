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
 */

class Utilizador {
	private $nome;
	private $sobrenome;
	private $email;
	private $password;
	private $argumentos;

	public function __construct( $arr ) {

		if ( gettype( $arr ) !== 'array' ) {
			throw new Exception( "Argumento nao e uma array" );
		}

		$this->argumentos     = array_keys( $arr );
		$this->nome           = isset( $arr['nome'] ) ? ucfirst( strtolower( $arr['nome'] ) ) : null;
		$this->sobrenome      = isset( $arr['sobrenome'] ) ? ucfirst( strtolower( $arr['sobrenome'] ) ) : null;
		$this->email          = isset( $arr['email'] ) ? strtolower( $arr['email'] ) : null;
		$this->password       = isset( $arr['password'] ) ? $arr['password'] : null;

		print_r( $arr );
	}

// Permite verificar se foi instanciado com todos os argumentos necessarios para metodos que necessitem destes
// Exemplo: Metodo registrar necessita de ter nome,sobrenome,email,password, dataNascimento definidos, caso nao tenham retorna false e mostra um erro
	private function validArgNecessarios( $argArr ) {

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

		} catch ( PDOException $e ) {
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
		} catch ( PDOException $e ) {
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
*/
	public function registrar() {

		// Validações
		if ( $this->validArgNecessarios( [ 'email', 'nome', 'sobrenome', 'password'] ) ) {
			if ( $this->validarEmail() ) {
				if ( ! $this->emailExiste() ) {
					if ( $this->validarPassword() ) {
						if( $this->validarNomes()){

						// Insert na base de dados



						echo '<b>Registrar</b>';

						// return true;
						} else {
							echo 'Nomes nao sao validos';
						}
					} else {
						echo 'Password demasiado fraca';
					}
					// Email ja existe
				} else {
					echo 'Email ja existe';
				}
			} else {
				echo 'Email nao é valido';
			}
		} else {
			echo 'Argumentos em falta';
		}
	}

	// Login
	public function login() {
		// Verificar se utilizou email e password

		// Verificar se email existe

		// Verificar password

		// Gerar Token

		// Enviar Resposta
	}


}
