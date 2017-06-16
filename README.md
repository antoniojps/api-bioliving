# Bioliving API

> API para Gestor de eventos para a bioliving desenvolvido no ambito da cadeira de LabMM4 com Slimapp

Este projeto encontra-se em desenvolvimento.

## Frontend
Repositórios para a frontend deste projeto:
* Website - [antoniojps/Bioliving-Frontend](https://github.com/antoniojps/Bioliving-Frontend)
* Mobile App - [rubenclferreira/biolivingMobile](https://github.com/rubenclferreira/biolivingMobile)


## Build Setup

``` bash
# instalar dependencies com o composer
composer install

# Criar ficheiro .env e inserir variaveis
touch .env

```
Para uma explicação mais detalhada de como o composer funciona, consulta [docs do composer](https://getcomposer.org/).

### .env
Este setup requer um ficheiro .env na diretória do mesmo, com as seguintes variáveis de environment definidas:
``` bash
# Substituir as variáveis com os seus valores
SECRET_KEY="supersecretkeyyoushouldntcommittogithub" # Segredo de JWT Token
PHP_ENV="desenvolvimento" # Mostra erros com 'desenvolvimento', omite com 'producao'
DB_HOST="localhost"
DB_NAME="database"
DB_USER="username"
DB_PASS="password"
```

### Criação da base de dados
Execute o ficheiro *sql/deca_16l4_78.sql* na sua base de dados MySQL e insira dados.

---

## Outros:
- [Documentação da Classe Token](docs/Token.md)
