<?php
// importar classes para scope
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


/////////////////// Pesquisa global //////////////////////////////////
//exemplo: /api/global/pesquisa?msg=henr&id=12&order=DESC&results=2&page=1&tipoeventos=false&locais=false&by=nome

//TODO adicionar tags
$app->get('/api/global/pesquisa', function (Request $request, Response $response) {

    //predefinições
    $defaults = array(
        "eventos" => "true",
        "utilizadores" => "true",
        "tiposEventos" => "true",
        "locais" => "true",
        "orderDefault" => "ASC",
        "maxResults" => 10,
        "minResults" => 1,
        "paginaDefault" => 1,
        "msg" => "1234azyo", //nunca acontece
        "id" => 0, //nunca acontece
        "byDefaultEventos" => 'id',
        "byDefaultUtilizadores" => 'id',
        "byDefaultTipoEventos" => 'id',
        "byDefaultLocais" => 'id'
    ,);
    $byArr = array(
        "Eventos" => [
            'id' => 'id_eventos',
            'nome' => 'nome_evento',
            'dataRegisto' => 'data_registo_evento',
            'dataEvento' => 'data_evento',
            'dataFim' => 'data_fim',
            'ativo' => 'ativo',
            'tipoEvento' => 'nome_tipo_evento',
            'local' => 'nome'
        ],
        "Utilizadores" => [
            'id' => 'id_utilizadores',
            'nome' => 'nome',
            'apelido' => 'apelido',
            'genero' => 'genero',
            'dataNascimento' => 'data_nascimento',
            'dataRegisto' => 'data_registo_user',
            'email' => 'email',
            'ativo' => 'ativo',
            'morada' => 'morada'
        ],
        "TipoEventos" => [
            'id' => 'id_tipo_evento',
            'nome' => 'nome_tipo_evento'
        ],
        "Locais" => [
            'id' => 'localizacao',
            'nome' => 'nome'
        ]


    );


    $parametros = $request->getQueryParams(); // obter parametros do querystring
    $page = isset($parametros['page']) ? (int)$parametros['page'] : $defaults['paginaDefault'];
    $results = isset($parametros['results']) ? (int)$parametros['results'] : $defaults['maxResults'];
    $order = isset($parametros['order']) ? $parametros['order'] : $defaults['orderDefault'];
    $msg = isset($parametros['msg']) ? $parametros['msg'] : $defaults['msg'];
    $id = isset($parametros['id']) ? (int)$parametros['id'] : $defaults['id'];
    $eventos = isset($parametros['eventos']) ? $parametros['eventos'] : $defaults['eventos'];
    $utilzadores = isset($parametros['utilizadores']) ? $parametros['utilizadores'] : $defaults['utilizadores'];
    $tipoEventos = isset($parametros['tipoeventos']) ? $parametros['tipoeventos'] : $defaults['tiposEventos'];
    $locais = isset($parametros['locais']) ? $parametros['locais'] : $defaults['locais'];
    $byEventos = isset($parametros['byeventos']) ? $parametros['byEventos'] : $defaults['byDefaultEventos'];
    $byUtilizadores = isset($parametros['byutilizadores']) ? $parametros['byUtilizadores'] : $defaults['byDefaultUtilizadores'];
    $byTipoEventos = isset($parametros['bytipo']) ? $parametros['byTipo'] : $defaults['byDefaultTipoEventos'];
    $byLocais = isset($parametros['bylocais']) ? $parametros['byLocais'] : $defaults['byDefaultLocais'];

    //tornar by validos
    $byEventos = array_key_exists($byEventos, $byArr['Eventos']) ? $byEventos : $defaults['byDefaultEventos'];
    $byUtilizadores = array_key_exists($byUtilizadores, $byArr['Utilizadores']) ? $byUtilizadores : $defaults['byDefaultUtilizadores'];
    $byTipoEventos = array_key_exists($byTipoEventos, $byArr['TipoEventos']) ? $byTipoEventos : $defaults['byDefaultTipoEventos'];
    $byLocais = array_key_exists($byLocais, $byArr['Locais']) ? $byLocais : $defaults['byDefaultLocais'];


    if ($page > 0 && $results > 0 && ($msg != $defaults['msg'] || $id != $defaults['id'])) {
        // definir numero de resultados
        // caso request tenha parametros superiores ao maximo permitido entao repor com maximo permitido e vice-versa
        $results = $results > $defaults['maxResults'] ? $defaults['maxResults'] : $results;
        $results = $results < $defaults['minResults'] ? $defaults['minResults'] : $results;

        // A partir de quando seleciona resultados
        $limitNumber = ($page - 1) * $results;


        $sql = array();
        if ($eventos === "true" || $utilzadores === "true" || $tipoEventos === "true" || $locais === "true") {
            if ($eventos === "true") {
                $passar = $byArr['Eventos'][$byEventos];
                $sql['Eventos'] = $order === $defaults['orderDefault'] ? "SELECT * from eventos where  nome_evento LIKE :msg OR eventos.id_eventos = :id ORDER by $passar LIMIT :limit, :results " : "SELECT * from eventos where  nome_evento LIKE :msg OR eventos.id_eventos = :id ORDER by $passar DESC LIMIT :limit, :results ";
            }
            if ($utilzadores === "true") {
                $passar = $byArr['Utilizadores'][$byUtilizadores];
                $sql['Utilizadores'] = $order === $defaults['orderDefault'] ? "SELECT * from utilizadores where  nome LIKE :msg OR id_utilizadores = :id ORDER by $passar LIMIT :limit, :results " : "SELECT * from utilizadores where  nome LIKE :msg OR id_utilizadores = :id ORDER by $passar DESC LIMIT :limit, :results ";
            }
            if ($tipoEventos === "true") {
                $passar = $byArr['TipoEventos'][$byTipoEventos];
                $sql['TipoEventos'] = $order === $defaults['orderDefault'] ? "SELECT * from tipo_evento where  nome_tipo_evento LIKE :msg OR id_tipo_evento = :id ORDER by $passar LIMIT :limit, :results " : "SELECT * from tipo_evento where  nome_tipo_evento LIKE :msg OR id_tipo_evento = :id ORDER by $passar DESC LIMIT :limit, :results ";
            }
            if ($locais === "true") {
                $passar = $byArr['Locais'][$byLocais];
                $sql['Locais'] = $order === $defaults['orderDefault'] ? "SELECT * from localizacao where  nome LIKE :msg OR localizacao = :id ORDER by $passar LIMIT :limit, :results " : "SELECT * from localizacao where  nome LIKE :msg OR localizacao = :id ORDER by $passar DESC LIMIT :limit, :results ";
            }
        }
        $dadosTotais = array();

        foreach ($sql as $key => $i) {
            try {

                $status = 200; // OK
                // iniciar ligação à base de dados
                $db = new Db();
                $msgEnv = "%$msg%";
                // conectar
                $db = $db->connect();
                $stmt = $db->prepare($i);
                $stmt->bindValue(':limit', (int)$limitNumber, PDO::PARAM_INT);
                $stmt->bindValue(':results', (int)$results, PDO::PARAM_INT);
                $stmt->bindValue(':msg', $msgEnv, PDO::PARAM_INT);
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
                if ($dadosLength === 0) {
                    $dados = ["info" => 'não foram encontrados dados'];
                    $status = 404; // Page not found

                } else if ($dadosLength < $results) {
                    $dadosExtra = ['info' => 'final dos resultados'];
                    array_push($dados, $dadosExtra);
                } else {
                    $nextPageUrl = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
                    $dadosExtra = ['proxPagina' => "$nextPageUrl?page=" . ++$page . "&results=$results" . "&msg=$msg" . "&id=$id"];
                    array_push($dados, $dadosExtra);
                }


                $responseData = [
                    'status' => "$status",
                    "$key" =>
                        $dados

                ];


                $dadosTotais[] = $responseData;

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


        }

        return $response
            ->withJson($dadosTotais, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    } else {
        $status = 422; // Unprocessable Entity
        $errorMsg = [
            "error" => [
                "status" => "$status",
                "text" => 'Parametros invalidos ou acabaram se os resultados'

            ]
        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }


});