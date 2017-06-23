<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Token as Token;


$app->delete('/api/eventos/{idevento}/inscritos/{idutilizador}', function (Request $request, Response $response) {
    $idEventos = (int)$request->getAttribute('idevento'); // ir buscar id do evento
    $idUtilizadores = (int)$request->getAttribute('idutilizador'); // ir buscar id do evento

    if (Token::validarScopes('admin')) {
        //verificar se id's são validos
        if (is_int($idEventos) && $idEventos > 0 && is_int($idUtilizadores) && $idUtilizadores) {
            $sql = "SELECT * from participantes WHERE`eventos_id_eventos`=:ideventos AND `utilizadores_id_utilizadores`=:idutilizadores";
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

                    $sql = "DELETE FROM participantes WHERE eventos_id_eventos = :idEvento && utilizadores_id_utilizadores=:idUtilizador";

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
                            'info' => "Inscrito anulado com sucesso!"
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
                            "info" => 'Inscrição já não existe'
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
                        "error" => 'Servico Indisponivel'
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


/////////////Apagar um inscrito a partir do id que está logado////////////////////
$app->delete('/api/eventos/{id}/inscrito', function (Request $request, Response $response) {
    $idEventos = (int)$request->getAttribute('id'); // ir buscar id
    if (Token::validarScopes('admin', Token::getUtilizador())) {
        $idUtilizador = (int)Token::getUtilizador();

        //verificar se id é válido
        if (is_int($idEventos) && $idEventos > 0 && is_int($idUtilizador) && $idUtilizador) {
            $sql = "SELECT * FROM participantes WHERE eventos_id_eventos = :idEvento && utilizadores_id_utilizadores = :idUtilizador";

            try {
                // Get DB object
                $db = new db();
                //connect
                $db = $db->connect();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':idEvento', $idEventos, PDO::PARAM_INT);
                $stmt->bindValue(':idUtilizador', $idUtilizador, PDO::PARAM_INT);
                $stmt->execute();
                $db = null;
                $dados = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($dados) {
                    //delete

                    $sql = "DELETE FROM participantes WHERE eventos_id_eventos = :idEvento && utilizadores_id_utilizadores=:idUtilizador";

                    try {
                        // Get DB object
                        $db = new db();
                        //connect
                        $db = $db->connect();
                        $stmt = $db->prepare($sql);
                        $stmt->bindValue(':idEvento', $idEventos, PDO::PARAM_INT);
                        $stmt->bindValue(':idUtilizador', $idUtilizador, PDO::PARAM_INT);
                        $stmt->execute();
                        $db = null;
                        $responseData = [
                            "status" => 200,
                            'info' => "Inscrição anulada com sucesso!"
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
                                "status"=> 503,
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
                        "status"=>503,
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