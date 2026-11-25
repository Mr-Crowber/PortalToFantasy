<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();
$codigoUsuario = (int) $_SESSION['usuario_id'];
$busca = trim($_GET['q'] ?? '');
$sql = 'SELECT COD_CRIATURA, NOME_CRIATURA, TIPO, TAMANHO, ND, PV, CA, IMAGEM, COD_USUARIO_FK FROM CRIATURA WHERE (COD_USUARIO_FK IS NULL OR COD_USUARIO_FK = ?)';
$valores = [$codigoUsuario];
if ($busca !== '') {
    $sql .= ' AND (NOME_CRIATURA LIKE ? OR TIPO LIKE ?)';
    $termo = '%' . $busca . '%';
    array_push($valores, $termo, $termo);
}
$sql .= ' ORDER BY NOME_CRIATURA';
$consulta = $conexao->prepare($sql);
$consulta->execute($valores);
$criaturas = $consulta->fetchAll();
$tituloPagina = 'Bestiário';
$navegacaoAtiva = 'bestiario';
require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina">
 <div>
  <span class="rotulo-superior">
   COMPÊNDIO
  </span>
  <h1>
   Bestiário
  </h1>
  <p>
   Criaturas de referência e registros adicionados por você.
  </p>
 </div>
 <a class="botao botao-principal" href="criar_criatura.php">
  <?= icone('mais') ?> Nova criatura
 </a>
</div>
<form class="barra-busca" method="get">
 <div class="caixa-busca">
  <?= icone('buscar') ?>
  <input name="q" placeholder="Buscar por nome ou tipo..." type="search" value="<?= escapar_html($busca) ?>"/>
 </div>
 <span class="quantidade-resultados">
  <?= count($criaturas) ?> <?= count($criaturas) === 1 ? 'criatura' : 'criaturas' ?>
 </span>
</form>
<?php if ($criaturas): ?>
<div class="grade-criaturas">
 <?php foreach ($criaturas as $criatura): $imagem = !empty($criatura['IMAGEM']) ? $criatura['IMAGEM'] : imagem_padrao_criatura($criatura['NOME_CRIATURA']); ?>
 <a class="cartao-criatura cartao-pergaminho" href="criatura.php?id=<?= (int) $criatura['COD_CRIATURA'] ?>">
  <div class="arte-cartao-criatura">
   <img alt="Ilustração de <?= escapar_html($criatura['NOME_CRIATURA']) ?>" loading="lazy" src="<?= escapar_html($imagem) ?>"/>
   <span class="tipo-criatura">
    <?= escapar_html($criatura['TIPO']) ?>
   </span>
  </div>
  <div class="corpo-cartao-criatura">
   <div class="linha-nome-criatura">
    <h3>
     <?= escapar_html($criatura['NOME_CRIATURA']) ?>
    </h3>
    <span class="selo-nivel">
     <?= $criatura['COD_USUARIO_FK'] !== null ? 'PESSOAL' : 'BASE EDITÁVEL' ?>
    </span>
   </div>
   <p>
    <?= escapar_html($criatura['TAMANHO']) ?> · ND <?= escapar_html(rtrim(rtrim(number_format((float) $criatura['ND'], 3, '.', ''), '0'), '.')) ?>
   </p>
   <div class="atributos-criatura">
    <span>
     <?= icone('vida') ?> <?= (int) $criatura['PV'] ?> PV
    </span>
    <span>
     <?= icone('escudo') ?> CA <?= (int) $criatura['CA'] ?>
    </span>
   </div>
   <span class="link-cartao-criatura">
    Consultar registro <?= icone('seta') ?>
   </span>
  </div>
 </a>
 <?php endforeach; ?>
</div>
<?php else: ?>
<div class="estado-vazio painel-pergaminho">
 <h3>
  <?= $busca ? 'Nenhuma criatura encontrada' : 'Nenhuma criatura disponível' ?>
 </h3>
 <p>
  <?= $busca ? 'Tente outro nome ou tipo.' : 'Adicione sua primeira criatura ao bestiário.' ?>
 </p>
 <?php if (!$busca): ?>
 <a class="botao botao-principal" href="criar_criatura.php">
  Adicionar criatura
 </a>
 <?php endif; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/partes/rodape.php'; ?>
