<?php
// ===== Confere a conta conectada =====
require_once __DIR__ . '/funcoes.php';
require_once __DIR__ . '/conexao.php';

if (esta_logado()) {
    $consulta = $conexao->prepare(
        'SELECT COD_USUARIO, NOME_USUARIO, EMAIL FROM USUARIO WHERE COD_USUARIO = ?'
    );
    $consulta->execute([(int) $_SESSION['usuario_id']]);
    $usuarioAtual = $consulta->fetch();

    if (!$usuarioAtual) {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        header('Location: index.php');
        exit;
    }

    $_SESSION['usuario_nome'] = $usuarioAtual['NOME_USUARIO'];
}
