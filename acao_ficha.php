<?php
// ===== Respostas e validações =====
ob_start();

require_once __DIR__ . '/sistema/funcoes.php';
require_once __DIR__ . '/sistema/conexao.php';

$pedidoAssincrono = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
    || str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');

$codigoUsuario = (int) ($_SESSION['usuario_id'] ?? 0);
$tipoFicha = ($_POST['tipo'] ?? '') === 'criatura' ? 'criatura' : 'personagem';
$codigoFicha = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
$paginaDeVolta = $tipoFicha === 'criatura'
    ? 'criatura.php?id=' . $codigoFicha
    : 'ficha.php?id=' . $codigoFicha;

function responder_acao(array $dados, string $destino, bool $pedidoAssincrono): never
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    if ($pedidoAssincrono) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

        echo json_encode(
            $dados,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_INVALID_UTF8_SUBSTITUTE
        );

        exit;
    }

    definir_aviso(
        (string) ($dados['mensagem'] ?? 'Ação concluída.'),
        !empty($dados['ok']) ? 'sucesso' : 'erro'
    );

    http_response_code(303);
    redirecionar($destino);
}

if (!esta_logado()) {
    http_response_code(401);

    responder_acao(
        [
            'ok' => false,
            'mensagem' => 'Sua sessão expirou. Entre novamente no PortalToFantasy.',
        ],
        'index.php',
        $pedidoAssincrono
    );
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);

        responder_acao(
            ['ok' => false, 'mensagem' => 'Método não permitido.'],
            $paginaDeVolta,
            $pedidoAssincrono
        );
    }

    $tokenEnviado = $_POST['token_csrf'] ?? '';
    $tokenDaSessao = $_SESSION['token_csrf'] ?? '';

    if (!is_string($tokenEnviado) || !hash_equals($tokenDaSessao, $tokenEnviado)) {
        http_response_code(419);

        responder_acao(
            [
                'ok' => false,
                'mensagem' => 'A sessão do formulário expirou. Atualize a página e tente novamente.',
            ],
            $paginaDeVolta,
            $pedidoAssincrono
        );
    }

    if ($codigoFicha < 1) {
        http_response_code(400);

        responder_acao(
            ['ok' => false, 'mensagem' => 'Registro inválido.'],
            $paginaDeVolta,
            $pedidoAssincrono
        );
    }

    if ($tipoFicha === 'personagem') {
        $consulta = $conexao->prepare(
            'SELECT * FROM PERSONAGEM
             WHERE COD_PERSONAGEM = ? AND COD_USUARIO_FK = ?'
        );

        $consulta->execute([$codigoFicha, $codigoUsuario]);
    } else {
        $consulta = $conexao->prepare(
            'SELECT * FROM CRIATURA
             WHERE COD_CRIATURA = ?
             AND (COD_USUARIO_FK IS NULL OR COD_USUARIO_FK = ?)'
        );

        $consulta->execute([$codigoFicha, $codigoUsuario]);
    }

    $ficha = $consulta->fetch();

    if (!$ficha) {
        http_response_code(404);

        responder_acao(
            [
                'ok' => false,
                'mensagem' => 'Ficha não encontrada ou sem permissão de acesso.',
            ],
            $paginaDeVolta,
            $pedidoAssincrono
        );
    }

    $acaoPedida = $_POST['acao'] ?? '';

    // ===== Atualização de vida =====
    if ($acaoPedida === 'vida') {
        $variacao = (int) ($_POST['variacao'] ?? 0);
        $variacoesPermitidas = [-10, -5, -1, 1, 5, 10];

        if (!in_array($variacao, $variacoesPermitidas, true)) {
            http_response_code(422);

            responder_acao(
                ['ok' => false, 'mensagem' => 'Variação de vida inválida.'],
                $paginaDeVolta,
                $pedidoAssincrono
            );
        }

        if ($tipoFicha === 'personagem') {
            $maximo = max(1, (int) ($ficha['PV_MAXIMO'] ?? $ficha['PV'] ?? 1));
            $atual = max(0, min($maximo, (int) ($ficha['PV'] ?? 0) + $variacao));

            $atualizar = $conexao->prepare(
                'UPDATE PERSONAGEM
                 SET PV = ?
                 WHERE COD_PERSONAGEM = ? AND COD_USUARIO_FK = ?'
            );

            $atualizar->execute([$atual, $codigoFicha, $codigoUsuario]);
        } else {
            $maximo = max(1, (int) ($ficha['PV'] ?? 1));

            if ($ficha['COD_USUARIO_FK'] === null) {
                $chaveVida = (string) $codigoFicha;
                $atual = (int) ($_SESSION['vidas_criaturas'][$chaveVida] ?? $maximo);
                $atual = max(0, min($maximo, $atual + $variacao));

                $_SESSION['vidas_criaturas'][$chaveVida] = $atual;
            } else {
                $atual = max(
                    0,
                    min($maximo, (int) ($ficha['PV_ATUAL'] ?? $maximo) + $variacao)
                );

                $atualizar = $conexao->prepare(
                    'UPDATE CRIATURA
                     SET PV_ATUAL = ?
                     WHERE COD_CRIATURA = ? AND COD_USUARIO_FK = ?'
                );

                $atualizar->execute([$atual, $codigoFicha, $codigoUsuario]);
            }
        }

        $percentual = (int) round(($atual / $maximo) * 100);

        responder_acao(
            [
                'ok' => true,
                'tipo' => 'vida',
                'vida_atual' => $atual,
                'vida_maxima' => $maximo,
                'percentual' => $percentual,
                'mensagem' => "Pontos de vida atualizados: {$atual}/{$maximo}.",
            ],
            $paginaDeVolta,
            $pedidoAssincrono
        );
    }

    // ===== Rolagens =====
    if ($acaoPedida === 'rolar') {
        $valorDado = random_int(1, 20);
        $bonusDoTeste = 0;
        $nomeDoTeste = 'Teste livre';

        if ($tipoFicha === 'personagem') {
            $periciaEscolhida = (string) ($_POST['teste'] ?? '');
            $pericias = lista_pericias();

            if (isset($pericias[$periciaEscolhida])) {
                [$nomeDoTeste, $atributo] = $pericias[$periciaEscolhida];
                $colunaAtributo = 'ATRIBUTO_' . $atributo;

                $bonusDoTeste = modificador_atributo(
                    (int) ($ficha[$colunaAtributo] ?? 10)
                );

                $grauProficiencia = max(
                    0,
                    min(2, (int) ($ficha[$periciaEscolhida] ?? 0))
                );

                if ($grauProficiencia > 0) {
                    $bonusDoTeste += bonus_proficiencia(
                        (int) ($ficha['NIVEL'] ?? 1)
                    ) * $grauProficiencia;
                }
            }
        } else {
            $atributos = [
                'forca' => ['Força', 'ATRIBUTO_FOR'],
                'destreza' => ['Destreza', 'ATRIBUTO_DES'],
                'constituicao' => ['Constituição', 'ATRIBUTO_CONST'],
                'inteligencia' => ['Inteligência', 'ATRIBUTO_INT'],
                'sabedoria' => ['Sabedoria', 'ATRIBUTO_SAB'],
                'carisma' => ['Carisma', 'ATRIBUTO_CAR'],
            ];

            $periciaEscolhida = (string) ($_POST['teste'] ?? '');

            if (isset($atributos[$periciaEscolhida])) {
                [$nomeDoTeste, $colunaAtributo] = $atributos[$periciaEscolhida];
                $nomeDoTeste = 'Teste de ' . $nomeDoTeste;

                $bonusDoTeste = modificador_atributo(
                    (int) ($ficha[$colunaAtributo] ?? 10)
                );
            }
        }

        $bonusExtra = max(-20, min(20, (int) ($_POST['bonus_livre'] ?? 0)));
        $bonusFinal = $bonusDoTeste + $bonusExtra;
        $resultadoFinal = $valorDado + $bonusFinal;
        $critico = $valorDado === 20;
        $falha = $valorDado === 1;

        $formula = ['D20 (' . $valorDado . ')'];

        if ($bonusDoTeste !== 0) {
            $formula[] = $nomeDoTeste . ' (' . formatar_modificador($bonusDoTeste) . ')';
        } elseif ($nomeDoTeste !== 'Teste livre') {
            $formula[] = $nomeDoTeste . ' (+0)';
        }

        if ($bonusExtra !== 0) {
            $formula[] = 'Bônus livre (' . formatar_modificador($bonusExtra) . ')';
        }

        $formulaCompleta = implode(' + ', $formula) . ' = ' . $resultadoFinal;
        $chaveHistorico = $tipoFicha . '_' . $codigoFicha;

        if (!isset($_SESSION['historico_rolagens'][$chaveHistorico])) {
            $_SESSION['historico_rolagens'][$chaveHistorico] = [];
        }

        array_unshift(
            $_SESSION['historico_rolagens'][$chaveHistorico],
            [
                'nome' => $nomeDoTeste,
                'dado' => $valorDado,
                'bonus' => $bonusFinal,
                'total' => $resultadoFinal,
                'formula' => $formulaCompleta,
                'hora' => date('H:i'),
                'critico' => $critico,
                'falha' => $falha,
            ]
        );

        $_SESSION['historico_rolagens'][$chaveHistorico] = array_slice(
            $_SESSION['historico_rolagens'][$chaveHistorico],
            0,
            10
        );

        $mensagem = $formulaCompleta;

        if ($critico) {
            $mensagem .= ' · Sucesso crítico';
        } elseif ($falha) {
            $mensagem .= ' · Falha crítica';
        }

        responder_acao(
            [
                'ok' => true,
                'tipo' => 'rolagem',
                'dado' => $valorDado,
                'bonus_base' => $bonusDoTeste,
                'bonus_livre' => $bonusExtra,
                'bonus_total' => $bonusFinal,
                'total' => $resultadoFinal,
                'nome_teste' => $nomeDoTeste,
                'formula' => $formulaCompleta,
                'mensagem' => $mensagem,
                'critico' => $critico,
                'falha' => $falha,
                'historico' => $_SESSION['historico_rolagens'][$chaveHistorico],
            ],
            $paginaDeVolta,
            $pedidoAssincrono
        );
    }

    http_response_code(422);

    responder_acao(
        ['ok' => false, 'mensagem' => 'Ação desconhecida.'],
        $paginaDeVolta,
        $pedidoAssincrono
    );
} catch (Throwable $erro) {
    error_log('PortalToFantasy - ação de ficha: ' . $erro->getMessage());
    http_response_code(500);

    responder_acao(
        [
            'ok' => false,
            'mensagem' => 'Não foi possível concluir a ação. O servidor registrou o erro para diagnóstico.',
        ],
        $paginaDeVolta,
        $pedidoAssincrono
    );
}
