<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Token as Token;


$app->post('/eventos/interesses', function (Request $request, Response $response) {
    $idEventos = (int)$request->getParam('idevento');
    $idUtilizadores = (int)$request->getParam('idutilizador');

    if (Token::validarScopes('admin')) {
        //verificar se id's são validos
        if (is_int($idEventos) && $idEventos > 0 && is_int($idUtilizadores) && $idUtilizadores) {
            $sql = "SELECT * from interesses WHERE`eventos_id_eventos`=:ideventos AND `utilizadores_id_utilizadores`=:idutilizadores";
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
                if (!$dados) {
                    //post

                    $sql = "INSERT INTO `interesses` (`eventos_id_eventos`, `utilizadores_id_utilizadores`) VALUES (:idEvento, :idUtilizador);";

                    try {
                        $status = 200;
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
                            'info' => "Interesse introduzido com sucesso!"
                        ];

                        return $response
                            ->withJson($responseData,$status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

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
                        "info" => 'Interesse já existe'


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


$app->post('/eventos/{id}/interesse', function (Request $request, Response $response) {
    $idEventos = (int)$request->getAttribute('id'); // ir buscar id do evento
    //verificar se Evento está disponivel

    if (Token::validarScopes('admin', Token::getUtilizador())) {
        $idUtilizador = (int)Token::getUtilizador();

        //verificar se id de eventos é valido
        if (is_int($idEventos) && $idEventos > 0 && is_int($idUtilizador) && $idUtilizador) {
            $sql = "SELECT * FROM eventos WHERE id_eventos = :id ";

            try {
                // Get DB object
                $db = new db();
                //connect
                $db = $db->connect();
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':id', $idEventos);
                $stmt->execute();
                $db = null;
                $dados = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($dados) {
                    //verificar se interesse já não existe

                    $sql = "SELECT * FROM interesses WHERE eventos_id_eventos = :idEvento && utilizadores_id_utilizadores = :idUtilizador";

                    try {
                        // Get DB object
                        $status = 200;
                        $db = new db();
                        //connect
                        $db = $db->connect();
                        $stmt = $db->prepare($sql);
                        $stmt->bindValue(':idEvento', $idEventos, PDO::PARAM_INT);
                        $stmt->bindValue(':idUtilizador', $idUtilizador, PDO::PARAM_INT);
                        $stmt->execute();
                        $db = null;
                        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (!$dados) {

                            $sql = "INSERT INTO `interesses` (`eventos_id_eventos`, `utilizadores_id_utilizadores`) VALUES (:idEvento, :idUtilizador)";
                            try {

                                $status = 200; // OK

                                // iniciar ligação à base de dados
                                $db = new Db();

                                // conectar
                                $db = $db->connect();
                                $stmt = $db->prepare($sql);
                                $stmt->bindValue(':idEvento', $idEventos, PDO::PARAM_INT);
                                $stmt->bindValue(':idUtilizador', $idUtilizador, PDO::PARAM_INT);
                                $stmt->execute();
                                $db = null;


                                $responseData = [
                                    'status' => "$status",
                                    'info' => "Interesse introduzido com sucesso!"
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
                                    "info" => 'Interesse já existe'


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
                    $status = 404; // Unprocessable Entity
                    $errorMsg = [

                            "status" => "$status",
                            "info" => 'Evento não se encontra disponivel'


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