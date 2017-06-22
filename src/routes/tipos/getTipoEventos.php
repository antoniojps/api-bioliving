<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;


//////////Receber todos os locais/Pesquisar em todos os locais/////////////
# Variaveis alteráveis:
#   min: 1, max: 10
# Exemplo: /api/eventostipos?page=1&results=2&by=nome&order=DESC&msg=desporto&id=4
$app->get('/api/tiposEventos', function (Request $request, Response $response) {

    $byArr = [
        'id' => 'id_tipo_evento',
        'nome' => 'nome_tipo_evento'

    ]; // Valores para ordernar por, fizemos uma array para simplificar queries

    $maxResults = 10; // maximo de resultados por pagina
    $minResults = 1; // minimo de resultados por pagina
    $byDefault = 'id'; // order by predefinido
    $paginaDefault = 1; // pagina predefenida
    $orderDefault = "ASC"; //ordenação predefenida
    $msgDefault = "";
    $idDefault = 0;


    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $page = isset($parametros['page']) ? (int)$parametros['page'] : $paginaDefault;
    $results = isset($parametros['results']) ? (int)$parametros['results'] : $maxResults;
    $by = isset($parametros['by']) ? $parametros['by'] : $byDefault;
    $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;
    $id = isset($parametros['id']) ? (int)$parametros['id'] : $idDefault;
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

        // A partir de quando seleciona resultados
        $limitNumber = ($page - 1) * $results;
        $passar = $byArr[$by];


        if ($msg != $msgDefault && $id != $idDefault) $extraWhere = " WHERE id_tipo_evento = :id OR nome_tipo_evento LIKE :msg ";
        elseif ($msg != $msgDefault && $id == $idDefault) $extraWhere = " WHERE  nome_tipo_evento LIKE :msg ";
        elseif ($msg === $msgDefault && $id != $idDefault) $extraWhere = " WHERE id_tipo_evento = :id ";
        else$extraWhere = "";


        if ($order == $orderDefault) {
            $sql = "SELECT * FROM `tipo_evento` " . $extraWhere . " ORDER BY $passar  LIMIT :limit , :results";
        } else {
            $sql = "SELECT * FROM `tipo_evento`" . $extraWhere . " ORDER BY $passar DESC LIMIT :limit , :results";

        }

        try {
            $responseData="";
            $status = 200; // OK
            // iniciar ligação à base de dados
            $db = new Db();
            $msgEnv = "%$msg%";
            // conectar
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', (int)$limitNumber, PDO::PARAM_INT);
            $stmt->bindValue(':results', (int)$results, PDO::PARAM_INT);
            if ($id != $idDefault) $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            if ($msg != $msgDefault) $stmt->bindValue(':msg', $msgEnv, PDO::PARAM_INT);
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
                $responseData =[
                    'status' => $status,
                    'info' =>'pagina inexistente'
                ];

            } else if ($dadosLength < $results) {
                $dadosExtra = ['info' => 'final dos resultados'];
                array_push($dados, $dadosExtra);
            } else {
                $nextPageUrl = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
                $dadosExtra = ['proxPagina' => "$nextPageUrl?page=" . ++$page . "&results=$results"];
                array_push($dados, $dadosExtra);
            }
            if($responseData===""){
                $responseData = [
                    'status' => "$status",
                    'data' => $dados
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