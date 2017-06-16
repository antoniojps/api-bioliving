# [Token](../src/config/Custom/Token.php)

Verificação e gestão do sistema de validação e atribuição de tokens implementado o conceito de autenticação com access e refresh tokens, em seguida poderá ler todos os métodos existentes com uma pequena descrição do que fazem.

## Namespace import

```php
use Bioliving\Custom\Token as Token;
```

## Utilização básica
Por exemplo para gerar ambos os tokens (refresh e access token) basta:

```php

// Apenas se chamaria este método após a validação do username e password submetidas
// 19 seria o id do utilizador

Token::gerarAcessToken(Token::gerarRefreshToken(19));
```
## Métodos disponiveis:
    - `getAccessToken()`;
    - `getRefreshToken()`;
    - `adeusCookies()`;
    - `autenticar($token)`;
    - `getSegRestantes($token)`;
    - `getUtilizador($token)`;
    - `getScopes($token)`;
    - `verificarRefresh($refreshToken)`;
    - `gerarAccessToken($refreshToken)`;
    - `verificarAtivoScopes($idUtilizador)`;
    - `gerarRefreshToken($idUtilizador)`;
    - `desativarRefreshToken($jti)`;
    - `apagarTokens($refreshToken)`;


#### 1. getAccessToken() e getRefreshToken()
Retorna os tokens nas cookies ou false caso não exista um token com o nome ::$accessCookieName ou ::$refreshCookieName (‘token’ ou ‘token_refresh’ por default)

---

#### 2. adeusCookies()
Apaga as cookies dos tokens.

---

#### 3. autenticar($token)
Autenticação do JWT, se token não for válido retorna false, caso contrário retorna o token.
O parâmetro $token não é obrigatório, caso não esteja definido irá buscar o access token às cookies.

---

#### 4. getSegRestantes($token)
Retorna quanto tempo falta até o token, ou (caso nao seja definido um parametro) o access token nas cookies, expirar em segundos. Caso não seja possível retorna false.

---

#### 5. getUtilizador($token)
Retorna o ID do utilizador nas claims de um token passado no parametro ou por default do access token nas cookies, caso nao encontre retorna false.

---

#### 6. getScopes($token)
Retorna a claim scope de um token definido ou por default o access token, caso nao encontre retorna false.

---

#### 7. verificarRefresh($refreshToken)
Verifica se o refresh token passado no parametro ou por default o das cookies existe na base de dados de acordo com o utilizador que esta na payload e se estes (utilizador e token) estão ativos. Retorna true se o token e o utilizador estão ativos e false se não.

---

#### 8. gerarAccessToken($refreshToken)
Gerar e guardar access token nas cookies através do refresh token, ou seja apenas cria um novo access token se o refresh token for válido, replica os valores da payload do refresh token excepto “exp” ou seja tempo até expirar que neste será de apenas 1 hora por default, pode ser alterado através da propriedade ::$tempoAccessToken.

É de salientar que a verificação do refresh as cookies ‘token’ e ‘token_refresh’ serão apagadas.

---

#### 9. verificarAtivoScopes($idUtilizador)
Método utilizado sobretudo para auxiliar a criação de refresh tokens. Através do id to utilizador (parâmetro obrigatório) verifica se o mesmo se encontra ativo e o seu estatuto, caso o id seja válido, o utilizador está ativo e o estatuto é válido irá retornar uma array com as claims “idVoluntario” e “scope”:
```php
Array
(
    [idUtilizador] => 19
    [scope] => Array
        (
            [0] => publico
            [1] => normal
            [2] => socio
            [3] => colaborador
            [4] => admin
        )

)
```
Caso contrário retorna false.

---

### 10. gerarRefreshToken($idUtilizador)
Gerar novo refresh token através de ID do utilizador. Através do método anterior define as claims necessárias de acordo com o utilizador e codifica um refresh token, este terá um prazo de validade de 4 anos (optamos por este valor pois é o tempo médio de vida de um telemóvel o que permite que idealmente o utilizador só tenha de fazer login uma vez numa hipotética aplicação).

Também cria um número random para ser utilizado como id to token na claim “jti“ (JSON Web Token ID)  e na base de dados. A criação de um id programaticamente e não automaticamente pelo SQL permite-nos atribuir um id ao token sem ter que anteriormente fazer um query à base de dados para saber qual seria o id deste.

Este é guardado posterior numa cookie HTTP Only.

---

### 11. desativarRefreshToken(string || array $jti)
Desativa um ou mais refresh tokes a partir da claim jti (json web token id). Faz a verificação que é um jti válido (alfanumerico, entre 15 e 50 caracteres e sem espaços) e de seguida faz update da coluna “ativo” dos mesmos para 0 ou seja desativo. Aceita uma array the jtis ou uma string.

---

### 12. apagarTokens($refreshToken)
Verifica se existe a  cookie com o nome ‘token_refresh’ caso exista invoca o metodo verificarRefresh para averiguar se o mesmo é valido, se não é valido apenas apaga a(s) cookie(s) ( token e token_refresh ), caso seja válido torna o mesmo inválido (na base de dados) e só de seguida é que apaga a(s) cookie(s) através dos métodos desativarRefresh e adeusCookies respetivamente.






