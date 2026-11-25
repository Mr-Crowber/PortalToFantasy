<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();

$codigoPersonagem = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$codigoUsuario = (int) $_SESSION['usuario_id'];

$consulta = $conexao->prepare(
    'SELECT * FROM PERSONAGEM
     WHERE COD_PERSONAGEM = ? AND COD_USUARIO_FK = ?'
);
$consulta->execute([$codigoPersonagem, $codigoUsuario]);
$personagem = $consulta->fetch();

if (!$personagem) {
    definir_aviso('Personagem não encontrado.', 'erro');
    redirecionar('personagens.php');
}

$consulta = $conexao->prepare(
    'SELECT * FROM HISTORIA_PERSONAGEM WHERE COD_PERSONAGEM_FK = ?'
);
$consulta->execute([$codigoPersonagem]);

$historia = $consulta->fetch() ?: [
    'HISTORIA' => '',
    'TRACOS_PERSONALIDADE' => '',
    'IDEAIS' => '',
    'VINCULOS' => '',
    'DEFEITOS' => '',
];

$atributos = [
    'FOR' => ['Força', 'ATRIBUTO_FOR'],
    'DES' => ['Destreza', 'ATRIBUTO_DES'],
    'CONST' => ['Constituição', 'ATRIBUTO_CONST'],
    'INT' => ['Inteligência', 'ATRIBUTO_INT'],
    'SAB' => ['Sabedoria', 'ATRIBUTO_SAB'],
    'CAR' => ['Carisma', 'ATRIBUTO_CAR'],
];

$camposCombate = [
    'ATAQUES' => 'Ataques',
    'INVENTARIO' => 'Inventário e equipamentos',
    'PODER_RACA' => 'Poderes de raça',
    'PODER_CLASSE' => 'Poderes de classe',
    'RECURSOS_CLASSE' => 'Recursos de classe',
    'TRACOS' => 'Traços e características',
    'ESPACOS_MAGIA' => 'Espaços de magia',
    'MAGIAS' => 'Magias',
];

$camposHistoria = [
    'HISTORIA' => 'História',
    'TRACOS_PERSONALIDADE' => 'Traços de personalidade',
    'IDEAIS' => 'Ideais',
    'VINCULOS' => 'Vínculos',
    'DEFEITOS' => 'Defeitos',
];

$bonusProficiencia = bonus_proficiencia((int) $personagem['NIVEL']);
$vidaMaxima = max(1, (int) $personagem['PV_MAXIMO']);
$percentualVida = (int) round(
    max(0, min(100, ((int) $personagem['PV'] / $vidaMaxima) * 100))
);

$historicoRolagens = array_slice(
    $_SESSION['historico_rolagens']['personagem_' . $codigoPersonagem] ?? [],
    0,
    5
);

$tituloPagina = 'Ficha de ' . $personagem['NOME_PERSONAGEM'];
$navegacaoAtiva = 'personagens';

require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina titulo-personagem">
 <div>
  <a class="link-voltar" href="personagens.php">
   <?= icone('voltar') ?> Todos os personagens
  </a>
  <span class="rotulo-superior">
   FICHA DE AVENTUREIRO
  </span>
  <h1>
   <?= escapar_html($personagem['NOME_PERSONAGEM']) ?>
  </h1>
  <p>
   <?= escapar_html($personagem['RACA']) ?>
   <span class="separador-cartao">
    |
   </span>
   <?= escapar_html($personagem['CLASSE']) ?>
   <span class="separador-cartao">
    |
   </span>
   Nível <?= (int) $personagem['NIVEL'] ?>
  </p>
 </div>
 <a class="botao botao-principal" href="editar_personagem.php?id=<?= $codigoPersonagem ?>">
  <?= icone('editar') ?> Editar ficha
 </a>
</div>
<section class="destaque-ficha-personagem painel-pergaminho">
 <div class="identidade-ficha">
  <?php if (!empty($personagem['IMAGEM'])): ?>
  <img alt="Retrato de <?= escapar_html($personagem['NOME_PERSONAGEM']) ?>" class="retrato-ficha" src="<?= escapar_html($personagem['IMAGEM']) ?>"/>
  <?php else: ?>
  <span class="retrato-ficha retrato-vazio-ficha">
   <?= icone('usuario') ?>
  </span>
  <?php endif; ?>
  <div class="texto-identidade-ficha">
   <span class="rotulo-superior">
    PERSONAGEM
   </span>
   <h2>
    <?= escapar_html($personagem['NOME_PERSONAGEM']) ?>
   </h2>
   <p>
    <?= escapar_html($personagem['ANTECEDENTE'] ?: 'Sem antecedente') ?> · <?= escapar_html($personagem['ALINHAMENTO'] ?: 'Alinhamento não definido') ?>
   </p>
   <span class="nome-jogador-ficha">
    Jogador: <?= escapar_html($personagem['NOME_JOGADOR']) ?>
   </span>
  </div>
 </div>
 <div class="cartao-vida-ficha">
  <div class="topo-vida-ficha">
   <span>
    <?= icone('vida') ?> PONTOS DE VIDA
   </span>
   <strong>
    <b data-pv-atual="">
     <?= (int) $personagem['PV'] ?>
    </b>
    <small>
     /
     <span data-pv-maximo="">
      <?= (int) $personagem['PV_MAXIMO'] ?>
     </span>
    </small>
   </strong>
  </div>
  <div aria-label="Pontos de vida" aria-valuemax="<?= (int) $personagem['PV_MAXIMO'] ?>" aria-valuemin="0" aria-valuenow="<?= (int) $personagem['PV'] ?>" class="barra-vida-ficha" role="progressbar">
   <i data-pv-bar="" style="width:<?= $percentualVida ?>%">
   </i>
  </div>
  <div class="base-vida-ficha">
   <span data-pv-label="">
    <?= $percentualVida ?>% da vida
   </span>
   <span class="controles-vida-ficha">
    <?php foreach ([-5,-1,1,5] as $variacao): if ($variacao === -5): ?>
    <span class="rotulo-controle-pv rotulo-dano-pv">
     Dano
    </span>
    <?php elseif ($variacao === 1): ?>
    <span class="rotulo-controle-pv rotulo-cura-pv">
     Cura
    </span>
    <?php endif; ?>
    <form action="acao_ficha.php" data-envio-assincrono="" data-id="<?= $codigoPersonagem ?>" data-tipo="personagem" method="post">
     <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
     <input name="tipo" type="hidden" value="personagem"/>
     <input name="id" type="hidden" value="<?= $codigoPersonagem ?>"/>
     <input name="acao" type="hidden" value="vida"/>
     <input name="variacao" type="hidden" value="<?= $variacao ?>"/>
     <button aria-label="<?= $variacao > 0 ? 'Recuperar ' . $variacao : 'Remover ' . abs($variacao) ?> pontos de vida" class="passo-pv <?= $variacao < 0 ? 'dano-pv' : 'cura-pv' ?>" type="submit">
      <?= $variacao > 0 ? '+' : '' ?><?= $variacao ?>
     </button>
    </form>
    <?php endforeach; ?>
   </span>
  </div>
  <small aria-live="polite" class="retorno-pv" data-pv-feedback="">
   Use os controles para ajustar a vida durante a sessão.
  </small>
 </div>
 <div class="atributos-secundarios-ficha">
  <div>
   <span>
    CLASSE DE ARMADURA
   </span>
   <strong>
    <?= (int) $personagem['CA'] ?>
   </strong>
   <small>
    CA
   </small>
  </div>
  <div>
   <span>
    BÔNUS DE PROFICIÊNCIA
   </span>
   <strong>
    <?= formatar_modificador($bonusProficiencia) ?>
   </strong>
   <small>
    Atual no nível <?= (int) $personagem['NIVEL'] ?>
   </small>
  </div>
 </div>
</section>
<section class="painel-rolagem painel-pergaminho" data-painel-dados="">
 <div class="principal-painel-rolagem">
  <div class="titulo-painel-rolagem">
   <div>
    <span class="rotulo-superior">
     TESTES E RESULTADOS
    </span>
    <h2>
     Rolagem de dados
    </h2>
    <p>
     Escolha uma perícia e ajuste um bônus adicional, se necessário.
    </p>
   </div>
   <span class="rotulo-dado-rolagem">
    D20
   </span>
  </div>
  <div class="conteudo-painel-rolagem">
   <div class="coluna-visual-dado">
    <div aria-label="Dado de vinte faces" class="d20-icone" data-dado-visual="">
     <img alt="" aria-hidden="true" src="imagens/d20-icosaedro.svg"/>
     <span data-valor-dado="">
      20
     </span>
    </div>
    <span class="rotulo-dado-pronto" data-estado-dado="">
     PRONTO PARA ROLAR
    </span>
   </div>
   <form action="acao_ficha.php" class="formulario-rolagem formulario-rolagem-ficha" data-envio-assincrono="" data-id="<?= $codigoPersonagem ?>" data-tipo="personagem" method="post">
    <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
    <input name="tipo" type="hidden" value="personagem"/>
    <input name="id" type="hidden" value="<?= $codigoPersonagem ?>"/>
    <input name="acao" type="hidden" value="rolar"/>
    <div class="campo-dado">
     <label for="teste-ficha">
      Perícia
     </label>
     <select id="teste-ficha" name="teste">
      <option value="">
       Teste livre (+0)
      </option>
      <?php foreach (lista_pericias() as $coluna => [$nome, $atributo]): ?>
      <option value="<?= escapar_html($coluna) ?>">
       <?= escapar_html($nome) ?>
      </option>
      <?php endforeach; ?>
     </select>
    </div>
    <div class="campo-dado campo-bonus-dado">
     <label for="bonus-livre-ficha">
      Bônus livre
     </label>
     <div class="area-bonus">
      <span>
       +
      </span>
      <input id="bonus-livre-ficha" inputmode="numeric" max="20" min="-20" name="bonus_livre" type="number" value="0"/>
     </div>
     <small>
      Use valores negativos ou positivos.
     </small>
    </div>
    <button class="botao botao-principal enviar-rolagem" type="submit">
     <?= icone('dado') ?> Rolar D20
    </button>
   </form>
  </div>
  <div aria-live="polite" class="cartao-resultado-dado" data-resultado-rolagem="">
   <span>
    ÚLTIMO RESULTADO
   </span>
   <strong>
    —
   </strong>
   <small>
    As rolagens aparecerão aqui.
   </small>
  </div>
 </div>
 <aside class="painel-historico-dados">
  <div class="titulo-historico-dados">
   <div>
    <span class="rotulo-superior">
     SESSÃO ATUAL
    </span>
    <h3>
     Rolagens anteriores
    </h3>
   </div>
   <span class="quantidade-historico" data-quantidade-historico="">
    <?= count($historicoRolagens) ?>
   </span>
  </div>
  <ol class="lista-historico-dados" data-historico-rolagens="">
   <?php foreach ($historicoRolagens as $rolagem): ?>
   <li class="item-historico-dados <?= !empty($rolagem['critico']) ? 'esta-critico' : (!empty($rolagem['falha']) ? 'esta-falha-critica' : '') ?>">
    <div class="conteudo-rolagem-historico">
     <strong>
      <?= escapar_html($rolagem['nome']) ?>
     </strong>
     <small>
      <?= escapar_html($rolagem['hora']) ?> · <?= escapar_html($rolagem['formula']) ?>
     </small>
    </div>
    <b>
     <?= (int) $rolagem['total'] ?>
    </b>
   </li>
   <?php endforeach; ?>
  </ol>
  <?php if (!$historicoRolagens): ?>
  <p class="historico-vazio" data-historico-vazio="">
   Nenhuma rolagem nesta sessão.
  </p>
  <?php endif; ?>
  <small class="nota-historico">
   Últimas rolagens desta sessão
  </small>
 </aside>
</section>
<section class="painel-ficha painel-pergaminho visualizacao-painel">
 <div class="titulo-secao-formulario">
  <span class="selo-secao">
   <?= icone('escudo') ?>
  </span>
  <div>
   <h2>
    Atributos e perícias
   </h2>
   <p>
    Valores, modificadores e proficiências do personagem.
   </p>
  </div>
 </div>
 <div class="grade-habilidades">
  <?php foreach ($atributos as $chave => [$rotulo, $coluna]): $valor = (int) $personagem[$coluna]; ?>
  <div class="cartao-habilidade">
   <span>
    <?= escapar_html($rotulo) ?>
   </span>
   <strong>
    <?= $valor ?>
   </strong>
   <small>
    <?= formatar_modificador(modificador_atributo($valor)) ?>
   </small>
  </div>
  <?php endforeach; ?>
 </div>
 <div class="divisoria-formulario">
 </div>
 <div class="subtitulo-ficha">
  <h3>
   Perícias
  </h3>
  <span>
   Indicador preenchido = proficiência
  </span>
 </div>
 <div class="grade-pericias">
  <?php foreach (lista_pericias() as $coluna => [$nome, $atributo]):
            $valorAtributo = (int) $personagem['ATRIBUTO_' . $atributo];
            $proficiencia = (int) ($personagem[$coluna] ?? 0);
            $bonus = modificador_atributo($valorAtributo) + ($proficiencia > 0 ? $bonusProficiencia * $proficiencia : 0);
        ?>
  <div class="item-pericia">
   <span class="ponto-pericia <?= $proficiencia > 0 ? 'treinado' : '' ?>">
   </span>
   <span>
    <?= escapar_html($nome) ?>
   </span>
   <small>
    <?= $proficiencia === 2 ? 'Especialista' : ($proficiencia === 1 ? 'Proficiente' : 'Sem proficiência') ?>
   </small>
   <strong>
    <?= formatar_modificador($bonus) ?>
   </strong>
  </div>
  <?php endforeach; ?>
 </div>
</section>
<section class="detalhes-duas-colunas">
 <div class="painel-ficha painel-pergaminho visualizacao-painel">
  <div class="titulo-secao-formulario">
   <span class="selo-secao">
    <?= icone('espadas') ?>
   </span>
   <div>
    <h2>
     Combate e equipamentos
    </h2>
    <p>
     Informações para consulta durante a aventura.
    </p>
   </div>
  </div>
  <?php
  $ataquesPersonagem = trim((string) ($personagem['ATAQUES'] ?? ''));
  if ($ataquesPersonagem === '' && isset($personagem['ATAQUE'])) {
      $ataquesPersonagem = trim((string) $personagem['ATAQUE']);
  }
  ?>
  <div class="campo-somente-leitura">
   <h3>Ataques</h3>
   <div>
    <?php if ($ataquesPersonagem !== ''): ?>
     <?= nl2br(escapar_html($ataquesPersonagem)) ?>
    <?php else: ?>
     <span class="nota-discreta">Nenhum ataque cadastrado.</span>
    <?php endif; ?>
   </div>
  </div>
  <?php foreach ($camposCombate as $coluna => $titulo): ?>
   <?php if ($coluna === 'ATAQUES' || trim((string) ($personagem[$coluna] ?? '')) === '') continue; ?>
   <div class="campo-somente-leitura">
    <h3><?= escapar_html($titulo) ?></h3>
    <div><?= nl2br(escapar_html((string) $personagem[$coluna])) ?></div>
   </div>
  <?php endforeach; ?>
 </div>
 <div class="painel-ficha painel-pergaminho visualizacao-painel">
  <div class="titulo-secao-formulario">
   <span class="selo-secao">
    <?= icone('pergaminho') ?>
   </span>
   <div>
    <h2>
     História
    </h2>
    <p>
     Origem e personalidade.
    </p>
   </div>
  </div>
  <?php foreach ($camposHistoria as $coluna => $titulo): ?>
            <?php if (trim((string) ($historia[$coluna] ?? '')) === '') continue; ?>
  <div class="campo-somente-leitura">
   <h3>
    <?= escapar_html($titulo) ?>
   </h3>
   <div>
    <?= nl2br(escapar_html($historia[$coluna])) ?>
   </div>
  </div>
  <?php endforeach; ?>
        <?php
        $temHistoria = count(array_filter(
            $historia,
            fn ($valor) => trim((string) $valor) !== ''
        )) > 0;
        ?>
        <?php if (!$temHistoria): ?>
  <p class="nota-discreta">
   Nenhuma história foi registrada.
  </p>
  <?php endif; ?>
 </div>
</section>
<?php require __DIR__ . '/partes/rodape.php'; ?>
