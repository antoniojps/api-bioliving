<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Bioliving\Custom\Token as Token;
use Bioliving\Custom\Helper as Helper;



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

            if($inscrito=="true"){
                $operador = "=";
            }else{
                $operador = "<>";
            }

            if ($order == $orderDefault) {
                $sql = "SELECT eventos.id_eventos,eventos.nome_evento,eventos.data_registo_evento,eventos.data_evento,eventos.preco,eventos.desconto_socio,eventos.descricao_short,eventos.descricao,eventos.max_participantes,eventos.min_participantes,eventos.idade_minima,eventos.data_fim,eventos.ativo,eventos.utilizadores_id_utilizadores AS criador_evento,
participantes.utilizadores_id_utilizadores,localizacao.nome,tipo_evento.nome_tipo_evento, icons.classe AS tipo_classe FROM participantes INNER JOIN eventos ON eventos.id_eventos = participantes.eventos_id_eventos  INNER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao INNER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento INNER JOIN icons ON icons.id_icons = tipo_evento.icons_id WHERE participantes.`utilizadores_id_utilizadores` $operador :id ORDER BY $passar  LIMIT :limit , :results";
            } else {
                $sql = "SELECT eventos.id_eventos,eventos.nome_evento,eventos.data_registo_evento,eventos.data_evento,eventos.preco,eventos.desconto_socio,eventos.descricao_short,eventos.descricao,eventos.max_participantes,eventos.min_participantes,eventos.idade_minima,eventos.data_fim,eventos.ativo,eventos.utilizadores_id_utilizadores AS criador_evento,
participantes.utilizadores_id_utilizadores,localizacao.nome,tipo_evento.nome_tipo_evento, icons.classe AS tipo_classe FROM participantes INNER JOIN eventos ON eventos.id_eventos = participantes.eventos_id_eventos  INNER JOIN localizacao ON eventos.localizacao_localizacao = localizacao.localizacao INNER JOIN tipo_evento ON tipo_evento.id_tipo_evento = eventos.tipo_evento_id_tipo_evento INNER JOIN icons ON icons.id_icons = tipo_evento.icons_id WHERE participantes.`utilizadores_id_utilizadores` $operador :id ORDER BY $passar DESC LIMIT :limit , :results";
            }

                echo $sql;


            try {
                $status = 200; // OK
                // iniciar ligação à base de dados
                $db = new Db();
                $db = $db->connect();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':id', $idUtilizador, PDO::PARAM_INT);
                $stmt->bindValue(':limit', (int)$limitNumber, PDO::PARAM_INT);
                $stmt->bindValue(':results',(int)$results, PDO::PARAM_INT);
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