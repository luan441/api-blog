# API para blog

Essa API é totamente construida em symfony. Para desmontração de conhecimento em REST FULL.

## Pré-requisitos

- PHP >= 8.1
- Composer
- Docker
- Docker compose

## Instalação

Clone do repositório
´´´
git clone https://github.com/luan441/api-blog.git
´´´

Instalação de dependência
´´´
composer install
´´´

Instalação do banco de dados, esse comando instalará o banco de dados e iniciará.
´´´
docker-compose up -d database
´´´

Para apenas iniciar o banco de dado use esse comando
´´´
docker-compose start database
´´´

## Rodar aplicação

Se tiver o symfony-cli use:
´´´
symfony server:start
´´´

Caso contrário use:
´´´
php -S localhost:8000 -t public
´´´
