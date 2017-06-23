<?php

// importar classes para scope
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

/////////// Adicionar um novo evento ////////////
#Parametros obrigatórios do evento a criar
#   nome_evento
#   id_evento (auto)
#   data_registo_evento (auto)
#   ativo (default = 1 ) :
#       quanto ativo = 1 quer dizer que o evento está ativo
#       quando ativo = 0 quer dizer que evento já não se encontra ativo
$app->post('/api/eventos', function (Request $request, Response $response) {
    //ir buscar todos os parametros do metodo post do form
    $nomeEvento = $request->getParam('nomeEvento');
    $data = $request->getParam('data');
    $descricao = $request->getParam('descricao');
    $descricaoShort = $request->getParam('descricaoShort');
    $maxParticipantes = $request->getParam('maxParticipantes');
    $minParticipantes = $request->getParam('minParticipantes');
    $iddMinima = $request->getParam('iddMinima');
    $dataFim = $request->getParam('dataFim');
    $ativo = $request->getParam('ativo');


    $error = "";
    // TODO verificações de fotos
    // TODO verificar se existe id_localização na bd, senão inserir com respectivas lat e lng
    // TODO verificar se existe id_tipo_evento na bd, senão inserir

    //verificaçoes necessárias para garantir que nao sao introduzidos valores inválidos
    ////////////validar nome evento///////////
    #Parametro obrigatório do evento
    #
    # $minCar significa o minimo de carateres para o nome
    #  $minCar significa o máximo de carateres para o nome(neste caso 64, pois é o máximo que o fb aceita)
    $minCar = 1;
    $maxCar = 64;
    if (is_null($nomeEvento) || strlen($nomeEvento) < $minCar) {
        $error .= "Insira um nome para o evento com mais que " . $minCar . " caracter. Este campo é obrigatório! ";
    } elseif (strlen($nomeEvento) > $maxCar) {
        $error.="Nome excedeu limite máximo. ";
    }

    ///////////função para validar datas/////////////
    #Requisitos:
    #Formato predefinido(datetime): YYYY-MM-DD HH:MM:SS
    function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }

    ///////////função para validar descrições///////////
    #$desc significa a descrição para validar
    #$max o numero de caracteres máximo para essa descrição
    #$min o numero de caracteres minimo para essa descrição
    #$errorName o nome para returnar uma mensagem de erro
    #$error referencia à variável global erro onde armazenamos erros
    function validaDesc(&$desc, $max, $min, $errorName, &$error)
    {

        if (is_null($desc) || strlen($desc) == 0 || $desc == "") {
            $desc = null;
        } elseif (strlen($desc) > $max) {

            $error.= $errorName . " excedeu limite máximo de " . $max . " caracteres ";

        } elseif (strlen($desc) < $min) {

            $error.=$errorName . " tem de ter pelo menos " . $min . " caracteres ";
        }
    }

    ////////////validar datas introduzidas //////////////

        if (!is_null($dataFim) && $dataFim != "") {
            if (!validateDate($dataFim)) {
                $error.= "Data introduzida inválida, insira novamente com o seguinte formato YYYY-MM-DD hh:mm:ss ";
            }
        } else {
            $dataFim = null;
        }


    if (!is_null($data) && $data != "") {
        if (!validateDate($data)) {
            $error.="Data introduzida inválida, insira novamente com o seguinte formato YYYY-MM-DD hh:mm:ss ";
        }
    } else {
        $data = null;
    }

    ///////////validar descricoes introduzidas /////////////
    validaDesc($descricao, 6000, 2, "descricao", $error);
    validaDesc($descricaoShort, 200, 2, "descricao_pequena", $error);


    /* ------>validação do maximo de participantes, minimo participantes e idade minima pelos parametros da bd(exemplo int(2))   <-------- */
    if ($maxParticipantes != "") {
        $maxParticipantes = $maxParticipantes > 999999 ? $maxParticipantes = 999999 : $maxParticipantes;
    } else {
        $maxParticipantes = null;
    }

    if ($minParticipantes != "") {
        $minParticipantes = $minParticipantes > 999999 ? $minParticipantes = 999999 : $minParticipantes;
    } else {
        $minParticipantes = null;
    }

    if ($iddMinima != "") {
        $iddMinima = $iddMinima > 99 ? $iddMinima = 99 : $iddMinima;
    } else {
        $iddMinima = null;
    }
    /* ------/>validação do maximo de participantes, minimo participantes e idade minima pelos parametros da bd(exemplo int(2))   </-------- */

    ////////////////validação do ativo////////////////
    $ativo = $ativo != 1 && $ativo != 0 || $ativo == "" ? $ativo = 1 : $ativo;


    if (count($error) == 0) {// não existe erro logo insere na bd
        $sql = "INSERT INTO eventos (nome_evento,data_evento,descricao,descricao_short,max_participantes,min_participantes,idade_minima,data_fim,ativo) VALUES
    (:nomeEvento,:data,:descricao,:descricaoShort,:max,:min,:idd,:dataFim,:ativo)";

        try {
            // Get DB object
            $db = new db();
            //connect
            $db = $db->connect();
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':nomeEvento', $nomeEvento);
            $stmt->bindParam(':data', $data);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':descricaoShort', $descricaoShort);
            $stmt->bindParam(':max', $maxParticipantes);
            $stmt->bindParam(':min', $minParticipantes);
            $stmt->bindParam(':idd', $iddMinima);
            $stmt->bindParam(':dataFim', $dataFim);
            $stmt->bindParam(':ativo', $ativo);
            $stmt->execute();
            $db = null;

            $responseData = [
                "status" => 200,
                'info' => "Evento adicionado com sucesso!"
            ];

            return $response
                ->withJson($responseData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);


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
                    "status"=> 503,
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
                "info" =>
                    $error

        ];

        return $response
            ->withJson($errorMsg, $status, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }

});
