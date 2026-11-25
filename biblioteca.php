<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();
$codigoUsuario = (int) $_SESSION['usuario_id'];
$busca = trim($_GET['q'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');
$sql = 'SELECT COD_DOCUMENTACAO, TITULO, CATEGORIA, RESUMO, DATA_ATUALIZACAO
        FROM DOCUMENTACAO
        WHERE COD_USUARIO_FK = ?';

$valores = [$codigoUsuario];

if ($busca !== '') {
    $sql .= ' AND (TITULO LIKE ? OR RESUMO LIKE ? OR CONTEUDO LIKE ?)';
    $termo = '%' . $busca . '%';
    array_push($valores, $termo, $termo, $termo);
}

if ($categoria !== '') {
    $sql .= ' AND CATEGORIA = ?';
    $valores[] = $categoria;
}

$sql .= ' ORDER BY DATA_ATUALIZACAO DESC';
$consulta = $conexao->prepare($sql);
$consulta->execute($valores);
$documentos = $consulta->fetchAll();
$categorias = ['Lore','NPCs','Locais','Regras','Anotações','Outros'];
$tituloPagina = 'Biblioteca';
$navegacaoAtiva = 'biblioteca';
require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina">
 <div>
  <span class="rotulo-superior">
   ARQUIVO PESSOAL
  </span>
  <h1>
   Biblioteca
  </h1>
  <p>
   Textos, registros e anotações guardados na sua conta.
  </p>
 </div>
 <a class="botao botao-principal" href="editar_documento.php">
  <?= icone('mais') ?> Novo documento
 </a>
</div>
<form class="barra-busca filtro-biblioteca" method="get">
 <div class="caixa-busca">
  <?= icone('buscar') ?>
  <input name="q" placeholder="Buscar nos seus documentos..." type="search" value="<?= escapar_html($busca) ?>"/>
 </div>
 <select aria-label="Filtrar por categoria" name="categoria">
  <option value="">
   Todas as categorias
  </option>
  <?php foreach ($categorias as $item): ?>
  <option value="<?= escapar_html($item) ?>" <?= $categoria === $item ? 'selected' : '' ?>>
   <?= escapar_html($item) ?>
  </option>
  <?php endforeach; ?>
 </select>
 <button class="botao botao-discreto" type="submit">
  Filtrar
 </button>
</form>
<?php if ($documentos): ?>
<div class="grade-documentos">
 <?php foreach ($documentos as $documento): ?>
 <a class="cartao-documento cartao-pergaminho" href="documento.php?id=<?= (int) $documento['COD_DOCUMENTACAO'] ?>">
  <div class="linha-superior-documento">
   <span class="categoria-documento">
    <?= escapar_html($documento['CATEGORIA']) ?>
   </span>
   <span class="indice-cartao-documento">
    <?= icone('pergaminho') ?>
   </span>
  </div>
  <h3>
   <?= escapar_html($documento['TITULO']) ?>
  </h3>
  <p>
   <?= escapar_html($documento['RESUMO'] ?: 'Abra para consultar o conteúdo.') ?>
  </p>
  <div class="rodape-cartao-documento">
   <small>
    Atualizado em <?= date('d/m/Y', strtotime($documento['DATA_ATUALIZACAO'])) ?>
   </small>
   <span>
    Abrir <?= icone('seta') ?>
   </span>
  </div>
 </a>
 <?php endforeach; ?>
</div>
<?php else: ?>
<div class="estado-vazio painel-pergaminho">
 <div class="imagem-vazia">
  <img alt="Papel envelhecido" src="imagens/textura-pergaminho.webp"/>
 </div>
 <h3>
  <?= ($busca || $categoria) ? 'Nenhum documento encontrado' : 'Sua biblioteca está vazia' ?>
 </h3>
 <p>
  <?= ($busca || $categoria) ? 'Altere os filtros ou tente outra busca.' : 'Crie um documento para começar a organizar suas anotações.' ?>
 </p>
 <?php if (!$busca && !$categoria): ?>
 <a class="botao botao-principal" href="editar_documento.php">
  Criar primeiro documento
 </a>
 <?php endif; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/partes/rodape.php'; ?>
