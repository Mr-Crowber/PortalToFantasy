<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();
$codigoPersonagem = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$consulta = $conexao->prepare('SELECT COD_PERSONAGEM, NOME_PERSONAGEM, IMAGEM FROM PERSONAGEM WHERE COD_PERSONAGEM = ? AND COD_USUARIO_FK = ?');
$consulta->execute([$codigoPersonagem, (int) $_SESSION['usuario_id']]);
$personagem = $consulta->fetch();
if (!$personagem) { definir_aviso('Personagem não encontrado.', 'erro'); redirecionar('personagens.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $excluir = $conexao->prepare('DELETE FROM PERSONAGEM WHERE COD_PERSONAGEM = ? AND COD_USUARIO_FK = ?');
    $excluir->execute([$codigoPersonagem, (int) $_SESSION['usuario_id']]);
    remover_imagem_enviada((string) ($personagem['IMAGEM'] ?? ''), 'personagens');
    definir_aviso('Personagem excluído.', 'sucesso');
    redirecionar('personagens.php');
}
$tituloPagina = 'Excluir personagem';
$navegacaoAtiva = 'personagens';
require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina">
 <div>
  <a class="link-voltar" href="editar_personagem.php?id=<?= $codigoPersonagem ?>">
   <?= icone('voltar') ?> Voltar à edição
  </a>
  <span class="rotulo-superior">
   EXCLUIR FICHA
  </span>
  <h1>
   Excluir personagem
  </h1>
  <p>
   Esta ação não poderá ser desfeita.
  </p>
 </div>
</div>
<div class="painel-pergaminho painel-confirmar-exclusao">
 <h2>
  <?= escapar_html($personagem['NOME_PERSONAGEM']) ?>
 </h2>
 <p>
  Tem certeza de que deseja excluir esta ficha e sua história? Os dados do personagem serão removidos permanentemente.
 </p>
 <form method="post">
  <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
  <a class="botao botao-discreto" href="editar_personagem.php?id=<?= $codigoPersonagem ?>">
   Cancelar
  </a>
  <button class="botao botao-perigo-suave" type="submit">
   <?= icone('lixeira') ?> Confirmar exclusão
  </button>
 </form>
</div>
<?php require __DIR__ . '/partes/rodape.php'; ?>
