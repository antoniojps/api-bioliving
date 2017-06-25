<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Token as Token;


$app->put('/utilizadores/{id}', function (Request $request, Response $response) {
    $idUtilizador = (int)$request->getAttribute('id');
    if (is_int($idUtilizador) && $idUtilizador > 0) {
        if (Token::validarScopes('admin', $idUtilizador)) {

            //buscar todos os parametros
            $nome = $request->getParam('nome');
            $apelido = $request->getParam('apelido');
            $genero = $request->getParam('genero');
            $dataNasc = (int)$request->getParam('dataNascimento');
            $email = $request->getParam('email');
            //$foto = $request->getParam('foto');
            $sobre = $request->getParam('sobre');
            $sobreMini = $request->getParam('sobreMini');
            $telemovel = $request->getParam('telemovel');
            $idLocal = $request->getParam('idLocal');
            $idEstatuto = $request->getParam('idEstatuto');

            $error = array();

            //verificações
            $dataMinUnix = 1;
            $dataMaxUnix = 2147403600;

            if (!v::stringType()->noWhitespace()->alpha()->length(1, 50)->validate($nome)) $error['nome'] = "Nome inválido."; //obrigatório
            if (!v::stringType()->noWhitespace()->alpha()->length(1, 50)->validate($apelido)) $error['apelido'] = "Apelido inválido.";//obrigatório
            if ($genero && !v::intVal()->between(0, 1)->validate($genero)) $error['genero'] = "Genero inválido.";
            if ($dataNasc && !v::intVal()->between($dataMinUnix, $dataMaxUnix)->validate($dataNasc)) $error['dataNasc'] = "Data de nascimento inválida.";
            if (!v::filterVar(FILTER_VALIDATE_EMAIL)->length(1, 180)->validate($email)) $error['email'] = "Email inválido."; //obrigatório
            if ($sobre && !v::stringType()->length(1, 65535)->validate($sobre)) $error['sobre'] = "Sobre inválido.";
            if ($sobreMini && !v::stringType()->length(1, 30)->validate($sobreMini)) $error['sobreMini'] = "Sobre mini inválido";
            if ($telemovel && !v::intVal()->length(9, 9)->validate($telemovel)) $error['telemovel'] = "Numero de telemovel inválido";
            if ($idLocal && !v::intVal()->validate($idLocal)) $error['idLocal'] = "Id do local inválido";
            if ($idEstatuto && !v::intVal()->validate($idEstatuto)) $error['idEstatuto'] = "Id do estatuto inválido";
            if ($dataNasc) $dataNasc = gmdate("Y-m-d", $dataNasc); //


            if (count($error) === 0) {

                $sql = "SELECT * FROM utilizadores WHERE email = :email";
                try {

                    // iniciar ligação à base de dados
                    $db = new Db();
                    // colocar mensagem no formato correto
                    // conectar
                    $db = $db->connect();
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(':email', $email, PDO::PARAM_INT);
                    $stmt->execute();
                    $db = null;
                    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($dados) $error ['email'] = "Email já existe.";
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


                if ($idLocal) {
                    $sql = "SELECT * FROM localizacao WHERE localizacao=:id";
                    try {

                        // iniciar ligação à base de dados
                        $db = new Db();
                        // colocar mensagem no formato correto
                        // conectar
                        $db = $db->connect();
                        $stmt = $db->prepare($sql);
                        $stmt->bindValue(':id', $idLocal, PDO::PARAM_INT);
                        $stmt->execute();
                        $db = null;
                        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if (!$dados) $error ['idLocal'] = "Id do local não se encontra disponivel.";
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


                }
                if ($idEstatuto) {

                    $sql = "SELECT * FROM estatutos WHERE id_estatutos=:id";
                    try {

                        // iniciar ligação à base de dados
                        $db = new Db();
                        // colocar mensagem no formato correto
                        // conectar
                        $db = $db->connect();
                        $stmt = $db->prepare($sql);
                        $stmt->bindValue(':id', $idEstatuto, PDO::PARAM_INT);
                        $stmt->execute();
                        $db = null;
                        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if (!$dados) $error ['idEstatuto'] = "Id do estatuto não se encontra disponivel.";
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
                }
                if (count($error) === 0) {
                    if (!$genero) $genero = NULL;
                    if (!$dataNasc) $dataNasc = NULL;
                    if (!$sobre) $sobre = NULL;
                    if (!$sobreMini) $sobreMini = NULL;
                    if (!$telemovel) $telemovel = NULL;
                    if (!$idLocal) $idLocal = NULL;
                    if (!$idEstatuto) $idEstatuto = 3;


                    $sql = "UPDATE `utilizadores` SET 
                                      `nome` = :nome,
                                      `apelido` = :apelido,
                                      `genero` = :genero, 
                                      `data_nascimento` = :dataNasc, 
                                      `email` = :email,
                                      `sobre` = :sobre, 
                                      `sobre_mini` = :sobreMini, 
                                      `telemovel` = :telemovel, 
                                      `localizacao_id_localizacao` = :idLocal, 
                                      `estatutos_id_estatutos` = :idEstatuto 
                              WHERE `utilizadores`.`id_utilizadores` = :id";
                    try {


                        $status = 200;
                        // Get DB object
                        $db = new db();
                        //connect
                        $db = $db->connect();
                        $stmt = $db->prepare($sql);

                        $stmt->bindParam(':nome', $nome);
                        $stmt->bindParam(':apelido', $apelido);
                        $stmt->bindParam(':genero', $genero);
                        $stmt->bindParam(':dataNasc', $dataNasc);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':sobre', $sobre);
                        $stmt->bindParam(':sobreMini', $sobreMini);
                        $stmt->bindParam(':telemovel', $telemovel);
                        $stmt->bindParam(':idLocal', $idLocal);
                        $stmt->bindParam(':idEstatuto', $idEstatuto);
                        $stmt->bindParam(':id', $idUtilizador);
                        $stmt->execute();

                        $responseData = [
                            "status" => 200,
                            'info' => "Utilizador alterado com sucesso!"
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
                    $status = 422; // Unprocessable Entity
                    $errorMsg = [
                        "status" => "$status",
                        "info" => "parametros inválidos",
                        "data" => $error
                    ];

                    return $response
                        ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
                }

            } else {
                $status = 422; // Unprocessable Entity

                $errorMsg = [
                    "status" => "$status",
                    "info" => "parametros inválidos",
                    "data" => $error
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
    } else {
        $status = 422; // Unprocessable Entity

        $errorMsg = [
            "status" => "$status",
            "info" => "parametros inválidos"
        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }
});


$app->put('/utilizadores/ative/{id}', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id');
    if (Token::validarScopes('admin', $id)) {
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


$app->put('/utilizadores/disable/{id}', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id');
    if (Token::validarScopes('admin', $id)) {
        $ativo = 0; //valor na bd que equivale a não ativo
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
                        'Resposta' => "Utilizador desativado com sucesso!"
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
