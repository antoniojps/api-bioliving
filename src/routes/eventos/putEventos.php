<?php
// importar classes para scope
use Bioliving\Database\Db as Db;
use Bioliving\Errors\Errors as Errors;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

//////////////Alterar um evento///////////////////
//Scopes: admin ou token do request é igual ao {id}(colaborador que criou )
$app->put('/api/eventos/alter/{id}', function (Request $request, Response $response) {
$id = (int)$request->getAttribute('id');
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
//add foto
//  \Cloudinary\Uploader::upload($_FILES["fileToUpload"]["tmp_name"]);

$error = array();
// TODO verificações de fotos
// TODO verificar se existe id_localização na bd, senão inserir com respectivas lat e lng
// TODO verificar se existe id_tipo_evento na bd, senão inserir
if (is_int($id) && $id > 0) {
//ver se id existe na bd antes de editar
$sql = "SELECT * FROM eventos WHERE id_eventos = :id";
$db = new Db();
// conectar
$db = $db->connect();
$stmt = $db->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$db = null;
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($dados) >= 1) {
//verificaçoes necessárias para garantir que nao sao introduzidos valores inválidos
////////////validar nome evento///////////
#Parametro obrigatório do evento
#
# $minCar significa o minimo de carateres para o nome
#  $minCar significa o máximo de carateres para o nome(neste caso 64, pois é o máximo que o fb aceita)
$minCar = 1;
$maxCar = 64;
if (is_null($nomeEvento) || strlen($nomeEvento) < $minCar) {
$error[] = array("nome" => "Insira um nome para o evento com mais que " . $minCar . " caracter. Este campo é obrigatório!");
} elseif (strlen($nomeEvento) > $maxCar) {
$error[] = array("nome" => "Nome excedeu limite máximo");
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

$error[] = array($errorName => $errorName . " excedeu limite máximo de " . $max . " caracteres");

} elseif (strlen($desc) < $min) {

$error[] = array($errorName => $errorName . " tem de ter pelo menos " . $min . " caracteres");
}
}

////////////validar datas introduzidas //////////////
if (!is_null($dataFim) && $dataFim != "") {
if (!validateDate($dataFim)) {
$error[] = array("data_fim" => "Data introduzida inválida, insira novamente com o seguinte formato YYYY-MM-DD hh:mm:ss");
}
} else {
$dataFim = null;
}
if (!is_null($data) && $data != "") {
if (!validateDate($data)) {
$error[] = array("data" => "Data introduzida inválida, insira novamente com o seguinte formato YYYY-MM-DD hh:mm:ss");
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
$sql = "UPDATE eventos SET nome_evento = :nomeEvento, data_evento = :data , descricao = :descricao,descricao_short = :descricaoShort,max_participantes = :max,min_participantes = :min,idade_minima = :idd,data_fim = :dataFim,ativo = :ativo WHERE id_eventos = $id";
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

$responseData = [
'Resposta' => "Evento alterado com sucesso!"
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
"text" => [
$error
]

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
"text" => 'Evento já não se encontra disponivel'

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

/////////////////PUT para tornar evento ativo////////////////////
$app->put('/api/eventos/ative/{id}', function (Request $request, Response $response) {
    $id = (int)$request->getAttribute('id');
    $ativo = 1; //valor na bd que equivale a ativo
    if (is_int($id) && $id > 0) {
        //ver se id existe na bd antes de editar
        $sql = "SELECT * FROM eventos WHERE id_eventos = :id";
        $db = new Db();
        // conectar
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $db = null;
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($dados) >= 1) {
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
        $sql = "SELECT * FROM eventos WHERE id_eventos = :id";
        $db = new Db();
        // conectar
        $db = $db->connect();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $db = null;
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($dados) >= 1) {
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