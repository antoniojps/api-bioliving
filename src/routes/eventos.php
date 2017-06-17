<?php
// TODO: GET para pesquisar eventos por nome, tags , por horas, tipo e localização etc individualmente e em grupo
// TODO: GET contagem de eventos de x em x tempo

/*
 * Routes para todos endpoints relativos aos eventos:
 * Scopes: admin recebe todas as informações, outros recebem apenas necessárias
 *
 *
 * DELETE /eventos/{id}
 * Scopes: admin ou colaborador que criou evento
 *
 */
// importar classes para scope
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

//////////// Obter todos os eventos ////////////
# Variaveis alteráveis:
#   min: 1, max: 10
# Exemplo: /api/eventos?page=1&results=2&by=id&order=ASC
$app->get( '/api/eventos', function ( Request $request, Response $response ) {

	$byArr = [
			'id'          => 'id_eventos',
			'nome'        => 'nome_evento',
			'dataRegisto' => 'data_registo_evento',
			'dataEvento'  => 'data_evento',
			'dataFim'     => 'data_fim',
			'ativo'       => 'ativo',
			'tipoEvento'  => 'nome_tipo_evento',
			'local'       => 'nome'
	]; // Valores para ordernar por, fizemos uma array para simplificar queries


	$maxResults    = 10; // maximo de resultados por pagina
	$minResults    = 1; // minimo de resultados por pagina
	$byDefault     = 'id'; // order by predefinido
	$paginaDefault = 1; // pagina predefenida
	$orderDefault  = "DESC"; //ordenação predefenida

	$parametros = $request->getQueryParams(); // obter parametros do querystring
	$page       = isset( $parametros['page'] ) ? (int) $parametros['page'] : $paginaDefault;
	$results    = isset( $parametros['results'] ) ? (int) $parametros['results'] : $maxResults;
	$by         = isset( $parametros['by'] ) ? $parametros['by'] : $byDefault;
	$order      = isset( $parametros['order'] ) ? $parametros['order'] : $orderDefault;

	if ( $page > 0 && $results > 0 ) {
		//definir numero de resultados
		//caso request tenha parametros superiores ao numero máximo permitido então repor com o valor maximo permitido e vice-versa
		$results = $results > $maxResults ? $maxResults : $results; //se o querystring results for maior que o valor maximo definido passa a ser esse valor maximo definido
		$results = $results < $minResults ? $minResults : $results; //se o querystring results for menor que o valor minimo definido passa a ser esse valor minimo definido
		//caso tenha parametros diferentes de "ASC" ou "DESC" então repor com o predefinido
		$order = $order == "ASC" || $order == "DESC" ? $order : $orderDefault;
		//order by se existe como key no array, caso nao repor com o predefenido
		$by = array_key_exists( $by, $byArr ) ? $by : $byDefault;

		// A partir de quando seleciona resultados
		$limitNumber = ( $page - 1 ) * $results;
		$passar      = $byArr[ $by ];
		if ( $order == $orderDefault ) {
			$sql = "SELECT id_eventos, nome_evento,nome_tipo_evento as tipo,data_registo_evento, data_evento,data_fim,descricao_short,descricao,max_participantes,min_participantes,idade_minima,fotos, ativo, COUNT(utilizadores_id_utilizadores) AS participantes,lat,lng,localizacao.nome as local FROM eventos LEFT OUTER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao LEFT OUTER JOIN participantes ON eventos.id_eventos = participantes.eventos_id_eventos LEFT OUTER JOIN tipo_evento ON eventos.tipo_evento_id_tipo_evento = tipo_evento.id_tipo_evento GROUP BY id_eventos ORDER BY $passar DESC LIMIT :limit , :results";
		} else {
			$sql = "SELECT id_eventos, nome_evento,nome_tipo_evento as tipo,data_registo_evento, data_evento,data_fim,descricao_short,descricao,max_participantes,min_participantes,idade_minima,fotos, ativo, COUNT(utilizadores_id_utilizadores) AS participantes,lat,lng,localizacao.nome as local FROM eventos LEFT OUTER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao LEFT OUTER JOIN participantes ON eventos.id_eventos = participantes.eventos_id_eventos LEFT OUTER JOIN tipo_evento ON eventos.tipo_evento_id_tipo_evento = tipo_evento.id_tipo_evento GROUP BY id_eventos ORDER BY $passar  LIMIT :limit , :results";

		}

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
			$dados = array_filter( array_map( function ( $evento ) {
				return $evento = array_filter( $evento, function ( $coluna ) {
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
					->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


		} catch ( PDOException $err ) {
			$status = 503; // Service unavailable
			// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
			$errorMsg = Errors::filtroReturn( function ( $err ) {
				return [
						"error" => [
								"status" => $err->getCode(),
								"text"   => $err->getMessage()
						]
				];
			}, function () {
				return [
						"error" => 'Servico Indisponivel'
				];
			}, $err );

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
		}

	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => 'Parametros invalidos'

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}
} );

//////////// Obter dados de um evento através do ID ////////////
$app->get( '/api/eventos/{id}', function ( Request $request, Response $response ) {
	$id = (int) $request->getAttribute( 'id' ); // ir buscar id
	//verificar se é um id válido
	if ( is_int( $id ) && $id > 0 ) {

		$sql = "SELECT eventos.*,tipo_evento.nome_tipo_evento as tipo,localizacao.*,COUNT(utilizadores_id_utilizadores) as participantes FROM eventos LEFT OUTER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao LEFT OUTER JOIN participantes ON eventos.id_eventos = participantes.eventos_id_eventos LEFT OUTER JOIN tipo_evento ON eventos.tipo_evento_id_tipo_evento = tipo_evento.id_tipo_evento  WHERE id_eventos = :id";

		try {

			$status = 200; // OK

			// iniciar ligação à base de dados
			$db = new Db();

			// conectar
			$db   = $db->connect();
			$stmt = $db->prepare( $sql );
			$stmt->bindValue( ':id', $id, PDO::PARAM_INT );
			$stmt->execute();
			$db    = null;
			$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );

			// remover nulls e strings vazias
			$dados = array_filter( array_map( function ( $evento ) {
				return $evento = array_filter( $evento, function ( $coluna ) {
					return $coluna !== null && $coluna !== '';
				} );
			}, $dados ) );


			$dadosLength        = (int) sizeof( $dados );
			$dadosDoArrayLength = (int) sizeof( $dados[0] ); // filtrar os participantes = 0
			if ( $dadosLength === 0 || $dadosDoArrayLength === 1 || $dadosDoArrayLength === 0 ) {
				$dados  = [ "error" => 'evento inexistente' ];
				$status = 404; // Page not found
			}

			$responseData = [
					'status' => "$status",
					'data'   => [
							$dados
					]
			];

			return $response
					->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


		} catch ( PDOException $err ) {
			$status = 503; // Service unavailable
			// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
			$errorMsg = Errors::filtroReturn( function ( $err ) {
				return [
						"error" => [
								"status" => $err->getCode(),
								"text"   => $err->getMessage()
						]
				];
			}, function () {
				return [
						"error" => 'Servico Indisponivel'
				];
			}, $err );

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

		}
	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => 'Parametros invalidos'

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}
} );

//////////// Obter utilizadores que participaram/inscreveram num evento ////////////
# Parametros:
#   *page = pagina de resultados *obrigatorio
#   results = número de resultados por página
#   min: 1, max: 10
# Exemplo: /api/eventos/2/utilizadores?page=1&results=2&by=id&order=ASC
$app->get( '/api/eventos/{id}/utilizadores', function ( Request $request, Response $response ) {
	$id = (int) $request->getAttribute( 'id' ); // ir buscar id

	$byArr = [
			'id'           => 'id_utilizadores',
			'nome'         => 'nome',
			'nomeEstatuto' => 'nome_estatuto'

	]; // Valores para ordernar por, fizemos uma array para simplificar queries


	//valores default
	$maxResults    = 10; // maximo de resultados por pagina
	$minResults    = 1; // minimo de resultados por pagina
	$byDefault     = 'id'; // order by predefinido
	$paginaDefault = 1; // pagina predefenida
	$orderDefault  = "DESC"; //ordenação predefenida


	$parametros = $request->getQueryParams(); // obter parametros do querystring
	$page       = isset( $parametros['page'] ) ? (int) $parametros['page'] : $paginaDefault;
	$results    = isset( $parametros['results'] ) ? (int) $parametros['results'] : $maxResults;
	$by         = isset( $parametros['by'] ) ? $parametros['by'] : $byDefault;
	$order      = isset( $parametros['order'] ) ? $parametros['order'] : $orderDefault;


	if ( is_int( $id ) && $id > 0 && $page > 0 && $results > 0 ) {

		//caso request tenha parametros superiores ao numero máximo permitido então repor com o valor maximo permitido e vice-versa
		$results = $results > $maxResults ? $maxResults : $results; //se o querystring results for maior que o valor maximo definido passa a ser esse valor maximo definido
		$results = $results < $minResults ? $minResults : $results; //se o querystring results for menor que o valor minimo definido passa a ser esse valor minimo definido
		//caso tenha parametros diferentes de "ASC" ou "DESC" então repor com o predefinido
		$order = $order == "ASC" || $order == "DESC" ? $order : $orderDefault;
		//order by se existe como key no array, caso nao repor com o predefenido
		$by = array_key_exists( $by, $byArr ) ? $by : $byDefault;
		//A partir de quando seleciona resultados
		$limitNumber = ( $page - 1 ) * $results;
		$passar      = $byArr[ $by ];

		//Apenas informações básicas dos inscritos para por exemplo cards, e ao clicar na card o utilizador pode ser reencaminhado para o masterdetail do utlizador da card
		if ( $order == $orderDefault ) {
			$sql = "SELECT id_estatutos,nome_estatuto, `id_utilizadores`,`nome`,`apelido`,`foto`,`sobre_mini`,`telemovel` FROM `utilizadores` INNER JOIN estatutos ON utilizadores.`estatutos_id_estatutos` = estatutos.id_estatutos INNER JOIN participantes ON utilizadores.id_utilizadores = participantes.utilizadores_id_utilizadores WHERE eventos_id_eventos = :id ORDER BY $passar DESC LIMIT :limit , :results";
		} else {
			$sql = "SELECT id_estatutos,nome_estatuto, `id_utilizadores`,`nome`,`apelido`,`foto`,`sobre_mini`,`telemovel` FROM `utilizadores` INNER JOIN estatutos ON utilizadores.`estatutos_id_estatutos` = estatutos.id_estatutos INNER JOIN participantes ON utilizadores.id_utilizadores = participantes.utilizadores_id_utilizadores WHERE eventos_id_eventos = :id ORDER BY $passar  LIMIT :limit , :results";

		}


		try {
			$status = 200; // OK

			// iniciar ligação à base de dados
			$db = new Db();

			// conectar
			$db   = $db->connect();
			$stmt = $db->prepare( $sql );
			$stmt->bindValue( ':id', $id, PDO::PARAM_INT );
			$stmt->bindValue( ':limit', (int) $limitNumber, PDO::PARAM_INT );
			$stmt->bindValue( ':results', (int) $results, PDO::PARAM_INT );
			$stmt->execute();
			$db    = null;
			$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );

			// remover nulls e strings vazias
			$dados = array_filter( array_map( function ( $evento ) {
				return $evento = array_filter( $evento, function ( $coluna ) {
					return $coluna !== null && $coluna !== '';
				} );
			}, $dados ) );


			$dadosLength = (int) sizeof( $dados );

			if ( $dadosLength === 0 ) {
				$dados  = [ "error" => 'participantes/página inexistentes' ];
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
					->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


		} catch ( PDOException $err ) {
			$status = 503; // Service unavailable

			// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
			$errorMsg = Errors::filtroReturn( function ( $err ) {
				return [
						"error" => [
								"status" => $err->getCode(),
								"text"   => $err->getMessage()
						]
				];
			}, function () {
				return [
						"error" => 'Servico Indisponivel'
				];
			}, $err );

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
		}
	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => 'Parametros invalidos'

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}


} );

//////////// Obter colaboradores de um evento ////////////
# Parametros:
#   *page = pagina de resultados *obrigatorio
#   results = número de resultados por página
#   min: 1, max: 10
#   Exemplo: /api/eventos/2/colaboradores?page=1&results=4&by=id&order=ASC
$app->get( '/api/eventos/{id}/colaboradores', function ( Request $request, Response $response ) {
	$id = (int) $request->getAttribute( 'id' ); // ir buscar id

	$byArr = [
			'id'   => 'colaboradores_id_colaboradores',
			'nome' => 'nome'

	]; // Valores para ordernar por, fizemos uma array para simplificar queries


	//valores default
	$maxResults    = 3; // maximo de resultados por pagina
	$minResults    = 1; // minimo de resultados por pagina
	$byDefault     = 'id'; // order by predefinido
	$paginaDefault = 1; // pagina predefenida
	$orderDefault  = "DESC"; //ordenação predefenida


	$parametros = $request->getQueryParams(); // obter parametros do querystring
	$page       = isset( $parametros['page'] ) ? (int) $parametros['page'] : $paginaDefault;
	$results    = isset( $parametros['results'] ) ? (int) $parametros['results'] : $maxResults;
	$by         = isset( $parametros['by'] ) ? $parametros['by'] : $byDefault;
	$order      = isset( $parametros['order'] ) ? $parametros['order'] : $orderDefault;


	if ( is_int( $id ) && $id > 0 && $page > 0 && $results > 0 ) {

		//caso request tenha parametros superiores ao numero máximo permitido então repor com o valor maximo permitido e vice-versa
		$results = $results > $maxResults ? $maxResults : $results; //se o querystring results for maior que o valor maximo definido passa a ser esse valor maximo definido
		$results = $results < $minResults ? $minResults : $results; //se o querystring results for menor que o valor minimo definido passa a ser esse valor minimo definido
		//caso tenha parametros diferentes de "ASC" ou "DESC" então repor com o predefinido
		$order = $order == "ASC" || $order == "DESC" ? $order : $orderDefault;
		//order by se existe como key no array, caso nao repor com o predefenido
		$by = array_key_exists( $by, $byArr ) ? $by : $byDefault;
		//A partir de quando seleciona resultados
		$limitNumber = ( $page - 1 ) * $results;
		$passar      = $byArr[ $by ];

		//Apenas informações básicas dos inscritos para por exemplo cards, e ao clicar na card o utilizador pode ser reencaminhado para o masterdetail do utlizador da card
		if ( $order == $orderDefault ) {
			$sql = "SELECT nome, descricao, tipo_colaborador, image_src FROM `participantes_colaboradores` RIGHT JOIN colaboradores ON participantes_colaboradores.colaboradores_id_colaboradores = colaboradores.id_colaboradores WHERE eventos_id_eventos = :id ORDER BY $passar DESC LIMIT :limit , :results";
		} else {
			$sql = "SELECT nome, descricao, tipo_colaborador, image_src FROM `participantes_colaboradores` RIGHT JOIN colaboradores ON participantes_colaboradores.colaboradores_id_colaboradores = colaboradores.id_colaboradores WHERE eventos_id_eventos = :id ORDER BY $passar  LIMIT :limit , :results";
		}


		try {
			$status = 200; // OK

			// iniciar ligação à base de dados
			$db = new Db();

			// conectar
			$db   = $db->connect();
			$stmt = $db->prepare( $sql );
			$stmt->bindValue( ':id', $id, PDO::PARAM_INT );
			$stmt->bindValue( ':limit', (int) $limitNumber, PDO::PARAM_INT );
			$stmt->bindValue( ':results', (int) $results, PDO::PARAM_INT );
			$stmt->execute();
			$db    = null;
			$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );

			// remover nulls e strings vazias
			$dados = array_filter( array_map( function ( $evento ) {
				return $evento = array_filter( $evento, function ( $coluna ) {
					return $coluna !== null && $coluna !== '';
				} );
			}, $dados ) );


			$dadosLength = (int) sizeof( $dados );

			if ( $dadosLength === 0 ) {
				$dados  = [ "error" => 'página inexistentes' ];
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
					->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


		} catch ( PDOException $err ) {
			$status = 503; // Service unavailable

			// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
			$errorMsg = Errors::filtroReturn( function ( $err ) {
				return [
						"error" => [
								"status" => $err->getCode(),
								"text"   => $err->getMessage()
						]
				];
			}, function ( $err ) {
				return [
						"error" => 'Servico Indisponivel'
				];
			}, $err );

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

		}
	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => 'Parametros invalidos'

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}


} );

//////////// Obter extras de um evento + icons ////////////
//////////// Obter colaboradores de um evento ////////////
# Parametros:
#   *page = pagina de resultados *obrigatorio
#   results = número de resultados por página
#   min: 1, max: 10
#   Exemplo: /api/eventos/2/extras?page=1&results=2&by=id&order=ASC
$app->get( '/api/eventos/{id}/extras', function ( Request $request, Response $response ) {
	$id = (int) $request->getAttribute( 'id' ); // ir buscar id

	$byArr = [
			'id'   => 'id_extras',
			'nome' => 'titulo'

	]; // Valores para ordernar por, fizemos uma array para simplificar queries


	//valores default
	$maxResults    = 10; // maximo de resultados por pagina
	$minResults    = 1; // minimo de resultados por pagina
	$byDefault     = 'nome'; // order by predefinido
	$paginaDefault = 1; // pagina predefenida
	$orderDefault  = "DESC"; //ordenação predefenida


	$parametros = $request->getQueryParams(); // obter parametros do querystring
	$page       = isset( $parametros['page'] ) ? (int) $parametros['page'] : $paginaDefault;
	$results    = isset( $parametros['results'] ) ? (int) $parametros['results'] : $maxResults;
	$by         = isset( $parametros['by'] ) ? $parametros['by'] : $byDefault;
	$order      = isset( $parametros['order'] ) ? $parametros['order'] : $orderDefault;


	if ( is_int( $id ) && $id > 0 && $page > 0 && $results > 0 ) {

		//caso request tenha parametros superiores ao numero máximo permitido então repor com o valor maximo permitido e vice-versa
		$results = $results > $maxResults ? $maxResults : $results; //se o querystring results for maior que o valor maximo definido passa a ser esse valor maximo definido
		$results = $results < $minResults ? $minResults : $results; //se o querystring results for menor que o valor minimo definido passa a ser esse valor minimo definido
		//caso tenha parametros diferentes de "ASC" ou "DESC" então repor com o predefinido
		$order = $order == "ASC" || $order == "DESC" ? $order : $orderDefault;
		//order by se existe como key no array, caso nao repor com o predefenido
		$by = array_key_exists( $by, $byArr ) ? $by : $byDefault;
		//A partir de quando seleciona resultados
		$limitNumber = ( $page - 1 ) * $results;
		$passar      = $byArr[ $by ];

		//Apenas informações básicas dos inscritos para por exemplo cards, e ao clicar na card o utilizador pode ser reencaminhado para o masterdetail do utlizador da card
		if ( $order == $orderDefault ) {
			$sql = "SELECT titulo, descricao, iconsA.classe AS descricao_classe, descricao_pequena, iconsB.classe AS descricao_pequena_classe FROM `eventos_has_eventos_infos` LEFT JOIN eventos_infos ON eventos_has_eventos_infos.eventos_infos_id_extras = eventos_infos.id_extras LEFT JOIN icons iconsA ON eventos_infos.icons_id_icons = iconsA.id_icons LEFT JOIN icons iconsB ON eventos_infos.icons_pequeno_icons_id = iconsB.id_icons WHERE eventos_id_eventos = :id ORDER BY $passar DESC  LIMIT :limit , :results";
		} else {
			$sql = "SELECT titulo, descricao, iconsA.classe AS descricao_classe, descricao_pequena, iconsB.classe AS descricao_pequena_classe FROM `eventos_has_eventos_infos` LEFT JOIN eventos_infos ON eventos_has_eventos_infos.eventos_infos_id_extras = eventos_infos.id_extras LEFT JOIN icons iconsA ON eventos_infos.icons_id_icons = iconsA.id_icons LEFT JOIN icons iconsB ON eventos_infos.icons_pequeno_icons_id = iconsB.id_icons WHERE eventos_id_eventos = :id ORDER BY $passar  LIMIT :limit , :results";

		}


		try {
			$status = 200; // OK

			// iniciar ligação à base de dados
			$db = new Db();

			// conectar
			$db   = $db->connect();
			$stmt = $db->prepare( $sql );
			$stmt->bindValue( ':id', $id, PDO::PARAM_INT );
			$stmt->bindValue( ':limit', (int) $limitNumber, PDO::PARAM_INT );
			$stmt->bindValue( ':results', (int) $results, PDO::PARAM_INT );
			$stmt->execute();
			$db    = null;
			$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );

			// remover nulls e strings vazias
			$dados = array_filter( array_map( function ( $evento ) {
				return $evento = array_filter( $evento, function ( $coluna ) {
					return $coluna !== null && $coluna !== '';
				} );
			}, $dados ) );


			$dadosLength = (int) sizeof( $dados );

			if ( $dadosLength === 0 ) {
				$dados  = [ "error" => 'página inexistentes' ];
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
					->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


		} catch ( PDOException $err ) {
			$status = 503; // Service unavailable
			// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
			$errorMsg = Errors::filtroReturn( function ( $err ) {
				return [
						"error" => [
								"status" => $err->getCode(),
								"text"   => $err->getMessage()
						]
				];
			}, function () {
				return [
						"error" => 'Servico Indisponivel'
				];
			}, $err );

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

		}
	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => 'Parametros invalidos'

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}


} );

//////////// Obter tags de um evento ////////////
# Parametros:
#   *page = pagina de resultados *obrigatorio
#   results = número de resultados por página
#   min: 1, max: 10
#   Exemplo: /api/eventos/2/tags?page=1&results=2&by=id&order=ASC
$app->get( '/api/eventos/{id}/tags', function ( Request $request, Response $response ) {
	$id = (int) $request->getAttribute( 'id' ); // ir buscar id

	$byArr = [
			'id'   => 'id_tags',
			'nome' => 'tag_nome'

	]; // Valores para ordernar por, fizemos uma array para simplificar queries


	//valores default
	$maxResults    = 3; // maximo de resultados por pagina
	$minResults    = 1; // minimo de resultados por pagina
	$byDefault     = 'nome'; // order by predefinido
	$paginaDefault = 1; // pagina predefenida
	$orderDefault  = "DESC"; //ordenação predefenida


	$parametros = $request->getQueryParams(); // obter parametros do querystring
	$page       = isset( $parametros['page'] ) ? (int) $parametros['page'] : $paginaDefault;
	$results    = isset( $parametros['results'] ) ? (int) $parametros['results'] : $maxResults;
	$by         = isset( $parametros['by'] ) ? $parametros['by'] : $byDefault;
	$order      = isset( $parametros['order'] ) ? $parametros['order'] : $orderDefault;


	if ( is_int( $id ) && $id > 0 && $page > 0 && $results > 0 ) {

		//caso request tenha parametros superiores ao numero máximo permitido então repor com o valor maximo permitido e vice-versa
		$results = $results > $maxResults ? $maxResults : $results; //se o querystring results for maior que o valor maximo definido passa a ser esse valor maximo definido
		$results = $results < $minResults ? $minResults : $results; //se o querystring results for menor que o valor minimo definido passa a ser esse valor minimo definido
		//caso tenha parametros diferentes de "ASC" ou "DESC" então repor com o predefinido
		$order = $order == "ASC" || $order == "DESC" ? $order : $orderDefault;
		//order by se existe como key no array, caso nao repor com o predefenido
		$by = array_key_exists( $by, $byArr ) ? $by : $byDefault;
		//A partir de quando seleciona resultados
		$limitNumber = ( $page - 1 ) * $results;
		$passar      = $byArr[ $by ];

		//Apenas informações básicas dos inscritos para por exemplo cards, e ao clicar na card o utilizador pode ser reencaminhado para o masterdetail do utlizador da card
		if ( $order == $orderDefault ) {
			$sql = "SELECT tag_nome FROM `tags` INNER JOIN eventos_has_tags ON eventos_has_tags.`tags_id_tags` = tags.id_tags where eventos_has_tags.eventos_id_eventos=:id ORDER BY $passar DESC LIMIT :limit , :results";
		} else {
			$sql = "SELECT tag_nome FROM `tags` INNER JOIN eventos_has_tags ON eventos_has_tags.`tags_id_tags` = tags.id_tags where eventos_has_tags.eventos_id_eventos=:id ORDER BY $passar LIMIT :limit , :results";
		}

		try {
			$status = 200; // OK

			// iniciar ligação à base de dados
			$db = new Db();

			// conectar
			$db   = $db->connect();
			$stmt = $db->prepare( $sql );
			$stmt->bindValue( ':id', $id, PDO::PARAM_INT );
			$stmt->bindValue( ':limit', (int) $limitNumber, PDO::PARAM_INT );
			$stmt->bindValue( ':results', (int) $results, PDO::PARAM_INT );
			$stmt->execute();
			$db    = null;
			$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );

			// remover nulls e strings vazias
			$dados = array_filter( array_map( function ( $evento ) {
				return $evento = array_filter( $evento, function ( $coluna ) {
					return $coluna !== null && $coluna !== '';
				} );
			}, $dados ) );


			$dadosLength = (int) sizeof( $dados );

			if ( $dadosLength === 0 ) {
				$dados  = [ "error" => 'pagina/tags inexistentes' ];
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
					->withJson( $responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


		} catch ( PDOException $err ) {
			$status = 503; // Service unavailable
			// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
			$errorMsg = Errors::filtroReturn( function ( $err ) {
				return [
						"error" => [
								"status" => $err->getCode(),
								"text"   => $err->getMessage()
						]
				];
			}, function () {
				return [
						"error" => 'Servico Indisponivel'
				];
			}, $err );

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

		}
	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => 'Parametros invalidos'

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}


} );

/////////// Adicionar um novo evento ////////////
#Parametros obrigatórios do evento a criar
#   nome_evento
#   id_evento (auto)
#   data_registo_evento (auto)
#   ativo (default = 1 ) :
#       quanto ativo = 1 quer dizer que o evento está ativo
#       quando ativo = 0 quer dizer que evento já não se encontra ativo
$app->post( '/api/eventos/add', function ( Request $request, Response $response ) {
	//ir buscar todos os parametros do metodo post do form
	$nomeEvento       = $request->getParam( 'nomeEvento' );
	$data             = $request->getParam( 'data' );
	$descricao        = $request->getParam( 'descricao' );
	$descricaoShort   = $request->getParam( 'descricaoShort' );
	$maxParticipantes = $request->getParam( 'maxParticipantes' );
	$minParticipantes = $request->getParam( 'minParticipantes' );
	$iddMinima        = $request->getParam( 'iddMinima' );
	$dataFim          = $request->getParam( 'dataFim' );
	$ativo            = $request->getParam( 'ativo' );
	//add foto
	//  \Cloudinary\Uploader::upload($_FILES["fileToUpload"]["tmp_name"]);

	$error = array();
	// TODO verificações de fotos
	// TODO verificar se existe id_localização na bd, senão inserir com respectivas lat e lng
	// TODO verificar se existe id_tipo_evento na bd, senão inserir

	//verificaçoes necessárias para garantir que nao sao introduzidos valores inválidos
	////////////validar nome evento///////////
	#Parametro obrigatório do evento
	#
	# $minCar significa o minimo de carateres para o nome
	#  $minCar significa o máximo de carateres para o nome(neste caso 64, pois é o máximo que o fb aceita)
	$minCar = 1;
	$maxCar = 64;
	if ( is_null( $nomeEvento ) || strlen( $nomeEvento ) < $minCar ) {
		$error[] = array( "nome" => "Insira um nome para o evento com mais que " . $minCar . " caracter. Este campo é obrigatório!" );
	} elseif ( strlen( $nomeEvento ) > $maxCar ) {
		$error[] = array( "nome" => "Nome excedeu limite máximo" );
	}

	///////////função para validar datas/////////////
	#Requisitos:
	#Formato predefinido(datetime): YYYY-MM-DD HH:MM:SS
	function validateDate( $date, $format = 'Y-m-d H:i:s' ) {
		$d = DateTime::createFromFormat( $format, $date );

		return $d && $d->format( $format ) == $date;
	}

	///////////função para validar descrições///////////
	#$desc significa a descrição para validar
	#$max o numero de caracteres máximo para essa descrição
	#$min o numero de caracteres minimo para essa descrição
	#$errorName o nome para returnar uma mensagem de erro
	#$error referencia à variável global erro onde armazenamos erros
	function validaDesc( &$desc, $max, $min, $errorName, &$error ) {

		if ( is_null( $desc ) || strlen( $desc ) == 0 || $desc == "" ) {
			$desc = null;
		} elseif ( strlen( $desc ) > $max ) {

			$error[] = array( $errorName => $errorName . " excedeu limite máximo de " . $max . " caracteres" );

		} elseif ( strlen( $desc ) < $min ) {

			$error[] = array( $errorName => $errorName . " tem de ter pelo menos " . $min . " caracteres" );
		}
	}

	////////////validar datas introduzidas //////////////
	if ( ! is_null( $dataFim ) && $dataFim != "" ) {
		if ( ! validateDate( $dataFim ) ) {
			$error[] = array( "data_fim" => "Data introduzida inválida, insira novamente com o seguinte formato YYYY-MM-DD hh:mm:ss" );
		}
	} else {
		$dataFim = null;
	}
	if ( ! is_null( $data ) && $data != "" ) {
		if ( ! validateDate( $data ) ) {
			$error[] = array( "data" => "Data introduzida inválida, insira novamente com o seguinte formato YYYY-MM-DD hh:mm:ss" );
		}
	} else {
		$data = null;
	}

	///////////validar descricoes introduzidas /////////////
	validaDesc( $descricao, 6000, 2, "descricao", $error );
	validaDesc( $descricaoShort, 200, 2, "descricao_pequena", $error );


	/* ------>validação do maximo de participantes, minimo participantes e idade minima pelos parametros da bd(exemplo int(2))   <-------- */
	if ( $maxParticipantes != "" ) {
		$maxParticipantes = $maxParticipantes > 999999 ? $maxParticipantes = 999999 : $maxParticipantes;
	} else {
		$maxParticipantes = null;
	}

	if ( $minParticipantes != "" ) {
		$minParticipantes = $minParticipantes > 999999 ? $minParticipantes = 999999 : $minParticipantes;
	} else {
		$minParticipantes = null;
	}

	if ( $iddMinima != "" ) {
		$iddMinima = $iddMinima > 99 ? $iddMinima = 99 : $iddMinima;
	} else {
		$iddMinima = null;
	}
	/* ------/>validação do maximo de participantes, minimo participantes e idade minima pelos parametros da bd(exemplo int(2))   </-------- */

	////////////////validação do ativo////////////////
	$ativo = $ativo != 1 && $ativo != 0 || $ativo == "" ? $ativo = 1 : $ativo;


	if ( count( $error ) == 0 ) {// não existe erro logo insere na bd
		$sql = "INSERT INTO eventos (nome_evento,data_evento,descricao,descricao_short,max_participantes,min_participantes,idade_minima,data_fim,ativo) VALUES
    (:nomeEvento,:data,:descricao,:descricaoShort,:max,:min,:idd,:dataFim,:ativo)";

		try {
			// Get DB object
			$db = new db();
			//connect
			$db   = $db->connect();
			$stmt = $db->prepare( $sql );
			$stmt->bindParam( ':nomeEvento', $nomeEvento );
			$stmt->bindParam( ':data', $data );
			$stmt->bindParam( ':descricao', $descricao );
			$stmt->bindParam( ':descricaoShort', $descricaoShort );
			$stmt->bindParam( ':max', $maxParticipantes );
			$stmt->bindParam( ':min', $minParticipantes );
			$stmt->bindParam( ':idd', $iddMinima );
			$stmt->bindParam( ':dataFim', $dataFim );
			$stmt->bindParam( ':ativo', $ativo );
			$stmt->execute();
			$db = null;

			$responseData = [
					'Resposta' => "Evento adicionado com sucesso!"
			];

			return $response
					->withJson( $responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


		} catch ( PDOException $err ) {
			$status = 503; // Service unavailable
			// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
			$errorMsg = Errors::filtroReturn( function ( $err ) {
				return [
						"error" => [
								"status" => $err->getCode(),
								"text"   => $err->getMessage()
						]
				];
			}, function () {
				return [
						"error" => 'Servico Indisponivel'
				];
			}, $err );

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

		}


	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => [
								$error
						]

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}

} );

//POST tipo eventos
$app->post( '/api/eventos/tipo/add', function ( Request $request, Response $response ) {
	$tipoNome = $request->getParam( 'nomeTipoEvento' );
	$error    = array();
	$minCar   = 1;
	$maxCar   = 75;
	if ( is_null( $tipoNome ) || strlen( $tipoNome ) < $minCar ) {
		$error[] = array( "nome" => "Insira um nome para o tipo de evento com mais que " . $minCar . " caracter. Este campo é obrigatório!" );
	} elseif ( strlen( $tipoNome ) > $maxCar ) {
		$error[] = array( "nome" => "Nome excedeu limite máximo" );
	}
	if ( count( $error ) === 0 ) {

		//buscar db todos os customers
		$sql = "INSERT INTO tipo_evento (nome_tipo_evento) VALUES  (:nome)";
		try {
			// Get DB object
			$db = new db();
			//connect
			$db   = $db->connect();
			$stmt = $db->prepare( $sql );
			$stmt->bindParam( ':nome', $tipoNome );
			$stmt->execute();
			$db           = null;
			$responseData = [
					'Resposta' => "Tipo de evento adicionado com sucesso!"
			];

			return $response
					->withJson( $responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


		} catch ( PDOException $err ) {
			$status   = 503; // Service unavailable
			$errorMsg = [
					"error" => [
							"status" => $err->getCode(),
							"text"   => $err->getMessage()
					]
			];

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

		}
	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => [
								$error
						]

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}
} );

// POST localização
$app->post( '/api/eventos/localizacao/add', function ( Request $request, Response $response ) {
	$localizacao = $request->getParam( 'nomeLocalizacao' );
	$lat         = $request->getParam( 'lat' );
	$lng         = $request->getParam( 'lng' );

	$error  = array();
	$minCar = 1;
	$maxCar = 75;
	if ( is_null( $localizacao ) || strlen( $localizacao ) < $minCar ) {
		$error[] = array( "nome" => "Insira um nome para o tipo de evento com mais que " . $minCar . " caracter. Este campo é obrigatório!" );
	} elseif ( strlen( $localizacao ) > $maxCar ) {
		$error[] = array( "nome" => "Nome excedeu limite máximo" );
	}

	function validaLatLng( $tipo, $valor ) {
		$resultado = ( $tipo == 'latitude' )
				? '/^(\+|-)?(?:90(?:(?:\.0{1,8})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,8})?))$/'
				: '/^(\+|-)?(?:180(?:(?:\.0{1,8})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,8})?))$/';

		if ( preg_match( $resultado, $valor ) ) {
			return true;
		} else {
			return false;
		}
	}

	if ( validaLatLng( 'latitude', $lat ) === false ) {
		$error[] = array( "nome" => "Latitude inválida" );
	}

	if ( validaLatLng( 'longitude', $lng ) === false ) {
		$error[] = array( "nome" => "Longitude inválida" );
	}

	if ( count( $error ) === 0 ) {

		//buscar db todos os customers
		$sql = "INSERT INTO localizacao (lat,lng,nome) VALUES  (:lat,:lng,:nome)";
		try {
			// Get DB object
			$db = new db();
			//connect
			$db   = $db->connect();
			$stmt = $db->prepare( $sql );
			$stmt->bindParam( ':nome', $localizacao );
			$stmt->bindParam( ':lat', $lat );
			$stmt->bindParam( ':lng', $lng );
			$stmt->execute();
			$db           = null;
			$responseData = [
					'Resposta' => "Localização adicionada com sucesso!"
			];

			return $response
					->withJson( $responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


		} catch ( PDOException $err ) {
			$status = 503; // Service unavailable
			// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
			$errorMsg = Errors::filtroReturn( function ( $err ) {
				return [
						"error" => [
								"status" => $err->getCode(),
								"text"   => $err->getMessage()
						]
				];
			}, function () {
				return [
						"error" => 'Servico Indisponivel'
				];
			}, $err );

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

		}
	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => [
								$error
						]

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}
} );

//////////////Alterar um evento///////////////////
//Scopes: admin ou token do request é igual ao {id}(colaborador que criou )
$app->put( '/api/eventos/alter/{id}', function ( Request $request, Response $response ) {
	$id = (int) $request->getAttribute( 'id' );
	//ir buscar todos os parametros do metodo post do form
	$nomeEvento       = $request->getParam( 'nomeEvento' );
	$data             = $request->getParam( 'data' );
	$descricao        = $request->getParam( 'descricao' );
	$descricaoShort   = $request->getParam( 'descricaoShort' );
	$maxParticipantes = $request->getParam( 'maxParticipantes' );
	$minParticipantes = $request->getParam( 'minParticipantes' );
	$iddMinima        = $request->getParam( 'iddMinima' );
	$dataFim          = $request->getParam( 'dataFim' );
	$ativo            = $request->getParam( 'ativo' );
	//add foto
	//  \Cloudinary\Uploader::upload($_FILES["fileToUpload"]["tmp_name"]);

	$error = array();
	// TODO verificações de fotos
	// TODO verificar se existe id_localização na bd, senão inserir com respectivas lat e lng
	// TODO verificar se existe id_tipo_evento na bd, senão inserir
	if ( is_int( $id ) && $id > 0 ) {
		//ver se id existe na bd antes de editar
		$sql = "SELECT * FROM eventos WHERE id_eventos = :id";
		$db  = new Db();
		// conectar
		$db   = $db->connect();
		$stmt = $db->prepare( $sql );
		$stmt->bindValue( ':id', $id, PDO::PARAM_INT );
		$stmt->execute();
		$db    = null;
		$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
		if ( count( $dados ) >= 1 ) {
			//verificaçoes necessárias para garantir que nao sao introduzidos valores inválidos
			////////////validar nome evento///////////
			#Parametro obrigatório do evento
			#
			# $minCar significa o minimo de carateres para o nome
			#  $minCar significa o máximo de carateres para o nome(neste caso 64, pois é o máximo que o fb aceita)
			$minCar = 1;
			$maxCar = 64;
			if ( is_null( $nomeEvento ) || strlen( $nomeEvento ) < $minCar ) {
				$error[] = array( "nome" => "Insira um nome para o evento com mais que " . $minCar . " caracter. Este campo é obrigatório!" );
			} elseif ( strlen( $nomeEvento ) > $maxCar ) {
				$error[] = array( "nome" => "Nome excedeu limite máximo" );
			}

			///////////função para validar datas/////////////
			#Requisitos:
			#Formato predefinido(datetime): YYYY-MM-DD HH:MM:SS
			function validateDate( $date, $format = 'Y-m-d H:i:s' ) {
				$d = DateTime::createFromFormat( $format, $date );

				return $d && $d->format( $format ) == $date;
			}

			///////////função para validar descrições///////////
			#$desc significa a descrição para validar
			#$max o numero de caracteres máximo para essa descrição
			#$min o numero de caracteres minimo para essa descrição
			#$errorName o nome para returnar uma mensagem de erro
			#$error referencia à variável global erro onde armazenamos erros
			function validaDesc( &$desc, $max, $min, $errorName, &$error ) {

				if ( is_null( $desc ) || strlen( $desc ) == 0 || $desc == "" ) {
					$desc = null;
				} elseif ( strlen( $desc ) > $max ) {

					$error[] = array( $errorName => $errorName . " excedeu limite máximo de " . $max . " caracteres" );

				} elseif ( strlen( $desc ) < $min ) {

					$error[] = array( $errorName => $errorName . " tem de ter pelo menos " . $min . " caracteres" );
				}
			}

			////////////validar datas introduzidas //////////////
			if ( ! is_null( $dataFim ) && $dataFim != "" ) {
				if ( ! validateDate( $dataFim ) ) {
					$error[] = array( "data_fim" => "Data introduzida inválida, insira novamente com o seguinte formato YYYY-MM-DD hh:mm:ss" );
				}
			} else {
				$dataFim = null;
			}
			if ( ! is_null( $data ) && $data != "" ) {
				if ( ! validateDate( $data ) ) {
					$error[] = array( "data" => "Data introduzida inválida, insira novamente com o seguinte formato YYYY-MM-DD hh:mm:ss" );
				}
			} else {
				$data = null;
			}

			///////////validar descricoes introduzidas /////////////
			validaDesc( $descricao, 6000, 2, "descricao", $error );
			validaDesc( $descricaoShort, 200, 2, "descricao_pequena", $error );


			/* ------>validação do maximo de participantes, minimo participantes e idade minima pelos parametros da bd(exemplo int(2))   <-------- */
			if ( $maxParticipantes != "" ) {
				$maxParticipantes = $maxParticipantes > 999999 ? $maxParticipantes = 999999 : $maxParticipantes;
			} else {
				$maxParticipantes = null;
			}

			if ( $minParticipantes != "" ) {
				$minParticipantes = $minParticipantes > 999999 ? $minParticipantes = 999999 : $minParticipantes;
			} else {
				$minParticipantes = null;
			}

			if ( $iddMinima != "" ) {
				$iddMinima = $iddMinima > 99 ? $iddMinima = 99 : $iddMinima;
			} else {
				$iddMinima = null;
			}
			/* ------/>validação do maximo de participantes, minimo participantes e idade minima pelos parametros da bd(exemplo int(2))   </-------- */

			////////////////validação do ativo////////////////
			$ativo = $ativo != 1 && $ativo != 0 || $ativo == "" ? $ativo = 1 : $ativo;


			if ( count( $error ) == 0 ) {// não existe erro logo insere na bd
				$sql = "UPDATE eventos SET nome_evento = :nomeEvento, data_evento = :data , descricao = :descricao,descricao_short = :descricaoShort,max_participantes = :max,min_participantes = :min,idade_minima = :idd,data_fim = :dataFim,ativo = :ativo WHERE id_eventos = $id";
				try {
					// Get DB object
					$db = new db();
					//connect
					$db   = $db->connect();
					$stmt = $db->prepare( $sql );
					$stmt->bindParam( ':nomeEvento', $nomeEvento );
					$stmt->bindParam( ':data', $data );
					$stmt->bindParam( ':descricao', $descricao );
					$stmt->bindParam( ':descricaoShort', $descricaoShort );
					$stmt->bindParam( ':max', $maxParticipantes );
					$stmt->bindParam( ':min', $minParticipantes );
					$stmt->bindParam( ':idd', $iddMinima );
					$stmt->bindParam( ':dataFim', $dataFim );
					$stmt->bindParam( ':ativo', $ativo );

					$stmt->execute();

					$responseData = [
							'Resposta' => "Evento alterado com sucesso!"
					];

					return $response
							->withJson( $responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


				} catch ( PDOException $err ) {
					$status = 503; // Service unavailable
					// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
					$errorMsg = Errors::filtroReturn( function ( $err ) {
						return [
								"error" => [
										"status" => $err->getCode(),
										"text"   => $err->getMessage()
								]
						];
					}, function () {
						return [
								"error" => 'Servico Indisponivel'
						];
					}, $err );

					return $response
							->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

				}


			} else {
				$status   = 422; // Unprocessable Entity
				$errorMsg = [
						"error" => [
								"status" => "$status",
								"text"   => [
										$error
								]

						]
				];

				return $response
						->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
			}

		} else {
			$status   = 422; // Unprocessable Entity
			$errorMsg = [
					"error" => [
							"status" => "$status",
							"text"   => 'Evento já não se encontra disponivel'

					]
			];

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
		}

	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => 'Parametros inválidos'

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}

} );

/////////////Apagar um evento////////////////////
$app->delete( '/api/eventos/delete/{id}', function ( Request $request, Response $response ) {
	$id = (int) $request->getAttribute( 'id' ); // ir buscar id
	if ( is_int( $id ) && $id > 0 ) {
		//ver se id existe na bd antes de editar
		$sql = "SELECT * FROM eventos WHERE id_eventos = :id";
		$db  = new Db();
		// conectar
		$db   = $db->connect();
		$stmt = $db->prepare( $sql );
		$stmt->bindValue( ':id', $id, PDO::PARAM_INT );
		$stmt->execute();
		$db    = null;
		$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
		if ( count( $dados ) >= 1 ) {

			$sql = "DELETE FROM eventos WHERE id_eventos = $id";

			try {
				// Get DB object
				$db = new db();
				//connect
				$db = $db->connect();

				$stmt = $db->prepare( $sql );
				$stmt->execute();
				$db           = null;
				$responseData = [
						'Resposta' => "Evento apagado com sucesso!"
				];

				return $response
						->withJson( $responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


			} catch ( PDOException $err ) {
				$status = 503; // Service unavailable
				// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
				$errorMsg = Errors::filtroReturn( function ( $err ) {
					return [
							"error" => [
									"status" => $err->getCode(),
									"text"   => $err->getMessage()
							]
					];
				}, function () {
					return [
							"error" => 'Servico Indisponivel'
					];
				}, $err );

				return $response
						->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

			}

		} else {
			$status   = 422; // Unprocessable Entity
			$errorMsg = [
					"error" => [
							"status" => "$status",
							"text"   => 'Evento já não se encontra disponivel'

					]
			];

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
		}

	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => 'Parametros inválidos'

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}
} );

//PUT para tornar evento ativo
$app->put( '/api/eventos/ative/{id}', function ( Request $request, Response $response ) {
	$id    = (int) $request->getAttribute( 'id' );
	$ativo = 1; //valor na bd que equivale a ativo
	if ( is_int( $id ) && $id > 0 ) {
		//ver se id existe na bd antes de editar
		$sql = "SELECT * FROM eventos WHERE id_eventos = :id";
		$db  = new Db();
		// conectar
		$db   = $db->connect();
		$stmt = $db->prepare( $sql );
		$stmt->bindValue( ':id', $id, PDO::PARAM_INT );
		$stmt->execute();
		$db    = null;
		$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
		if ( count( $dados ) >= 1 ) {
			$sql = "UPDATE eventos SET ativo = :ativo WHERE id_eventos = $id";

			try {
				// Get DB object
				$db = new db();
				//connect
				$db   = $db->connect();
				$stmt = $db->prepare( $sql );
				$stmt->bindParam( ':ativo', $ativo );
				$stmt->execute();
				$responseData = [
						'Resposta' => "Evento ativado com sucesso!"
				];

				return $response
						->withJson( $responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


			} catch ( PDOException $err ) {
				$status = 503; // Service unavailable
				// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
				$errorMsg = Errors::filtroReturn( function ( $err ) {
					return [
							"error" => [
									"status" => $err->getCode(),
									"text"   => $err->getMessage()
							]
					];
				}, function () {
					return [
							"error" => 'Servico Indisponivel'
					];
				}, $err );

				return $response
						->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

			}
		} else {
			$status   = 422; // Unprocessable Entity
			$errorMsg = [
					"error" => [
							"status" => "$status",
							"text"   => 'Evento não se encontra disponivel'

					]
			];

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
		}

	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => 'Parametros inválidos'

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}

} );

//PUT para tornar evento inativo
$app->put( '/api/eventos/disable/{id}', function ( Request $request, Response $response ) {
	$id    = (int) $request->getAttribute( 'id' );
	$ativo = 0; //valor na bd que equivale a ativo
	if ( is_int( $id ) && $id > 0 ) {
		//ver se id existe na bd antes de editar
		$sql = "SELECT * FROM eventos WHERE id_eventos = :id";
		$db  = new Db();
		// conectar
		$db   = $db->connect();
		$stmt = $db->prepare( $sql );
		$stmt->bindValue( ':id', $id, PDO::PARAM_INT );
		$stmt->execute();
		$db    = null;
		$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
		if ( count( $dados ) >= 1 ) {
			$sql = "UPDATE eventos SET ativo = :ativo WHERE id_eventos = $id";

			try {
				// Get DB object
				$db = new db();
				//connect
				$db   = $db->connect();
				$stmt = $db->prepare( $sql );
				$stmt->bindParam( ':ativo', $ativo );
				$stmt->execute();
				$responseData = [
						'Resposta' => "Evento desativado com sucesso!"
				];

				return $response
						->withJson( $responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );


			} catch ( PDOException $err ) {
				$status = 503; // Service unavailable
				// Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
				$errorMsg = Errors::filtroReturn( function ( $err ) {
					return [
							"error" => [
									"status" => $err->getCode(),
									"text"   => $err->getMessage()
							]
					];
				}, function () {
					return [
							"error" => 'Servico Indisponivel'
					];
				}, $err );

				return $response
						->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );

			}
		} else {
			$status   = 422; // Unprocessable Entity
			$errorMsg = [
					"error" => [
							"status" => "$status",
							"text"   => 'Evento não se encontra disponivel'

					]
			];

			return $response
					->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
		}

	} else {
		$status   = 422; // Unprocessable Entity
		$errorMsg = [
				"error" => [
						"status" => "$status",
						"text"   => 'Parametros inválidos'

				]
		];

		return $response
				->withJson( $errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS );
	}

} );

