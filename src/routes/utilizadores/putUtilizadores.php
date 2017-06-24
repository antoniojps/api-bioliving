<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Token as Token;


$app->put('/api/utilizadores/ative/{id}', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id');
    $ativo = 1; //valor na bd que equivale a ativo
    if (is_int($id) && $id > 0) {
        //ver se id existe na bd antes de editar
        $sql = "SELECT * FROM utilizadores WHERE id_utilizadores = :id";
        $db = new Db();
        // conectar
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $db = null;
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($dados) >= 1) {
            $sql = "UPDATE utilizadores SET ativo = :ativo WHERE id_utilizadores = $id";

            try {
                // Get DB object
                $db = new db();
                //connect
                $db = $db->connect();
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':ativo', $ativo);
                $stmt->execute();
                $responseData = [
                    'Resposta' => "Utilizador ativado com sucesso!"
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
        } else {
            $status = 422; // Unprocessable Entity
            $errorMsg = [
                "error" => [
                    "status" => "$status",
                    "text" => 'Utilizador não se encontra disponivel'

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
                "text" => 'Parametros inválidos'

            ]
        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }
});