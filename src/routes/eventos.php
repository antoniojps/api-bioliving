<?php

/*
 * Routes para todos endpoints relativos aos eventos:
 * Scopes: admin recebe todas as informações, outros recebem apenas necessárias
 *
 * GET /eventos?page=(int)&results=(int)&by=(coluna)&order=(asc||desc)
 * Obter todos os eventos
 *
 * GET /eventos/{id}
 * Obter dados sobre um evento
 *
 * GET /eventos/{id}/utilizadores
 * Obter inscritos num evento
 *
 * GET /eventos/{id}/colaboradores
 * Obter colaboradores de um evento
 *
 * GET /eventos/{id}/eventosInfos
 * Obter extras de um evento
 *
 * GET /eventos/{id}/tags
 * Obter tags de um evento
 *
 * POST /eventos
 * Registar novo evento
 *
 * PUT /eventos/{id}
 * Atualizar evento
 * Scopes: admin ou token do request é igual ao {id}(colaborador que criou )
 *
 * DELETE /eventos/{id}
 * Tornar evento inativo
 * Scopes: admin ou colaborador que criou evento
 *
 */

// importar classes para scope
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

//////////// Obter todos os eventos ////////////
# Parametros:
#   *page = pagina de resultados *obrigatorio
#   results = número de resultados por página
#   min: 1, max: 10
# Exemplo: /api/eventos?page=1&results=2&by=id&order=ASC
$app->get('/api/eventos', function (Request $request, Response $response) {

    $byArr = [
        'id' => 'id_eventos',
        'nome' => 'nome_evento',
        'dataRegisto' => 'data_registo_evento',
        'dataEvento' => 'data_evento',
        'dataFim' => 'data_fim',
        'ativo' => 'ativo',
        'tipoEvento' => 'nome_tipo_evento',
        'local' => 'nome'
    ]; // Valores para ordernar por, fizemos uma array para simplificar queries


    $maxResults = 10; // maximo de resultados por pagina
    $minResults = 1; // minimo de resultados por pagina
    $byDefault = 'id'; // order by predefinido
    $paginaDefault = 1; // pagina predefenida
    $orderDefault = "DESC"; //ordenação predefenida


    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $page = isset($parametros['page']) ? (int)$parametros['page'] : $paginaDefault;
    $results = isset($parametros['results']) ? (int)$parametros['results'] : $maxResults;
    $by = isset($parametros['by']) ? $parametros['by'] : $byDefault;
    $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;

    if ($page > 0 && $results > 0) {
        //definir numero de resultados
        //caso request tenha parametros superiores ao numero máximo permitido então repor com o valor maximo permitido e vice-versa
        $results = $results > $maxResults ? $maxResults : $results; //se o querystring results for maior que o valor maximo definido passa a ser esse valor maximo definido
        $results = $results < $minResults ? $minResults : $results; //se o querystring results for menor que o valor minimo definido passa a ser esse valor minimo definido
        //caso tenha parametros diferentes de "ASC" ou "DESC" então repor com o predefinido
        $order = $order == "ASC" || $order == "DESC" ? $order : $orderDefault;
        //order by se existe como key no array, caso nao repor com o predefenido
        $by = array_key_exists($by, $byArr) ? $by : $byDefault;

        // A partir de quando seleciona resultados
        $limitNumber = ($page - 1) * $results;
        $passar = $byArr[$by];
        if ($order == $orderDefault) {
            $sql = "SELECT id_eventos, nome_evento,nome_tipo_evento as tipo,data_registo_evento, data_evento,data_fim,descricao_short,descricao,max_participantes,min_participantes,idade_minima,fotos, ativo, COUNT(utilizadores_id_utilizadores) AS participantes,lat,lng,localizacao.nome as local FROM eventos LEFT OUTER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao LEFT OUTER JOIN participantes ON eventos.id_eventos = participantes.eventos_id_eventos LEFT OUTER JOIN tipo_evento ON eventos.tipo_evento_id_tipo_evento = tipo_evento.id_tipo_evento GROUP BY id_eventos ORDER BY $passar DESC LIMIT :limit , :results";
        } else {
            $sql = "SELECT id_eventos, nome_evento,nome_tipo_evento as tipo,data_registo_evento, data_evento,data_fim,descricao_short,descricao,max_participantes,min_participantes,idade_minima,fotos, ativo, COUNT(utilizadores_id_utilizadores) AS participantes,lat,lng,localizacao.nome as local FROM eventos LEFT OUTER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao LEFT OUTER JOIN participantes ON eventos.id_eventos = participantes.eventos_id_eventos LEFT OUTER JOIN tipo_evento ON eventos.tipo_evento_id_tipo_evento = tipo_evento.id_tipo_evento GROUP BY id_eventos ORDER BY $passar  LIMIT :limit , :results";

        }

        try {

            $status = 200; // OK
            // iniciar ligação à base de dados
            $db = new Db();

            // conectar
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limitNumber, PDO::PARAM_INT);
            $stmt->bindValue(':results', (int)$results, PDO::PARAM_INT);
            $stmt->execute();
            $db = null;
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // remover nulls e strings vazias
            $dados = array_filter(array_map(function ($evento) {
                return $evento = array_filter($evento, function ($coluna) {
                    return $coluna !== null && $coluna !== '';
                });
            }, $dados));

            $dadosLength = (int)sizeof($dados);
            if ($dadosLength === 0) {
                $dados = ["error" => 'pagina inexistente'];
                $status = 404; // Page not found

            } else if ($dadosLength < $results) {
                $dadosExtra = ['info' => 'final dos resultados'];
                array_push($dados, $dadosExtra);
            } else {
                $nextPageUrl = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
                $dadosExtra = ['proxPagina' => "$nextPageUrl?page=" . ++$page . "&results=$results"];
                array_push($dados, $dadosExtra);
            }

            $responseData = [
                'status' => "$status",
                'data' => [
                    $dados
                ]
            ];

            return $response
                ->withJson($responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


        } catch (PDOException $err) {
            $status = 503; // Service unavailable
            $errorMsg = [
                "error" => [
                    "status" => $err->getCode(),
                    "text" => $err->getMessage()
                ]
            ];

            return $response
                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
        }

    } else {
        $status = 422; // Unprocessable Entity
        $errorMsg = [
            "error" => [
                "status" => "$status",
                "text" => 'Parametros invalidos'

            ]
        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }
});


//////////// Obter todos os dados sobre um evento através do id ////////////
$app->get('/api/eventos/{id}', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id'); // ir buscar id
    //verificar se é um id válido
    if (is_int($id) && $id > 0) {

        $sql = "SELECT eventos.*,tipo_evento.nome_tipo_evento as tipo,localizacao.*,COUNT(utilizadores_id_utilizadores) as participantes FROM eventos LEFT OUTER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao LEFT OUTER JOIN participantes ON eventos.id_eventos = participantes.eventos_id_eventos LEFT OUTER JOIN tipo_evento ON eventos.tipo_evento_id_tipo_evento = tipo_evento.id_tipo_evento  WHERE id_eventos = :id";

        try {

            $status = 200; // OK

            // iniciar ligação à base de dados
            $db = new Db();

            // conectar
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $db = null;
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // remover nulls e strings vazias
            $dados = array_filter(array_map(function ($evento) {
                return $evento = array_filter($evento, function ($coluna) {
                    return $coluna !== null && $coluna !== '';
                });
            }, $dados));


            $dadosLength = (int)sizeof($dados);
            $dadosDoArrayLength = (int)sizeof($dados[0]); // filtrar os participantes = 0
            if ($dadosLength === 0 || $dadosDoArrayLength === 1 || $dadosDoArrayLength === 0) {
                $dados = ["error" => 'evento inexistente'];
                $status = 404; // Page not found
            }

            $responseData = [
                'status' => "$status",
                'data' => [
                    $dados
                ]
            ];

            return $response
                ->withJson($responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


        } catch (PDOException $err) {
            $status = 503; // Service unavailable
            $errorMsg = [
                "error" => [
                    "status" => $err->getCode(),
                    "text" => $err->getMessage()
                ]
            ];

            return $response
                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

        }
    } else {
        $status = 422; // Unprocessable Entity
        $errorMsg = [
            "error" => [
                "status" => "$status",
                "text" => 'Parametros invalidos'

            ]
        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }
});


//////////// Obter todos os eventos ////////////
# Parametros:
#   *page = pagina de resultados *obrigatorio
#   results = número de resultados por página
#   min: 1, max: 10
# Exemplo: /api/eventos/2/utilizadores?page=1&results=2&by=id&order=ASC
$app->get('/api/eventos/{id}/utilizadores', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id'); // ir buscar id

    $byArr = [
        'id' => 'id_utilizadores',
        'nome' => 'nome',
        'nomeEstatuto' => 'nome_estatuto'

    ]; // Valores para ordernar por, fizemos uma array para simplificar queries


    //valores default
    $maxResults = 10; // maximo de resultados por pagina
    $minResults = 1; // minimo de resultados por pagina
    $byDefault = 'id'; // order by predefinido
    $paginaDefault = 1; // pagina predefenida
    $orderDefault = "DESC"; //ordenação predefenida


    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $page = isset($parametros['page']) ? (int)$parametros['page'] : $paginaDefault;
    $results = isset($parametros['results']) ? (int)$parametros['results'] : $maxResults;
    $by = isset($parametros['by']) ? $parametros['by'] : $byDefault;
    $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;


    if (is_int($id) && $id > 0 && $page > 0 && $results > 0) {

        //caso request tenha parametros superiores ao numero máximo permitido então repor com o valor maximo permitido e vice-versa
        $results = $results > $maxResults ? $maxResults : $results; //se o querystring results for maior que o valor maximo definido passa a ser esse valor maximo definido
        $results = $results < $minResults ? $minResults : $results; //se o querystring results for menor que o valor minimo definido passa a ser esse valor minimo definido
        //caso tenha parametros diferentes de "ASC" ou "DESC" então repor com o predefinido
        $order = $order == "ASC" || $order == "DESC" ? $order : $orderDefault;
        //order by se existe como key no array, caso nao repor com o predefenido
        $by = array_key_exists($by, $byArr) ? $by : $byDefault;
        //A partir de quando seleciona resultados
        $limitNumber = ($page - 1) * $results;
        $passar = $byArr[$by];

        //Apenas informações básicas dos inscritos para por exemplo cards, e ao clicar na card o utilizador pode ser reencaminhado para o masterdetail do utlizador da card
        if ($order == $orderDefault) {
            $sql = "SELECT id_estatutos,nome_estatuto, `id_utilizadores`,`nome`,`apelido`,`foto`,`sobre_mini`,`telemovel` FROM `utilizadores` INNER JOIN estatutos ON utilizadores.`estatutos_id_estatutos` = estatutos.id_estatutos INNER JOIN participantes ON utilizadores.id_utilizadores = participantes.utilizadores_id_utilizadores WHERE eventos_id_eventos = :id ORDER BY $passar DESC LIMIT :limit , :results";
        } else {
            $sql = "SELECT id_estatutos,nome_estatuto, `id_utilizadores`,`nome`,`apelido`,`foto`,`sobre_mini`,`telemovel` FROM `utilizadores` INNER JOIN estatutos ON utilizadores.`estatutos_id_estatutos` = estatutos.id_estatutos INNER JOIN participantes ON utilizadores.id_utilizadores = participantes.utilizadores_id_utilizadores WHERE eventos_id_eventos = :id ORDER BY $passar  LIMIT :limit , :results";

        }


        try {
            $status = 200; // OK

            // iniciar ligação à base de dados
            $db = new Db();

            // conectar
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limitNumber, PDO::PARAM_INT);
            $stmt->bindValue(':results', (int)$results, PDO::PARAM_INT);
            $stmt->execute();
            $db = null;
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // remover nulls e strings vazias
            $dados = array_filter(array_map(function ($evento) {
                return $evento = array_filter($evento, function ($coluna) {
                    return $coluna !== null && $coluna !== '';
                });
            }, $dados));


            $dadosLength = (int)sizeof($dados);

            if ($dadosLength === 0) {
                $dados = ["error" => 'participantes/página inexistentes'];
                $status = 404; // Page not found
            } else if ($dadosLength < $results) {
                $dadosExtra = ['info' => 'final dos resultados'];
                array_push($dados, $dadosExtra);
            } else {
                $nextPageUrl = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
                $dadosExtra = ['proxPagina' => "$nextPageUrl?page=" . ++$page . "&results=$results"];
                array_push($dados, $dadosExtra);
            }

            $responseData = [
                'status' => "$status",
                'data' => [
                    $dados
                ]
            ];

            return $response
                ->withJson($responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


        } catch (PDOException $err) {
            $status = 503; // Service unavailable
            $errorMsg = [
                "error" => [
                    "status" => $err->getCode(),
                    "text" => $err->getMessage()
                ]
            ];

            return $response
                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

        }
    } else {
        $status = 422; // Unprocessable Entity
        $errorMsg = [
            "error" => [
                "status" => "$status",
                "text" => 'Parametros invalidos'

            ]
        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }


});


//////////// Obter colaboradores de um evento ////////////
# Parametros:
#   *page = pagina de resultados *obrigatorio
#   results = número de resultados por página
#   min: 1, max: 10
#   Exemplo: /api/eventos/2/colaboradores?page=1&results=2&by=id&order=AS
#   SELECT participantes_colaboradores.*,`nome`,`descricao`,`image_src` FROM `participantes_colaboradores` INNER JOIN colaboradores ON participantes_colaboradores.`colaboradores_id_colaboradores`= colaboradores.id_colaboradores WHERE `eventos_id_eventos` = 3
$app->get('/api/eventos/{id}/colaboradores', function (Request $request, Response $response){
    $id = (int)$request->getAttribute('id'); // ir buscar id

    $byArr = [
        'id' => 'colaboradores_id_colaboradores',
        'nome' => 'nome'

    ]; // Valores para ordernar por, fizemos uma array para simplificar queries


    //valores default
    $maxResults = 3; // maximo de resultados por pagina
    $minResults = 1; // minimo de resultados por pagina
    $byDefault = 'id'; // order by predefinido
    $paginaDefault = 1; // pagina predefenida
    $orderDefault = "DESC"; //ordenação predefenida


    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $page = isset($parametros['page']) ? (int)$parametros['page'] : $paginaDefault;
    $results = isset($parametros['results']) ? (int)$parametros['results'] : $maxResults;
    $by = isset($parametros['by']) ? $parametros['by'] : $byDefault;
    $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;


    if (is_int($id) && $id > 0 && $page > 0 && $results > 0) {

        //caso request tenha parametros superiores ao numero máximo permitido então repor com o valor maximo permitido e vice-versa
        $results = $results > $maxResults ? $maxResults : $results; //se o querystring results for maior que o valor maximo definido passa a ser esse valor maximo definido
        $results = $results < $minResults ? $minResults : $results; //se o querystring results for menor que o valor minimo definido passa a ser esse valor minimo definido
        //caso tenha parametros diferentes de "ASC" ou "DESC" então repor com o predefinido
        $order = $order == "ASC" || $order == "DESC" ? $order : $orderDefault;
        //order by se existe como key no array, caso nao repor com o predefenido
        $by = array_key_exists($by, $byArr) ? $by : $byDefault;
        //A partir de quando seleciona resultados
        $limitNumber = ($page - 1) * $results;
        $passar = $byArr[$by];

        //Apenas informações básicas dos inscritos para por exemplo cards, e ao clicar na card o utilizador pode ser reencaminhado para o masterdetail do utlizador da card
        if ($order == $orderDefault) {
            $sql = "SELECT participantes_colaboradores.*,`nome`,`descricao`,`image_src` FROM `participantes_colaboradores` INNER JOIN colaboradores ON participantes_colaboradores.`colaboradores_id_colaboradores`= colaboradores.id_colaboradores WHERE `eventos_id_eventos` = :id ORDER BY $passar DESC LIMIT :limit , :results";
        } else {
            $sql = "SELECT participantes_colaboradores.*,`nome`,`descricao`,`image_src` FROM `participantes_colaboradores` INNER JOIN colaboradores ON participantes_colaboradores.`colaboradores_id_colaboradores`= colaboradores.id_colaboradores WHERE `eventos_id_eventos` = :id ORDER BY $passar  LIMIT :limit , :results";

        }


        try {
            $status = 200; // OK

            // iniciar ligação à base de dados
            $db = new Db();

            // conectar
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limitNumber, PDO::PARAM_INT);
            $stmt->bindValue(':results', (int)$results, PDO::PARAM_INT);
            $stmt->execute();
            $db = null;
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // remover nulls e strings vazias
            $dados = array_filter(array_map(function ($evento) {
                return $evento = array_filter($evento, function ($coluna) {
                    return $coluna !== null && $coluna !== '';
                });
            }, $dados));


            $dadosLength = (int)sizeof($dados);

            if ($dadosLength === 0) {
                $dados = ["error" => 'página inexistentes'];
                $status = 404; // Page not found
            } else if ($dadosLength  < $results ) {
                $dadosExtra = ['info' => 'final dos resultados'];
                array_push($dados, $dadosExtra);
            } else {
                $nextPageUrl = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
                $dadosExtra = ['proxPagina' => "$nextPageUrl?page=" . ++$page . "&results=$results"];
                array_push($dados, $dadosExtra);
            }

            $responseData = [
                'status' => "$status",
                'data' => [
                    $dados
                ]
            ];

            return $response
                ->withJson($responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


        } catch (PDOException $err) {
            $status = 503; // Service unavailable
            $errorMsg = [
                "error" => [
                    "status" => $err->getCode(),
                    "text" => $err->getMessage()
                ]
            ];

            return $response
                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

        }
    } else {
        $status = 422; // Unprocessable Entity
        $errorMsg = [
            "error" => [
                "status" => "$status",
                "text" => 'Parametros invalidos'

            ]
        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }


});



//////////// Obter extras de um evento ////////////



//////////// Obter tags de um evento ////////////