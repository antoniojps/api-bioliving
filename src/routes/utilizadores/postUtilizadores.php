<?php
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Bioliving\Custom\Token as Token;


$app->post('/utilizadores', function (Request $request, Response $response) {

    if (Token::validarScopes('admin')) {


        //buscar todos os parametros
        $nome = $request->getParam('nome');
        $apelido = $request->getParam('apelido');
        $genero = $request->getParam('genero');
        $dataNasc = $request->getParam('dataNascimento');
        $password = $request->getParam('password');
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


        if (!v::stringType()->noWhitespace()->alpha()->length(1, 50)->validate($nome)) $error['nome'] = " Nome inválido."; //obrigatório
        if (!v::stringType()->noWhitespace()->alpha()->length(1, 50)->validate($apelido)) $error['apelido'] = " Apelido inválido.";//obrigatório
        if ($genero && !v::intVal()->between(0, 1)->validate($genero)) $error['genero'] = " Genero inválido.";
        if ($dataNasc && !v::intVal()->between($dataMinUnix, $dataMaxUnix)->validate($dataNasc)) $error['dataNasc'] = " Data de nascimento inválida.";
        if (!v::filterVar(FILTER_VALIDATE_EMAIL)->length(1, 180)->validate($email)) $error['email'] = " Email inválido."; //obrigatório
        if ($sobre && !v::stringType()->length(1, 65535)->validate($sobre)) $error['sobre'] = " Sobre inválido.";
        if ($sobreMini && !v::stringType()->length(1, 30)->validate($sobreMini)) $error['sobreMini'] = " Sobre mini inválido";
        if ($telemovel && !v::intVal()->length(9, 9)->validate($telemovel)) $error['telemovel'] = " Numero de telemovel inválido";
        if ($idLocal && !v::intVal()->validate($idLocal)) $error['idLocal'] = " Id do local inválido";
        if ($idEstatuto && !v::intVal()->validate($idEstatuto)) $error['idEstatuto'] = " Id do estatuto inválido";
        if($dataNasc)$dataNasc = gmdate("Y-m-d", $dataNasc);

//        if(!validarPW($password,$nome,$apelido,$email))$error['password'] = " Password inválida";
//
//        function validarPW($pass, $nome, $apelido, $email)
//        {
//            $minCaracteres = 8;
//            $passwordValida = false;
//            if (strlen($pass) >= $minCaracteres && $pass !== strtolower($nome) && $pass !== strtolower($apelido) && $pass !== strtolower($email) && $pass !== $_SERVER['SERVER_NAME'] && validarPwCaracteres($pass)) {
//                if (verificarPwListNegra($pass)) {
//                    $passwordValida = false;
//                } else {
//                    $passwordValida = true;
//                }
//            } else {
//                $passwordValida = false;
//            }
//
//            return (bool)$passwordValida;
//        }
//
//        function validarPwCaracteres($pass)
//        {
//            $maxRepeticoes = 6;
//            $caracteresValidos = false;
//            // Ver quantas vezes sao utilizados caracteres
//            $charCount = array_count_values(str_split($pass));
//            // Encontrar algum caracter que é repetido $maxRepetidos ou mais
//            foreach ($charCount as $charRepetidos) {
//                if ($charRepetidos >= $maxRepeticoes) {
//                    $caracteresValidos = false;
//                    break;
//                } else {
//                    $caracteresValidos = true;
//                }
//            }
//            return $caracteresValidos;
//        }
//
//        function verificarPwListNegra($pass)
//        {
//
//            // Ver se password está na lista negra
//            $naListaNegra = true;
//            try {
//
//                $sql = "SELECT * FROM `passwords_blacklist` WHERE `passwords` = :password";
//                $db = new Db();
//
//                // Conectar
//                $db = $db->connect();
//                $stmt = $db->prepare($sql);
//                $stmt->bindParam(':password', $pass);
//
//                if ($stmt->execute()) {
//                    $db = null;
//                    $resultados = $stmt->fetch(PDO::FETCH_ASSOC)['passwords'];
//                    if (empty($resultados)) {
//                        $naListaNegra = false;
//                    } else {
//                        $naListaNegra = true;
//                    }
//                }
//            } catch (\PDOException $e) {
//                echo $e->getMessage();
//                echo $e->getCode();
//            }
//
//            return (bool)$naListaNegra;
//
//        }


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
                if ($dados) $error ['email'] = " Email já existe.";
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
                    if (!$dados) $error ['idLocal'] = " Id do local não se encontra disponivel.";
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
                    if (!$dados) $error ['idEstatuto'] = " Id do estatuto não se encontra disponivel.";
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


                $sql = "INSERT INTO `utilizadores` 
                        (`nome`, 
                        `apelido`, 
                        `genero`, 
                        `data_nascimento`, 
                        `email`,
                        password,
                        `sobre`, 
                        `sobre_mini`, 
                        `telemovel`, 
                        `localizacao_id_localizacao`,  
                        `estatutos_id_estatutos`) 
                        VALUES 
                        (:nome,
                        :apelido,
                        :genero, 
                        :dataNasc, 
                        :email,
                        :password,
                        :sobre, 
                        :sobreMini,
                        :telemovel, 
                        :idLocal, 
                        :idEstatuto
                        );";
                try {
                    $password = password_hash($password, PASSWORD_DEFAULT);
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
                    $stmt->bindParam(':password', $password);
                    $stmt->bindParam(':sobre', $sobre);
                    $stmt->bindParam(':sobreMini', $sobreMini);
                    $stmt->bindParam(':telemovel', $telemovel);
                    $stmt->bindParam(':idLocal', $idLocal);
                    $stmt->bindParam(':idEstatuto', $idEstatuto);

                    $stmt->execute();

                    $responseData = [
                        "status" => 200,
                        'info' => "Utilizador adicionado com sucesso!"
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

});