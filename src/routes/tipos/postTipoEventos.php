<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

/////////////////////POST tipo eventos///////////////
$app->post('/api/eventos/tipo/add', function (Request $request, Response $response) {
    $tipoNome = $request->getParam('nomeTipoEvento');
    $error = array();
    $minCar = 1;
    $maxCar = 75;
    if (is_null($tipoNome) || strlen($tipoNome) < $minCar) {
        $error[] = array("nome" => "Insira um nome para o tipo de evento com mais que " . $minCar . " caracter. Este campo é obrigatório!");
    } elseif (strlen($tipoNome) > $maxCar) {
        $error[] = array("nome" => "Nome excedeu limite máximo");
    }
    if (count($error) === 0) {

        //buscar db todos os customers
        $sql = "INSERT INTO tipo_evento (nome_tipo_evento) VALUES  (:nome)";
        try {
            // Get DB object
            $db = new db();
            //connect
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nome', $tipoNome);
            $stmt->execute();
            $db = null;
            $responseData = [
                'Resposta' => "Tipo de evento adicionado com sucesso!"
            ];

            return $response
                ->withJson($responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


        } catch (PDOException $err) {
            $status = 503; // Service unavailable
            $errorMsg = [
                "error" => [
                    "status" => $err->getCode(),
                    "text" => $err->getMessage()
                ]
            ];

            return $response
                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

        }
    } else {
        $status = 422; // Unprocessable Entity
        $errorMsg = [
            "error" => [
                "status" => "$status",
                "text" => [
                    $error
                ]

            ]
        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }
});
