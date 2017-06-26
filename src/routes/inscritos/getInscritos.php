<?php


// importar classes para scope
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Token as Token;
use Bioliving\Custom\Helper as Helper;


//////////// Obter utilizadores que inscreveram num evento ////////////
# Parametros:
#   *page = pagina de resultados *obrigatorio
#   results = número de resultados por página
#   min: 1, max: 10
# Exemplo: /api/eventos/2/inscritos?page=1&results=2&by=id&order=ASC
$app->get('/eventos/{id}/inscritos', function (Request $request, Response $response) {
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
            $sql = "SELECT id_estatutos,nome_estatuto, `id_utilizadores`,`nome`,`apelido`,`foto`,`sobre_mini`,`telemovel` FROM `utilizadores` INNER JOIN estatutos ON utilizadores.`estatutos_id_estatutos` = estatutos.id_estatutos INNER JOIN participantes ON utilizadores.id_utilizadores = participantes.utilizadores_id_utilizadores WHERE eventos_id_eventos = :id ORDER BY $passar  LIMIT :limit , :results";
        } else {
            $sql = "SELECT id_estatutos,nome_estatuto, `id_utilizadores`,`nome`,`apelido`,`foto`,`sobre_mini`,`telemovel` FROM `utilizadores` INNER JOIN estatutos ON utilizadores.`estatutos_id_estatutos` = estatutos.id_estatutos INNER JOIN participantes ON utilizadores.id_utilizadores = participantes.utilizadores_id_utilizadores WHERE eventos_id_eventos = :id ORDER BY $passar DESC LIMIT :limit , :results";

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
                $nextPageUrl = Helper::nextPageUrl();
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


});

////////////GET de para saber se um utilizador está inscrito////////////////////
$app->get('/eventos/{id}/inscrito', function (Request $request, Response $response) {
    $idEventos = (int)$request->getAttribute('id'); // ir buscar id

    if (Token::validarScopes('admin', Token::getUtilizador())) {
        $idUtilizador = (int)Token::getUtilizador();


        //verificar se é um id válido
        if (is_int($idEventos) && $idEventos > 0 && is_int($idUtilizador) && $idUtilizador) {

            $sql = "SELECT COUNT(`eventos_id_eventos`) AS inscrito FROM `participantes` WHERE`eventos_id_eventos`= :idEventos AND `utilizadores_id_utilizadores`= :idUtilizadores";

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

                if ($dados['inscrito'] === '1') {

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
});