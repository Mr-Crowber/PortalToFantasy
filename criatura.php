<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();

$codigoCriatura = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$codigoUsuario = (int) $_SESSION['usuario_id'];

$consulta = $conexao->prepare(
    'SELECT * FROM CRIATURA
     WHERE COD_CRIATURA = ?
     AND (COD_USUARIO_FK IS NULL OR COD_USUARIO_FK = ?)'
);
$consulta->execute([$codigoCriatura, $codigoUsuario]);
$criatura = $consulta->fetch();

if (!$criatura) {
    definir_aviso('Criatura não encontrada.', 'erro');
    redirecionar('bestiario.php');
}

$ehDono = $criatura['COD_USUARIO_FK'] !== null
    && (int) $criatura['COD_USUARIO_FK'] === $codigoUsuario;

    $imagem = caminho_imagem(
    (string) ($criatura['IMAGEM'] ?? ''),
    'criaturas'
    );

    if ($imagem === '') {
        $imagem = imagem_padrao_criatura(
            $criatura['NOME_CRIATURA']
        );
    }

$valorND = (float) ($criatura['ND'] ?? 0);
if (abs($valorND - 0.125) < 0.0005) {
    $nd = '1/8';
} elseif (abs($valorND - 0.25) < 0.0005) {
    $nd = '1/4';
} elseif (abs($valorND - 0.5) < 0.0005) {
    $nd = '1/2';
} else {
    $nd = rtrim(rtrim(number_format($valorND, 3, ',', ''), '0'), ',');
}

$vidaInicial = $ehDono
    ? (int) ($criatura['PV_ATUAL'] ?? $criatura['PV'])
    : (int) ($_SESSION['vidas_criaturas'][(string) $codigoCriatura] ?? $criatura['PV']);

$vidaMaxima = max(1, (int) $criatura['PV']);
$criatura['PV_ATUAL'] = max(0, min($vidaMaxima, $vidaInicial));
$percentualVida = (int) round(
    ($criatura['PV_ATUAL'] / $vidaMaxima) * 100
);

$atributos = [
    'Força' => 'ATRIBUTO_FOR',
    'Destreza' => 'ATRIBUTO_DES',
    'Constituição' => 'ATRIBUTO_CONST',
    'Inteligência' => 'ATRIBUTO_INT',
    'Sabedoria' => 'ATRIBUTO_SAB',
    'Carisma' => 'ATRIBUTO_CAR',
];

$atributosParaRolagem = [
    'forca' => 'Força',
    'destreza' => 'Destreza',
    'constituicao' => 'Constituição',
    'inteligencia' => 'Inteligência',
    'sabedoria' => 'Sabedoria',
    'carisma' => 'Carisma',
];

$historicoRolagens = array_slice(
    $_SESSION['historico_rolagens']['criatura_' . $codigoCriatura] ?? [],
    0,
    5
);

$tituloPagina = $criatura['NOME_CRIATURA'];
$navegacaoAtiva = 'bestiario';

require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina titulo-pagina-criatura">
 <div>
  <a class="link-voltar" href="bestiario.php">
   <?= icone('voltar') ?> Voltar ao bestiário
  </a>
  <span class="rotulo-superior">
   COMPÊNDIO DE CRIATURAS
  </span>
  <h1>
   <?= escapar_html($criatura['NOME_CRIATURA']) ?>
  </h1>
  <p>
   <?= escapar_html($criatura['TIPO']) ?>
   <span class="separador-cartao">
    |
   </span>
   <?= escapar_html($criatura['TAMANHO']) ?>
   <span class="separador-cartao">
    |
   </span>
   ND <?= escapar_html($nd) ?>
  </p>
 </div>
 <a class="botao botao-principal" href="editar_criatura.php?id=<?= $codigoCriatura ?>">
  <?= icone('editar') ?> <?= $ehDono ? 'Editar criatura' : 'Personalizar criatura' ?>
 </a>
</div>
<section class="destaque-ficha-criatura painel-pergaminho">
 <div class="arte-ficha-criatura">
  <img alt="Imagem de <?= escapar_html($criatura['NOME_CRIATURA']) ?>" src="<?= escapar_html($imagem) ?>"/>
 </div>
 <div class="identidade-ficha-criatura">
  <span class="rotulo-superior">
   <?= $ehDono ? 'REGISTRO PESSOAL' : 'CRIATURA DO BESTIÁRIO' ?>
  </span>
  <h2>
   <?= escapar_html($criatura['NOME_CRIATURA']) ?>
  </h2>
  <p>
   <?= escapar_html($criatura['DESCRICAO'] ?: 'Nenhuma descrição foi registrada para esta criatura.') ?>
  </p>
  <div class="linha-detalhes-criatura">
   <span>
    <?= escapar_html($criatura['TIPO']) ?>
   </span>
   <span>
    <?= escapar_html($criatura['TAMANHO']) ?>
   </span>
   <span>
    Nível de desafio <?= escapar_html($nd) ?>
   </span>
  </div>
 </div>
 <div aria-label="Resumo de combate" class="resumo-combate-criatura">
  <div class="indicador-resumo-criatura">
   <span>
    Classe de armadura
   </span>
   <strong>
    <?= (int) $criatura['CA'] ?>
   </strong>
   <small>
    CA
   </small>
  </div>
  <div class="indicador-resumo-criatura">
   <span>
    Nível de desafio
   </span>
   <strong>
    <?= escapar_html($nd) ?>
   </strong>
   <small>
    ND
   </small>
  </div>
 </div>
</section>
<section class="area-sessao-criatura">
 <section class="painel-vida-criatura painel-pergaminho">
  <div class="titulo-painel-criatura">
   <div>
    <span class="rotulo-superior">
     CONTROLE DO ENCONTRO
    </span>
    <h2>
     Pontos de vida
    </h2>
   </div>
   <strong class="pv-atual-criatura">
    <span data-pv-atual="">
     <?= (int) $criatura['PV_ATUAL'] ?>
    </span>
    <small>
     /
     <span data-pv-maximo="">
      <?= $vidaMaxima ?>
     </span>
     PV
    </small>
   </strong>
  </div>
  <div aria-label="Pontos de vida da criatura" aria-valuemax="<?= $vidaMaxima ?>" aria-valuemin="0" aria-valuenow="<?= (int) $criatura['PV_ATUAL'] ?>" class="barra-vida-ficha barra-vida-criatura" role="progressbar">
   <i data-pv-bar="" style="width:<?= $percentualVida ?>%">
   </i>
  </div>
  <div class="info-vida-criatura">
   <span data-pv-label="">
    <?= $percentualVida ?>% da vida
   </span>
   <span>
    Máximo: <?= $vidaMaxima ?> PV
   </span>
  </div>
  <div class="controles-vida-criatura">
   <span class="rotulo-controle-pv rotulo-dano-pv">
    Dano
   </span>
   <?php foreach ([-5, -1] as $variacao): ?>
   <form action="acao_ficha.php" data-envio-assincrono="" data-id="<?= $codigoCriatura ?>" data-tipo="criatura" method="post">
    <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
    <input name="tipo" type="hidden" value="criatura"/>
    <input name="id" type="hidden" value="<?= $codigoCriatura ?>"/>
    <input name="acao" type="hidden" value="vida"/>
    <input name="variacao" type="hidden" value="<?= $variacao ?>"/>
    <button aria-label="Remover <?= abs($variacao) ?> pontos de vida" class="passo-pv dano-pv" type="submit">
     <?= $variacao ?>
    </button>
   </form>
   <?php endforeach; ?>
   <span class="rotulo-controle-pv rotulo-cura-pv">
    Cura
   </span>
   <?php foreach ([1, 5] as $variacao): ?>
   <form action="acao_ficha.php" data-envio-assincrono="" data-id="<?= $codigoCriatura ?>" data-tipo="criatura" method="post">
    <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
    <input name="tipo" type="hidden" value="criatura"/>
    <input name="id" type="hidden" value="<?= $codigoCriatura ?>"/>
    <input name="acao" type="hidden" value="vida"/>
    <input name="variacao" type="hidden" value="<?= $variacao ?>"/>
    <button aria-label="Recuperar <?= $variacao ?> pontos de vida" class="passo-pv cura-pv" type="submit">
     +<?= $variacao ?>
    </button>
   </form>
   <?php endforeach; ?>
  </div>
  <small aria-live="polite" class="retorno-pv" data-pv-feedback="">
   Ajuste os pontos de vida durante o encontro.
  </small>
 </section>
 <section class="area-dados-criatura painel-pergaminho">
  <div class="painel-principal-dados-criatura">
   <div class="titulo-painel-criatura">
    <div>
     <span class="rotulo-superior">
      TESTES E RESULTADOS
     </span>
     <h2>
      Rolagem de dados
     </h2>
    </div>
    <span class="rotulo-dado-rolagem">
     D20
    </span>
   </div>
   <div class="layout-dados-criatura">
    <div aria-label="Dado de vinte faces" class="d20-icone d20-icone-criatura" data-dado-visual="">
     <img alt="" aria-hidden="true" src="imagens/d20-icosaedro.svg"/>
     <span data-valor-dado="">
      20
     </span>
    </div>
    <form action="acao_ficha.php" class="formulario-dados-criatura" data-envio-assincrono="" data-id="<?= $codigoCriatura ?>" data-tipo="criatura" method="post">
     <input name="token_csrf" type="hidden" value="<?= escapar_html(token_csrf()) ?>"/>
     <input name="tipo" type="hidden" value="criatura"/>
     <input name="id" type="hidden" value="<?= $codigoCriatura ?>"/>
     <input name="acao" type="hidden" value="rolar"/>
     <label class="campo-rolagem-criatura" for="teste-criatura">
      <span>
       Atributo do teste
      </span>
      <select id="teste-criatura" name="teste">
       <option value="">
        Teste livre (+0)
       </option>
       <?php foreach ($atributosParaRolagem as $valor => $nome): ?>
       <option value="<?= escapar_html($valor) ?>">
        <?= escapar_html($nome) ?>
       </option>
       <?php endforeach; ?>
      </select>
     </label>
     <label class="campo-rolagem-criatura" for="bonus-criatura">
      <span>
       Bônus adicional
      </span>
      <input id="bonus-criatura" inputmode="numeric" max="20" min="-20" name="bonus_livre" type="number" value="0"/>
     </label>
     <button class="botao botao-principal botao-rolagem-criatura" type="submit">
      <?= icone('dado') ?> Rolar D20
     </button>
    </form>
   </div>
   <div class="estado-rolagem" data-estado-dado="">
    PRONTO PARA ROLAR
   </div>
   <div aria-live="polite" class="cartao-resultado-dado cartao-resultado-criatura" data-resultado-rolagem="">
    <span>
     ÚLTIMO RESULTADO
    </span>
    <strong>
     —
    </strong>
    <small>
     Escolha um atributo e faça seu teste.
    </small>
   </div>
  </div>
  <aside class="painel-historico-dados painel-historico-criatura">
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
</section>
<section class="painel-ficha painel-pergaminho visualizacao-painel painel-atributos-criatura">
 <div class="titulo-secao-formulario">
  <span class="selo-secao">
   <?= icone('escudo') ?>
  </span>
  <div>
   <h2>
    Atributos
   </h2>
   <p>
    Valores e modificadores da criatura.
   </p>
  </div>
 </div>
 <div class="grade-habilidades">
  <?php foreach ($atributos as $nome => $coluna): $valor = (int) $criatura[$coluna]; ?>
  <div class="cartao-habilidade">
   <span>
    <?= escapar_html($nome) ?>
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
</section>
<section class="grade-conteudo-criatura">
 <article class="painel-ficha painel-pergaminho visualizacao-painel painel-conteudo-criatura">
  <div class="titulo-secao-formulario">
   <span class="selo-secao">
    <?= icone('espadas') ?>
   </span>
   <div>
    <h2>
     Ataques e habilidades
    </h2>
    <p>
     Ações e características de combate.
    </p>
   </div>
  </div>
  <div class="campo-somente-leitura">
   <h3>Ataques</h3>
   <div>
    <?php if (trim((string) ($criatura['ATAQUES'] ?? '')) !== ''): ?>
     <?= nl2br(escapar_html((string) $criatura['ATAQUES'])) ?>
    <?php else: ?>
     <span class="nota-discreta">Nenhum ataque cadastrado para esta criatura.</span>
    <?php endif; ?>
   </div>
  </div>
  <?php foreach (['PODERES'=>'Poderes e habilidades','MAGIAS'=>'Magias'] as $coluna => $titulo): ?>
   <?php if (trim((string) ($criatura[$coluna] ?? '')) === '') continue; ?>
   <div class="campo-somente-leitura">
    <h3><?= escapar_html($titulo) ?></h3>
    <div><?= nl2br(escapar_html((string) $criatura[$coluna])) ?></div>
   </div>
  <?php endforeach; ?>
 </article>
 <article class="painel-ficha painel-pergaminho visualizacao-painel painel-conteudo-criatura">
  <div class="titulo-secao-formulario">
   <span class="selo-secao">
    <?= icone('pergaminho') ?>
   </span>
   <div>
    <h2>
     Inventário e tesouro
    </h2>
    <p>
     Itens carregados pela criatura.
    </p>
   </div>
  </div>
  <?php if (trim((string) ($criatura['INVENTARIO'] ?? '')) !== ''): ?>
  <div class="campo-somente-leitura">
   <div>
    <?= nl2br(escapar_html($criatura['INVENTARIO'])) ?>
   </div>
  </div>
  <?php else: ?>
  <p class="nota-discreta">
   Nenhum item ou tesouro registrado.
  </p>
  <?php endif; ?>
 </article>
</section>
<?php require __DIR__ . '/partes/rodape.php'; ?>
