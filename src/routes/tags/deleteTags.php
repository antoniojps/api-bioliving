<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;


/////////////Apagar um local////////////////////
$app->delete('/api/tags/delete/{id}', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id'); // ir buscar id
    if (v::intVal()->validate($id) && $id > 0) {
        //ver se id existe na bd antes de editar
        $sql = "SELECT * FROM tags WHERE id_tags = :id";
        $db = new Db();
        // conectar
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $db = null;
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($dados) >= 1) {

            $sql = "DELETE FROM tags WHERE id_tags = :id";

            try {
                // Get DB object
                $db = new db();
                //connect
                $db = $db->connect();
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $db = null;
                $responseData = [
                    'Resposta' => "Tag apagada com sucesso!"
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
                    "text" => 'Tag  não se encontra disponivel'

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
                "text" => 'Atributo inválido'

            ]
        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }
});