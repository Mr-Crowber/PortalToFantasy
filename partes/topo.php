<?php
require_once __DIR__ . '/../sistema/autenticacao.php';
exigir_login();
$tituloPagina = $tituloPagina ?? 'Portal';
$navegacaoAtiva = $navegacaoAtiva ?? '';
$nomeDoUsuario = $_SESSION['usuario_nome'] ?? 'Aventureiro';
$inicialUsuario = primeiro_caractere($nomeDoUsuario);

$sqlDestaques = <<<SQL
SELECT tipo, codigo, titulo, criado_em FROM (
    SELECT 'personagem' AS tipo, COD_PERSONAGEM AS codigo, NOME_PERSONAGEM AS titulo, DATA_CRIACAO AS criado_em
    FROM PERSONAGEM WHERE COD_USUARIO_FK = ?
    UNION ALL
    SELECT 'criatura' AS tipo, COD_CRIATURA AS codigo, NOME_CRIATURA AS titulo, DATA_CRIACAO AS criado_em
    FROM CRIATURA WHERE COD_USUARIO_FK = ?
    UNION ALL
    SELECT 'documento' AS tipo, COD_DOCUMENTACAO AS codigo, TITULO AS titulo, DATA_CRIACAO AS criado_em
    FROM DOCUMENTACAO WHERE COD_USUARIO_FK = ?
) AS destaques
ORDER BY criado_em DESC LIMIT 6
SQL;
$consultaDestaques = $conexao->prepare($sqlDestaques);
$consultaDestaques->execute([(int) $_SESSION['usuario_id'], (int) $_SESSION['usuario_id'], (int) $_SESSION['usuario_id']]);
$destaquesRecentes = $consultaDestaques->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <meta content="#17251d" name="theme-color"/>
  <title>
   <?= escapar_html($tituloPagina) ?> | PortalToFantasy
  </title>
  <link href="https://fonts.googleapis.com" rel="preconnect"/>
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=EB+Garamond:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="estilos/estilo.css" rel="stylesheet"/>
 </head>
 <body class="portal-body">
  <div aria-hidden="true" class="fundo-floresta">
  </div>
  <header class="cabecalho">
   <div class="cabecalho-interno">
    <a aria-label="PortalToFantasy - início" class="marca-portal" href="painel.php">
     <span class="marca-titulo">
      PortalToFantasy
     </span>
     <span class="marca-subtitulo">
      Um portal de fichas de D&D
     </span>
    </a>
    <div class="conta-cabecalho">
     <span class="inicial-conta">
      <?= escapar_html($inicialUsuario) ?>
     </span>
     <span class="detalhes-conta">
      <strong>
       <?= escapar_html($nomeDoUsuario) ?>
      </strong>
      <small>
       Conta conectada
      </small>
     </span>
     <a aria-label="Sair da conta" class="sair-conta" href="sair.php" title="Sair da conta">
      <?= icone('sair') ?>
     </a>
    </div>
   </div>
  </header>
  <div class="area-portal">
   <div class="area-navegacao">
    <nav aria-label="Navegação principal" class="navegacao-principal">
     <a class="navegacao-link <?= $navegacaoAtiva === 'painel' ? 'ativo' : '' ?>" href="painel.php">
      <?= icone('inicio') ?>
      <span>
       Início
      </span>
     </a>
     <a class="navegacao-link <?= $navegacaoAtiva === 'personagens' ? 'ativo' : '' ?>" href="personagens.php">
      <?= icone('pessoas') ?>
      <span>
       Personagens
      </span>
     </a>
     <a class="navegacao-link <?= $navegacaoAtiva === 'bestiario' ? 'ativo' : '' ?>" href="bestiario.php">
      <?= icone('caveira') ?>
      <span>
       Bestiário
      </span>
     </a>
     <a class="navegacao-link <?= $navegacaoAtiva === 'biblioteca' ? 'ativo' : '' ?>" href="biblioteca.php">
      <?= icone('livro') ?>
      <span>
       Biblioteca
      </span>
     </a>
    </nav>
   </div>
   <div class="moldura-portal">
    <aside class="barra-lateral">
     <a class="acao-lateral" href="criar_personagem.php">
      <?= icone('mais') ?>
      <span>
       Criar personagem
      </span>
     </a>
     <section class="painel-lateral painel-destaques">
      <div class="titulo-lateral">
       Destaques recentes
      </div>
      <?php if ($destaquesRecentes): ?>
      <div class="lista-destaques">
       <?php foreach ($destaquesRecentes as $destaque): ?>
                            <?php
                            $destino = match ($destaque['tipo']) {
                                'personagem' => 'ficha.php?id=' . (int) $destaque['codigo'],
                                'criatura' => 'criatura.php?id=' . (int) $destaque['codigo'],
                                default => 'documento.php?id=' . (int) $destaque['codigo'],
                            };
                            $rotulo = match ($destaque['tipo']) {
                                'personagem' => 'Personagem',
                                'criatura' => 'Criatura',
                                default => 'Documento',
                            };
                            ?>
       <a class="item-destaque" href="<?= escapar_html($destino) ?>">
        <span class="tipo-destaque">
         <?= escapar_html($rotulo) ?>
        </span>
        <strong>
         <?= escapar_html($destaque['titulo']) ?>
        </strong>
        <small>
         <?= date('d/m/Y', strtotime($destaque['criado_em'])) ?>
        </small>
       </a>
       <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p class="destaques-vazios">
       Suas novas fichas, criaturas e documentos aparecerão aqui.
      </p>
      <?php endif; ?>
     </section>
     <div class="rodape-lateral">
      <span>
       PORTALTOFANTASY
      </span>
      <small>
       Personagens · Bestiário · Biblioteca
      </small>
     </div>
    </aside>
    <main class="conteudo-principal">
     <div class="caminho-conteudo">
      <span>
       PORTAL
      </span>
      <span class="separador-caminho">
       /
      </span>
      <strong>
       <?= escapar_html($tituloPagina) ?>
      </strong>
     </div>
     <div class="area-pagina">
      <?php if ($aviso = obter_aviso()): ?>
      <div class="aviso <?= escapar_html(classe_aviso($aviso['tipo'])) ?>" role="status">
       <span>
        <?= escapar_html($aviso['mensagem']) ?>
       </span>
      </div>
      <?php endif; ?>
