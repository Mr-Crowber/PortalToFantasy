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

$erro = '';

// ===== Recebe as alterações =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();

    $personagem['NOME_PERSONAGEM'] = trim($_POST['nome_personagem'] ?? '');
    $personagem['NOME_JOGADOR'] = trim($_POST['nome_jogador'] ?? '');
    $personagem['RACA'] = trim($_POST['raca'] ?? '');
    $personagem['CLASSE'] = trim($_POST['classe'] ?? '');
    $personagem['NIVEL'] = max(1, min(20, (int) ($_POST['nivel'] ?? 1)));
    $personagem['ANTECEDENTE'] = trim($_POST['antecedente'] ?? '');
    $personagem['ALINHAMENTO'] = trim($_POST['alinhamento'] ?? '');
    $personagem['EXP'] = max(0, (int) ($_POST['exp'] ?? 0));
    $personagem['PV_MAXIMO'] = max(1, min(999, (int) ($_POST['pv_maximo'] ?? 1)));
    $personagem['PV'] = max(
        0,
        min($personagem['PV_MAXIMO'], (int) ($_POST['pv'] ?? 0))
    );
    $personagem['CA'] = max(0, min(99, (int) ($_POST['ca'] ?? 0)));

    $atributos = ['FOR', 'DES', 'CONST', 'INT', 'SAB', 'CAR'];

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

    // ===== Salva as mudanças =====
    if (
        $personagem['NOME_PERSONAGEM'] === '' ||
        $personagem['RACA'] === '' ||
        $personagem['CLASSE'] === ''
    ) {
        $erro = 'Nome, raça e classe são obrigatórios.';
    } else {
        try {
            $imagemAnterior = (string) ($personagem['IMAGEM'] ?? '');

            $personagem['IMAGEM'] = salvar_imagem_enviada(
                'imagem_personagem',
                'personagens',
                $imagemAnterior
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
            ];

            $colunas = array_merge($colunas, $colunasExtras);

            $atribuicoes = array_map(
                fn ($coluna) => "`{$coluna}` = ?",
                $colunas
            );

            $valores = array_map(
                fn ($coluna) => $personagem[$coluna] ?? null,
                $colunas
            );

            $sql = 'UPDATE PERSONAGEM SET '
                . implode(', ', $atribuicoes)
                . ' WHERE COD_PERSONAGEM = ? AND COD_USUARIO_FK = ?';

            $atualizar = $conexao->prepare($sql);
            $atualizar->execute([
                ...$valores,
                $codigoPersonagem,
                $codigoUsuario,
            ]);

            if ($imagemAnterior !== $personagem['IMAGEM']) {
                remover_imagem_enviada($imagemAnterior, 'personagens');
            }

            $sqlHistoria = 'INSERT INTO HISTORIA_PERSONAGEM
                (COD_PERSONAGEM_FK, HISTORIA, TRACOS_PERSONALIDADE, IDEAIS, VINCULOS, DEFEITOS)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    HISTORIA = VALUES(HISTORIA),
                    TRACOS_PERSONALIDADE = VALUES(TRACOS_PERSONALIDADE),
                    IDEAIS = VALUES(IDEAIS),
                    VINCULOS = VALUES(VINCULOS),
                    DEFEITOS = VALUES(DEFEITOS)';

            $salvarHistoria = $conexao->prepare($sqlHistoria);
            $salvarHistoria->execute([
                $codigoPersonagem,
                $historia['HISTORIA'],
                $historia['TRACOS_PERSONALIDADE'],
                $historia['IDEAIS'],
                $historia['VINCULOS'],
                $historia['DEFEITOS'],
            ]);

            definir_aviso('Ficha atualizada.', 'sucesso');
            redirecionar('ficha.php?id=' . $codigoPersonagem);
        } catch (Throwable $problema) {
            $erro = $problema instanceof RuntimeException
                ? $problema->getMessage()
                : 'Não foi possível atualizar a ficha. Confira se o banco está atualizado.';
        }
    }
}

$tituloPagina = 'Editar personagem';
$navegacaoAtiva = 'personagens';
$modoEdicao = true;
$linkCancelar = 'ficha.php?id=' . $codigoPersonagem;

require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina">
 <div>
  <a class="link-voltar" href="ficha.php?id=<?= $codigoPersonagem ?>">
   <?= icone('voltar') ?> Voltar à ficha
  </a>
  <span class="rotulo-superior">
   EDIÇÃO DE FICHA
  </span>
  <h1>
   <?= escapar_html($personagem['NOME_PERSONAGEM']) ?>
  </h1>
  <p>
   Altere os dados e salve quando terminar.
  </p>
 </div>
 <a class="botao botao-perigo-suave" href="excluir_personagem.php?id=<?= $codigoPersonagem ?>">
  <?= icone('lixeira') ?> Excluir personagem
 </a>
</div>
<?php if ($erro): ?>
<div class="aviso aviso-erro">
 <?= escapar_html($erro) ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/partes/formulario_personagem.php'; ?>
<?php require __DIR__ . '/partes/rodape.php'; ?>
