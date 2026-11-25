<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();
$codigoDocumento = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$consulta = $conexao->prepare('SELECT * FROM DOCUMENTACAO WHERE COD_DOCUMENTACAO = ? AND COD_USUARIO_FK = ?');
$consulta->execute([$codigoDocumento, (int) $_SESSION['usuario_id']]);
$documento = $consulta->fetch();
if (!$documento) { definir_aviso('Documento não encontrado.', 'erro'); redirecionar('biblioteca.php'); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
  <title>
   <?= escapar_html($documento['TITULO']) ?> | PortalToFantasy
  </title>
  <link href="https://fonts.googleapis.com" rel="preconnect"/>
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600&family=EB+Garamond:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <link href="estilos/estilo.css" rel="stylesheet"/>
 </head>
 <body class="corpo-leitura-documento">
  <div class="barra-leitura-documento">
   <a class="botao botao-discreto" href="biblioteca.php">
    <?= icone('voltar') ?> Biblioteca
   </a>
   <a class="botao botao-principal" href="editar_documento.php?id=<?= $codigoDocumento ?>">
    <?= icone('editar') ?> Editar documento
   </a>
  </div>
  <main class="folha-leitura-documento">
   <article class="conteudo-leitura-documento">
    <h1>
     <?= escapar_html($documento['TITULO']) ?>
    </h1>
    <div class="conteudo-formatado-documento">
     <?= exibir_conteudo_documento((string) $documento['CONTEUDO']) ?>
    </div>
   </article>
  </main>
 </body>
</html>
