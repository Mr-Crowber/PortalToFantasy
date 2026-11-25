<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();

$criatura = [
    'NOME_CRIATURA' => '',
    'TIPO' => '',
    'TAMANHO' => 'Médio',
    'ND' => '0.25',
    'PV' => 10,
    'CA' => 10,
    'DESCRICAO' => '',
    'ATAQUES' => '',
    'PODERES' => '',
    'MAGIAS' => '',
    'INVENTARIO' => '',
];

$atributos = [
    'FOR' => 'forca',
    'DES' => 'destreza',
    'CONST' => 'constituicao',
    'INT' => 'inteligencia',
    'SAB' => 'sabedoria',
    'CAR' => 'carisma',
];

foreach (array_keys($atributos) as $atributo) {
    $criatura['ATRIBUTO_' . $atributo] = 10;
}

$erro = '';

// ===== Recebe os dados do formulário =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();

    $criatura['NOME_CRIATURA'] = trim($_POST['nome'] ?? '');
    $criatura['TIPO'] = trim($_POST['tipo'] ?? '');
    $criatura['TAMANHO'] = trim($_POST['tamanho'] ?? 'Médio');
    $criatura['ND'] = max(0, (float) ($_POST['nd'] ?? 0));

    foreach ($atributos as $coluna => $campo) {
        $criatura['ATRIBUTO_' . $coluna] = max(
            1,
            min(30, (int) ($_POST[$campo] ?? 10))
        );
    }

    $criatura['PV'] = max(1, (int) ($_POST['pv'] ?? 10));
    $criatura['CA'] = max(0, (int) ($_POST['ca'] ?? 10));

    $camposTexto = [
        'DESCRICAO' => 'descricao',
        'ATAQUES' => 'ataques',
        'PODERES' => 'poderes',
        'MAGIAS' => 'magias',
        'INVENTARIO' => 'inventario',
    ];

    foreach ($camposTexto as $coluna => $campo) {
        $criatura[$coluna] = trim($_POST[$campo] ?? '');
    }

    // ===== Valida e salva a criatura =====
    if ($criatura['NOME_CRIATURA'] === '' || $criatura['TIPO'] === '') {
        $erro = 'Nome e tipo são obrigatórios.';
    } else {
        try {
            $imagem = salvar_imagem_enviada('imagem_criatura', 'criaturas');

            $colunas = [
                'NOME_CRIATURA',
                'TIPO',
                'TAMANHO',
                'ND',
                'ATRIBUTO_FOR',
                'ATRIBUTO_DES',
                'ATRIBUTO_CONST',
                'ATRIBUTO_INT',
                'ATRIBUTO_SAB',
                'ATRIBUTO_CAR',
                'PV',
                'PV_ATUAL',
                'CA',
                'DESCRICAO',
                'ATAQUES',
                'INVENTARIO',
                'PODERES',
                'MAGIAS',
                'IMAGEM',
                'COD_USUARIO_FK',
            ];

            $valores = [
                $criatura['NOME_CRIATURA'],
                $criatura['TIPO'],
                $criatura['TAMANHO'],
                $criatura['ND'],
                $criatura['ATRIBUTO_FOR'],
                $criatura['ATRIBUTO_DES'],
                $criatura['ATRIBUTO_CONST'],
                $criatura['ATRIBUTO_INT'],
                $criatura['ATRIBUTO_SAB'],
                $criatura['ATRIBUTO_CAR'],
                $criatura['PV'],
                $criatura['PV'],
                $criatura['CA'],
                $criatura['DESCRICAO'],
                $criatura['ATAQUES'],
                $criatura['INVENTARIO'],
                $criatura['PODERES'],
                $criatura['MAGIAS'],
                $imagem,
                (int) $_SESSION['usuario_id'],
            ];

            $marcadores = implode(',', array_fill(0, count($colunas), '?'));
            $listaColunas = implode(',', $colunas);
            $sql = "INSERT INTO CRIATURA ({$listaColunas}) VALUES ({$marcadores})";

            $inserir = $conexao->prepare($sql);
            $inserir->execute($valores);

            $codigoCriatura = (int) $conexao->lastInsertId();

            definir_aviso('Criatura adicionada ao bestiário.', 'sucesso');
            redirecionar('criatura.php?id=' . $codigoCriatura);
        } catch (Throwable $problema) {
            $erro = $problema instanceof RuntimeException
                ? $problema->getMessage()
                : 'Não foi possível salvar a criatura. Confira se o banco está atualizado.';
        }
    }
}

$tituloPagina = 'Nova criatura';
$navegacaoAtiva = 'bestiario';
$modoEdicao = false;

require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina">
 <div>
  <a class="link-voltar" href="bestiario.php">
   <?= icone('voltar') ?> Bestiário
  </a>
  <span class="rotulo-superior">
   NOVO REGISTRO
  </span>
  <h1>
   Adicionar criatura
  </h1>
  <p>
   Cadastre uma criatura para consultar durante as sessões.
  </p>
 </div>
</div>
<?php if ($erro): ?>
<div class="aviso aviso-erro">
 <?= escapar_html($erro) ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/partes/formulario_criatura.php'; ?>
<?php require __DIR__ . '/partes/rodape.php'; ?>
