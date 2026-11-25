<?php
require_once __DIR__ . '/sistema/funcoes.php';
require_once __DIR__ . '/sistema/conexao.php';
if (esta_logado()) {
    redirecionar('painel.php');
}
$erro = '';
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmacao = $_POST['confirmar_senha'] ?? '';

    if (tamanho_texto($nome) < 2) {
        $erro = 'O nome precisa ter pelo menos 2 caracteres.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha precisa ter pelo menos 8 caracteres.';
    } elseif ($senha !== $confirmacao) {
        $erro = 'As senhas não coincidem.';
    } else {
        $consulta = $conexao->prepare('SELECT COD_USUARIO FROM USUARIO WHERE EMAIL = ?');
        $consulta->execute([$email]);
        if ($consulta->fetch()) $erro = 'Este e-mail já está cadastrado.';
        else {
            $inserir = $conexao->prepare('INSERT INTO USUARIO (NOME_USUARIO, EMAIL, SENHA_HASH) VALUES (?, ?, ?)');
            $inserir->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) $conexao->lastInsertId();
            $_SESSION['usuario_nome'] = $nome;
            redirecionar('painel.php');
        }
    }
}

$tituloPagina = 'Criar conta';
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
     NOVO CADASTRO
    </span>
    <h1>
     PortalToFantasy
    </h1>
    <p>
     Crie sua conta para guardar fichas, criaturas e documentos.
    </p>
   </div>
   <div class="base-arte-acesso">
    Área de cadastro
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
    CADASTRO
   </span>
   <h2>
    Criar conta
   </h2>
   <p class="subtitulo-acesso">
    Preencha os dados abaixo.
   </p>
   <?php if ($erro): ?>
   <div class="aviso aviso-erro">
    <?= escapar_html($erro) ?>
   </div>
   <?php endif; ?>
   <form class="pilha-formulario" method="post">
    <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
    <label class="rotulo-campo" for="nome">
     Nome
    </label>
    <div class="area-entrada">
     <input autocomplete="name" id="nome" name="nome" placeholder="Seu nome" required="" value="<?= escapar_html($nome) ?>"/>
    </div>
    <label class="rotulo-campo" for="email">
     E-mail
    </label>
    <div class="area-entrada">
     <input autocomplete="email" id="email" name="email" placeholder="voce@exemplo.com" required="" type="email" value="<?= escapar_html($email) ?>"/>
    </div>
    <label class="rotulo-campo" for="senha">
     Senha
     <small>
      (mínimo 8 caracteres)
     </small>
    </label>
    <div class="area-entrada">
     <input autocomplete="new-password" id="senha" name="senha" required="" type="password"/>
    </div>
    <label class="rotulo-campo" for="confirmar_senha">
     Confirme sua senha
    </label>
    <div class="area-entrada">
     <input autocomplete="new-password" id="confirmar_senha" name="confirmar_senha" required="" type="password"/>
    </div>
    <button class="botao botao-principal botao-largo" type="submit">
     Criar conta
    </button>
   </form>
   <p class="alternar-acesso">
    Já tem uma conta?
    <a href="index.php">
     Entrar
    </a>
   </p>
   <small class="nota-legal-acesso">
    Seus dados ficam associados à sua conta.
   </small>
  </div>
 </section>
</div>
