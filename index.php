<?php
require_once __DIR__ . '/sistema/funcoes.php';
require_once __DIR__ . '/sistema/conexao.php';
if (esta_logado()) {
    redirecionar('painel.php');
}
$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $consulta = $conexao->prepare('SELECT COD_USUARIO, NOME_USUARIO, SENHA_HASH FROM USUARIO WHERE EMAIL = ?');
    $consulta->execute([$email]);
    $usuario = $consulta->fetch();
    if ($usuario && password_verify($senha, $usuario['SENHA_HASH'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int) $usuario['COD_USUARIO'];
        $_SESSION['usuario_nome'] = $usuario['NOME_USUARIO'];
        redirecionar('painel.php');
    }
    $erro = 'E-mail ou senha não conferem.';
}

$tituloPagina = 'Entrar';
require __DIR__ . '/partes/topo_acesso.php';
?>
<div class="pagina-acesso">
 <section class="painel-arte-acesso">
  <div class="conteudo-arte-acesso">
   <a class="marca marca-clara" href="index.php">
    <span class="texto-marca">
     <strong>
      PortalToFantasy
     </strong>
     <small>
      PERSONAGENS · BESTIÁRIO · BIBLIOTECA
     </small>
    </span>
   </a>
   <div class="texto-arte-acesso">
    <span class="rotulo-superior">
     PORTAL DE AVENTUREIROS
    </span>
    <h1>
     PortalToFantasy
    </h1>
    <p>
     Suas fichas, criaturas e anotações reunidas em um só lugar.
    </p>
   </div>
   <div class="base-arte-acesso">
    Acesso pessoal
   </div>
  </div>
 </section>
 <section class="painel-formulario-acesso">
  <div class="area-formulario-acesso">
   <div class="marca-acesso-celular">
    <strong>
     PortalToFantasy
    </strong>
   </div>
   <span class="rotulo-superior">
    BEM-VINDO DE VOLTA
   </span>
   <h2>
    Entrar
   </h2>
   <p class="subtitulo-acesso">
    Acesse sua conta para continuar.
   </p>
   <?php if ($aviso = obter_aviso()): ?>
   <div class="aviso <?= escapar_html(classe_aviso($aviso['tipo'])) ?>">
    <?= escapar_html($aviso['mensagem']) ?>
   </div>
   <?php endif; ?>
            <?php if ($erro): ?>
   <div class="aviso aviso-erro">
    <?= escapar_html($erro) ?>
   </div>
   <?php endif; ?>
   <form class="pilha-formulario" method="post">
    <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
    <label class="rotulo-campo" for="email">
     E-mail
    </label>
    <div class="area-entrada">
     <input autocomplete="email" id="email" name="email" placeholder="voce@exemplo.com" required="" type="email" value="<?= escapar_html($email) ?>"/>
    </div>
    <label class="rotulo-campo" for="senha">
     Senha
    </label>
    <div class="area-entrada">
     <input autocomplete="current-password" id="senha" name="senha" placeholder="Sua senha" required="" type="password"/>
    </div>
    <button class="botao botao-principal botao-largo" type="submit">
     Entrar
    </button>
   </form>
   <p class="alternar-acesso">
    Ainda não tem uma conta?
    <a href="cadastro.php">
     Criar conta
    </a>
   </p>
   <small class="nota-legal-acesso">
    Acesso pessoal do PortalToFantasy.
   </small>
  </div>
 </section>
</div>
