<?php
$atributosFormulario = [
    'FOR' => 'Força',
    'DES' => 'Destreza',
    'CONST' => 'Constituição',
    'INT' => 'Inteligência',
    'SAB' => 'Sabedoria',
    'CAR' => 'Carisma',
];

$classesSugeridas = [
    'Bárbaro', 'Bardo', 'Bruxo', 'Clérigo', 'Druida', 'Feiticeiro',
    'Guerreiro', 'Ladino', 'Mago', 'Monge', 'Paladino', 'Patrulheiro',
];

$racasSugeridas = [
    'Anão', 'Draconato', 'Elfo', 'Gnomo', 'Halfling',
    'Humano', 'Meio-elfo', 'Meio-orc', 'Tiefling',
];

$periciasFormulario = lista_pericias();
?>
<form class="layout-formulario painel-pergaminho pergaminho-formulario" enctype="multipart/form-data" method="post">
 <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
 <div class="titulo-secao-formulario">
  <span class="selo-secao">
   <?= icone('usuario') ?>
  </span>
  <div>
   <h2>
    Identificação
   </h2>
   <p>
    Nome, origem e retrato do personagem.
   </p>
  </div>
 </div>
 <div class="grade-formulario duas-colunas">
  <div class="grupo-campo ocupar-duas-colunas">
   <label for="nome_personagem">
    Nome do personagem *
   </label>
   <input id="nome_personagem" maxlength="150" name="nome_personagem" required="" value="<?= escapar_html($personagem['NOME_PERSONAGEM'] ?? '') ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="nome_jogador">
    Nome do jogador
   </label>
   <input id="nome_jogador" maxlength="150" name="nome_jogador" value="<?= escapar_html($personagem['NOME_JOGADOR'] ?? $_SESSION['usuario_nome']) ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="nivel">
    Nível
   </label>
   <input id="nivel" max="20" min="1" name="nivel" type="number" value="<?= (int) ($personagem['NIVEL'] ?? 1) ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="raca">
    Raça / espécie *
   </label>
   <input id="raca" list="racas-sugeridas" maxlength="100" name="raca" required="" value="<?= escapar_html($personagem['RACA'] ?? '') ?>"/>
   <datalist id="racas-sugeridas">
    <?php foreach ($racasSugeridas as $raca): ?>
    <option value="<?= escapar_html($raca) ?>">
    </option>
    <?php endforeach; ?>
   </datalist>
  </div>
  <div class="grupo-campo">
   <label for="classe">
    Classe *
   </label>
   <input id="classe" list="classes-sugeridas" maxlength="100" name="classe" required="" value="<?= escapar_html($personagem['CLASSE'] ?? '') ?>"/>
   <datalist id="classes-sugeridas">
    <?php foreach ($classesSugeridas as $classe): ?>
    <option value="<?= escapar_html($classe) ?>">
    </option>
    <?php endforeach; ?>
   </datalist>
  </div>
  <div class="grupo-campo">
   <label for="antecedente">
    Antecedente
   </label>
   <input id="antecedente" maxlength="100" name="antecedente" value="<?= escapar_html($personagem['ANTECEDENTE'] ?? '') ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="alinhamento">
    Alinhamento
   </label>
   <input id="alinhamento" maxlength="100" name="alinhamento" value="<?= escapar_html($personagem['ALINHAMENTO'] ?? '') ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="exp">
    Experiência (XP)
   </label>
   <input id="exp" min="0" name="exp" type="number" value="<?= (int) ($personagem['EXP'] ?? 0) ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="imagem_personagem">
    Imagem de perfil
   </label>
   <input accept="image/jpeg,image/png,image/webp,image/gif" id="imagem_personagem" name="imagem_personagem" type="file"/>
   <small>
    JPG, PNG, WEBP ou GIF. Máximo de 2 MB.
   </small>
  </div>
  <?php if (!empty($personagem['IMAGEM'])): ?>
  <div class="grupo-campo campo-previa-perfil">
   <label>
    Imagem atual
   </label>
   <img alt="Retrato atual do personagem" class="previa-perfil" src="<?= escapar_html($personagem['IMAGEM']) ?>"/>
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
    Atributos
   </h2>
   <p>
    Valores principais do personagem.
   </p>
  </div>
 </div>
 <div class="grade-entrada-habilidades">
  <?php foreach ($atributosFormulario as $chave => $rotulo): ?>
            <?php $coluna = 'ATRIBUTO_' . $chave; ?>
  <div class="entrada-habilidade">
   <label for="atributo_<?= escapar_html($chave) ?>">
    <?= escapar_html($rotulo) ?>
   </label>
   <input id="atributo_<?= escapar_html($chave) ?>" max="30" min="1" name="<?= escapar_html($coluna) ?>" type="number" value="<?= (int) ($personagem[$coluna] ?? 10) ?>"/>
   <small>
    <?= formatar_modificador(modificador_atributo((int) ($personagem[$coluna] ?? 10))) ?>
   </small>
  </div>
  <?php endforeach; ?>
 </div>
 <div class="divisoria-formulario">
 </div>
 <div class="titulo-secao-formulario">
  <span class="selo-secao">
   <?= icone('dado') ?>
  </span>
  <div>
   <h2>
    Perícias
   </h2>
   <p>
    0 = sem proficiência, 1 = proficiente, 2 = especialista.
   </p>
  </div>
 </div>
 <div class="grade-edicao-pericias">
  <?php foreach ($periciasFormulario as $coluna => [$nomePericia, $atributo]): ?>
  <div class="grupo-campo">
   <label for="<?= escapar_html($coluna) ?>">
    <?= escapar_html($nomePericia) ?>
   </label>
   <select id="<?= escapar_html($coluna) ?>" name="<?= escapar_html($coluna) ?>">
    <option <?= (int) ($personagem[$coluna] ?? 0) === 0 ? 'selected' : '' ?>  value="0">
     Sem proficiência
    </option>
    <option <?= (int) ($personagem[$coluna] ?? 0) === 1 ? 'selected' : '' ?>  value="1">
     Proficiente
    </option>
    <option <?= (int) ($personagem[$coluna] ?? 0) === 2 ? 'selected' : '' ?>  value="2">
     Especialista
    </option>
   </select>
  </div>
  <?php endforeach; ?>
 </div>
 <div class="divisoria-formulario">
 </div>
 <div class="titulo-secao-formulario">
  <span class="selo-secao">
   <?= icone('espadas') ?>
  </span>
  <div>
   <h2>
    Combate
   </h2>
   <p>
    Vida, defesa e informações usadas durante a sessão.
   </p>
  </div>
 </div>
 <div class="grade-formulario tres-colunas">
  <div class="grupo-campo">
   <label for="pv">
    Pontos de vida atuais
   </label>
   <input id="pv" max="999" min="0" name="pv" type="number" value="<?= (int) ($personagem['PV'] ?? 10) ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="pv_maximo">
    Pontos de vida máximos
   </label>
   <input id="pv_maximo" max="999" min="1" name="pv_maximo" type="number" value="<?= (int) ($personagem['PV_MAXIMO'] ?? 10) ?>"/>
  </div>
  <div class="grupo-campo">
   <label for="ca">
    Classe de armadura (CA)
   </label>
   <input id="ca" max="99" min="0" name="ca" type="number" value="<?= (int) ($personagem['CA'] ?? 10) ?>"/>
  </div>
 </div>
 <div class="grade-formulario duas-colunas">
  <?php foreach ([
            'ATAQUES' => ['Ataques', 4],
            'INVENTARIO' => ['Inventário e equipamentos', 4],
            'PODER_RACA' => ['Poderes de raça', 3],
            'PODER_CLASSE' => ['Poderes de classe', 3],
            'RECURSOS_CLASSE' => ['Recursos de classe', 3],
            'TRACOS' => ['Traços e características', 3],
            'ESPACOS_MAGIA' => ['Espaços de magia', 3],
            'MAGIAS' => ['Magias', 3],
        ] as $coluna => [$rotulo, $linhas]): ?>
  <div class="grupo-campo">
   <label for="<?= strtolower($coluna) ?>">
    <?= escapar_html($rotulo) ?>
   </label>
   <textarea id="<?= strtolower($coluna) ?>" name="<?= strtolower($coluna) ?>" rows="<?= $linhas ?>"><?= escapar_html($personagem[$coluna] ?? '') ?></textarea>
  </div>
  <?php endforeach; ?>
 </div>
 <div class="divisoria-formulario">
 </div>
 <div class="titulo-secao-formulario">
  <span class="selo-secao">
   <?= icone('pergaminho') ?>
  </span>
  <div>
   <h2>
    História e personalidade
   </h2>
   <p>
    Informações narrativas para consultar na ficha.
   </p>
  </div>
 </div>
 <div class="grade-formulario duas-colunas">
  <div class="grupo-campo ocupar-duas-colunas">
   <label for="historia">
    História
   </label>
   <textarea id="historia" name="historia" rows="5"><?= escapar_html($historia['HISTORIA'] ?? '') ?></textarea>
  </div>
  <?php foreach ([
            'TRACOS_PERSONALIDADE' => 'Traços de personalidade',
            'IDEAIS' => 'Ideais',
            'VINCULOS' => 'Vínculos',
            'DEFEITOS' => 'Defeitos',
        ] as $coluna => $rotulo): ?>
  <div class="grupo-campo">
   <label for="<?= strtolower($coluna) ?>">
    <?= escapar_html($rotulo) ?>
   </label>
   <textarea id="<?= strtolower($coluna) ?>" name="<?= strtolower($coluna) ?>" rows="3"><?= escapar_html($historia[$coluna] ?? '') ?></textarea>
  </div>
  <?php endforeach; ?>
 </div>
 <div class="acoes-formulario">
  <a class="botao botao-discreto" href="<?= escapar_html($linkCancelar) ?>">
   Cancelar
  </a>
  <button class="botao botao-principal" type="submit">
   <?= $modoEdicao ? 'Salvar alterações' : 'Criar personagem' ?>
            <?= icone('seta') ?>
  </button>
 </div>
 <div class="canto-pergaminho">
 </div>
</form>
