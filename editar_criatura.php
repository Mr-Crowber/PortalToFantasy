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

$erro = '';

// ===== Recebe as alterações =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();

    $criatura['NOME_CRIATURA'] = trim($_POST['nome'] ?? '');
    $criatura['TIPO'] = trim($_POST['tipo'] ?? '');
    $criatura['TAMANHO'] = trim($_POST['tamanho'] ?? 'Médio');
    $criatura['ND'] = max(0, (float) ($_POST['nd'] ?? 0));

    $atributos = [
        'FOR' => 'forca',
        'DES' => 'destreza',
        'CONST' => 'constituicao',
        'INT' => 'inteligencia',
        'SAB' => 'sabedoria',
        'CAR' => 'carisma',
    ];

    foreach ($atributos as $coluna => $campo) {
        $criatura['ATRIBUTO_' . $coluna] = max(
            1,
            min(30, (int) ($_POST[$campo] ?? 10))
        );
    }

    $criatura['PV'] = max(1, (int) ($_POST['pv'] ?? 1));
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

    // ===== Salva as mudanças ou cria uma cópia =====
    if ($criatura['NOME_CRIATURA'] === '' || $criatura['TIPO'] === '') {
        $erro = 'Nome e tipo são obrigatórios.';
    } else {
        try {
            $imagemAnterior = (string) ($criatura['IMAGEM'] ?? '');
            $criatura['IMAGEM'] = salvar_imagem_enviada(
                'imagem_criatura',
                'criaturas',
                $imagemAnterior
            );

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
                'CA',
                'DESCRICAO',
                'ATAQUES',
                'INVENTARIO',
                'PODERES',
                'MAGIAS',
                'IMAGEM',
            ];

            $valores = [];

            foreach ($colunas as $coluna) {
                $valores[] = $criatura[$coluna];
            }

            if ($ehDono) {
                $atribuicoes = array_map(
                    fn ($coluna) => "`{$coluna}` = ?",
                    $colunas
                );

                $sql = 'UPDATE CRIATURA SET '
                    . implode(', ', $atribuicoes)
                    . ' WHERE COD_CRIATURA = ? AND COD_USUARIO_FK = ?';

                $atualizar = $conexao->prepare($sql);
                $atualizar->execute([
                    ...$valores,
                    $codigoCriatura,
                    $codigoUsuario,
                ]);

                if ($imagemAnterior !== $criatura['IMAGEM']) {
                    remover_imagem_enviada($imagemAnterior, 'criaturas');
                }

                $atualizarVida = $conexao->prepare(
                    'UPDATE CRIATURA
                     SET PV_ATUAL = LEAST(PV_ATUAL, PV)
                     WHERE COD_CRIATURA = ? AND COD_USUARIO_FK = ?'
                );
                $atualizarVida->execute([$codigoCriatura, $codigoUsuario]);

                $idDestino = $codigoCriatura;
                definir_aviso('Criatura atualizada.', 'sucesso');
            } else {
                $colunas[] = 'PV_ATUAL';
                $colunas[] = 'COD_USUARIO_FK';

                $valores[] = $criatura['PV'];
                $valores[] = $codigoUsuario;

                $marcadores = implode(',', array_fill(0, count($colunas), '?'));
                $listaColunas = implode(',', $colunas);
                $sql = "INSERT INTO CRIATURA ({$listaColunas}) VALUES ({$marcadores})";

                $inserir = $conexao->prepare($sql);
                $inserir->execute($valores);

                $idDestino = (int) $conexao->lastInsertId();
                definir_aviso('Cópia pessoal criada no seu bestiário.', 'sucesso');
            }

            redirecionar('criatura.php?id=' . $idDestino);
        } catch (Throwable $problema) {
            $erro = $problema instanceof RuntimeException
                ? $problema->getMessage()
                : 'Não foi possível salvar a criatura. Confira se o banco está atualizado.';
        }
    }
}

$tituloPagina = $ehDono ? 'Editar criatura' : 'Personalizar criatura';
$navegacaoAtiva = 'bestiario';
$modoEdicao = true;

require __DIR__ . '/partes/topo.php';
?>
<div class="linha-titulo-pagina">
 <div>
  <a class="link-voltar" href="criatura.php?id=<?= $codigoCriatura ?>">
   <?= icone('voltar') ?> Voltar à criatura
  </a>
  <span class="rotulo-superior">
   <?= $ehDono ? 'EDIÇÃO DO BESTIÁRIO' : 'MODELO DO BESTIÁRIO' ?>
  </span>
  <h1>
   <?= $ehDono
                ? escapar_html($criatura['NOME_CRIATURA'])
                : 'Personalizar ' . escapar_html($criatura['NOME_CRIATURA']) ?>
  </h1>
  <p>
   <?= $ehDono
                ? 'Altere os dados do seu registro.'
                : 'O modelo original será preservado. Ao salvar, uma cópia editável será criada no seu bestiário.' ?>
  </p>
 </div>
 <?php if ($ehDono): ?>
 <a class="botao botao-perigo-suave" href="excluir_criatura.php?id=<?= $codigoCriatura ?>">
  <?= icone('lixeira') ?> Excluir criatura
 </a>
 <?php endif; ?>
</div>
<?php if ($erro): ?>
<div class="aviso aviso-erro">
 <?= escapar_html($erro) ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/partes/formulario_criatura.php'; ?>
<?php require __DIR__ . '/partes/rodape.php'; ?>
