<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Token as Token;

$app->post('/api/eventos/{id}/inscrito', function (Request $request, Response $response) {
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
                        if (!$dados) {

                            $sql = "INSERT INTO `participantes` (`eventos_id_eventos`, `utilizadores_id_utilizadores`) VALUES (:idEvento, :idUtilizador)";
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
                                    'data' => "Inscrição efetuada com sucesso!"
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
                            $status = 404; // Unprocessable Entity
                            $errorMsg = [
                                "error" => [
                                    "status" => "$status",
                                    "text" => 'Interesse já existe'

                                ]
                            ];

                            return $response
                                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
                        }


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
                    $status = 404; // Unprocessable Entity
                    $errorMsg = [
                        "error" => [
                            "status" => "$status",
                            "text" => 'Evento não se encontra disponivel'

                        ]
                    ];

                    return $response
                        ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
                }


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
        $status = 401; // Unprocessable Entity
        $errorMsg = [
            "error" => [
                "status" => "$status",
                "text" => 'Acesso não autorizado'

            ]
        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }


});