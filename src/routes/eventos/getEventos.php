<?php
// TODO: GET para pesquisar eventos por nome, tags , por horas, tipo e localização etc individualmente e em grupo

/*
 * Routes para todos endpoints relativos aos eventos:
 * Scopes: admin recebe todas as informações, outros recebem apenas necessárias

 */
// importar classes para scope
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

//////////// Obter todos os eventos ////////////
# Variaveis alteráveis:
#   min: 1, max: 10
# Exemplo: /api/eventos?page=1&results=2&by=id&order=ASC&msg=random&id=9&datainic=
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
    $orderDefault = "ASC"; //ordenação predefenida

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
            $sql = "SELECT eventos.*,tipo_evento.*, COUNT(utilizadores_id_utilizadores) AS participantes , localizacao.*  FROM eventos LEFT OUTER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao LEFT OUTER JOIN participantes ON eventos.id_eventos = participantes.eventos_id_eventos LEFT OUTER JOIN tipo_evento ON eventos.tipo_evento_id_tipo_evento = tipo_evento.id_tipo_evento GROUP BY id_eventos ORDER BY $passar  LIMIT :limit , :results";
        } else {
            $sql = "SELECT eventos.*,tipo_evento.*, COUNT(utilizadores_id_utilizadores) AS participantes , localizacao.*  FROM eventos LEFT OUTER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao LEFT OUTER JOIN participantes ON eventos.id_eventos = participantes.eventos_id_eventos LEFT OUTER JOIN tipo_evento ON eventos.tipo_evento_id_tipo_evento = tipo_evento.id_tipo_evento GROUP BY id_eventos ORDER BY $passar DESC LIMIT :limit , :results";
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
            // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
            $errorMsg = Errors::filtroReturn(function ($err) {
                return [
                    "error" => [
                        "status" => $err->getCode(),
                        "text" => $err->getMessage()
                    ]
                ];
            }, function () {
                return [
                    "error" => 'Servico Indisponivel'
                ];
            }, $err);

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

//////////// Obter dados de um evento através do ID ////////////
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
            // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
            $errorMsg = Errors::filtroReturn(function ($err) {
                return [
                    "error" => [
                        "status" => $err->getCode(),
                        "text" => $err->getMessage()
                    ]
                ];
            }, function () {
                return [
                    "error" => 'Servico Indisponivel'
                ];
            }, $err);

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

//////////// Obter utilizadores que participaram/inscreveram num evento ////////////
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

            // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
            $errorMsg = Errors::filtroReturn(function ($err) {
                return [
                    "error" => [
                        "status" => $err->getCode(),
                        "text" => $err->getMessage()
                    ]
                ];
            }, function () {
                return [
                    "error" => 'Servico Indisponivel'
                ];
            }, $err);

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
#   Exemplo: /api/eventos/2/colaboradores?page=1&results=4&by=id&order=ASC
$app->get('/api/eventos/{id}/colaboradores', function (Request $request, Response $response) {
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
            $sql = "SELECT nome, descricao, tipo_colaborador, image_src FROM `participantes_colaboradores` RIGHT JOIN colaboradores ON participantes_colaboradores.colaboradores_id_colaboradores = colaboradores.id_colaboradores WHERE eventos_id_eventos = :id ORDER BY $passar DESC LIMIT :limit , :results";
        } else {
            $sql = "SELECT nome, descricao, tipo_colaborador, image_src FROM `participantes_colaboradores` RIGHT JOIN colaboradores ON participantes_colaboradores.colaboradores_id_colaboradores = colaboradores.id_colaboradores WHERE eventos_id_eventos = :id ORDER BY $passar  LIMIT :limit , :results";
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

            // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
            $errorMsg = Errors::filtroReturn(function ($err) {
                return [
                    "error" => [
                        "status" => $err->getCode(),
                        "text" => $err->getMessage()
                    ]
                ];
            }, function ($err) {
                return [
                    "error" => 'Servico Indisponivel'
                ];
            }, $err);

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

//////////// Obter extras de um evento + icons ////////////
# Parametros:
#   *page = pagina de resultados *obrigatorio
#   results = número de resultados por página
#   min: 1, max: 10
#   Exemplo: /api/eventos/2/extras?page=1&results=2&by=id&order=ASC
$app->get('/api/eventos/{id}/extras', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id'); // ir buscar id

    $byArr = [
        'id' => 'id_extras',
        'nome' => 'titulo'

    ]; // Valores para ordernar por, fizemos uma array para simplificar queries


    //valores default
    $maxResults = 10; // maximo de resultados por pagina
    $minResults = 1; // minimo de resultados por pagina
    $byDefault = 'nome'; // order by predefinido
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
            $sql = "SELECT titulo, descricao, iconsA.classe AS descricao_classe, descricao_pequena, iconsB.classe AS descricao_pequena_classe FROM `eventos_has_eventos_infos` LEFT JOIN eventos_infos ON eventos_has_eventos_infos.eventos_infos_id_extras = eventos_infos.id_extras LEFT JOIN icons iconsA ON eventos_infos.icons_id_icons = iconsA.id_icons LEFT JOIN icons iconsB ON eventos_infos.icons_pequeno_icons_id = iconsB.id_icons WHERE eventos_id_eventos = :id ORDER BY $passar DESC  LIMIT :limit , :results";
        } else {
            $sql = "SELECT titulo, descricao, iconsA.classe AS descricao_classe, descricao_pequena, iconsB.classe AS descricao_pequena_classe FROM `eventos_has_eventos_infos` LEFT JOIN eventos_infos ON eventos_has_eventos_infos.eventos_infos_id_extras = eventos_infos.id_extras LEFT JOIN icons iconsA ON eventos_infos.icons_id_icons = iconsA.id_icons LEFT JOIN icons iconsB ON eventos_infos.icons_pequeno_icons_id = iconsB.id_icons WHERE eventos_id_eventos = :id ORDER BY $passar  LIMIT :limit , :results";

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
            // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
            $errorMsg = Errors::filtroReturn(function ($err) {
                return [
                    "error" => [
                        "status" => $err->getCode(),
                        "text" => $err->getMessage()
                    ]
                ];
            }, function () {
                return [
                    "error" => 'Servico Indisponivel'
                ];
            }, $err);

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

//////////// Obter tags de um evento ////////////
# Parametros:
#   *page = pagina de resultados *obrigatorio
#   results = número de resultados por página
#   min: 1, max: 10
#   Exemplo: /api/eventos/2/tags?page=1&results=2&by=id&order=ASC
$app->get('/api/eventos/{id}/tags', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id'); // ir buscar id

    $byArr = [
        'id' => 'id_tags',
        'nome' => 'tag_nome'

    ]; // Valores para ordernar por, fizemos uma array para simplificar queries


    //valores default
    $maxResults = 3; // maximo de resultados por pagina
    $minResults = 1; // minimo de resultados por pagina
    $byDefault = 'nome'; // order by predefinido
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
            $sql = "SELECT tag_nome FROM `tags` INNER JOIN eventos_has_tags ON eventos_has_tags.`tags_id_tags` = tags.id_tags where eventos_has_tags.eventos_id_eventos=:id ORDER BY $passar DESC LIMIT :limit , :results";
        } else {
            $sql = "SELECT tag_nome FROM `tags` INNER JOIN eventos_has_tags ON eventos_has_tags.`tags_id_tags` = tags.id_tags where eventos_has_tags.eventos_id_eventos=:id ORDER BY $passar LIMIT :limit , :results";
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
                $dados = ["error" => 'pagina/tags inexistentes'];
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
            // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
            $errorMsg = Errors::filtroReturn(function ($err) {
                return [
                    "error" => [
                        "status" => $err->getCode(),
                        "text" => $err->getMessage()
                    ]
                ];
            }, function () {
                return [
                    "error" => 'Servico Indisponivel'
                ];
            }, $err);

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

/////////// Pesquisa de eventos ///////////

$app->get('/api/pesquisa/eventos', function (Request $request, Response $response) {
    $byArr = [
        'id' => 'id_eventos',
        'nome' => 'nome_evento',
        'dataRegisto' => 'data_registo_evento',
        'dataEvento' => 'data_evento',
        'dataFim' => 'data_fim',
        'ativo' => 'ativo',
        'tipoEvento' => 'nome_tipo_evento',
        'local' => 'nome',
    ]; // Valores para ordernar por, fizemos uma array para simplificar queries


    $maxResults = 10; // maximo de resultados por pagina
    $minResults = 1; // minimo de resultados por pagina
    $byDefault = 'id'; // order by predefinido
    $paginaDefault = 1; // pagina predefenida
    $orderDefault = "ASC"; //ordenação predefenida
    $dataMaxUnix = 2147403600; //correspondente a 2286-11-20 em formato Y-m-d UNIX
    $dataMinUnix = 1; // correspondente a 1970-01-01 em formato Y-m-d UNIX
    $fotoPeqW = 100; //
    $fotoPeqH = 100; //
    $fotoMedW = 200; //
    $fotoMedH = 200; //
    $fotoGraW = 300;
    $fotoGraH = 300;
    $msgDefault = '%%';


    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $page = isset($parametros['page']) ? (int)$parametros['page'] : $paginaDefault;
    $results = isset($parametros['results']) ? (int)$parametros['results'] : $maxResults;
    $by = isset($parametros['by']) ? $parametros['by'] : $byDefault;
    $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;
    $dataMin = isset($parametros['dataMin']) ? (int)$parametros['dataMin'] : false;
    $dataMax = isset($parametros['dataMax']) ? (int)$parametros['dataMax'] : false;
    $msg = isset($parametros['msg']) ? $parametros['msg'] : $msgDefault;

    if ($page > 0 && $results > 0) {

        //definir numero de resultados
        //caso request tenha parametros superiores ao numero máximo permitido então repor com o valor maximo permitido e vice-versa
        $results = $results > $maxResults ? $maxResults : $results; //se o querystring results for maior que o valor maximo definido passa a ser esse valor maximo definido
        $results = $results < $minResults ? $minResults : $results; //se o querystring results for menor que o valor minimo definido passa a ser esse valor minimo definido
        //caso tenha parametros diferentes de "ASC" ou "DESC" então repor com o predefinido
        $order = $order == "ASC" || $order == "DESC" ? $order : $orderDefault;
        //order by se existe como key no array, caso nao repor com o predefenido
        $by = array_key_exists($by, $byArr) ? $by : $byDefault;
        //verificar se UNIX é valido senão repor com valores válidos quer para o valor de data max, quer para a data minima
        if ($dataMin) $dataMin = $dataMin < $dataMinUnix ? $dataMin : $dataMinUnix;
        if ($dataMax) $dataMax = $dataMax > $dataMaxUnix ? $dataMax : $dataMaxUnix;

        //passar dataMin e dataMax para o formato yyyy-mm-dd

        // A partir de quando seleciona resultados
        $limitNumber = ($page - 1) * $results;
        $passar = $byArr[$by];
        $dataMinFormat = gmdate("Y-m-d", (int)$dataMin); //colocar a data minima no formato adquado á comparação
        $dataMaxFormat = gmdate("Y-m-d", (int)$dataMax);//colocar a data máxima no formato adquado á comparação
        if ($order == $orderDefault) {
            if ($dataMin && $dataMax) $sql = "SELECT id_eventos,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(participantes.eventos_id_eventos) AS inscritos FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos  WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND `data_evento` BETWEEN :datamin AND :datamax GROUP BY eventos.id_eventos ORDER BY $passar  LIMIT :limit, :results";
            elseif ($dataMin && !$dataMax) $sql = "SELECT id_eventos,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(participantes.eventos_id_eventos) AS inscritos FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos  WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND `data_evento` >= :datamin  GROUP BY eventos.id_eventos ORDER BY $passar  LIMIT :limit, :results";
            elseif (!$dataMin && $dataMax) $sql = "SELECT id_eventos,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(participantes.eventos_id_eventos) AS inscritos FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos  WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND `data_evento` <= :datamax GROUP BY eventos.id_eventos ORDER BY $passar  LIMIT :limit, :results";
            else {
                $sql = "SELECT id_eventos,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(participantes.eventos_id_eventos) AS inscritos FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos  WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) GROUP BY eventos.id_eventos ORDER BY $passar  LIMIT :limit, :results";
            }
        } else {
            if ($dataMin && $dataMax) $sql = "SELECT id_eventos,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(participantes.eventos_id_eventos) AS inscritos FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos  WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND `data_evento` BETWEEN :datamin AND :datamax GROUP BY eventos.id_eventos ORDER BY $passar DESC LIMIT :limit, :results";
            elseif ($dataMin && !$dataMax) $sql = "SELECT id_eventos,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(participantes.eventos_id_eventos) AS inscritos FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos  WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND `data_evento` >= :datamin  GROUP BY eventos.id_eventos ORDER BY $passar DESC  LIMIT :limit, :results";
            elseif (!$dataMin && $dataMax) $sql = "SELECT id_eventos,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(participantes.eventos_id_eventos) AS inscritos FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos  WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND `data_evento` <= :datamax GROUP BY eventos.id_eventos ORDER BY $passar DESC LIMIT :limit, :results";
            else {
                $sql = "SELECT id_eventos,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(participantes.eventos_id_eventos) AS inscritos FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos  WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) GROUP BY eventos.id_eventos ORDER BY $passar  LIMIT :limit, :results";
            }
        }

        try {

            $status = 200; // OK
            // iniciar ligação à base de dados
            $db = new Db();
            $msgEnv = $msg != $msgDefault ? "%$msg%" : $msg;
            // colocar mensagem no formato correto
            // conectar
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limitNumber, PDO::PARAM_INT);
            $stmt->bindValue(':results', (int)$results, PDO::PARAM_INT);
            $stmt->bindValue(':msg', $msgEnv, PDO::PARAM_INT);
            $stmt->bindValue(':datamin', $dataMinFormat, PDO::PARAM_INT);
            $stmt->bindValue(':datamax', $dataMaxFormat, PDO::PARAM_INT);
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
                'data' =>
                    $dados
            ];

            return $response
                ->withJson($responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


        } catch (PDOException $err) {
            $status = 503; // Service unavailable
            // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
            $errorMsg = Errors::filtroReturn(function ($err) {
                return [
                    "error" => [
                        "status" => $err->getCode(),
                        "text" => $err->getMessage()
                    ]
                ];
            }, function () {
                return [
                    "error" => 'Servico Indisponivel'
                ];
            }, $err);

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
