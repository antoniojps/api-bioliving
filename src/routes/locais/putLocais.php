<?php

// importar classes para scope
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;


//////////////// PUT local//////////////////
$app->put('/api/localizacao/update/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $localizacao = $request->getParam('nomeLocal');
    $lat = $request->getParam('lat');
    $lng = $request->getParam('lng');
    echo $localizacao;
    $error = array();
    $minCar = 1;
    $maxCar = 75;
    if (is_null($localizacao) || strlen($localizacao) < $minCar) {
        $error[] = array("error" => "Insira um nome para o tipo de evento com mais que " . $minCar . " caracter. Este campo é obrigatório!");
    } elseif (strlen($localizacao) > $maxCar) {
        $error[] = array("error" => "Nome excedeu limite máximo");
    }

    function validaLatLng($tipo, $valor)
    {
        $resultado = ($tipo == 'latitude')
            ? '/^(\+|-)?(?:90(?:(?:\.0{1,8})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,8})?))$/'
            : '/^(\+|-)?(?:180(?:(?:\.0{1,8})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,8})?))$/';

        if (preg_match($resultado, $valor)) {
            return true;
        } else {
            return false;
        }
    }

    if (validaLatLng('latitude', $lat) === false) {
        $error[] = array("error" => "Latitude inválida");
    }

    if (validaLatLng('longitude', $lng) === false) {
        $error[] = array("error" => "Longitude inválida");
    }
    if (count($error) === 0) {

        //verificar se id já existe
        $sql = "SELECT * FROM `localizacao` WHERE localizacao=:id";

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
                'error' => "id da localização não existe!"
            ];
            return $response
                ->withJson($responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

        } else {
            //verificar se localização já existe
            $sql = "SELECT * FROM `localizacao` WHERE `nome`=:nome AND `lat` LIKE :lat AND `lng` LIKE :lng";

            // Get DB object
            $db = new db();
            //connect
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nome', $localizacao);
            $stmt->bindParam(':lat', $lat);
            $stmt->bindParam(':lng', $lng);
            $stmt->execute();
            $db = null;
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($dados)) {
                $responseData = [
                    'Resposta' => "Localização já existe!"
                ];
                return $response
                    ->withJson($responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);

            } else {

                //buscar db todos os customers
                $sql = "UPDATE `localizacao` SET `lat` = :lat, `lng` = :lng, `nome` = :nome WHERE `localizacao` = :id;";
                try {
                    // Get DB object
                    $db = new db();
                    //connect
                    $db = $db->connect();
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':nome', $localizacao);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->bindParam(':lat', $lat);
                    $stmt->bindParam(':lng', $lng);
                    $stmt->execute();
                    $db = null;
                    $responseData = [
                        'Resposta' => "Localização alterada com sucesso!"
                    ];

                    return $response
                        ->withJson($responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


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