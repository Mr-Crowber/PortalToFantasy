<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();
$codigoDocumento = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$consulta = $conexao->prepare('SELECT COD_DOCUMENTACAO, TITULO FROM DOCUMENTACAO WHERE COD_DOCUMENTACAO = ? AND COD_USUARIO_FK = ?');
$consulta->execute([$codigoDocumento, (int) $_SESSION['usuario_id']]);
$documento = $consulta->fetch();
if (!$documento) { definir_aviso('Documento não encontrado.', 'erro'); redirecionar('biblioteca.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $excluir = $conexao->prepare('DELETE FROM DOCUMENTACAO WHERE COD_DOCUMENTACAO = ? AND COD_USUARIO_FK = ?');
    $excluir->execute([$codigoDocumento, (int) $_SESSION['usuario_id']]);
    definir_aviso('Documento excluído.', 'sucesso');
    redirecionar('biblioteca.php');
}
$tituloPagina = 'Excluir documento';
$navegacaoAtiva = 'biblioteca';
require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina">
 <div>
  <a class="link-voltar" href="documento.php?id=<?= $codigoDocumento ?>">
   <?= icone('voltar') ?> Voltar ao documento
  </a>
  <span class="rotulo-superior">
   EXCLUIR DOCUMENTO
  </span>
  <h1>
   Excluir documento
  </h1>
 </div>
</div>
<div class="painel-pergaminho painel-confirmar-exclusao">
 <h2>
  <?= escapar_html($documento['TITULO']) ?>
 </h2>
 <p>
  Deseja excluir este documento da sua biblioteca? Esta ação não poderá ser desfeita.
 </p>
 <form method="post">
  <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
  <a class="botao botao-discreto" href="documento.php?id=<?= $codigoDocumento ?>">
   Cancelar
  </a>
  <button class="botao botao-perigo-suave" type="submit">
   <?= icone('lixeira') ?> Confirmar exclusão
  </button>
 </form>
</div>
<?php require __DIR__ . '/partes/rodape.php'; ?>
