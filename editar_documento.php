<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();

$codigoDocumento = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$documento = [
    'TITULO' => '',
    'CATEGORIA' => 'Anotações',
    'RESUMO' => '',
    'CONTEUDO' => '',
];

$novo = $codigoDocumento < 1;
$codigoUsuario = (int) $_SESSION['usuario_id'];
$categorias = ['Lore', 'NPCs', 'Locais', 'Regras', 'Anotações', 'Outros'];
$erro = '';

// ===== Carrega o documento, quando for uma edição =====
if (!$novo) {
    $consulta = $conexao->prepare(
        'SELECT * FROM DOCUMENTACAO
         WHERE COD_DOCUMENTACAO = ? AND COD_USUARIO_FK = ?'
    );
    $consulta->execute([$codigoDocumento, $codigoUsuario]);
    $documento = $consulta->fetch();

    if (!$documento) {
        definir_aviso('Documento não encontrado.', 'erro');
        redirecionar('biblioteca.php');
    }
}

// ===== Recebe e salva o conteúdo =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();

    $documento['TITULO'] = trim($_POST['titulo'] ?? '');
    $documento['CATEGORIA'] = trim($_POST['categoria'] ?? 'Anotações');
    $documento['RESUMO'] = trim($_POST['resumo'] ?? '');

    $conteudoOriginal = (string) ($_POST['conteudo'] ?? '');
    $textoOriginal = trim(strip_tags($conteudoOriginal));

    $temImagemBase64 = (bool) preg_match(
        '~<img\b[^>]*src=["\']data:image/(jpeg|png|webp|gif);base64,~i',
        $conteudoOriginal
    );

    $temImagemSalva = (bool) preg_match(
        '~<img\b[^>]*src=["\']arquivos/documentos/[a-zA-Z0-9._-]+["\']~i',
        $conteudoOriginal
    );

    if ($documento['TITULO'] === '' || ($textoOriginal === '' && !$temImagemBase64 && !$temImagemSalva)) {
        $erro = 'Preencha o título e escreva algum conteúdo ou insira uma imagem.';
    } elseif (!in_array($documento['CATEGORIA'], $categorias, true)) {
        $erro = 'Selecione uma categoria válida.';
    } else {
        $conteudoComImagens = processar_imagens_do_documento($conteudoOriginal);
        $documento['CONTEUDO'] = limpar_html_documento($conteudoComImagens);

        if ($novo) {
            $sql = 'INSERT INTO DOCUMENTACAO
                (TITULO, CATEGORIA, RESUMO, CONTEUDO, COD_USUARIO_FK)
                VALUES (?, ?, ?, ?, ?)';

            $salvar = $conexao->prepare($sql);
            $salvar->execute([
                $documento['TITULO'],
                $documento['CATEGORIA'],
                $documento['RESUMO'],
                $documento['CONTEUDO'],
                $codigoUsuario,
            ]);

            $codigoDocumento = (int) $conexao->lastInsertId();
            definir_aviso('Documento criado.', 'sucesso');
        } else {
            $sql = 'UPDATE DOCUMENTACAO
                SET TITULO = ?, CATEGORIA = ?, RESUMO = ?, CONTEUDO = ?
                WHERE COD_DOCUMENTACAO = ? AND COD_USUARIO_FK = ?';

            $salvar = $conexao->prepare($sql);
            $salvar->execute([
                $documento['TITULO'],
                $documento['CATEGORIA'],
                $documento['RESUMO'],
                $documento['CONTEUDO'],
                $codigoDocumento,
                $codigoUsuario,
            ]);

            definir_aviso('Documento atualizado.', 'sucesso');
        }

        redirecionar('documento.php?id=' . $codigoDocumento);
    }
}

$conteudoEditor = trim((string) $documento['CONTEUDO']) === ''
    ? '<p><br></p>'
    : exibir_conteudo_documento((string) $documento['CONTEUDO']);

$tituloPagina = $novo ? 'Novo documento' : 'Editar documento';
$navegacaoAtiva = 'biblioteca';

require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina">
 <div>
  <a class="link-voltar" href="<?= $novo ? 'biblioteca.php' : 'documento.php?id=' . $codigoDocumento ?>">
   <?= icone('voltar') ?> <?= $novo ? 'Biblioteca' : 'Voltar ao documento' ?>
  </a>
  <span class="rotulo-superior">
   EDITOR DE DOCUMENTOS
  </span>
  <h1>
   <?= $novo ? 'Novo documento' : 'Editar documento' ?>
  </h1>
  <p>
   Escreva o texto e insira imagens onde desejar.
  </p>
 </div>
 <?php if (!$novo): ?>
 <a class="botao botao-perigo-suave" href="excluir_documento.php?id=<?= $codigoDocumento ?>">
  <?= icone('lixeira') ?> Excluir
 </a>
 <?php endif; ?>
</div>
<?php if ($erro): ?>
<div class="aviso aviso-erro">
 <?= escapar_html($erro) ?>
</div>
<?php endif; ?>
<form class="layout-formulario painel-pergaminho pergaminho-formulario formulario-editor-documento" id="formulario-documento" method="post">
 <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
 <div class="grade-formulario duas-colunas">
  <div class="grupo-campo ocupar-duas-colunas">
   <label for="titulo">
    Título *
   </label>
   <input id="titulo" maxlength="150" name="titulo" required="" value="<?= escapar_html($documento['TITULO']) ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="categoria">
    Categoria
   </label>
   <select id="categoria" name="categoria">
    <?php foreach ($categorias as $categoria): ?>
    <option value="<?= escapar_html($categoria) ?>" <?= $documento['CATEGORIA'] === $categoria ? 'selected' : '' ?>>
     <?= escapar_html($categoria) ?>
    </option>
    <?php endforeach; ?>
   </select>
  </div>
  <div class="grupo-campo">
   <label for="resumo">
    Resumo para a biblioteca
   </label>
   <input id="resumo" maxlength="255" name="resumo" value="<?= escapar_html($documento['RESUMO']) ?>"/>
  </div>
 </div>
 <div class="divisoria-formulario">
 </div>
 <div aria-label="Formatação do documento" class="barra-editor-documento" role="toolbar">
  <button aria-label="Negrito" data-comando="bold" type="button">
   <strong>
    B
   </strong>
  </button>
  <button aria-label="Itálico" data-comando="italic" type="button">
   <em>
    I
   </em>
  </button>
  <button aria-label="Sublinhado" data-comando="underline" type="button">
   <u>
    U
   </u>
  </button>
  <span class="separador-barra-ferramentas">
  </span>
  <button data-comando="formatBlock" data-valor="h2" type="button">
   Título
  </button>
  <button data-comando="formatBlock" data-valor="p" type="button">
   Parágrafo
  </button>
  <button data-comando="insertUnorderedList" type="button">
   Lista
  </button>
  <button data-comando="insertOrderedList" type="button">
   Lista numerada
  </button>
  <span class="separador-barra-ferramentas">
  </span>
  <button id="botao-inserir-imagem" type="button">
   <?= icone('imagem') ?> Inserir imagem
  </button>
  <input accept="image/jpeg,image/png,image/webp,image/gif" hidden="" id="arquivo-imagem-documento" type="file"/>
 </div>
 <div class="pergaminho-editor-documento">
  <div aria-label="Conteúdo do documento" aria-multiline="true" class="documento-editavel" contenteditable="true" id="editor-documento" role="textbox">
   <?= $conteudoEditor ?>
  </div>
 </div>
 <textarea hidden="" id="conteudo-documento" name="conteudo">
    </textarea>
 <small class="editor-dica">
  As imagens são salvas junto ao documento. Use arquivos JPG, PNG, WEBP ou GIF com até 1 MB cada. Limite de 3 imagens por documento.
 </small>
 <div class="acoes-formulario">
  <a class="botao botao-discreto" href="<?= $novo ? 'biblioteca.php' : 'documento.php?id=' . $codigoDocumento ?>">
   Cancelar
  </a>
  <button class="botao botao-principal" type="submit">
   Salvar documento <?= icone('seta') ?>
  </button>
 </div>
 <div class="canto-pergaminho">
 </div>
</form>
<?php require __DIR__ . '/partes/rodape.php'; ?>
