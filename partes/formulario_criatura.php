<form class="layout-formulario painel-pergaminho pergaminho-formulario" enctype="multipart/form-data" method="post">
 <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
 <div class="titulo-secao-formulario">
  <span class="selo-secao">
   <?= icone('caveira') ?>
  </span>
  <div>
   <h2>
    Identificação
   </h2>
   <p>
    Nome, tipo e imagem de perfil da criatura.
   </p>
  </div>
 </div>
 <div class="grade-formulario duas-colunas">
  <div class="grupo-campo ocupar-duas-colunas">
   <label for="nome">
    Nome da criatura *
   </label>
   <input id="nome" maxlength="150" name="nome" required="" value="<?= escapar_html($criatura['NOME_CRIATURA'] ?? '') ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="tipo">
    Tipo *
   </label>
   <input id="tipo" maxlength="150" name="tipo" placeholder="Ex.: Humanoide" required="" value="<?= escapar_html($criatura['TIPO'] ?? '') ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="tamanho">
    Tamanho *
   </label>
   <select id="tamanho" name="tamanho" required="">
    <?php foreach (['Miúdo', 'Pequeno', 'Médio', 'Grande', 'Enorme', 'Imenso'] as $tamanho): ?>
    <option value="<?= escapar_html($tamanho) ?>" <?= ($criatura['TAMANHO'] ?? 'Médio') === $tamanho ? 'selected' : '' ?>>
     <?= escapar_html($tamanho) ?>
    </option>
    <?php endforeach; ?>
   </select>
  </div>
  <div class="grupo-campo">
   <label for="nd">
    Nível de desafio (ND)
   </label>
   <input id="nd" max="99" min="0" name="nd" step="0.125" type="number" value="<?= escapar_html((string) ($criatura['ND'] ?? '0.25')) ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="imagem_criatura">
    Imagem de perfil
   </label>
   <input accept="image/jpeg,image/png,image/webp,image/gif" id="imagem_criatura" name="imagem_criatura" type="file"/>
   <small>
    JPG, PNG, WEBP ou GIF. Máximo de 2 MB.
   </small>
  </div>
  <?php if (!empty($criatura['IMAGEM'])): ?>
  <div class="grupo-campo campo-previa-perfil">
   <label>
    Imagem atual
   </label>
   <img alt="Imagem atual da criatura" class="previa-perfil" src="<?= escapar_html($criatura['IMAGEM']) ?>"/>
  </div>
  <?php endif; ?>
 </div>
 <div class="divisoria-formulario">
 </div>
 <div class="titulo-secao-formulario">
  <span class="selo-secao">
   <?= icone('escudo') ?>
  </span>
  <div>
   <h2>
    Atributos e combate
   </h2>
   <p>
    Estatísticas para consulta durante os encontros.
   </p>
  </div>
 </div>
 <div class="grade-formulario tres-colunas">
  <?php foreach ([
            'FOR' => ['Força', 'forca'],
            'DES' => ['Destreza', 'destreza'],
            'CONST' => ['Constituição', 'constituicao'],
            'INT' => ['Inteligência', 'inteligencia'],
            'SAB' => ['Sabedoria', 'sabedoria'],
            'CAR' => ['Carisma', 'carisma'],
        ] as $atributo => [$rotulo, $campo]): ?>
  <div class="grupo-campo">
   <label for="<?= escapar_html($campo) ?>">
    <?= escapar_html($rotulo) ?>
   </label>
   <input id="<?= escapar_html($campo) ?>" max="30" min="1" name="<?= escapar_html($campo) ?>" type="number" value="<?= (int) ($criatura['ATRIBUTO_' . $atributo] ?? 10) ?>"/>
  </div>
  <?php endforeach; ?>
  <div class="grupo-campo">
   <label for="pv">
    Pontos de vida
   </label>
   <input id="pv" max="9999" min="1" name="pv" type="number" value="<?= (int) ($criatura['PV'] ?? 10) ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="ca">
    Classe de armadura (CA)
   </label>
   <input id="ca" max="99" min="0" name="ca" type="number" value="<?= (int) ($criatura['CA'] ?? 10) ?>"/>
  </div>
 </div>
 <div class="divisoria-formulario">
 </div>
 <div class="titulo-secao-formulario">
  <span class="selo-secao">
   <?= icone('pergaminho') ?>
  </span>
  <div>
   <h2>
    Descrição e habilidades
   </h2>
   <p>
    Informações para consultar durante a aventura.
   </p>
  </div>
 </div>
 <div class="grade-formulario duas-colunas">
  <div class="grupo-campo ocupar-duas-colunas">
   <label for="descricao">
    Descrição
   </label>
   <textarea id="descricao" name="descricao" rows="4"><?= escapar_html($criatura['DESCRICAO'] ?? '') ?></textarea>
  </div>
  <?php foreach ([
            'ataques' => ['Ataques', 'ATAQUES'],
            'poderes' => ['Poderes e habilidades', 'PODERES'],
            'magias' => ['Magias', 'MAGIAS'],
            'inventario' => ['Inventário / tesouro', 'INVENTARIO'],
        ] as $campo => [$rotulo, $coluna]): ?>
  <div class="grupo-campo">
   <label for="<?= escapar_html($campo) ?>">
    <?= escapar_html($rotulo) ?>
   </label>
   <textarea id="<?= escapar_html($campo) ?>" name="<?= escapar_html($campo) ?>" rows="4"><?= escapar_html($criatura[$coluna] ?? '') ?></textarea>
  </div>
  <?php endforeach; ?>
 </div>
 <div class="acoes-formulario">
  <a class="botao botao-discreto" href="bestiario.php">
   Cancelar
  </a>
  <button class="botao botao-principal" type="submit">
   <?= $modoEdicao ? (!empty($ehDono) && $ehDono ? 'Salvar alterações' : 'Salvar cópia pessoal') : 'Salvar criatura' ?>
            <?= icone('seta') ?>
  </button>
 </div>
 <div class="canto-pergaminho">
 </div>
</form>
