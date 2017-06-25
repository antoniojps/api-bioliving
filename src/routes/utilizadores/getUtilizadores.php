<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Bioliving\Custom\Token as Token;
use Bioliving\Custom\Helper as Helper;


$app->get('/api/utilizadores', function (Request $request, Response $response) {
    if (Token::validarScopes('admin')) {

        $byArr = [
            'id' => 'id_utilizadores',
            'nome' => 'utilizadores.nome',
            'apelido' => 'apelido',
            'genero' => 'genero',
            'dataNasc' => 'data_nascimento',
            'dataRegisto' => 'data_registo_user',
            'email' => 'email',
            'sobre' => 'sobre',
            'sobreMini' => 'sobre_mini',
            'telemovel' => 'telemovel',
            'local' => 'localizacao.nome',
            'ativo' => 'ativo',
            'estatuto' => 'estatutos.nome_estatuto'
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


            if ($dataMin && $dataMax) $extraWhere = " AND `data_registo_user` >= :datamin AND  `data_registo_user` <= :datamax  ";
            elseif ($dataMin && !$dataMax) $extraWhere = " AND `data_registo_user` >= :datamin  ";
            elseif (!$dataMin && $dataMax) $extraWhere = " AND `data_registo_user` <= :datamax ";

            if ($order == $orderDefault) {
                $sql = "SELECT utilizadores.*,localizacao.nome,localizacao.lat,localizacao.lng,estatutos.nome_estatuto FROM `utilizadores` LEFT OUTER JOIN localizacao ON utilizadores.localizacao_id_localizacao = localizacao.localizacao LEFT OUTER JOIN estatutos ON utilizadores.estatutos_id_estatutos = estatutos.id_estatutos WHERE (utilizadores.`id_utilizadores` LIKE :msg OR utilizadores.`nome` LIKE :msg OR `apelido`LIKE :msg OR `data_nascimento`LIKE :msg OR `data_registo_user` LIKE :msg OR email LIKE :msg OR telemovel LIKE :msg AND localizacao.nome LIKE :msg OR estatutos.nome_estatuto LIKE :msg) AND id_utilizadores LIKE :id $extraWhere ORDER BY $passar  LIMIT :limit, :results";
            } else {
                $sql = "SELECT utilizadores.*,localizacao.nome,localizacao.lat,localizacao.lng,estatutos.nome_estatuto FROM `utilizadores` LEFT OUTER JOIN localizacao ON utilizadores.localizacao_id_localizacao = localizacao.localizacao LEFT OUTER JOIN estatutos ON utilizadores.estatutos_id_estatutos = estatutos.id_estatutos WHERE (utilizadores.`id_utilizadores` LIKE :msg OR utilizadores.`nome` LIKE :msg OR `apelido`LIKE :msg OR `data_nascimento`LIKE :msg OR `data_registo_user` LIKE :msg OR email LIKE :msg OR telemovel LIKE :msg AND localizacao.nome LIKE :msg OR estatutos.nome_estatuto LIKE :msg) AND id_utilizadores LIKE :id $extraWhere ORDER BY $passar DESC LIMIT :limit, :results";

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


////////////////////////GET para receber evento em especifico////////////////
$app->get('/api/utilizadores/{id}', function (Request $request, Response $response) {
    $idUtilizador = (int)$request->getAttribute('id'); // ir buscar id
    if (Token::validarScopes('admin', $idUtilizador)) {
        if (is_int($idUtilizador) && $idUtilizador > 0) {

            $sql = "SELECT utilizadores.*,localizacao.nome,localizacao.lat,localizacao.lng,estatutos.nome_estatuto FROM `utilizadores` LEFT OUTER JOIN localizacao ON localizacao.localizacao = utilizadores.localizacao_id_localizacao LEFT OUTER JOIN estatutos ON estatutos.id_estatutos = utilizadores.estatutos_id_estatutos  WHERE id_utilizadores = :id ";

            try {

                $status = 200; // OK

                // iniciar ligação à base de dados
                $db = new Db();

                // conectar
                $db = $db->connect();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':id', $idUtilizador, PDO::PARAM_INT);
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
                        "info" => 'utilizador inexistente'
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
            $status = 401;
            $errorMsg = [

                "status" => "$status",
                "info" => 'Acesso não autorizado'


            ];

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

///////GET para receber eventos em que um utilizador está interessado///////////
$app->get('/api/utilizador/{id}/interesse', function (Request $request, Response $response) {
    $byArr = [
        'id' => 'id_eventos',
        'nome' => 'nome_evento',
        'dataRegisto' => 'data_registo_evento',
        'dataEvento' => 'data_evento',
        'dataFim' => 'data_fim',
        'ativo' => 'ativo',
        'tipoEvento' => 'nome_tipo_evento',
        'local' => 'nome'
    ];
    $maxResults = 10; // maximo de resultados por pagina
    $minResults = 1; // minimo de resultados por pagina
    $byDefault = 'id'; // order by predefinido
    $paginaDefault = 1; // pagina predefenida
    $orderDefault = "ASC"; //ordenação predefenida
    $interessesDefault = "true"; //se for false retorna os que não está inscrito


    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $page = isset($parametros['page']) ? (int)$parametros['page'] : $paginaDefault;
    $results = isset($parametros['results']) ? (int)$parametros['results'] : $maxResults;
    $by = isset($parametros['by']) ? $parametros['by'] : $byDefault;
    $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;
    $interesse = isset($parametros['interesse']) ? $parametros['interesse'] : $interessesDefault;


    if (Token::validarScopes('admin', Token::getUtilizador())) {
        $idUtilizador = (int)$request->getAttribute('id'); // ir buscar id
        if (is_int($idUtilizador) && $idUtilizador > 0 && ($interesse == "true" || $interesse == "false")) {

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

            if ($interesse == "false") {
                if ($order == $orderDefault) {
                    $sql = "SELECT  DISTINCT interesses.eventos_id_eventos,eventos.nome_evento,eventos.descricao_short,eventos.descricao,eventos.utilizadores_id_utilizadores AS criador,eventos.facebook_id,eventos.data_evento, tipo_evento.nome_tipo_evento,localizacao.nome,localizacao.lng,localizacao.lat,icons.classe AS tipo_classe 
FROM `interesses`
INNER  JOIN eventos ON eventos.id_eventos = interesses.eventos_id_eventos 
LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento 
LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao 
LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id 
LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos
WHERE interesses.utilizadores_id_utilizadores <> :id AND interesses.eventos_id_eventos NOT IN (SELECT `eventos_id_eventos` FROM interesses WHERE interesses.`utilizadores_id_utilizadores` = :id ) ORDER BY $passar  LIMIT :limit , :results";
                } else {
                    $sql = "SELECT  DISTINCT interesses.eventos_id_eventos,eventos.nome_evento,eventos.descricao_short,eventos.descricao,eventos.utilizadores_id_utilizadores AS criador,eventos.facebook_id,eventos.data_evento, tipo_evento.nome_tipo_evento,localizacao.nome,localizacao.lng,localizacao.lat,icons.classe AS tipo_classe 
FROM `interesses`
INNER  JOIN eventos ON eventos.id_eventos = interesses.eventos_id_eventos 
LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento 
LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao 
LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id 
LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos
WHERE interesses.utilizadores_id_utilizadores <> :id AND interesses.eventos_id_eventos NOT IN (SELECT `eventos_id_eventos` FROM interesses WHERE interesses.`utilizadores_id_utilizadores` = :id ) ORDER BY $passar DESC LIMIT :limit , :results";
                }
            } else {
                if ($order == $orderDefault) {
                    $sql = "SELECT  DISTINCT interesses.eventos_id_eventos,eventos.nome_evento,eventos.descricao_short,eventos.descricao,eventos.utilizadores_id_utilizadores AS criador,eventos.facebook_id,eventos.data_evento, tipo_evento.nome_tipo_evento,localizacao.nome,localizacao.lng,localizacao.lat,icons.classe AS tipo_classe 
FROM `interesses`
INNER  JOIN eventos ON eventos.id_eventos = interesses.eventos_id_eventos 
LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento 
LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao 
LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id 
LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos
WHERE interesses.utilizadores_id_utilizadores = :id ORDER BY $passar  LIMIT :limit , :results";
                } else {
                    $sql = "SELECT  DISTINCT interesses.eventos_id_eventos,eventos.nome_evento,eventos.descricao_short,eventos.descricao,eventos.utilizadores_id_utilizadores AS criador,eventos.facebook_id,eventos.data_evento, tipo_evento.nome_tipo_evento,localizacao.nome,localizacao.lng,localizacao.lat,icons.classe AS tipo_classe 
FROM `interesses`
INNER  JOIN eventos ON eventos.id_eventos = interesses.eventos_id_eventos 
LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento 
LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao 
LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id 
LEFT OUTER JOIN participantes ON participantes.eventos_id_eventos = eventos.id_eventos
WHERE interesses.utilizadores_id_utilizadores = :id ORDER BY $passar DESC  LIMIT :limit , :results";
                }
            }


            try {
                $status = 200; // OK
                // iniciar ligação à base de dados
                $db = new Db();
                $db = $db->connect();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':id', $idUtilizador, PDO::PARAM_INT);
                $stmt->bindValue(':limit', (int)$limitNumber, PDO::PARAM_INT);
                $stmt->bindValue(':results', (int)$results, PDO::PARAM_INT);
                $stmt->execute();
                $db = null;
                $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $dados = Helper::filtrarArrMulti($dados);

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
        $status = 401; // Unprocessable Entity
        $errorMsg = [

            "status" => "$status",
            "info" => 'Acesso não autorizado'


        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }

});

///////GET para receber eventos em que um utilizador está inscrito///////////
$app->get('/api/utilizador/{id}/incrito', function (Request $request, Response $response) {
    $byArr = [
        'id' => 'id_eventos',
        'nome' => 'nome_evento',
        'dataRegisto' => 'data_registo_evento',
        'dataEvento' => 'data_evento',
        'dataFim' => 'data_fim',
        'ativo' => 'ativo',
        'tipoEvento' => 'nome_tipo_evento',
        'local' => 'nome'
    ];
    $maxResults = 10; // maximo de resultados por pagina
    $minResults = 1; // minimo de resultados por pagina
    $byDefault = 'id'; // order by predefinido
    $paginaDefault = 1; // pagina predefenida
    $orderDefault = "ASC"; //ordenação predefenida
    $inscricoesDefault = "true"; //se for false retorna os que não está inscrito


    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $page = isset($parametros['page']) ? (int)$parametros['page'] : $paginaDefault;
    $results = isset($parametros['results']) ? (int)$parametros['results'] : $maxResults;
    $by = isset($parametros['by']) ? $parametros['by'] : $byDefault;
    $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;
    $inscrito = isset($parametros['inscrito']) ? $parametros['inscrito'] : $inscricoesDefault;


    if (Token::validarScopes('admin', Token::getUtilizador())) {
        $idUtilizador = (int)$request->getAttribute('id'); // ir buscar id
        if (is_int($idUtilizador) && $idUtilizador > 0 && ($inscrito == "true" || $inscrito == "false")) {

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

            if ($inscrito == "false") {
                if ($order == $orderDefault) {
                    $sql = "SELECT  DISTINCT participantes.eventos_id_eventos,eventos.nome_evento,eventos.descricao_short,eventos.descricao,eventos.utilizadores_id_utilizadores AS criador,eventos.facebook_id,eventos.data_evento, tipo_evento.nome_tipo_evento,localizacao.nome,localizacao.lng,localizacao.lat,icons.classe AS tipo_classe 
FROM `participantes`
INNER  JOIN eventos ON eventos.id_eventos = participantes.eventos_id_eventos 
LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento 
LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao 
LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id 
LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos
WHERE participantes.utilizadores_id_utilizadores <> :id AND participantes.eventos_id_eventos NOT IN (SELECT `eventos_id_eventos` FROM participantes WHERE participantes.`utilizadores_id_utilizadores` = :id ) ORDER BY $passar  LIMIT :limit , :results";
                } else {
                    $sql = "SELECT  DISTINCT participantes.eventos_id_eventos,eventos.nome_evento,eventos.descricao_short,eventos.descricao,eventos.utilizadores_id_utilizadores AS criador,eventos.facebook_id,eventos.data_evento, tipo_evento.nome_tipo_evento,localizacao.nome,localizacao.lng,localizacao.lat,icons.classe AS tipo_classe 
FROM `participantes`
INNER  JOIN eventos ON eventos.id_eventos = participantes.eventos_id_eventos 
LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento 
LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao 
LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id 
LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos
WHERE participantes.utilizadores_id_utilizadores <> :id AND participantes.eventos_id_eventos NOT IN (SELECT `eventos_id_eventos` FROM participantes WHERE participantes.`utilizadores_id_utilizadores` = :id ) ORDER BY $passar DESC LIMIT :limit , :results";
                }
            } else {
                if ($order == $orderDefault) {
                    $sql = "SELECT  DISTINCT participantes.eventos_id_eventos,eventos.nome_evento,eventos.descricao_short,eventos.descricao,eventos.utilizadores_id_utilizadores AS criador,eventos.facebook_id,eventos.data_evento, tipo_evento.nome_tipo_evento,localizacao.nome,localizacao.lng,localizacao.lat,icons.classe AS tipo_classe 
FROM `participantes`
INNER  JOIN eventos ON eventos.id_eventos = participantes.eventos_id_eventos 
LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento 
LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao 
LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id 
LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos
WHERE participantes.utilizadores_id_utilizadores = :id ORDER BY $passar  LIMIT :limit , :results";
                } else {
                    $sql = "SELECT  DISTINCT participantes.eventos_id_eventos,eventos.nome_evento,eventos.descricao_short,eventos.descricao,eventos.utilizadores_id_utilizadores AS criador,eventos.facebook_id,eventos.data_evento, tipo_evento.nome_tipo_evento,localizacao.nome,localizacao.lng,localizacao.lat,icons.classe AS tipo_classe 
FROM `participantes`
INNER  JOIN eventos ON eventos.id_eventos = participantes.eventos_id_eventos 
LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento 
LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao 
LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id 
LEFT OUTER JOIN interesses ON interesses.eventos_id_eventos = eventos.id_eventos
WHERE participantes.utilizadores_id_utilizadores = :id ORDER BY $passar DESC LIMIT :limit , :results";
                }
            }


            try {
                $status = 200; // OK
                // iniciar ligação à base de dados
                $db = new Db();
                $db = $db->connect();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':id', $idUtilizador, PDO::PARAM_INT);
                $stmt->bindValue(':limit', (int)$limitNumber, PDO::PARAM_INT);
                $stmt->bindValue(':results', (int)$results, PDO::PARAM_INT);
                $stmt->execute();
                $db = null;
                $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $dados = Helper::filtrarArrMulti($dados);

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
        $status = 401; // Unprocessable Entity
        $errorMsg = [

            "status" => "$status",
            "info" => 'Acesso não autorizado'


        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }

});