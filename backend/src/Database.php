<?php

class Database
{
    public static function connect(): PDO
    {
        //lê o .env e transforma em um array
        $env = parse_ini_file(__DIR__ . '/../.env');

        //Monta as informações do banco (host, porta, nome e o formato de caracteres utf8mb4)
        $dsn = "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']};charset=utf8mb4";

        //Faz a ligação com o banco e devolve a conexão aberta; se der errado, lança exceção
        return new PDO(
            $dsn,
            $env['DB_USER'],
            $env['DB_PASS'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // erro vira exceção (não passa em silêncio)
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //cada linha volta como um array com o nome das colunas
                PDO::ATTR_EMULATE_PREPARES   => false, //Impede que dados do PHP sejam encaminhados como comandos do BD exemplo: 'DROP TABLE'
            ]
        );
    }
}
