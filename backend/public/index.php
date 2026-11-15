<?php

//Porta de entrada da API, este arquivo entrega a requisição para o Router decidir o que fazer.
require __DIR__ . '/../src/Router.php'; // carrega a classe Router
require __DIR__ . '/../src/UserRepository.php'; //carrega a classe UserRepository

// Toda resposta desta API é JSON
header('Content-Type: application/json; charset=utf-8');

// CORS: autoriza a tela (Vite, porta 5173) a ler as respostas desta API
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Pré-verificação (preflight): antes de um POST, PUT ou DELETE, o navegador
// manda um pedido OPTIONS perguntando "posso fazer isso?".
// Respondemos só com as autorizações acima (204 = ok, sem conteúdo) e paramos aqui,
// sem passar pelo Router.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = new Router();

// ROTAS: cada add() diz "quando chegar MÉTODO + CAMINHO, rode esta função"

// GET /users → lista os usuários
// TEMPORÁRIO: devolve dados fixos só para testar o Router isolado do banco.
$router->add('GET', '/users', function () {
    $repository = new UserRepository();
    return $repository->findAll();
});


// Descobre o que foi pedido e entrega ao Router

// Método HTTP da requisição atual (GET, POST, PUT, DELETE...)
$metodo = $_SERVER['REQUEST_METHOD'];

// Caminho da URL SEM a query string: "/users?teste=1" vira "/users"
$caminho = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// O Router procura a rota correspondente e envia a resposta
$router->dispatch($metodo, $caminho);
