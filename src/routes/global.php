<?php
// importar classes para scope
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


/////////////////// Pesquisa global //////////////////////////////////
//Parametros:
#   *page = pagina de resultados *obrigatorio
#   results = número de resultados por página
#   min: 1, max: 10
//ordem predefinida: eventos / utilizadores / tipos de eventos /localizações
#como alterar?
#definir TODA a ordem como: by=4231  / neste caso a ordem seria localizacoes/utilizadores/tiposeventos/eventos
//default de resultados visiveis: on_eve=true&on_uti=true&on_tip&on_loc=true
#para alterar basta escolher os que nao queremos e colocar false
//default de ordem: ASC podendo ser alterada para DESC com o order=DESC
#msg é a mensagem recebida para procurar
#exemplo: api/global/pesquisa?page=1&results=8&order=DESC&on_eve=false&by=4231
$app->get('/api/global/pesquisa', function (Request $request, Response $response) {
    $maxResults = 10; // maximo de resultados por pagina
    $minResults = 1; // minimo de resultados por pagina
    $byDefault = "1234"; // order by predefinido
    $paginaDefault = 1; // pagina predefenida
    $orderDefault = "ASC"; //ordenação predefenida
    $ONeventosDefault = "true"; //mostrar eventos
    $ONutilzadoresDefault = "true"; //mostrar utilizadores
    $ONtiposDefault = "true"; //mostrar tipos
    $ONlocDefault = "true"; //mostrar localizações
    $msgDefault = "";


    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $page = isset($parametros['page']) ? (int)$parametros['page'] : $paginaDefault;
    $results = isset($parametros['results']) ? (int)$parametros['results'] : $maxResults;
    $by = isset($parametros['by']) ? $parametros['by'] : $byDefault;
    $order = isset($parametros['order']) ? $parametros['order'] : $orderDefault;
    $ONeventos = isset($parametros['on_eve']) ? $parametros['on_eve'] === "false" ? $parametros['on_eve'] : $ONeventosDefault : $ONeventosDefault;
    $ONutilizadores = isset($parametros['on_uti']) ? $parametros['on_uti'] === "false" ? $parametros['on_uti'] : $ONutilzadoresDefault : $ONutilzadoresDefault;
    $ONtipos = isset($parametros['on_tip']) ? $parametros['on_tip'] === "false" ? $parametros['on_tip'] : $ONtiposDefault : $ONtiposDefault;
    $ONlocalizacoes = isset($parametros['on_loc']) ? $parametros['on_loc'] === "false" ? $parametros['on_loc'] : $ONlocDefault : $ONlocDefault;
    $msg = isset($parametros['msg']) ? $parametros['msg'] : $msgDefault;
    $regex = "/^[1-4]+$/";
    $result = preg_match($regex, $by) ? true : false;
    if($msg!==""){
    if ($page > 0 && $results > 0 || strlen($by) != 4 || $result === false || strpos($by, '4') === false || strpos($by, '3') === false || strpos($by, '2') === false || strpos($by, '1') === false || $ONeventos === "false" && $ONutilizadores === "false" && $ONtipos === "false" && $ONlocalizacoes == "false") {
        //definir numero de resultados
        //caso request tenha parametros superiores ao numero máximo permitido então repor com o valor maximo permitido e vice-versa
        $results = $results > $maxResults ? $maxResults : $results; //se o querystring results for maior que o valor maximo definido passa a ser esse valor maximo definido
        $results = $results < $minResults ? $minResults : $results; //se o querystring results for menor que o valor minimo definido passa a ser esse valor minimo definido
        //caso tenha parametros diferentes de "ASC" ou "DESC" então repor com o predefinido
        $order = $order == "ASC" || $order == "DESC" ? $order : $orderDefault;
        //definir ordem
        $orderEvento = $by[0];
        $orderUser = $by[1];
        $orderTipo = $by[2];
        $orderLocalizacao = $by[3];
        $numMSG = (int)$msg;
        // A partir de quando seleciona resultados
        $limitNumber = ($page - 1) * $results;
        $sql = "";
        $sql .= $ONeventos === "true" ? "SELECT id_eventos, nome_evento, 'eventos' AS tabela , '$orderEvento' AS ordem FROM eventos WHERE nome_evento LIKE '%$msg%' OR eventos.id_eventos =$numMSG " : "";
        $sql .= $ONeventos === "true" && $ONutilizadores === "true" ? " UNION " : "";
        $sql .= $ONutilizadores === "true" ? " SELECT id_utilizadores, nome, 'utilizadores' AS tabela , '$orderUser' AS ordem FROM utilizadores WHERE nome LIKE '%$msg%' OR  utilizadores.id_utilizadores=$numMSG" : "";
        $sql .= $ONeventos === "true"|| $ONutilizadores  === "true" && $ONtipos==="true" ? " UNION " : "";
        $sql .= $ONtipos === "true" ? " SELECT  tipo_evento.id_tipo_evento, tipo_evento.nome_tipo_evento,'tipo_evento' AS tabela , '$orderTipo' AS ordem FROM tipo_evento WHERE tipo_evento.nome_tipo_evento LIKE '%$msg%' OR tipo_evento.id_tipo_evento=$numMSG " : "";
        $sql .= $ONeventos === "true" || $ONutilizadores === "true" || $ONtipos && $ONlocalizacoes==="true" ? " UNION " : "";
        $sql .= $ONlocalizacoes === "true" ? " SELECT localizacao.localizacao AS id_localizacao, localizacao.nome, 'localizacao' AS tabela , '$orderLocalizacao' AS ordem FROM localizacao WHERE localizacao.nome LIKE '%$msg%' OR localizacao.localizacao=$numMSG" : "";
        echo $sql;
        if ($order === $orderDefault) {
            $sql .= " ORDER by ordem LIMIT :limit, :results";
            echo $sql;
        } else {
            $sql .= " ORDER by ordem DESC LIMIT :limit, :results";
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

            $dadosLength      = (int) sizeof( $dados );
            $dadosDoArrayLength = (int) sizeof( $dados[0] ); // filtrar os participantes = 0
            if ( $dadosLength === 0 || $dadosDoArrayLength === 1 || $dadosDoArrayLength === 0 ) {
                $dados  = [ "info" => 'não foram encontrados resultados' ];
                $status = 404; // Page not found
            }else{
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
    }else {
        $status = 422; // Unprocessable Entity
        $errorMsg = [
            "error" => [
                "status" => "$status",
                "text" => 'Parametros invalidos, insira um valor no msg'

            ]
        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }
});