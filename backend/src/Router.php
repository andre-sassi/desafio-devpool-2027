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

    /**
     * Procura a rota que bate com a requisição atual e envia a resposta em JSON.
     * Se nenhuma bater, responde 404.
     */
    public function dispatch(string $method, string $path): void
    {
        // Percorre as rotas cadastradas, uma por vez
        foreach ($this->routes as $rota) {
            // A rota só serve se o método e o caminho forem exatamente iguais
            if ($rota['method'] === $method && $rota['path'] === $path) {
                $dados = $rota['handler'](); // os () executam a função guardada e pegam o que ela retorna
                echo json_encode($dados);    // converte o array PHP em texto JSON e envia como resposta
                return;                      // achou a rota: encerra aqui
            }
        }

        // Se chegou até aqui, nenhuma rota bateu
        http_response_code(404);                              // status HTTP 404 Not Found
        echo json_encode(['error' => 'Rota não encontrada']); // mensagem de erro em JSON
    }
}
