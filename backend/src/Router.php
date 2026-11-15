<?php

//Router: decide qual código executar para cada requisição.
class Router
{
    // Lista de rotas cadastradas. Cada item é um array com 'method', 'path' e 'handler'.
    private array $routes = [];

    // Cadastra uma rota. Não executa nada: só guarda na lista para ser usada depois.
    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,   // verbo HTTP: GET, POST, PUT, DELETE...
            'path' => $path,       // caminho da URL: /users
            'handler' => $handler, // função que roda quando a rota for acessada
        ];
    }

    // Procura a rota que bate com a requisição atual e envia a resposta em JSON; se nenhuma bater, responde 404
    public function dispatch(string $method, string $path): void
    {
        // Percorre as rotas cadastradas, uma por vez
        foreach ($this->routes as $rota) {
            // Método diferente: esta rota não serve, pula para a próxima
            if ($rota['method'] !== $method) {
                continue;
            }

            // Compara o molde da rota (/users/{id}) com o caminho pedido (/users/5)
            $params = $this->match($rota['path'], $path);

            // Caminho não bate com o molde: pula para a próxima rota
            if ($params === null) {
                continue;
            }

            // Bateu! Executa o handler entregando os parâmetros (ex.: ['5'] vira handler('5'))
            $dados = $rota['handler'](...$params);
            echo json_encode($dados, JSON_UNESCAPED_UNICODE); // converte o retorno em JSON e envia como resposta
            return;                   // achou a rota: encerra aqui
        }

        // Se chegou até aqui, nenhuma rota bateu
        http_response_code(404);                              // status HTTP 404 Not Found
        echo json_encode(['error' => 'Rota não encontrada'], JSON_UNESCAPED_UNICODE); // mensagem de erro em JSON
    }

    // Compara o molde da rota com o caminho pedido, pedaço por pedaço
    // Se bater, devolve os valores dos {parâmetros}; se não bater, devolve null
    private function match(string $routePath, string $requestPath): ?array
    {
        // Tira as barras das pontas e quebra em pedaços: "/users/5" vira ['users', '5']
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        // Quantidades diferentes de pedaços: não tem como bater
        if (count($routeParts) !== count($requestParts)) {
            return null;
        }

        $params = []; // valores encontrados nos {parâmetros}

        // $i é a posição do pedaço, usada para comparar o mesmo lugar nos dois caminhos
        foreach ($routeParts as $i => $part) {
            if (str_starts_with($part, '{')) {
                $params[] = $requestParts[$i]; // é um {parâmetro}: guarda o valor que veio na URL
            } elseif ($part !== $requestParts[$i]) {
                return null;                   // pedaço fixo diferente (ex.: users ≠ produtos): não bate
            }
        }

        return $params; // bateu: devolve os parâmetros (vazio se a rota não tiver nenhum)
    }
}
