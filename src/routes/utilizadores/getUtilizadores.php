<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Token as Token;

$app->get('/api/utilizador/incrito', function (Request $request, Response $response) {
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
        $idUtilizador = (int)Token::getUtilizador();
        if (is_int($idUtilizador) && $idUtilizador > 0 && ($inscrito=="true"|| $inscrito == "false")) {

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


            if ($order == $orderDefault) {
                $sql = "SELECT id_estatutos,nome_estatuto, `id_utilizadores`,`nome`,`apelido`,`foto`,`sobre_mini`,`telemovel` FROM `utilizadores` INNER JOIN estatutos ON utilizadores.`estatutos_id_estatutos` = estatutos.id_estatutos INNER JOIN interesses ON utilizadores.id_utilizadores = interesses.utilizadores_id_utilizadores WHERE eventos_id_eventos = :id ORDER BY $passar  LIMIT :limit , :results";
            } else {
                $sql = "SELECT id_estatutos,nome_estatuto, `id_utilizadores`,`nome`,`apelido`,`foto`,`sobre_mini`,`telemovel` FROM `utilizadores` INNER JOIN estatutos ON utilizadores.`estatutos_id_estatutos` = estatutos.id_estatutos INNER JOIN interesses ON utilizadores.id_utilizadores = interesses.utilizadores_id_utilizadores WHERE eventos_id_eventos = :id ORDER BY $passar DESC LIMIT :limit , :results";

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