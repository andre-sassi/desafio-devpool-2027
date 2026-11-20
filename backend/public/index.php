<?php
// Erros e avisos do PHP não aparecem na resposta (vão só para o terminal do servidor)
ini_set('display_errors', '0');

//Porta de entrada da API, este arquivo entrega a requisição para o Router decidir o que fazer.
require __DIR__ . '/../src/Router.php'; // carrega a classe Router
require __DIR__ . '/../src/UserRepository.php'; //carrega a classe UserRepository
require __DIR__ . '/../src/UserService.php'; //carrega a classe UserService

// Toda resposta desta API é JSON
header('Content-Type: application/json; charset=utf-8');

// CORS: autoriza a tela (Vite, porta 5173) a ler as respostas desta API
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header_remove('X-Powered-By'); //não revela a linguagem e a versão do servidor

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
$router->add('GET', '/users', function () {
    $repository = new UserRepository();
    return $repository->findAll();
});

// GET /users/{id} -> busca um usuário por id
// O Router entrega o valor do {id} no parâmetro $id (ex.: /users/5 → $id = '5')
$router->add('GET', '/users/{id}', function ($id) {
    // Confere se o id não é só dígitos
    if (!ctype_digit($id)) {
        http_response_code(400);
        return ['error' => 'O id deve ser um número inteiro.'];
    }

    $repository = new UserRepository();

    // (int) converte o texto da URL em número ("5" → 5; "abc" → 0)
    $retorno = $repository->findById((int) $id);

    // Usuário não existe: 404 com mensagem em JSON (requisito 5 do PDF)
    if ($retorno === null) {
        http_response_code(404);
        return ['error' => 'Usuário não encontrado'];
    }

    return $retorno; // achou: o Router transforma em JSON com status 200
});

// POST /users -> cadastra um usuário
$router->add('POST', '/users', function () {

    // transforma o texto JSON do corpo da requisição, localizado no php://input, em um array do PHP
    $data = json_decode(file_get_contents('php://input'), true);

    // Corpo vazio ou JSON malformado retorna erro, pois não dá para validar
    if (!is_array($data)) {
        http_response_code(400);
        return ['errors' => ['O corpo da requisição deve ser um JSON válido.']];
    }

    $service = new UserService();
    $result = $service->create($data);

    // Se tem erros na criação, retorna cod 400 + erros
    if (isset($result['errors'])) {
        http_response_code(400);
        return ['errors' => $result['errors']];
    }
    // Senão, retorna sucesso e informa o usuário.
    http_response_code(201);
    return $result['user'];
});

// PUT /users/{id} -> edita um usuário
$router->add('PUT', '/users/{id}', function ($id) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!is_array($data)) {
        http_response_code(400);
        return ['errors' => ['O corpo da requisição deve ser um JSON válido.']];
    }

    $service = new UserService();
    $result = $service->update((int) $id, $data);

    if (isset($result['notFound'])) {
        http_response_code(404);
        return ['error' => 'Usuário não encontrado'];
    }

    if (isset($result['errors'])) {
        http_response_code(400);
        return ['errors' => $result['errors']];
    }

    http_response_code(200);
    return $result['user'];
});

// DELETE /users/{id} -> exclusão física; o "desativar" já é feito pelo campo active
$router->add('DELETE', '/users/{id}', function ($id) {
    $repository = new UserRepository();

    $retorno = $repository->delete((int) $id);

    if ($retorno === false) {
        http_response_code(404);
        return ['error' => 'Usuário não encontrado'];
    }

    http_response_code(200);
    return ['message' => 'Usuário excluído com sucesso.'];
});

// Descobre o que foi pedido e entrega ao Router

// Método HTTP da requisição atual (GET, POST, PUT, DELETE...)
$metodo = $_SERVER['REQUEST_METHOD'];

// Caminho da URL SEM a query string: "/users?teste=1" vira "/users"
$caminho = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// O Router procura a rota e responde. Se algo inesperado quebrar (ex.: banco fora do ar),
// devolvemos um 500 genérico em JSON, sem expor caminhos, SQL ou detalhes internos
try {
    $router->dispatch($metodo, $caminho);
} catch (Throwable $e) {
    error_log($e->getMessage()); // o detalhe fica registrado no terminal do servidor, para quem desenvolve
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno no servidor.'], JSON_UNESCAPED_UNICODE);
}
