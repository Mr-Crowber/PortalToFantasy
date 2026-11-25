<?php
require_once __DIR__ . '/sistema/autenticacao.php';
exigir_login();

$personagem = [
    'NOME_PERSONAGEM' => '',
    'NOME_JOGADOR' => $_SESSION['usuario_nome'],
    'RACA' => '',
    'CLASSE' => '',
    'NIVEL' => 1,
    'ANTECEDENTE' => '',
    'ALINHAMENTO' => '',
    'EXP' => 0,
    'PV' => 10,
    'PV_MAXIMO' => 10,
    'CA' => 10,
    'ATAQUES' => '',
    'INVENTARIO' => '',
    'PODER_RACA' => '',
    'PODER_CLASSE' => '',
    'RECURSOS_CLASSE' => '',
    'TRACOS' => '',
    'ESPACOS_MAGIA' => '',
    'MAGIAS' => '',
    'IMAGEM' => '',
];

$atributos = ['FOR', 'DES', 'CONST', 'INT', 'SAB', 'CAR'];

foreach ($atributos as $atributo) {
    $personagem['ATRIBUTO_' . $atributo] = 10;
}

foreach (array_keys(lista_pericias()) as $pericia) {
    $personagem[$pericia] = 0;
}

$historia = [
    'HISTORIA' => '',
    'TRACOS_PERSONALIDADE' => '',
    'IDEAIS' => '',
    'VINCULOS' => '',
    'DEFEITOS' => '',
];

$erro = '';

// ===== Recebe os dados do formulário =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();

    $personagem['NOME_PERSONAGEM'] = trim($_POST['nome_personagem'] ?? '');
    $personagem['NOME_JOGADOR'] = trim($_POST['nome_jogador'] ?? $_SESSION['usuario_nome']);
    $personagem['RACA'] = trim($_POST['raca'] ?? '');
    $personagem['CLASSE'] = trim($_POST['classe'] ?? '');
    $personagem['NIVEL'] = max(1, min(20, (int) ($_POST['nivel'] ?? 1)));
    $personagem['ANTECEDENTE'] = trim($_POST['antecedente'] ?? '');
    $personagem['ALINHAMENTO'] = trim($_POST['alinhamento'] ?? '');
    $personagem['EXP'] = max(0, (int) ($_POST['exp'] ?? 0));
    $personagem['PV_MAXIMO'] = max(1, min(999, (int) ($_POST['pv_maximo'] ?? 10)));
    $personagem['PV'] = max(
        0,
        min(
            $personagem['PV_MAXIMO'],
            (int) ($_POST['pv'] ?? $personagem['PV_MAXIMO'])
        )
    );
    $personagem['CA'] = max(0, min(99, (int) ($_POST['ca'] ?? 10)));

    foreach ($atributos as $atributo) {
        $coluna = 'ATRIBUTO_' . $atributo;
        $personagem[$coluna] = max(
            1,
            min(30, (int) ($_POST[$coluna] ?? 10))
        );
    }

    foreach (array_keys(lista_pericias()) as $pericia) {
        $personagem[$pericia] = max(0, min(2, (int) ($_POST[$pericia] ?? 0)));
    }

    $camposDaFicha = [
        'ATAQUES',
        'INVENTARIO',
        'PODER_RACA',
        'PODER_CLASSE',
        'RECURSOS_CLASSE',
        'TRACOS',
        'ESPACOS_MAGIA',
        'MAGIAS',
    ];

    foreach ($camposDaFicha as $campo) {
        $personagem[$campo] = trim($_POST[strtolower($campo)] ?? '');
    }

    foreach (array_keys($historia) as $campo) {
        $historia[$campo] = trim($_POST[strtolower($campo)] ?? '');
    }

    // ===== Valida e salva o personagem =====
    if (
        $personagem['NOME_PERSONAGEM'] === '' ||
        $personagem['RACA'] === '' ||
        $personagem['CLASSE'] === ''
    ) {
        $erro = 'Nome, raça e classe são obrigatórios.';
    } else {
        try {
            $personagem['IMAGEM'] = salvar_imagem_enviada(
                'imagem_personagem',
                'personagens'
            );

            $colunas = [
                'NOME_PERSONAGEM',
                'NOME_JOGADOR',
                'RACA',
                'CLASSE',
                'NIVEL',
                'ANTECEDENTE',
                'ALINHAMENTO',
                'EXP',
            ];

            foreach ($atributos as $atributo) {
                $colunas[] = 'ATRIBUTO_' . $atributo;
            }

            foreach (array_keys(lista_pericias()) as $pericia) {
                $colunas[] = $pericia;
            }

            $colunasExtras = [
                'PV',
                'PV_MAXIMO',
                'CA',
                'ATAQUES',
                'INVENTARIO',
                'PODER_RACA',
                'PODER_CLASSE',
                'RECURSOS_CLASSE',
                'TRACOS',
                'ESPACOS_MAGIA',
                'MAGIAS',
                'IMAGEM',
                'COD_USUARIO_FK',
            ];

            $colunas = array_merge($colunas, $colunasExtras);
            $valores = [];

            foreach ($colunas as $coluna) {
                $valores[] = $coluna === 'COD_USUARIO_FK'
                    ? (int) $_SESSION['usuario_id']
                    : ($personagem[$coluna] ?? null);
            }

            $marcadores = implode(',', array_fill(0, count($colunas), '?'));
            $listaColunas = implode(',', $colunas);
            $sql = "INSERT INTO PERSONAGEM ({$listaColunas}) VALUES ({$marcadores})";

            $inserir = $conexao->prepare($sql);
            $inserir->execute($valores);

            $codigoPersonagem = (int) $conexao->lastInsertId();

            $salvarHistoria = $conexao->prepare(
                'INSERT INTO HISTORIA_PERSONAGEM
                (COD_PERSONAGEM_FK, HISTORIA, TRACOS_PERSONALIDADE, IDEAIS, VINCULOS, DEFEITOS)
                VALUES (?, ?, ?, ?, ?, ?)'
            );

            $salvarHistoria->execute([
                $codigoPersonagem,
                $historia['HISTORIA'],
                $historia['TRACOS_PERSONALIDADE'],
                $historia['IDEAIS'],
                $historia['VINCULOS'],
                $historia['DEFEITOS'],
            ]);

            definir_aviso('Personagem criado.', 'sucesso');
            redirecionar('ficha.php?id=' . $codigoPersonagem);
        } catch (Throwable $problema) {
            $erro = $problema instanceof RuntimeException
                ? $problema->getMessage()
                : 'Não foi possível salvar o personagem. Confira se o banco está atualizado.';
        }
    }
}

$tituloPagina = 'Criar personagem';
$navegacaoAtiva = 'personagens';
$modoEdicao = false;
$linkCancelar = 'personagens.php';

require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina">
 <div>
  <a class="link-voltar" href="personagens.php">
   <?= icone('voltar') ?> Personagens
  </a>
  <span class="rotulo-superior">
   NOVA FICHA
  </span>
  <h1>
   Criar personagem
  </h1>
  <p>
   Preencha os dados básicos e complete os detalhes que quiser.
  </p>
 </div>
</div>
<?php if ($erro): ?>
<div class="aviso aviso-erro">
 <?= escapar_html($erro) ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/partes/formulario_personagem.php'; ?>
<?php require __DIR__ . '/partes/rodape.php'; ?>
