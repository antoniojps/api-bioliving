<?php

/*
 * Routes para todos endpoints relativos aos utilizadores:
 * Scopes: admin recebe todas as informações, outros recebem apenas necessárias
 *
 * GET /utilizadores?page=(int)&results=(int)&by=(coluna)&order=(asc||desc)
 * Obter todos os utilizadores
 *
 * GET /utilizadores/{id}
 * Obter dados sobre um utilizador
 *
 * GET /utilizadores/{id}/eventos
 * Obter eventos ao qual utilizador esta inscrito ou participou
 *
 * POST /utilizadores
 * Registar novo utilizador
 *
 * PUT /utilizadores/{id}
 * Atualizar utilizador
 * Scopes: admin ou token do request é igual ao {id}
 *
 * DELETE /utilizadores/{id}
 * Tornar utilizador inativo e cancelar todos os refresh tokens
 * Scopes: admin
 *
 */

// importar classes para scope
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Bioliving\Database\Db as Db;
use Bioliving\Custom\Token as Token;
use Bioliving\Errors\Errors as Errors;

//////////// Obter todos os utilizadores ////////////
# Parametros:
#   *page = pagina de resultados *obrigatorio
#   results = número de resultados por página
#   min: 1, max: 10
# Exemplo: /api/utilizadores?page=1&results=2
# Scope : admin

$app->get( '/api/utilizadores', function ( Request $request, Response $response ) {
	// ler scope $this->jwt->scope [  "normal", "socio", "admin" ]
	// Todo Apenas pode fazer request de todos os utilizadores com scope de admin
	// Todo Ordernar dinamicamente por coluna (byArr) e por ASC ou DESC
	// Todo verificação dos parametros

	if(Token::validarScopes('admin')){

	$byArr = [
			'id_utilizadores' => 'id',
			'nome'            => 'nome',
			'apelido'         => 'apelido',
			'genero'          => 'genero',
			'dataNascimento'  => 'data_nascimento',
			'dataRegisto'     => 'data_registo_user',
			'email'           => 'email',
			'ativo'           => 'ativo',
			'morada'          => 'morada'
	]; // Valores para ordernar por, fizemos uma array para simplificar queries

	$maxResults    = 10; // maximo de resultados por pagina
	$minResults    = 1; // minimo de resultados por pagina
	$byDefault     = $byArr['dataRegisto']; // order by predefinido
	$paginaDefault = 1; // pagina predefenida


	$parametros = $request->getQueryParams(); // obter parametros do query

	$page    = isset( $parametros['page'] ) ? (int) $parametros['page'] : $paginaDefault;
	$results = isset( $parametros['results'] ) ? (int) $parametros['results'] : $maxResults;
	$by      = isset( $parametros['by'] ) ? $parametros['by'] : $byDefault;

	if ( $page > 0 && $results > 0 ) {
		// definir numero de resultados
		// caso request tenha parametros superiores ao maximo permitido entao repor com maximo permitido e vice-versa
		$results = $results > $maxResults ? $maxResults : $results;
		$results = $results < $minResults ? $minResults : $results;

		// A partir de quando seleciona resultados
		$limitNumber = ( $page - 1 ) * $results;

		$sql = "SELECT id_utilizadores,utilizadores.nome AS nome,apelido,genero,data_nascimento,data_registo_user,email,foto,sobre,sobre_mini,telemovel,ativo,estatutos_id_estatutos,lat,lng,localizacao.nome AS morada FROM utilizadores LEFT OUTER JOIN estatutos ON utilizadores.estatutos_id_estatutos = estatutos.id_estatutos LEFT OUTER JOIN localizacao ON utilizadores.localizacao_id_localizacao = localizacao.localizacao ORDER BY data_registo_user DESC LIMIT :limit , :results";

		try {

			$status = 200; // OK
			// iniciar ligação à base de dados
			$db = new Db();

			// conectar
			$db   = $db->connect();
			$stmt = $db->prepare( $sql );
			$stmt->bindValue( ':limit', (int) $limitNumber, PDO::PARAM_INT );
			$stmt->bindValue( ':results', (int) $results, PDO::PARAM_INT );
			$stmt->execute();
			$db    = null;
			$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );

			// remover nulls e strings vazias
			$dados = array_filter( array_map( function ( $utilizador ) {
				return $utilizador = array_filter( $utilizador, function ( $coluna ) {
					return $coluna !== null && $coluna !== '';
				} );
			}, $dados ) );

			$dadosLength = (int) sizeof( $dados );
			if ( $dadosLength === 0 ) {
				$dados  = [ "error" => 'pagina inexistente' ];
				$status = 404; // Page not found

			} else if ( $dadosLength < $results ) {
				$dadosExtra = [ 'info' => 'final dos resultados' ];
				array_push( $dados, $dadosExtra );
			} else {
				$nextPageUrl = explode( '?', $_SERVER['REQUEST_URI'], 2 )[0];
				$dadosExtra  = [ 'proxPagina' => "$nextPageUrl?page=" . ++ $page . "&results=$results" ];
				array_push( $dados, $dadosExtra );
			}

			$responseData = [
					'status' => "$status",
					'data'   => [
							$dados
					]
			];

			return $response
					->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES |  JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


		} catch ( PDOException $err ) {
			$status   = 503; // Service unavailable
			// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
			$errorMsg = Errors::filtroReturn(function ($err) {
				return [

								"status" => $err->getCode(),
								"info" => $err->getMessage()

				];
			}, function () {
				return [
				        "status"=>503,
						"info" => 'Servico Indisponivel'
				];
			}, $err);

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
		}

	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [

						"status" => "$status",
						"info"   => 'Parametros invalidos'


		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}}else{

		$status   = 401; // Unauthorized
		$errorMsg = [

						"status" => "$status",
						"info"   => 'Unauthorized'


		];
		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

	}

});

//////////// Get Unico Utilizador ////////////
$app->get( '/api/utilizadores/{id}', function ( Request $request, Response $response, $args ) {
	$id = $args['id'];

	$sql = "SELECT id_utilizadores,utilizadores.nome AS nome,apelido,genero,data_nascimento,data_registo_user,foto,sobre,sobre_mini,ativo,estatutos_id_estatutos,localizacao.nome AS morada FROM utilizadores LEFT OUTER JOIN estatutos ON utilizadores.estatutos_id_estatutos = estatutos.id_estatutos LEFT OUTER JOIN localizacao ON utilizadores.localizacao_id_localizacao = localizacao.localizacao WHERE id_utilizadores = :id";

	// Todo verificação, sql e respostas

} );

/*
//////////// Registrar utilizador ////////////
$app->post('/api/utilizadores',function(Request $request, Response $response){

	// Obter parametros no body



	// SQL de Insert


});

//////////// Update Utilizador ////////////
$app->put('/api/customers/{id}',function(Request $request, Response $response){
	$id = $request->getAttribute('id');
	$first_name = $request->getParam('first_name');
	$last_name = $request->getParam('last_name');
	$phone = $request->getParam('phone');
	$email = $request->getParam('email');
	$address = $request->getParam('address');
	$city = $request->getParam('city');
	$state = $request->getParam('state');



	$sql = "UPDATE customers SET first_name = :first_name,
					last_name = :last_name,
					phone = :phone,
					email = :email,
					address = :address,
					city = :city,
					state = :state
					WHERE id = $id";

	try {
		$db = new Db();

		// conectar
		$db = $db->connect();
		$stmt = $db->prepare($sql);

		$stmt->bindParam(':first_name',$first_name);
		$stmt->bindParam(':last_name',$last_name);
		$stmt->bindParam(':phone',$phone);
		$stmt->bindParam(':email',$email);
		$stmt->bindParam(':address',$address);
		$stmt->bindParam(':city',$city);
		$stmt->bindParam(':state',$state);

		$stmt->execute();

		$db = null;

		$message = ['notice'=>'customer updated'];
		echo json_encode($message);


	} catch(PDOException $e) {
		$err = [
				"error"=>[
						"status"=>$e->getCode(),
						"text"=>$e->getMessage()
				]
		];
		echo json_encode($err);
	}
});

//////////// Delete Utilizador ////////////
$app->delete('/api/customers/{id}',function(Request $request, Response $response){
	$id = $request->getAttribute('id');
	$sql = "DELETE FROM customers WHERE id = $id";

	try {
		$db = new Db();

		// conectar
		$db = $db->connect();
		$stmt = $db->query($sql);
		$db = null;

		$message = ["notice"=>"customer $id deleted"];
		echo json_encode($message);

	} catch(PDOException $e) {
		$err = [
				"error"=>[
						"status"=>$e->getCode(),
						"text"=>$e->getMessage()
				]
		];
		echo json_encode($err);
	}
});

*/