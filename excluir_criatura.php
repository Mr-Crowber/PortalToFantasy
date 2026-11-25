<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();
$codigoCriatura = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$consulta = $conexao->prepare(
    'SELECT COD_CRIATURA, NOME_CRIATURA, IMAGEM
     FROM CRIATURA
     WHERE COD_CRIATURA = ? AND COD_USUARIO_FK = ?'
);
$consulta->execute([$codigoCriatura, (int) $_SESSION['usuario_id']]);
$criatura = $consulta->fetch();
if (!$criatura) {
    definir_aviso('Criatura não encontrada.', 'erro');
    redirecionar('bestiario.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $excluir = $conexao->prepare(
        'DELETE FROM CRIATURA
         WHERE COD_CRIATURA = ? AND COD_USUARIO_FK = ?'
    );
    $excluir->execute([$codigoCriatura, (int) $_SESSION['usuario_id']]);
    remover_imagem_enviada((string) ($criatura['IMAGEM'] ?? ''), 'criaturas');
    definir_aviso('Criatura removida do bestiário.', 'sucesso');
    redirecionar('bestiario.php');
}
$tituloPagina = 'Excluir criatura';
$navegacaoAtiva = 'bestiario';
require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina">
 <div>
  <a class="link-voltar" href="editar_criatura.php?id=<?= $codigoCriatura ?>">
   <?= icone('voltar') ?> Voltar à edição
  </a>
  <span class="rotulo-superior">
   EXCLUIR REGISTRO
  </span>
  <h1>
   Excluir criatura
  </h1>
 </div>
</div>
<div class="painel-pergaminho painel-confirmar-exclusao">
 <h2>
  <?= escapar_html($criatura['NOME_CRIATURA']) ?>
 </h2>
 <p>
  Deseja remover esta criatura do seu bestiário? Esta ação não poderá ser desfeita.
 </p>
 <form method="post">
  <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
  <a class="botao botao-discreto" href="editar_criatura.php?id=<?= $codigoCriatura ?>">
   Cancelar
  </a>
  <button class="botao botao-perigo-suave" type="submit">
   <?= icone('lixeira') ?> Confirmar exclusão
  </button>
 </form>
</div>
<?php require __DIR__ . '/partes/rodape.php'; ?>
