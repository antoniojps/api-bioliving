<?php
use \Firebase\JWT\JWT;
use Bioliving\Custom\Token as Token;
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Helper as Helper;

$app->get('/certificados/{token}', function (Request $request, Response $response) {
    if (Token::validarScopes('admin', Token::getUtilizador())) {
        $token = $request->getAttribute('token'); // ir buscar id do evento
        $secret = getenv('SECRET_KEY_CERTIFICADO');

        try {
            $decoded = JWT::decode($token, $secret, array('HS256'));

            //$decoded['iat'] = data inicial do evento
            //$decoded['exp'] = data final para validar evento
            //$decoded["idEvento"] = id do evento

            //fazer selects eventos para saber se id é um id de um evento válido
            //se sim fazer select do id do evento para retornar token
            //validar se tokens são iguais
            //se sim adicionar participacao

            $dataInicial = $decoded->iat;
            $dataLimite = $decoded->exp;
            $idEvento = $decoded->idEvento;
            $tempoAgora = time();

            if ($tempoAgora >= $dataInicial && $tempoAgora <= $dataLimite) {
                $sql = "SELECT token_certificado FROM eventos WHERE `eventos`.`id_eventos` = :id";
                try {

                    // iniciar ligação à base de dados
                    $db = new Db();
                    // colocar mensagem no formato correto
                    // conectar
                    $db = $db->connect();
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(':id', $idEvento, PDO::PARAM_INT);
                    $stmt->execute();
                    $db = null;
                    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
                    $idUtilizador = Token::getUtilizador();
                    if ($dados) {
                        if ($dados['token_certificado'] == $token) {
                            //verificar se já existe
                            $sql = "SELECT * FROM validavalidaparticipantes WHERE utilizadores_id_utilizadores=:idUtilizador AND  eventos_id_eventos=:idEvento";
                            try {

                                // iniciar ligação à base de dados
                                $db = new Db();
                                // colocar mensagem no formato correto
                                // conectar
                                $db = $db->connect();
                                $stmt = $db->prepare($sql);
                                $stmt->bindValue(':idEvento', $idEvento, PDO::PARAM_INT);
                                $stmt->bindValue(':idUtilizador', $idUtilizador, PDO::PARAM_INT);
                                $stmt->execute();
                                $db = null;
                                $dados = $stmt->fetch(PDO::FETCH_ASSOC);
                                if (!$dados) {
                                    echo "$idEvento   $idUtilizador";
                                    $sql = "INSERT INTO `validavalidaparticipantes` (`utilizadores_id_utilizadores`, `eventos_id_eventos`) VALUES (:idUtilizador, :idEvento);";
                                    try {

                                        $status = 200; // OK

                                        // iniciar ligação à base de dados
                                        $db = new Db();

                                        // conectar
                                        $db = $db->connect();
                                        $stmt = $db->prepare($sql);
                                        $stmt->bindValue(':idEvento', $idEvento, PDO::PARAM_INT);
                                        $stmt->bindValue(':idUtilizador', $idUtilizador, PDO::PARAM_INT);
                                        $stmt->execute();
                                        $db = null;


                                        $responseData = [
                                            'status' => "$status",
                                            'info' => "Validação de certificado efetuada com sucesso!"
                                        ];

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
                                                "status" => "503",
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
                                        "info" => 'Validação de certificado
                                já existe'
                                    ];

                                    return $response
                                        ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


                                }
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
                                        "status" => "503",
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


                        $status = 422; // Unprocessable Entity
                        $errorMsg = [

                            "status" => "$status",
                            "info" => 'Certeficado para o evento não se encontra disponivel'
                        ];

                        return $response
                            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


                    }

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
                    "info" => "Certificado não se encontra disponivel"

                ];

                return $response
                    ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


            }

        } catch (\Firebase\JWT\ExpiredException $e) {
            $status = 401; // Unprocessable Entity
            $errorMsg = [

                "status" => "$status",
                "info" => 'Expirou'
            ];

            return $response
                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
        } catch (Exception $e) {
            $status = 401; // Unprocessable Entity
            $errorMsg = [

                "status" => "$status",
                "info" => 'Acesso não autorizado'
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





$app->get('/utilizador/{idUtilizador}/certificado', function (Request $request, Response $response) {
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
    $certificadoDefault = "true"; //se for false retorna os que não está inscrito


    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $page = isset($parametros['page']) ? (int)$parametros['page'] : $paginaDefault;
    $results = isset($parametros['results']) ? (int)$parametros['results'] : $maxResults;
    $by = isset($parametros['by']) ? $parametros['by'] : $byDefault;
    $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;
    $inscrito = isset($parametros['inscrito']) ? $parametros['inscrito'] : $certificadoDefault;
    $idUtilizador = (int)$request->getAttribute('idUtilizador'); // ir buscar id
    if (Token::validarScopes('admin', $idUtilizador)) {
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
                    $sql = "SELECT  DISTINCT validaparticipantes.eventos_id_eventos,eventos.nome_evento,eventos.descricao_short,eventos.descricao,eventos.utilizadores_id_utilizadores AS criador,eventos.facebook_id,eventos.data_evento, tipo_evento.nome_tipo_evento,localizacao.nome,localizacao.lng,localizacao.lat,icons.classe AS tipo_classe 
FROM `validaparticipantes`
INNER  JOIN eventos ON eventos.id_eventos = validaparticipantes.eventos_id_eventos 
LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento 
LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao 
LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id 
WHERE validaparticipantes.utilizadores_id_utilizadores <> :id AND validaparticipantes.eventos_id_eventos NOT IN (SELECT `eventos_id_eventos` FROM validaparticipantes WHERE validaparticipantes.`utilizadores_id_utilizadores` = :id ) ORDER BY $passar  LIMIT :limit , :results";
                } else {
                    $sql = "SELECT  DISTINCT validaparticipantes.eventos_id_eventos,eventos.nome_evento,eventos.descricao_short,eventos.descricao,eventos.utilizadores_id_utilizadores AS criador,eventos.facebook_id,eventos.data_evento, tipo_evento.nome_tipo_evento,localizacao.nome,localizacao.lng,localizacao.lat,icons.classe AS tipo_classe 
FROM `validaparticipantes`
INNER  JOIN eventos ON eventos.id_eventos = validaparticipantes.eventos_id_eventos 
LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento 
LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao 
LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id 
WHERE validaparticipantes.utilizadores_id_utilizadores <> :id AND validaparticipantes.eventos_id_eventos NOT IN (SELECT `eventos_id_eventos` FROM validaparticipantes WHERE validaparticipantes.`utilizadores_id_utilizadores` = :id ) ORDER BY $passar DESC LIMIT :limit , :results";
                }
            } else {
                if ($order == $orderDefault) {
                    $sql = "SELECT  DISTINCT validaparticipantes.eventos_id_eventos,eventos.nome_evento,eventos.descricao_short,eventos.descricao,eventos.utilizadores_id_utilizadores AS criador,eventos.facebook_id,eventos.data_evento, tipo_evento.nome_tipo_evento,localizacao.nome,localizacao.lng,localizacao.lat,icons.classe AS tipo_classe 
FROM `validaparticipantes`
INNER  JOIN eventos ON eventos.id_eventos = validaparticipantes.eventos_id_eventos 
LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento 
LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao 
LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id 

WHERE validaparticipantes.utilizadores_id_utilizadores = :id ORDER BY $passar  LIMIT :limit , :results";
                } else {
                    $sql = "SELECT  DISTINCT validaparticipantes.eventos_id_eventos,eventos.nome_evento,eventos.descricao_short,eventos.descricao,eventos.utilizadores_id_utilizadores AS criador,eventos.facebook_id,eventos.data_evento, tipo_evento.nome_tipo_evento,localizacao.nome,localizacao.lng,localizacao.lat,icons.classe AS tipo_classe 
FROM `validaparticipantes`
INNER  JOIN eventos ON eventos.id_eventos = validaparticipantes.eventos_id_eventos 
LEFT OUTER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento 
LEFT OUTER JOIN localizacao ON localizacao.localizacao = eventos.localizacao_localizacao 
LEFT OUTER JOIN icons ON icons.id_icons = tipo_evento.icons_id 

WHERE valida.utilizadores_id_utilizadores = :id ORDER BY $passar DESC LIMIT :limit , :results";
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


$app->get('/certificados/utilizador/{idUtilizador}/evento/{idEvento}', function (Request $request, Response $response) {
    $idUtilizador = (int)$request->getAttribute('idUtilizador'); // ir buscar id
    $idEventos = (int)$request->getAttribute('idEvento'); // ir buscar id
    if ($idUtilizador > 0 && $idEventos > 0) {
        if (Token::validarScopes('admin', $idUtilizador)) {


            //verificar se é um id válido
            if (is_int($idUtilizador) && $idUtilizador > 0 && is_int($idEventos) && $idEventos) {

                $sql = "SELECT COUNT(`eventos_id_eventos`) AS certificado FROM `validaparticipantes` WHERE`eventos_id_eventos`= :idEventos AND `utilizadores_id_utilizadores`= :idUtilizadores";

                try {

                    $status = 200; // OK

                    // iniciar ligação à base de dados
                    $db = new Db();

                    // conectar
                    $db = $db->connect();
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(':idEventos', $idEventos, PDO::PARAM_INT);
                    $stmt->bindValue(':idUtilizadores', $idUtilizador, PDO::PARAM_INT);
                    $stmt->execute();
                    $db = null;
                    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($dados['certificado'] === '1') {

                        $dados = [
                            "status" => 200,
                            "info" => 'true'
                        ];
                    } else {
                        $dados = [
                            "status" => 200,
                            "info" => 'false'
                        ];
                    }

                    $responseData = $dados;

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

                "status" => $status,
                "info" => "Acesso não autorizado"

            ];
            return $response
                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

        }
    } else {
        $status = 422; // Unprocessable Entity
        $errorMsg = [

            "status" => $status,
            "info" => "Parametros invalidos"

        ];
        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

    }


});

$app->post('/certificados/utilizador/{idUtilizador}/evento/{idEvento}', function (Request $request, Response $response) {
    $idUtilizador = (int)$request->getAttribute('idUtilizador'); // ir buscar id
    $idEventos = (int)$request->getAttribute('idEvento'); // ir buscar id
    if ($idUtilizador > 0 && $idEventos > 0) {
        if (Token::validarScopes('admin', $idUtilizador)) {
            //verificar se id do utilizador existe na bd
            //verificar se id do evento existe na bd
            //fazer o insert
            $sql = "SELECT * FROM eventos WHERE id_eventos=:idEvento";
            try {
                // Get DB object
                $db = new db();
                //connect
                $db = $db->connect();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':idEvento', $idEventos, PDO::PARAM_INT);

                $stmt->execute();
                $db = null;
                $dados = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($dados) {
                    $sql = "SELECT * FROM utilizadores WHERE id_utilizadores=:idUtilizador";
                    try {
                        // Get DB object
                        $db = new db();
                        //connect
                        $db = $db->connect();
                        $stmt = $db->prepare($sql);
                        $stmt->bindValue(':idUtilizador', $idUtilizador, PDO::PARAM_INT);
                        $stmt->execute();
                        $db = null;
                        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($dados) {
                            //insert

                            $sql = "INSERT INTO `validavalidaparticipantes` (`eventos_id_eventos`, `utilizadores_id_utilizadores`) VALUES (:idEventos, :idUtilizadores);";

                            try {
                                $db = new db();
                                //connect
                                $db = $db->connect();
                                $stmt = $db->prepare($sql);
                                $stmt->bindValue(':idUtilizadores', $idUtilizador, PDO::PARAM_INT);
                                $stmt->bindValue(':idEventos', $idEventos, PDO::PARAM_INT);
                                $stmt->execute();
                                $db = null;
                                $responseData = [
                                    "status" => 200,
                                    'info' => "Validação de certificado efetuada com sucesso!"
                                ];

                                return $response
                                    ->withJson($responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


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
                    $status = 404; // Unprocessable Entity
                    $errorMsg = [

                        "status" => $status,
                        "info" => "id do evento já não se encontra disponivel"

                    ];
                    return $response
                        ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

                }


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
            $status = 401; // Unprocessable Entity
            $errorMsg = [

                "status" => $status,
                "info" => "Acesso não autorizado"

            ];
            return $response
                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

        }
    } else {
        $status = 422; // Unprocessable Entity
        $errorMsg = [

            "status" => $status,
            "info" => "Parametros invalidos"

        ];
        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

    }
});

$app->delete('/certificados/utilizador/{idUtilizador}/evento/{idEvento}', function (Request $request, Response $response) {
    $idEventos = (int)$request->getAttribute('idEvento'); // ir buscar id do evento
    $idUtilizadores = (int)$request->getAttribute('idUtilizador'); // ir buscar id do evento

    if (Token::validarScopes('admin')) {
        //verificar se id's são validos
        if (is_int($idEventos) && $idEventos > 0 && is_int($idUtilizadores) && $idUtilizadores) {
            $sql = "SELECT * from validavalidaparticipantes WHERE`eventos_id_eventos`=:ideventos AND `utilizadores_id_utilizadores`=:idutilizadores";
            try {
                // Get DB object
                $db = new db();
                //connect
                $db = $db->connect();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':ideventos', $idEventos, PDO::PARAM_INT);
                $stmt->bindValue(':idutilizadores', $idUtilizadores, PDO::PARAM_INT);
                $stmt->execute();
                $db = null;
                $dados = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($dados) {
                    //delete

                    $sql = "DELETE FROM validavalidaparticipantes WHERE eventos_id_eventos = :idEvento && utilizadores_id_utilizadores=:idUtilizador";

                    try {
                        // Get DB object
                        $db = new db();
                        //connect
                        $db = $db->connect();
                        $stmt = $db->prepare($sql);
                        $stmt->bindValue(':idEvento', $idEventos, PDO::PARAM_INT);
                        $stmt->bindValue(':idUtilizador', $idUtilizadores, PDO::PARAM_INT);
                        $stmt->execute();
                        $db = null;
                        $responseData = [
                            "status" => 200,
                            'info' => "Certificado anulado com sucesso!"
                        ];

                        return $response
                            ->withJson($responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

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
                    $status = 404; // Unprocessable Entity
                    $errorMsg = [
                        "status" => "$status",
                        "info" => 'Interesse já não existe'
                    ];

                    return $response
                        ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
                }

            } catch
            (PDOException $err) {
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

$app->get('/eventos/{id}/certificados', function (Request $request, Response $response) {
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
        if ($order == $orderDefault) {
            $sql = "SELECT id_estatutos,nome_estatuto, `id_utilizadores`,`nome`,`apelido`,`foto`,`sobre_mini`,`telemovel` FROM `utilizadores` INNER JOIN estatutos ON utilizadores.`estatutos_id_estatutos` = estatutos.id_estatutos INNER JOIN validaparticipantes ON utilizadores.id_utilizadores = validaparticipantes.utilizadores_id_utilizadores WHERE eventos_id_eventos = :id ORDER BY $passar  LIMIT :limit , :results";
        } else {
            $sql = "SELECT id_estatutos,nome_estatuto, `id_utilizadores`,`nome`,`apelido`,`foto`,`sobre_mini`,`telemovel` FROM `utilizadores` INNER JOIN estatutos ON utilizadores.`estatutos_id_estatutos` = estatutos.id_estatutos INNER JOIN validaparticipantes ON utilizadores.id_utilizadores = validaparticipantes.utilizadores_id_utilizadores WHERE eventos_id_eventos = :id ORDER BY $passar DESC LIMIT :limit , :results";

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
                    "info" => "Servico Indisponive"
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