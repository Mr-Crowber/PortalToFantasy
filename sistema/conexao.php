<?php
// ===== Conexão com o banco =====
$servidorBanco = getenv('DB_HOST') ?: 'localhost';
$nomeBanco = getenv('DB_NAME') ?: 'FICHA_RPG';
$usuarioBanco = getenv('DB_USER') ?: 'root';
$senhaBanco = getenv('DB_PASS') ?: 'ili50';
$codificacao = 'utf8mb4';

$enderecoBanco = "mysql:host={$servidorBanco};dbname={$nomeBanco};charset={$codificacao}";
$opcoesConexao = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $conexao = new PDO(
        $enderecoBanco,
        $usuarioBanco,
        $senhaBanco,
        $opcoesConexao
    );
} catch (PDOException $erro) {
    error_log(
        'PortalToFantasy - falha na conexão MySQL: '
        . $erro->getMessage()
    );

    http_response_code(500);
    exit(
        'Não foi possível conectar ao MySQL. '
        . 'Confira as credenciais no arquivo conexao.php.'
    );
}
