<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

/////////////////////POST tipo eventos///////////////
$app->put('/api/tiposEventos/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $tipoNome = $request->getParam('nomeTipoEvento');
    $error = "";
    $minCar = 1;
    $maxCar = 75;
    if (is_null($tipoNome) || strlen($tipoNome) < $minCar) {
        $error= "Insira um nome para o tipo de evento com mais que " . $minCar . " caracter. Este campo é obrigatório!";
    } elseif (strlen($tipoNome) > $maxCar) {
        $error =  "Nome excedeu limite máximo";
    }
    if ($error === "") {
        //verificar se tipo já existe
        $sql = "SELECT * FROM tipo_evento WHERE id_tipo_evento = :id";

        try {
            // Get DB object
            $db = new db();
            //connect
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $db = null;
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!count($dados)) {
                $responseData = [
                    "status" => "422",
                    'info' => "id da tipo de eventos já não existe!"
                ];
                return $response
                    ->withJson($responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

            } else {
                //verificar se localização já existe
                $sql = "SELECT * FROM `tipo_evento` WHERE `nome_tipo_evento`=:nome ";

                // Get DB object
                $db = new db();
                //connect
                $db = $db->connect();
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':nome', $tipoNome);
                $stmt->execute();
                $db = null;
                $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($dados)) {
                    $responseData = [
                        "status"=> "422",
                        'Resposta' => "Tipo de evento já existe!"
                    ];
                    return $response
                        ->withJson($responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

                } else {


                    $sql = "UPDATE `tipo_evento` SET `nome_tipo_evento` = :nome WHERE `id_tipo_evento` = :id";
                    try {
                        // Get DB object
                        $db = new db();
                        //connect
                        $db = $db->connect();
                        $stmt = $db->prepare($sql);
                        $stmt->bindParam(':nome', $tipoNome);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                        $db = null;
                        $responseData = [
                            'status' => 200,
                            'Resposta' => "Tipo de evento alterado com sucesso!"
                        ];

                        return $response
                            ->withJson($responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


                    } catch (PDOException $err) {
                        $status = 503; // Service unavailable
                        $errorMsg = [

                                "status" => $err->getCode(),
                                "info" => $err->getMessage()

                        ];

                        return $response
                            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

                    }

                }
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
                "info" => [
                    $error
                ]


        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }

});

