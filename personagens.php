<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();
$codigoUsuario = (int) $_SESSION['usuario_id'];
$busca = trim($_GET['q'] ?? '');
$sql = 'SELECT COD_PERSONAGEM, NOME_PERSONAGEM, RACA, CLASSE, NIVEL, PV, PV_MAXIMO, CA, IMAGEM FROM PERSONAGEM WHERE COD_USUARIO_FK = ?';
$valores = [$codigoUsuario];
if ($busca !== '') {
    $sql .= ' AND (NOME_PERSONAGEM LIKE ? OR CLASSE LIKE ? OR RACA LIKE ?)';
    $termo = '%' . $busca . '%';
    array_push($valores, $termo, $termo, $termo);
}
$sql .= ' ORDER BY NOME_PERSONAGEM ASC';
$consulta = $conexao->prepare($sql);
$consulta->execute($valores);
$personagens = $consulta->fetchAll();

$tituloPagina = 'Personagens';
$navegacaoAtiva = 'personagens';
require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina">
 <div>
  <span class="rotulo-superior">
   FICHAS CADASTRADAS
  </span>
  <h1>
   Personagens
  </h1>
  <p>
   Abra uma ficha para consultar ou entre na edição para alterar os dados.
  </p>
 </div>
 <a class="botao botao-principal" href="criar_personagem.php">
  <?= icone('mais') ?> Novo personagem
 </a>
</div>
<form class="barra-busca" method="get">
 <div class="caixa-busca">
  <?= icone('buscar') ?>
  <input name="q" placeholder="Buscar por nome, raça ou classe..." type="search" value="<?= escapar_html($busca) ?>"/>
 </div>
 <span class="quantidade-resultados">
  <?= count($personagens) ?> <?= count($personagens) === 1 ? 'personagem' : 'personagens' ?>
 </span>
</form>
<?php if ($personagens): ?>
<div class="grade-personagens">
 <?php foreach ($personagens as $personagem): ?>
    <?php $percentualVida = (int) $personagem['PV_MAXIMO'] > 0 ? max(0, min(100, ((int) $personagem['PV'] / (int) $personagem['PV_MAXIMO']) * 100)) : 0; ?>
 <article class="cartao-personagem cartao-pergaminho">
  <a class="conteudo-cartao-personagem" href="ficha.php?id=<?= (int) $personagem['COD_PERSONAGEM'] ?>">
   <div class="topo-cartao-personagem">
    <?php if (!empty($personagem['IMAGEM'])): ?>
    <img alt="Retrato de <?= escapar_html($personagem['NOME_PERSONAGEM']) ?>" class="retrato-cartao" src="<?= escapar_html($personagem['IMAGEM']) ?>"/>
    <?php else: ?>
    <span class="retrato-inicial-personagem">
     <?= escapar_html(strtoupper(primeiro_caractere($personagem['NOME_PERSONAGEM']))) ?>
    </span>
    <?php endif; ?>
    <span class="selo-nivel">
     NÍVEL <?= (int) $personagem['NIVEL'] ?>
    </span>
   </div>
   <h3>
    <?= escapar_html($personagem['NOME_PERSONAGEM']) ?>
   </h3>
   <p>
    <?= escapar_html($personagem['RACA']) ?>
    <span class="separador-cartao">
     |
    </span>
    <?= escapar_html($personagem['CLASSE']) ?>
   </p>
  </a>
  <div class="vida-resumida">
   <span>
    PV
   </span>
   <div>
    <i style="width:<?= $percentualVida ?>%">
    </i>
   </div>
   <strong>
    <?= (int) $personagem['PV'] ?>/<?= (int) $personagem['PV_MAXIMO'] ?>
   </strong>
  </div>
  <div class="rodape-cartao">
   <span>
    CA <?= (int) $personagem['CA'] ?>
   </span>
   <a href="ficha.php?id=<?= (int) $personagem['COD_PERSONAGEM'] ?>">
    Ver ficha <?= icone('seta') ?>
   </a>
  </div>
 </article>
 <?php endforeach; ?>
</div>
<?php else: ?>
<div class="estado-vazio painel-pergaminho">
 <div class="imagem-vazia">
  <img alt="Vila medieval na floresta" src="imagens/vila-portal.webp"/>
 </div>
 <h3>
  <?= $busca ? 'Nenhum personagem encontrado' : 'Nenhum personagem cadastrado' ?>
 </h3>
 <p>
  <?= $busca ? 'Tente buscar por outro nome, raça ou classe.' : 'Crie sua primeira ficha.' ?>
 </p>
 <?php if (!$busca): ?>
 <a class="botao botao-principal" href="criar_personagem.php">
  Criar personagem
 </a>
 <?php endif; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/partes/rodape.php'; ?>
