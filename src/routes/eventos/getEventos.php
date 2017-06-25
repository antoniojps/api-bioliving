<?php

// importar classes para scope
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Token as Token;
use Bioliving\Custom\Helper as H;

//////////// Obter todos os eventos ////////////

$app->get('/eventos', function (Request $request, Response $response) {
    if (Token::validarScopes('admin')) {
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
        $dataMaxUnix = 2147403600; //correspondente a 2286-11-20 em formato Y-m-d UNIX
        $dataMinUnix = 1; // correspondente a 1970-01-01 em formato Y-m-d UNIX
        $msgDefault = '%';
        $idDefault = '%';


        $parametros = $request->getQueryParams(); // obter parametros do querystring
        $page = isset($parametros['page']) ? (int)$parametros['page'] : $paginaDefault;
        $results = isset($parametros['results']) ? (int)$parametros['results'] : $maxResults;
        $by = isset($parametros['by']) ? $parametros['by'] : $byDefault;
        $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;
        $dataMin = isset($parametros['dataMin']) ? (int)$parametros['dataMin'] : false;
        $dataMax = isset($parametros['dataMax']) ? (int)$parametros['dataMax'] : false;
        $msg = isset($parametros['msg']) ? $parametros['msg'] : $msgDefault;
        $id = isset($parametros['id']) ? (int)$parametros['id'] : $idDefault;


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
            if ($dataMin) $dataMin = $dataMin > $dataMinUnix ? $dataMin : $dataMinUnix;
            if ($dataMax) $dataMax = $dataMax < $dataMaxUnix ? $dataMax + 86400 * 2 : $dataMaxUnix;


            // A partir de quando seleciona resultados
            $limitNumber = ($page - 1) * $results;
            $passar = $byArr[$by];

            //passar dataMin e dataMax para o formato yyyy-mm-dd
            $dataMinFormat = gmdate("Y-m-d", (int)$dataMin); //colocar a data minima no formato adquado á comparação
            $dataMaxFormat = gmdate("Y-m-d", (int)$dataMax);//colocar a data máxima no formato adquado á comparação


            if ($dataMin && $dataMax) $extraWhere = " AND `data_evento` >= :datamin AND  `data_evento` <= :datamax  ";
            elseif ($dataMin && !$dataMax) $extraWhere = " AND `data_evento` >= :datamin  ";
            elseif (!$dataMin && $dataMax) $extraWhere = " AND `data_evento` <= :datamax ";


            if ($order == $orderDefault) {
                $sql = "SELECT eventos.*,tipo_evento.nome_tipo_evento,icons.classe AS tipo_classe, COUNT(participantes.utilizadores_id_utilizadores) AS participantes ,localizacao.nome,localizacao.lat,localizacao.lng FROM eventos LEFT OUTER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao LEFT OUTER JOIN participantes ON eventos.id_eventos = participantes.eventos_id_eventos LEFT OUTER JOIN tipo_evento ON eventos.tipo_evento_id_tipo_evento = tipo_evento.id_tipo_evento LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id WHERE (nome_evento LIKE :msg OR data_evento LIKE :msg OR descricao_short LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND id_eventos LIKE :id GROUP BY id_eventos ORDER BY $passar  LIMIT :limit , :results";
            } else {
                $sql = "SELECT eventos.*,tipo_evento.nome_tipo_evento,icons.classe AS tipo_classe, COUNT(participantes.utilizadores_id_utilizadores) AS participantes ,localizacao.nome,localizacao.lat,localizacao.lng FROM eventos LEFT OUTER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao LEFT OUTER JOIN participantes ON eventos.id_eventos = participantes.eventos_id_eventos LEFT OUTER JOIN tipo_evento ON eventos.tipo_evento_id_tipo_evento = tipo_evento.id_tipo_evento LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id WHERE (nome_evento LIKE :msg OR data_evento LIKE :msg OR descricao_short LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND id_eventos LIKE :id GROUP BY id_eventos ORDER BY $passar  LIMIT :limit , :results ORDER BY $passar DESC LIMIT :limit , :results";
            }

            try {

                $status = 200; // OK
                // iniciar ligação à base de dados
                $db = new Db();
                $msgEnv = $msg != $msgDefault ? "%$msg%" : $msg;
                // conectar
                $db = $db->connect();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':limit', (int)$limitNumber, PDO::PARAM_INT);
                $stmt->bindValue(':results', (int)$results, PDO::PARAM_INT);
                $stmt->bindValue(':msg', $msgEnv, PDO::PARAM_INT);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                if ($dataMin && $dataMax) {
                    $stmt->bindValue(':datamin', $dataMinFormat, PDO::PARAM_INT);
                    $stmt->bindValue(':datamax', $dataMaxFormat, PDO::PARAM_INT);
                } elseif ($dataMin && !$dataMax) $stmt->bindValue(':datamin', $dataMinFormat, PDO::PARAM_INT);
                elseif (!$dataMin && $dataMax) $stmt->bindValue(':datamax', $dataMaxFormat, PDO::PARAM_INT);
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
                    $responseData = [
                        "status" => 404,
                        "info" => 'pagina inexistente'
                    ];
                    // Page not found

                } else if ($dadosLength < $results) {
                    $responseData = [
                        "status" => 200,
                        "data" => $dados,
                        "info" => "final dos resultados"
                    ];


                } else {
                    $nextPageUrl = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
                    $responseData = [
                        "status" => 200,
                        "data" => $dados,
                        "proxPagina" => "$nextPageUrl?page=" . ++$page . "&results=$results"
                    ];
                }


                return $response
                    ->withJson($responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


            } catch (PDOException $err) {
                $status = 503; // Service unavailable
                // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
                $errorMsg = Errors::filtroReturn(function ($err) {
                    return [

                        "status" => $err->getCode(),
                        "info" => $err->getMessage()

                    ];
                }, function () {
                    return [
                        "status" => 503,
                        "info" => 'Servico Indisponivel'
                    ];
                }, $err);

                return $response
                    ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
            }

        } else {
            $status = 422; // Unprocessable Entity
            $errorMsg = [

                "status" => "$status",
                "info" => 'Parametros invalidos'


            ];

            return $response
                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
        }
    } else {
        $status = 401;
        $errorMsg = [

            "status" => "$status",
            "info" => 'Acesso não autorizado'


        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }
});


//////////// Obter dados de um evento através do ID ////////////
$app->get('/eventos/{id}', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id'); // ir buscar id
    //verificar se é um id válido

    if (is_int($id) && $id > 0) {

        $sql = "SELECT eventos.*,tipo_evento.nome_tipo_evento as tipo,localizacao.*,COUNT(participantes.utilizadores_id_utilizadores) as participantes,facebook_id FROM eventos LEFT OUTER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao LEFT OUTER JOIN participantes ON eventos.id_eventos = participantes.eventos_id_eventos LEFT OUTER JOIN tipo_evento ON eventos.tipo_evento_id_tipo_evento = tipo_evento.id_tipo_evento  WHERE id_eventos = :id ";

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
                $status = 404;
                $responseData = [
                    "status" => 404,
                    "info" => 'evento inexistente'
                ];// Page not found
            } else {
                $responseData = [
                    'status' => "$status",
                    'data' =>
                        $dados
                ];
            }


            return $response
                ->withJson($responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


        } catch (PDOException $err) {
            $status = 503; // Service unavailable
            // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
            $errorMsg = Errors::filtroReturn(function ($err) {
                return [
                    "status" => $err->getCode(),
                    "info" => $err->getMessage()
                ];
            }, function () {
                return [
                    "status" => 503,
                    "info" => 'Servico Indisponivel'
                ];
            }, $err);

            return $response
                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

        }
    } else {
        $status = 422; // Unprocessable Entity
        $errorMsg = [

            "status" => "$status",
            "info" => 'Parametros invalidos'

        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }
});


//////////// Obter colaboradores de um evento ////////////
$app->get('/eventos/{id}/colaboradores', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id'); // ir buscar id

    $byArr = [
        'id' => 'colaboradores_id_colaboradores',
        'nome' => 'nome'

    ]; // Valores para ordernar por, fizemos uma array para simplificar queries

    //valores default
    $maxResults = 3; // maximo de resultados por pagina
    $minResults = 1; // minimo de resultados por pagina
    $byDefault = 'nome'; // order by predefinido
    $paginaDefault = 1; // pagina predefenida
    $orderDefault = "ASC"; //ordenação predefenida


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
        if ($order != $orderDefault) {
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
                $status = 404;
                $responseData = [
                    "status" => 404,
                    "info" => 'página inexistentes'
                ]; // Page not found
            } else if ($dadosLength < $results) {

                $responseData = [
                    "status" => 200,
                    "data" => $dados,
                    'info' => 'final dos resultados'
                ];

            } else {
                $nextPageUrl = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
                $responseData = [
                    "status" => 200,
                    "data" => $dados,
                    "proxPagina" => "$nextPageUrl?page=" . ++$page . "&results=$results"
                ];
            }

            return $response
                ->withJson($responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


        } catch (PDOException $err) {
            $status = 503; // Service unavailable

            // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
            $errorMsg = Errors::filtroReturn(function ($err) {
                return [

                    "status" => $err->getCode(),
                    "info" => $err->getMessage()

                ];
            }, function ($err) {
                return [
                    "status" => 503,
                    "info" => 'Servico Indisponivel'
                ];
            }, $err);

            return $response
                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

        }
    } else {
        $status = 422; // Unprocessable Entity
        $errorMsg = [

            "status" => "$status",
            "info" => 'Parametros invalidos'


        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }


});

//////////// Obter extras de um evento + icons ////////////
$app->get('/eventos/{id}/extras', function (Request $request, Response $response) {
    $id = $request->getAttribute('id'); // ir buscar id
    // Valores para ordernar por, fizemos uma array para simplificar queries
    $orderDefault = "ASC"; //ordenação predefenida
    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;
    if (v::intVal()->validate($id) && $id > 0) {
        $dados = '';
        $info = 'Servico indisponivel';
        //caso tenha parametros diferentes de "ASC" ou "DESC" então repor com o predefinido
        $order = $order == "ASC" || $order == "DESC" ? $order : $orderDefault;
        //Apenas informações básicas dos inscritos para por exemplo cards, e ao clicar na card o utilizador pode ser reencaminhado para o masterdetail do utlizador da card
        if ($order != $orderDefault) {
            $sql = "SELECT titulo, descricao, iconsA.classe AS descricao_classe, descricao_pequena, iconsB.classe AS descricao_pequena_classe FROM `eventos_has_eventos_infos` LEFT JOIN eventos_infos ON eventos_has_eventos_infos.eventos_infos_id_extras = eventos_infos.id_extras LEFT JOIN icons iconsA ON eventos_infos.icons_id_icons = iconsA.id_icons LEFT JOIN icons iconsB ON eventos_infos.icons_pequeno_icons_id = iconsB.id_icons WHERE eventos_id_eventos = :id ORDER BY eventos_infos.id_extras DESC";
        } else {
            $sql = "SELECT titulo, descricao, iconsA.classe AS descricao_classe, descricao_pequena, iconsB.classe AS descricao_pequena_classe FROM `eventos_has_eventos_infos` LEFT JOIN eventos_infos ON eventos_has_eventos_infos.eventos_infos_id_extras = eventos_infos.id_extras LEFT JOIN icons iconsA ON eventos_infos.icons_id_icons = iconsA.id_icons LEFT JOIN icons iconsB ON eventos_infos.icons_pequeno_icons_id = iconsB.id_icons WHERE eventos_id_eventos = :id ORDER BY eventos_infos.id_extras";
        }
        try {
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
            $dados = H::filtrarArrMulti($dados);
            $dadosLength = (int)sizeof($dados);

            if ($dadosLength === 0) {
                $status = 404;
                $info = 'evento inexistente ou sem extras';
                $status = 404; // Page not found
            } else {
                $info = 'extras obtidos com sucesso';
                $status = 200; // Ok
            }
        } catch (PDOException $err) {
            $status = 503; // Service unavailable
            // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
            $info = Errors::filtroReturn(function ($err) {
                return $err->getCode();
            }, function () {
                return 'servico indisponivel';
            }, $err);
        }
    } else {
        $status = 422; // Unprocessable Entity
        $info = 'Parametros invalidos';
    }
    $responseData = [
        'status' => "$status",
        'info' => "$info",
        'data' => $dados
    ];
    $responseData = H::filtrarArr($responseData);
    return $response
        ->withJson($responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
});


//////////// Obter tags de um evento ////////////
$app->get('/eventos/{id}/tags', function (Request $request, Response $response) {
    $dados = null;
    $id = $request->getAttribute('id'); // ir buscar id

    //valores default
    $orderDefault = "ASC"; //ordenação predefenida


    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;


    if (v::intVal()->validate($id) && $id > 0) {
        //Apenas informações básicas dos inscritos para por exemplo cards, e ao clicar na card o utilizador pode ser reencaminhado para o masterdetail do utlizador da card
        if ($order != $orderDefault) {
            $sql = "SELECT tag_nome FROM `tags` INNER JOIN eventos_has_tags ON eventos_has_tags.`tags_id_tags` = tags.id_tags where eventos_has_tags.eventos_id_eventos=:id ORDER BY tag_nome DESC";
        } else {
            $sql = "SELECT tag_nome FROM `tags` INNER JOIN eventos_has_tags ON eventos_has_tags.`tags_id_tags` = tags.id_tags where eventos_has_tags.eventos_id_eventos=:id ORDER BY tag_nome";
        }

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
            $dados = H::filtrarArrMulti($dados);

            // Converter array multi-dimensional em string seperada por ','
            $dadosTags = implode(",", array_map(function ($a) {
                return implode("~", $a);
            }, $dados));
            $dadosTags = explode(',', $dadosTags);
            $dadosLength = (int)sizeof($dados);
            $dados = [];


            if ($dadosLength === 0) {
                $info = 'pagina/tags inexistentes';
                $status = 404; // Page not found
            } else {
                $info = 'tags obtidas com sucesso';
                $dados['tags'] = $dadosTags;
            }


        } catch (PDOException $err) {
            $status = 503; // Service unavailable
            // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
            $info = Errors::filtroReturn(function ($err) {
                return $err->getMessage();
            }, function () {
                return 'servico indisponivel';
            }, $err);
        }
    } else {
        $status = 422; // Unprocessable Entity
        $info = 'parametros invalidos';
    }

    $responseData = [
        'status' => "$status",
        'info' => "$info",
        'data' => $dados
    ];

    $responseData = H::filtrarArr($responseData);


    return $response
        ->withJson($responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


});

/////////// Pesquisa de eventos ///////////
/*
 * Exemplo:/api/pesquisa/eventos?msg=desporto&by=nome&page=1&results=10&order=ASC&dataMax=2140403600&dataMin=16
 *
 * @param string msg -> pesquisa eventos por nome, localização e tipo
 * @param string by -> coluna a ordenar (id,nome,dataRegisto,dataEvento,dataFim,ativo,tipoEvento,local) default: data do evento
 *
 */
$app->get('/pesquisa/eventos', function (Request $request, Response $response) {
    $byArr = [
        'id' => 'id_eventos',
        'nome' => 'nome_evento',
        'dataRegisto' => 'data_registo_evento',
        'dataEvento' => 'data_evento',
        'dataFim' => 'data_fim',
        'tipoEvento' => 'tipo_evento.nome_tipo_evento',
        'local' => 'localizacao.nome',
    ]; // Valores para ordernar por, fizemos uma array para simplificar queries


    $maxResults = 10; // maximo de resultados por pagina
    $minResults = 1; // minimo de resultados por pagina
    $byDefault = 'dataEvento'; // order by predefinido
    $paginaDefault = 1; // pagina predefenida
    $orderDefault = "ASC"; //ordenação predefenida
    $dataMaxUnix = 2147403600; //correspondente a 2286-11-20 em formato Y-m-d UNIX
    $dataMinUnix = 1; // correspondente a 1970-01-01 em formato Y-m-d UNIX
    $msgDefault = '%%';
    $latDefault = false;
    $lngDefault = false;
    //TODO :
    // - lat
    // - lng
    //- facebook_Id
    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $page = isset($parametros['page']) ? (int)$parametros['page'] : $paginaDefault;
    $results = isset($parametros['results']) ? (int)$parametros['results'] : $maxResults;
    $by = isset($parametros['by']) ? $parametros['by'] : $byDefault;
    $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;
    $dataMin = isset($parametros['dataMin']) ? (int)$parametros['dataMin'] : false;
    $dataMax = isset($parametros['dataMax']) ? (int)$parametros['dataMax'] : false;
    $msg = isset($parametros['msg']) ? $parametros['msg'] : $msgDefault;
    $lat = isset($parametros['lat']) ? $parametros['lat'] : $latDefault;
    $lng = isset($parametros['lng']) ? $parametros['lng'] : $lngDefault;

    if ($lat != false && $lng != false) {
        $extraWhere = " AND lat LIKE :lat AND lng LIKE :lng ";
    } elseif ($lat != false && $lng == false) {
        $extraWhere = " AND lat LIKE :lat ";
    } elseif ($lat == false && $lng != false) {
        $extraWhere = " AND lng LIKE :lng ";
    } else {
        $extraWhere = "";
    }


    if ($page > 0 && $results > 0) {

        //definir numero de resultados
        //caso request tenha parametros superiores ao numero máximo permitido então repor com o valor maximo permitido e vice-versa
        $results = $results > $maxResults ? $maxResults : $results; //se o querystring results for maior que o valor maximo definido passa a ser esse valor maximo definido
        $results = $results < $minResults ? $minResults : $results; //se o querystring results for menor que o valor minimo definido passa a ser esse valor minimo definido
        //caso tenha parametros diferentes de "ASC" ou "DESC" então repor com o predefinido
        $order = $order == "ASC" || $order == "DESC" ? $order : $orderDefault;
        //order by se existe como key no array, caso nao repor com o predefenido
        $by = array_key_exists($by, $byArr) ? $by : $byDefault;
        $limitNumber = ($page - 1) * $results;
        $passar = $byArr[$by];
        //verificar se UNIX é valido senão repor com valores válidos quer para o valor de data max, quer para a data minima
        if ($dataMin || $dataMax) {
            if ($dataMin) $dataMin = $dataMin > $dataMinUnix ? $dataMin : $dataMinUnix;
            if ($dataMax) $dataMax = $dataMax < $dataMaxUnix ? $dataMax + 86400 * 2 : $dataMaxUnix;

            //passar dataMin e dataMax para o formato yyyy-mm-dd

            // A partir de quando seleciona resultados
            $dataMinFormat = gmdate("Y-m-d", (int)$dataMin); //colocar a data minima no formato adquado á comparação
            $dataMaxFormat = gmdate("Y-m-d", (int)$dataMax);//colocar a data máxima no formato adquado á comparação
            if ($order == $orderDefault) {
                if ($dataMin && $dataMax) $sql = "SELECT id_eventos,eventos.utilizadores_id_utilizadores AS criador ,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(DISTINCT participantes.utilizadores_id_utilizadores) AS inscritos, COUNT(DISTINCT interesses.utilizadores_id_utilizadores) AS interessados,lat,lng,facebook_id,icons.classe AS tipo_classe FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos  LEFT OUTER JOIN icons ON tipo_evento.icons_id = icons.id_icons WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND `data_evento` >= :datamin AND`data_evento` <= :datamax $extraWhere AND eventos.ativo =1 GROUP BY eventos.data_registo_evento ORDER BY $passar  LIMIT :limit, :results";
                elseif ($dataMin && !$dataMax) $sql = "SELECT id_eventos,eventos.utilizadores_id_utilizadores AS criador,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(DISTINCT participantes.utilizadores_id_utilizadores) AS inscritos, COUNT(DISTINCT interesses.utilizadores_id_utilizadores) AS interessados,lat,lng,facebook_id,icons.classe AS tipo_classe FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos  LEFT OUTER JOIN icons ON tipo_evento.icons_id = icons.id_icons WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND `data_evento` >= :datamin $extraWhere AND eventos.ativo =1 GROUP BY eventos.data_registo_evento ORDER BY $passar  LIMIT :limit, :results";
                elseif (!$dataMin && $dataMax) $sql = "SELECT id_eventos,eventos.utilizadores_id_utilizadores AS criador,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(DISTINCT participantes.utilizadores_id_utilizadores) AS inscritos, COUNT(DISTINCT interesses.utilizadores_id_utilizadores) AS interessados,lat,lng,facebook_id,icons.classe AS tipo_classe FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos  LEFT OUTER JOIN icons ON tipo_evento.icons_id = icons.id_icons WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND `data_evento` <= :datamax $extraWhere AND eventos.ativo =1 GROUP BY eventos.id_eventos ORDER BY $passar  LIMIT :limit, :results";
                else {
                    $sql = "SELECT id_eventos,eventos.utilizadores_id_utilizadores AS criador,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(DISTINCT participantes.utilizadores_id_utilizadores) AS inscritos, COUNT(DISTINCT interesses.utilizadores_id_utilizadores) AS interessados,lat,lng,facebook_id,icons.classe AS tipo_classe FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos  LEFT OUTER JOIN icons ON tipo_evento.icons_id = icons.id_icons WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg)$extraWhere  AND eventos.ativo =1 GROUP BY eventos.id_eventos ORDER BY $passar  LIMIT :limit, :results";
                }
            } else {
                if ($dataMin && $dataMax) $sql = "SELECT id_eventos,eventos.utilizadores_id_utilizadores AS criador,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(DISTINCT participantes.utilizadores_id_utilizadores) AS inscritos, COUNT(DISTINCT interesses.utilizadores_id_utilizadores) AS interessados,lat,lng,facebook_id,icons.classe AS tipo_classe FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos  LEFT OUTER JOIN icons ON tipo_evento.icons_id = icons.id_icons WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND `data_evento` >= :datamin AND`data_evento` <= :datamax $extraWhere AND eventos.ativo =1 GROUP BY eventos.id_eventos ORDER BY $passar DESC LIMIT :limit, :results";
                elseif ($dataMin && !$dataMax) $sql = "SELECT id_eventos,eventos.utilizadores_id_utilizadores AS criador,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(DISTINCT participantes.utilizadores_id_utilizadores) AS inscritos, COUNT(DISTINCT interesses.utilizadores_id_utilizadores) AS interessados,lat,lng,facebook_id,icons.classe AS tipo_classe FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos  LEFT OUTER JOIN icons ON tipo_evento.icons_id = icons.id_icons WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND `data_evento` >= :datamin $extraWhere  AND eventos.ativo =1 GROUP BY eventos.id_eventos ORDER BY $passar DESC  LIMIT :limit, :results";
                elseif (!$dataMin && $dataMax) $sql = "SELECT id_eventos,eventos.utilizadores_id_utilizadores AS criador,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(DISTINCT participantes.utilizadores_id_utilizadores) AS inscritos, COUNT(DISTINCT interesses.utilizadores_id_utilizadores) AS interessados,lat,lng,facebook_id,icons.classe AS tipo_classe FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos  LEFT OUTER JOIN icons ON tipo_evento.icons_id = icons.id_icons WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) AND `data_evento` <= :datamax $extraWhere AND eventos.ativo =1  GROUP BY eventos.id_eventos  ORDER BY $passar DESC LIMIT :limit, :results";
                else {
                    $sql = "SELECT id_eventos,eventos.utilizadores_id_utilizadores AS criador,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(DISTINCT participantes.utilizadores_id_utilizadores) AS inscritos, COUNT(DISTINCT interesses.utilizadores_id_utilizadores) AS interessados,lat,lng,facebook_id,icons.classe AS tipo_classe FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos  LEFT OUTER JOIN icons ON tipo_evento.icons_id = icons.id_icons WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg) $extraWhere AND eventos.ativo =1  GROUP BY eventos.id_eventos ORDER BY $passar  LIMIT :limit, :results";
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
                if ($lat != false && $lng != false) {
                    $stmt->bindValue(':lat', $lat, PDO::PARAM_INT);
                    $stmt->bindValue(':lng', $lng, PDO::PARAM_INT);
                } elseif ($lat != false && $lng == false) {
                    $stmt->bindValue(':lat', $lat, PDO::PARAM_INT);
                } elseif ($lat == false && $lng != false) {
                    $stmt->bindValue(':lng', $lng, PDO::PARAM_INT);
                }
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
                    $status = 404;
                    $responseData = [
                        "status" => 404,
                        "info" => 'pagina inexistente'
                    ]; // Page not found

                } else if ($dadosLength < $results) {
                    $responseData = [
                        "status" => 200,
                        "data" => $dados,
                        "info" => "final dos resultados"
                    ];

                } else {
                    $nextPageUrl = H::nextPageUrl();
                    $responseData = [
                        "status" => 200,
                        "data" => $dados,
                        "proxPagina" => "$nextPageUrl?page=" . ++$page . "&results=$results"
                    ];
                }


                return $response
                    ->withJson($responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


            } catch (PDOException $err) {
                $status = 503; // Service unavailable
                // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
                $errorMsg = Errors::filtroReturn(function ($err) {
                    return [

                        "status" => $err->getCode(),
                        "info" => $err->getMessage()

                    ];
                }, function () {
                    return [
                        "status" => 503,
                        "info" => 'Servico Indisponivel'
                    ];
                }, $err);

                return $response
                    ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
            }

        } else {
            if ($order == $orderDefault) {
                $sql = "SELECT id_eventos,eventos.utilizadores_id_utilizadores AS criador,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(DISTINCT participantes.utilizadores_id_utilizadores) AS inscritos, COUNT(DISTINCT interesses.utilizadores_id_utilizadores) AS interessados,lat,lng,facebook_id,icons.classe AS tipo_classe FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos  LEFT OUTER JOIN icons ON tipo_evento.icons_id = icons.id_icons WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg)  AND eventos.ativo =1 AND data_evento > CAST(CURRENT_TIMESTAMP AS DATE) GROUP BY eventos.id_eventos  
UNION
SELECT id_eventos,eventos.utilizadores_id_utilizadores AS criador,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(DISTINCT participantes.utilizadores_id_utilizadores) AS inscritos, COUNT(DISTINCT interesses.utilizadores_id_utilizadores) AS interessados,lat,lng,facebook_id,icons.classe AS tipo_classe FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos  LEFT OUTER JOIN icons ON tipo_evento.icons_id = icons.id_icons WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg)  AND eventos.ativo =1 AND data_evento < CAST(CURRENT_TIMESTAMP AS DATE) GROUP BY eventos.id_eventos  ORDER BY $passar  LIMIT :limit, :results";
            } else {
                $sql = "SELECT id_eventos,eventos.utilizadores_id_utilizadores AS criador,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(DISTINCT participantes.utilizadores_id_utilizadores) AS inscritos, COUNT(DISTINCT interesses.utilizadores_id_utilizadores) AS interessados,lat,lng,facebook_id,icons.classe AS tipo_classe FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos  LEFT OUTER JOIN icons ON tipo_evento.icons_id = icons.id_icons WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg)  AND eventos.ativo =1 AND data_evento > CAST(CURRENT_TIMESTAMP AS DATE) GROUP BY eventos.id_eventos  
UNION
SELECT id_eventos,eventos.utilizadores_id_utilizadores AS criador,nome_evento,tipo_evento.nome_tipo_evento,localizacao.nome,data_evento,descricao_short, COUNT(DISTINCT participantes.utilizadores_id_utilizadores) AS inscritos, COUNT(DISTINCT interesses.utilizadores_id_utilizadores) AS interessados,lat,lng,facebook_id,icons.classe AS tipo_classe FROM eventos LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento=eventos.tipo_evento_id_tipo_evento LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos  LEFT OUTER JOIN icons ON tipo_evento.icons_id = icons.id_icons WHERE (nome_evento LIKE :msg OR tipo_evento.nome_tipo_evento LIKE :msg OR localizacao.nome LIKE :msg)  AND eventos.ativo =1 AND data_evento < CAST(CURRENT_TIMESTAMP AS DATE) GROUP BY eventos.id_eventos  ORDER BY $passar  DESC LIMIT :limit, :results";
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
                if ($lat != false && $lng != false) {
                    $stmt->bindValue(':lat', $lat, PDO::PARAM_INT);
                    $stmt->bindValue(':lng', $lng, PDO::PARAM_INT);
                } elseif ($lat != false && $lng == false) {
                    $stmt->bindValue(':lat', $lat, PDO::PARAM_INT);
                } elseif ($lat == false && $lng != false) {
                    $stmt->bindValue(':lng', $lng, PDO::PARAM_INT);
                }
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
                    $status = 404;
                    $responseData = [
                        "status" => 404,
                        "info" => 'pagina inexistente'
                    ]; // Page not found

                } else if ($dadosLength < $results) {
                    $responseData = [
                        "status" => 200,
                        "data" => $dados,
                        "info" => "final dos resultados"
                    ];

                } else {
                    $nextPageUrl = H::nextPageUrl();
                    $responseData = [
                        "status" => 200,
                        "data" => $dados,
                        "proxPagina" => "$nextPageUrl?page=" . ++$page . "&results=$results"
                    ];
                }


                return $response
                    ->withJson($responseData, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


            } catch (PDOException $err) {
                $status = 503; // Service unavailable
                // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
                $errorMsg = Errors::filtroReturn(function ($err) {
                    return [

                        "status" => $err->getCode(),
                        "info" => $err->getMessage()

                    ];
                }, function () {
                    return [
                        "status" => 503,
                        "info" => 'Servico Indisponivel'
                    ];
                }, $err);

                return $response
                    ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
            }

        }
    } else {
        $status = 422; // Unprocessable Entity
        $errorMsg = [

            "status" => "$status",
            "info" => 'Parametros invalidos'


        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }
});
