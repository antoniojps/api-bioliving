<?php



// importar classes para scope
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Token as Token;


$app->get('/api/eventos/{id}/inscritos', function (Request $request, Response $response) {
    $idEventos = (int)$request->getAttribute('id'); // ir buscar id

    if (Token::validarScopes('admin',Token::getUtilizador())) {
        $idUtilizador =(int) Token::getUtilizador();


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

                if($dados['inscrito']==='1'){

                    $dados = ["inscrito" => 'true'];
                }else{
                    $dados = ["inscrito" => 'false'];
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
    } else {


    }
});