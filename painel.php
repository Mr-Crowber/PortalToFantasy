<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();
$codigoUsuario = (int) $_SESSION['usuario_id'];

$consulta = $conexao->prepare('SELECT COUNT(*) FROM PERSONAGEM WHERE COD_USUARIO_FK = ?');
$consulta->execute([$codigoUsuario]);
$totalPersonagens = (int) $consulta->fetchColumn();
$consulta = $conexao->prepare('SELECT COUNT(*) FROM CRIATURA WHERE COD_USUARIO_FK IS NULL OR COD_USUARIO_FK = ?');
$consulta->execute([$codigoUsuario]);
$totalCriaturas = (int) $consulta->fetchColumn();
$consulta = $conexao->prepare('SELECT COUNT(*) FROM DOCUMENTACAO WHERE COD_USUARIO_FK = ?');
$consulta->execute([$codigoUsuario]);
$totalDocumentos = (int) $consulta->fetchColumn();

$consulta = $conexao->prepare(
    'SELECT COD_PERSONAGEM, NOME_PERSONAGEM, RACA, CLASSE,
            NIVEL, PV, PV_MAXIMO, CA, IMAGEM
     FROM PERSONAGEM
     WHERE COD_USUARIO_FK = ?
     ORDER BY COD_PERSONAGEM DESC
     LIMIT 4'
);
$consulta->execute([$codigoUsuario]);
$personagensRecentes = $consulta->fetchAll();

$tituloPagina = 'Início';
$navegacaoAtiva = 'painel';
require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina titulo-painel">
 <div>
  <span class="rotulo-superior">
   ÁREA PESSOAL
  </span>
  <h1>
   Início
  </h1>
  <p>
   Resumo dos seus registros.
  </p>
 </div>
 <a class="botao botao-principal" href="criar_personagem.php">
  <?= icone('mais') ?> Criar personagem
 </a>
</div>
<section aria-label="Resumo do portal" class="grade-indicadores">
 <a class="cartao-indicador" href="personagens.php">
  <span class="icone-indicador verde">
   <?= icone('pessoas') ?>
  </span>
  <span class="texto-indicador">
   <small>
    PERSONAGENS
   </small>
   <strong>
    <?= $totalPersonagens ?>
   </strong>
   <span>
    Fichas cadastradas
   </span>
  </span>
  <?= icone('seta') ?>
 </a>
 <a class="cartao-indicador" href="bestiario.php">
  <span class="icone-indicador musgo">
   <?= icone('caveira') ?>
  </span>
  <span class="texto-indicador">
   <small>
    BESTIÁRIO
   </small>
   <strong>
    <?= $totalCriaturas ?>
   </strong>
   <span>
    Criaturas disponíveis
   </span>
  </span>
  <?= icone('seta') ?>
 </a>
 <a class="cartao-indicador" href="biblioteca.php">
  <span class="icone-indicador dourado">
   <?= icone('livro') ?>
  </span>
  <span class="texto-indicador">
   <small>
    BIBLIOTECA
   </small>
   <strong>
    <?= $totalDocumentos ?>
   </strong>
   <span>
    Documentos salvos
   </span>
  </span>
  <?= icone('seta') ?>
 </a>
</section>
<section class="bloco-secao">
 <div class="titulo-secao">
  <div>
   <span class="rotulo-superior">
    FICHAS
   </span>
   <h2>
    Personagens recentes
   </h2>
  </div>
  <a class="link-texto" href="personagens.php">
   Ver todos <?= icone('seta') ?>
  </a>
 </div>
 <?php if ($personagensRecentes): ?>
 <div class="grade-personagens grade-compacta">
  <?php foreach ($personagensRecentes as $personagem): ?>
        <?php $percentualVida = (int) $personagem['PV_MAXIMO'] > 0 ? max(0, min(100, ((int) $personagem['PV'] / (int) $personagem['PV_MAXIMO']) * 100)) : 0; ?>
  <a class="cartao-personagem cartao-pergaminho" href="ficha.php?id=<?= (int) $personagem['COD_PERSONAGEM'] ?>">
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
    <span>
     Abrir ficha <?= icone('seta') ?>
    </span>
   </div>
  </a>
  <?php endforeach; ?>
 </div>
 <?php else: ?>
 <div class="estado-vazio painel-pergaminho">
  <div class="imagem-vazia">
   <img alt="Vila medieval cercada por floresta" src="imagens/vila-portal.webp"/>
  </div>
  <h3>
   Nenhum personagem cadastrado
  </h3>
  <p>
   Crie sua primeira ficha para começar.
  </p>
  <a class="botao botao-principal" href="criar_personagem.php">
   Criar personagem
  </a>
 </div>
 <?php endif; ?>
</section>
<section class="bloco-secao">
 <div class="titulo-secao">
  <div>
   <span class="rotulo-superior">
    CONSULTA
   </span>
   <h2>
    Outras áreas
   </h2>
  </div>
 </div>
 <div class="grade-atalhos">
  <a class="cartao-atalho imagem-cartao-atalho" href="bestiario.php">
   <img alt="Ilustração de goblins" loading="lazy" src="https://upload.wikimedia.org/wikipedia/commons/e/eb/Goblins.png"/>
   <div>
    <strong>
     Bestiário
    </strong>
    <small>
     Consultar criaturas e estatísticas.
    </small>
   </div>
   <?= icone('seta') ?>
  </a>
  <a class="cartao-atalho imagem-cartao-atalho" href="biblioteca.php">
   <img alt="Textura de pergaminho envelhecido" loading="lazy" src="imagens/textura-pergaminho.webp"/>
   <div>
    <strong>
     Biblioteca
    </strong>
    <small>
     Consultar seus documentos e anotações.
    </small>
   </div>
   <?= icone('seta') ?>
  </a>
 </div>
</section>
<?php require __DIR__ . '/partes/rodape.php'; ?>
