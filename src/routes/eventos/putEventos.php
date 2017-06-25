<?php
// importar classes para scope
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Token as Token;

$app->put('/api/eventos/{id}', function (Request $request, Response $response) {
    $idCriador = (int)$request->getParam('idCriador');

    if (Token::validarScopes('admin', $idCriador)) {
        $idEvento = (int)$request->getAttribute('id');
        //ir buscar todos os parametros do metodo post do form
        $nomeEvento = $request->getParam('nomeEvento');
        $data = $request->getParam('data');
        $descricao = $request->getParam('descricao');
        $descricaoShort = $request->getParam('descricaoShort');
        $maxParticipantes = $request->getParam('maxParticipantes');
        $minParticipantes = $request->getParam('minParticipantes');
        $iddMinima = $request->getParam('iddMinima');
        $dataFim = $request->getParam('dataFim');
        $idLocal = $request->getParam('local');
        $idTipo = $request->getParam('tipo');
        $fb = $request->getParam('facebook');
        $preco = $request->getParam('preco');
        $descontoSocio = $request->getParam('descontoSocio');

        if (is_int($idEvento) && $idEvento > 0) {

            $error = array();


            //verificações
            $dataMinUnix = 1;
            $dataMaxUnix = 2147403600;
            $maximoParticipantesDefaultBD = 999999;
            $minimoParticipantesDefaultBD = 999999;


            if (!v::stringType()->alnum()->length(1, 65)->validate($nomeEvento)) $error['nomeEvento'] = "Nome do evento inválido.";//obrigatório
            if ($data && !v::intVal()->between($dataMinUnix, $dataMaxUnix)->validate($data)) $error['data'] = "Data do evento inválida.";
            if ($descricao && !v::stringType()->length(1, 6000)->validate($descricao)) $error['descricao'] = "Descrição inválida.";
            if ($descricaoShort && !v::stringType()->length(1, 30)->validate($descricaoShort)) $error['descricaoShort'] = "Descrição pequena inválida";
            if ($maxParticipantes && !v::intVal()->between($minParticipantes, $maximoParticipantesDefaultBD)->validate($maxParticipantes)) $error['maxParticipantes'] = "Máximo de participantes inválido";
            if ($minParticipantes && !v::intVal()->between(1, $maxParticipantes)->validate($maxParticipantes)) $error['minParticipantes'] = "Minimo de participantes inválido";
            if ($iddMinima && !v::intVal()->between(1, 99)->validate($iddMinima)) $error['iddMinima'] = "Idade minima inválida";
            if ($dataFim && !v::intVal()->between($dataMinUnix, $dataMaxUnix)->validate($dataFim)) $error['dataFim'] = "Data do fim do evento inválida.";
            if ($idLocal && !v::intVal()->validate($idLocal)) $error['idLocal'] = "Id do local inválido";
            if ($idTipo && !v::intVal()->validate($idTipo)) $error['idTipo'] = "Id do tipo de evento inválido";
            if ($preco && !v::floatVal()->length(0, 7)->validate($preco)) $error['preco'] = "Preço inválido";
            if ($descontoSocio && !v::intVal()->between(1, 100)->validate($descontoSocio)) $error['descontoSocio'] = "Desconto socio inválido";

            if ($data) $data = gmdate("Y-m-d H:i:s", $data); //
            if ($dataFim) $dataFim = gmdate("Y-m-d H:i:s", $dataFim); //


            if (count($error) === 0) {
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
                if ($idTipo) {
                    $sql = "SELECT * FROM tipo_evento WHERE id_tipo_evento=:id";
                    try {

                        // iniciar ligação à base de dados
                        $db = new Db();
                        // colocar mensagem no formato correto
                        // conectar
                        $db = $db->connect();
                        $stmt = $db->prepare($sql);
                        $stmt->bindValue(':id', $idTipo, PDO::PARAM_INT);
                        $stmt->execute();
                        $db = null;
                        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if (!$dados) $error ['idTipo'] = "Id do Tipo de evento não se encontra disponivel.";
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
                    if (!$data) $data = NULL;
                    if (!$descricao) $descricao = NULL;
                    if (!$descricaoShort) $descricaoShort = NULL;
                    if (!$maxParticipantes) $maxParticipantes = NULL;
                    if (!$minParticipantes) $minParticipantes = NULL;
                    if (!$iddMinima) $iddMinima = NULL;
                    if (!$dataFim) $dataFim = NULL;
                    if (!$idLocal) $idLocal = NULL;
                    if (!$idTipo) $idTipo = NULL;
                    if (!$preco) $preco = NULL;
                    if (!$descontoSocio) $descontoSocio = NULL;

                    $sql = "UPDATE `eventos` SET 
                            `nome_evento` = :nome,
                            `data_evento` = :data, 
                            `preco` = :preco,
                            `desconto_socio` = :desconto,
                            `descricao_short` = :descricaoShort,
                            `descricao` = :descricao,
                            `max_participantes` = :maxPart, 
                            `min_participantes` = :minPart, 
                            `idade_minima` = :iddMin, 
                            `data_fim` = :dataFim,
                            `localizacao_localizacao` = :idLoc, 
                            `tipo_evento_id_tipo_evento` = :idTipo, 
                            `facebook_id` = :idFace 

                                 WHERE `id_eventos` = :idEvento;
                    
      
                    ";

                    try {


                        $status = 200;
                        // Get DB object
                        $db = new db();
                        //connect
                        $db = $db->connect();
                        $stmt = $db->prepare($sql);

                        $stmt->bindParam(':nome', $nomeEvento);
                        $stmt->bindParam(':data', $data);
                        $stmt->bindParam(':preco', $preco);
                        $stmt->bindParam(':desconto', $descontoSocio);
                        $stmt->bindParam(':descricaoShort', $descricaoShort);
                        $stmt->bindParam(':descricao', $descricao);
                        $stmt->bindParam(':maxPart', $maxParticipantes);
                        $stmt->bindParam(':minPart', $minParticipantes);
                        $stmt->bindParam(':iddMin', $iddMinima);
                        $stmt->bindParam(':dataFim', $dataFim);
                        $stmt->bindParam(':idLoc', $idLocal);
                        $stmt->bindParam(':idTipo', $idTipo);
                        $stmt->bindParam(':idFace', $fb);
                        $stmt->bindParam(':idEvento', $idEvento);
                        $stmt->execute();

                        $responseData = [
                            "status" => 200,
                            'info' => "Evento alterado com sucesso!"
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
            $status = 422; // Unprocessable Entity

            $errorMsg = [
                "status" => "$status",
                "info" => "parametros inválidos"
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


//$app->put('/api/eventos/{id}', function (Request $request, Response $response) {
//
//    $idCriador = (int)$request->getParam('idCriador');
//    if (Token::validarScopes('admin', $idCriador)) {
//        $id = (int)$request->getAttribute('id');
//        //ir buscar todos os parametros do metodo post do form
//        $nomeEvento = $request->getParam('nomeEvento');
//        $data = $request->getParam('data');
//        $descricao = $request->getParam('descricao');
//        $descricaoShort = $request->getParam('descricaoShort');
//        $maxParticipantes = $request->getParam('maxParticipantes');
//        $minParticipantes = $request->getParam('minParticipantes');
//        $iddMinima = $request->getParam('iddMinima');
//        $dataFim = $request->getParam('dataFim');
//        $ativo = $request->getParam('ativo');
//        $local = (int)$request->getParam('local');
//        $tipo = (int)$request->getParam('tipo');
//        $fb = (int)$request->getParam('facebook');
//
//        if ($idCriador == 0) $idCriador = false;
//
//        $error = "";
//
//        if (!is_null($local) && $local != "") {
//            if (!v::intVal()->validate($local)) $error .= "Id do local inváldo ";
//
//        } else {
//            $local = null;
//        }
//        if (!is_null($tipo) && $tipo != "") {
//            if (!v::intVal()->validate($tipo)) $error .= "Id do tipo inváldo ";
//        } else {
//            $tipo = null;
//        }
//        if (!is_null($fb) && $fb != "") {
//            if (!v::intVal()->validate($fb)) $error .= "Id do fb inváldo ";
//        } else {
//            $fb = null;
//        }
//
//
//        if (is_int($id) && $id > 0) {
//            //ver se id existe na bd antes de editar
//            $sql = "SELECT * FROM eventos WHERE id_eventos = :id";
//            $db = new Db();
//            // conectar
//            $db = $db->connect();
//            $stmt = $db->prepare($sql);
//            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
//            $stmt->execute();
//            $db = null;
//            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
//            if (count($dados) >= 1) {
//                //verificaçoes necessárias para garantir que nao sao introduzidos valores inválidos
//                ////////////validar nome evento///////////
//                #Parametro obrigatório do evento
//                #
//                # $minCar significa o minimo de carateres para o nome
//                #  $minCar significa o máximo de carateres para o nome(neste caso 64, pois é o máximo que o fb aceita)
//                $minCar = 1;
//                $maxCar = 64;
//                if (is_null($nomeEvento) || strlen($nomeEvento) < $minCar) {
//                    $error .= "Insira um nome para o evento com mais que " . $minCar . " caracter. Este campo é obrigatório! ";
//                } elseif (strlen($nomeEvento) > $maxCar) {
//                    $error .= "Nome excedeu limite máximo";
//                }
//
//                ///////////função para validar datas/////////////
//                #Requisitos:
//                #Formato predefinido(datetime): YYYY-MM-DD HH:MM:SS
//                function validateDate($date, $format = 'Y-m-d H:i:s')
//                {
//                    $d = DateTime::createFromFormat($format, $date);
//
//                    return $d && $d->format($format) == $date;
//                }
//
//                ///////////função para validar descrições///////////
//                #$desc significa a descrição para validar
//                #$max o numero de caracteres máximo para essa descrição
//                #$min o numero de caracteres minimo para essa descrição
//                #$errorName o nome para returnar uma mensagem de erro
//                #$error referencia à variável global erro onde armazenamos erros
//                function validaDesc(&$desc, $max, $min, $errorName, &$error)
//                {
//
//                    if (is_null($desc) || strlen($desc) == 0 || $desc == "") {
//                        $desc = null;
//                    } elseif (strlen($desc) > $max) {
//
//                        $error .= $errorName . " excedeu limite máximo de " . $max . " caracteres. ";
//
//                    } elseif (strlen($desc) < $min) {
//
//                        $error .= $errorName . " tem de ter pelo menos " . $min . " caracteres. ";
//                    }
//                }
//
//                ////////////validar datas introduzidas //////////////
//                if (!is_null($dataFim) && $dataFim != "") {
//                    if (!validateDate($dataFim)) {
//                        $error .= "Data introduzida inválida, insira novamente com o seguinte formato YYYY-MM-DD hh:mm:ss";
//                    }
//                } else {
//                    $dataFim = null;
//                }
//                if (!is_null($data) && $data != "") {
//                    if (!validateDate($data)) {
//                        $error .= "Data introduzida inválida, insira novamente com o seguinte formato YYYY-MM-DD hh:mm:ss";
//                    }
//                } else {
//                    $data = null;
//                }
//
//                ///////////validar descricoes introduzidas /////////////
//                validaDesc($descricao, 6000, 2, "descricao", $error);
//                validaDesc($descricaoShort, 200, 2, "descricao_pequena", $error);
//
//
//                /* ------>validação do maximo de participantes, minimo participantes e idade minima pelos parametros da bd(exemplo int(2))   <-------- */
//                if ($maxParticipantes != "") {
//                    $maxParticipantes = $maxParticipantes > 999999 ? $maxParticipantes = 999999 : $maxParticipantes;
//                } else {
//                    $maxParticipantes = null;
//                }
//
//                if ($minParticipantes != "") {
//                    $minParticipantes = $minParticipantes > 999999 ? $minParticipantes = 999999 : $minParticipantes;
//                } else {
//                    $minParticipantes = null;
//                }
//
//                if ($iddMinima != "") {
//                    $iddMinima = $iddMinima > 99 ? $iddMinima = 99 : $iddMinima;
//                } else {
//                    $iddMinima = null;
//                }
//                /* ------/>validação do maximo de participantes, minimo participantes e idade minima pelos parametros da bd(exemplo int(2))   </-------- */
//
//                ////////////////validação do ativo////////////////
//                $ativo = $ativo != 1 && $ativo != 0 || $ativo == "" ? $ativo = 1 : $ativo;
//
//
//                if ($error == "") {// não existe erro logo insere na bd
//                    $sql = "UPDATE eventos SET nome_evento = :nomeEvento, data_evento = :data , descricao = :descricao,descricao_short = :descricaoShort,max_participantes = :max,min_participantes = :min,idade_minima = :idd,data_fim = :dataFim,ativo = :ativo,tipo_evento_id_tipo_evento = :tipo, localizacao_localizacao = :local , facebook_id = :fb, utilizadores_id_utilizadores = :idUtilizadores WHERE id_eventos = :id";
//                    try {
//                        // Get DB object
//                        $db = new db();
//                        //connect
//                        $db = $db->connect();
//                        $stmt = $db->prepare($sql);
//                        $stmt->bindParam(':nomeEvento', $nomeEvento);
//                        $stmt->bindParam(':data', $data);
//                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
//                        $stmt->bindParam(':descricao', $descricao);
//                        $stmt->bindParam(':descricaoShort', $descricaoShort);
//                        $stmt->bindParam(':max', $maxParticipantes);
//                        $stmt->bindParam(':min', $minParticipantes);
//                        $stmt->bindParam(':idd', $iddMinima);
//                        $stmt->bindParam(':dataFim', $dataFim);
//                        $stmt->bindParam(':ativo', $ativo);
//                        $stmt->bindParam(':local', $local);
//                        $stmt->bindParam(':tipo', $tipo);
//                        $stmt->bindParam(':idUtilizadores', $idCriador);
//                        $stmt->bindParam(':fb', $fb);
//                        $stmt->execute();
//
//                        $responseData = [
//                            "status" => 200,
//                            'info' => "Evento alterado com sucesso!"
//                        ];
//
//                        return $response
//                            ->withJson($responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
//
//
//                    } catch (PDOException $err) {
//                        $status = 503; // Service unavailable
//                        // Primeiro callback chamado em ambiente de desenvolvimento, segundo em producao
//                        $errorMsg = Errors::filtroReturn(function ($err) {
//                            return [
//
//                                "status" => $err->getCode(),
//                                "info" => $err->getMessage()
//
//                            ];
//                        }, function () {
//                            return [
//                                "status" => 503,
//                                "info" => 'Servico Indisponivel'
//                            ];
//                        }, $err);
//
//                        return $response
//                            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
//
//                    }
//
//
//                } else {
//                    $status = 422; // Unprocessable Entity
//                    $errorMsg = [
//                        "status" => "$status",
//                        "info" => $error
//                    ];
//
//                    return $response
//                        ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
//                }
//
//            } else {
//                $status = 422; // Unprocessable Entity
//                $errorMsg = [
//
//                    "status" => "$status",
//                    "info" => 'Evento já não se encontra disponivel'
//
//
//                ];
//
//                return $response
//                    ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
//            }
//
//        } else {
//            $status = 422; // Unprocessable Entity
//            $errorMsg = [
//
//                "status" => "$status",
//                "info" => 'Parametros inválidos'
//
//
//            ];
//
//            return $response
//                ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
//        }
//
//    } else {
//        $status = 401; // Unprocessable Entity
//        $errorMsg = [
//
//            "status" => "$status",
//            "info" => 'Acesso não autorizado'
//
//
//        ];
//
//        return $response
//            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
//    }
//});

/////////////////PUT para tornar evento ativo////////////////////
$app->put('/api/eventos/ative/{id}', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id');
    $ativo = 1; //valor na bd que equivale a ativo
    if (is_int($id) && $id > 0) {
        //ver se id existe na bd antes de editar
        $sql = "SELECT utilizadores_id_utilizadores,id_eventos FROM eventos WHERE id_eventos = :id";
        $db = new Db();
        // conectar
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $db = null;
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($dados["utilizadores_id_utilizadores"]) {
            $idCriador = (int)$dados["utilizadores_id_utilizadores"];
            if (Token::validarScopes('admin', $idCriador)) {
                $sql = "UPDATE eventos SET ativo = :ativo WHERE id_eventos = $id";

                try {
                    // Get DB object
                    $db = new db();
                    //connect
                    $db = $db->connect();
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':ativo', $ativo);
                    $stmt->execute();
                    $responseData = [
                        'Resposta' => "Evento ativado com sucesso!"
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
                "error" => [
                    "status" => "$status",
                    "text" => 'Evento não se encontra disponivel'

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

////////////////PUT para tornar evento inativo/////////////////
$app->put('/api/eventos/disable/{id}', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id');
    $ativo = 0; //valor na bd que equivale a ativo
    if (is_int($id) && $id > 0) {
        //ver se id existe na bd antes de editar
        $sql = "SELECT utilizadores_id_utilizadores,id_eventos FROM eventos WHERE id_eventos = :id";
        $db = new Db();
        // conectar
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $db = null;
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($dados["utilizadores_id_utilizadores"]) {
            $idCriador = (int)$dados["utilizadores_id_utilizadores"];
            if (Token::validarScopes('admin', $idCriador)) {
                $sql = "UPDATE eventos SET ativo = :ativo WHERE id_eventos = $id";

                try {
                    // Get DB object
                    $db = new db();
                    //connect
                    $db = $db->connect();
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':ativo', $ativo);
                    $stmt->execute();
                    $responseData = [
                        'Resposta' => "Evento desativado com sucesso!"
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
                "error" => [
                    "status" => "$status",
                    "text" => 'Evento não se encontra disponivel'

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
