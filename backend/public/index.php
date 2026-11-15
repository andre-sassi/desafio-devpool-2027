<?php

//Porta de entrada da API, este arquivo entrega a requisição para o Router decidir o que fazer.
require __DIR__ . '/../src/Router.php'; // carrega a classe Router

// Toda resposta desta API é JSON
header('Content-Type: application/json; charset=utf-8');

$router = new Router();

// ROTAS: cada add() diz "quando chegar MÉTODO + CAMINHO, rode esta função"

// GET /users → lista os usuários
// TEMPORÁRIO: devolve dados fixos só para testar o Router isolado do banco.
$router->add('GET', '/users', function () {
    return [
        [
            'id'     => 1,
            'name'   => 'Ana Souza',
            'email'  => 'ana@exemplo.com',
            'login'  => 'ana',
            'active' => true,
        ],
        [
            'id'     => 2,
            'name'   => 'André Miguel',
            'email'  => 'andre@gmail.com',
            'login'  => 'andre',
            'active' => false,
        ]
    ];
});


// Descobre o que foi pedido e entrega ao Router

// Método HTTP da requisição atual (GET, POST, PUT, DELETE...)
$metodo = $_SERVER['REQUEST_METHOD'];

// Caminho da URL SEM a query string: "/users?teste=1" vira "/users"
$caminho = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// O Router procura a rota correspondente e envia a resposta
$router->dispatch($metodo, $caminho);
