<?php
require_once __DIR__ . '/../sistema/funcoes.php';
$tituloPagina = $tituloPagina ?? 'Acessar';
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
 <body class="corpo-acesso">
  <div class="fundo-acesso">
  </div>
 </body>
</html>
