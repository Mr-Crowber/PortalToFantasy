<?php
// ===== Sessão e proteção =====
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function escapar_html(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function redirecionar(string $destino): never
{
    header('Location: ' . $destino);
    exit;
}

function esta_logado(): bool
{
    return isset($_SESSION['usuario_id']);
}

function exigir_login(): void
{
    if (!esta_logado()) {
        definir_aviso('Entre na sua conta para acessar o portal.', 'aviso');
        redirecionar('index.php');
    }
}

function definir_aviso(string $mensagem, string $tipo = 'sucesso'): void
{
    $_SESSION['aviso'] = [
        'mensagem' => $mensagem,
        'tipo' => $tipo,
    ];
}

function classe_aviso(string $tipo): string
{
    return match ($tipo) {
        'sucesso' => 'aviso-sucesso',
        'erro' => 'aviso-erro',
        default => 'aviso-alerta',
    };
}

function obter_aviso(): ?array
{
    if (!isset($_SESSION['aviso'])) {
        return null;
    }

    $aviso = $_SESSION['aviso'];
    unset($_SESSION['aviso']);

    return $aviso;
}

function token_csrf(): string
{
    if (empty($_SESSION['token_csrf'])) {
        $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['token_csrf'];
}

function verificar_csrf(): void
{
    $token = $_POST['token_csrf'] ?? '';
    $tokenDaSessao = $_SESSION['token_csrf'] ?? '';

    if (!is_string($token) || !hash_equals($tokenDaSessao, $token)) {
        http_response_code(419);
        exit('O formulário expirou. Volte à página e tente novamente.');
    }
}

// ===== Texto e ícones =====
function tamanho_texto(string $texto): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($texto, 'UTF-8');
    }

    preg_match_all('/./us', $texto, $caracteres);

    return count($caracteres[0]);
}

function primeiro_caractere(string $texto): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($texto, 0, 1, 'UTF-8');
    }

    return preg_match('/^./us', $texto, $resultado) ? $resultado[0] : '';
}

function icone(string $nome, string $classe = ''): string
{
    $desenhos = [
        'inicio' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
        'pessoas' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'pergaminho' => '<path d="M8 21h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><path d="M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 1-2-2z"/><path d="M8 8h6M8 12h6M8 16h3"/>',
        'caveira' => '<circle cx="12" cy="10" r="8"/><path d="M8 16v5h8v-5M9 10h.01M15 10h.01M9 14h6"/>',
        'livro' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
        'mais' => '<path d="M12 5v14M5 12h14"/>',
        'buscar' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'sair' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>',
        'espadas' => '<path d="m14.5 17.5 3 3L22 16l-3-3M8 8l8 8M3 7l4-4 4 4-4 4zM13 5l6-3 3 3-3 6"/><path d="m2 22 7-7"/>',
        'vida' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/>',
        'escudo' => '<path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>',
        'dado' => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 8h.01M16 8h.01M12 12h.01M8 16h.01M16 16h.01"/>',
        'folha' => '<path d="M20 4c-7 0-13 2-15 8-1.5 4.5 2 8 6 7 6-1.5 9-8 9-15Z"/><path d="M4 21c3-5 7-8 13-11"/>',
        'editar' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>',
        'lixeira' => '<path d="M3 6h18M8 6V4h8v2m3 0-1 15H6L5 6m4 4v7m6-7v7"/>',
        'seta' => '<path d="M5 12h14m-7-7 7 7-7 7"/>',
        'voltar' => '<path d="M19 12H5m7 7-7-7 7-7"/>',
        'usuario' => '<circle cx="12" cy="8" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/>',
        'fechar' => '<path d="m18 6-12 12M6 6l12 12"/>',
        'imagem' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>',
        'estrela' => '<path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9Z"/>',
    ];

    $desenho = $desenhos[$nome] ?? $desenhos['folha'];

    return '<svg class="icone ' . escapar_html($classe)
        . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        . ' stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"'
        . ' aria-hidden="true">' . $desenho . '</svg>';
}

// ===== Regras de D&D =====
function modificador_atributo(int $valor): int
{
    return (int) floor(($valor - 10) / 2);
}

function formatar_modificador(int $valor): string
{
    return ($valor >= 0 ? '+' : '') . $valor;
}

function bonus_proficiencia(int $nivel): int
{
    return 2 + (int) floor(max(0, $nivel - 1) / 4);
}

function lista_pericias(): array
{
    return [
        'PERICIA_ACROBACIA' => ['Acrobacia', 'DES'],
        'PERICIA_ADESTRAR_ANIMAIS' => ['Adestrar animais', 'SAB'],
        'PERICIA_ARCANISMO' => ['Arcanismo', 'INT'],
        'PERICIA_ATLETISMO' => ['Atletismo', 'FOR'],
        'PERICIA_ATUACAO' => ['Atuação', 'CAR'],
        'PERICIA_ENGANACAO' => ['Enganação', 'CAR'],
        'PERICIA_FURTIVIDADE' => ['Furtividade', 'DES'],
        'PERICIA_HISTORIA' => ['História', 'INT'],
        'PERICIA_INTIMIDACAO' => ['Intimidação', 'CAR'],
        'PERICIA_INTUICAO' => ['Intuição', 'SAB'],
        'PERICIA_INVESTIGACAO' => ['Investigação', 'INT'],
        'PERICIA_MEDICINA' => ['Medicina', 'SAB'],
        'PERICIA_NATUREZA' => ['Natureza', 'INT'],
        'PERICIA_PERCEPCAO' => ['Percepção', 'SAB'],
        'PERICIA_PERSUASAO' => ['Persuasão', 'CAR'],
        'PERICIA_PRESTIDIGITACAO' => ['Prestidigitação', 'DES'],
        'PERICIA_RELIGIAO' => ['Religião', 'INT'],
        'PERICIA_SOBREVIVENCIA' => ['Sobrevivência', 'SAB'],
    ];
}

// ===== Imagens =====
function caminho_imagem(string $caminho, string $tipo): string
{
    $inicioEsperado = 'arquivos/' . $tipo . '/';

    if ($caminho !== '' && str_starts_with($caminho, $inicioEsperado)) {
        return $caminho;
    }

    return '';
}

function salvar_imagem_enviada(
    string $campo,
    string $tipo,
    string $imagemAtual = ''
): string {
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return $imagemAtual;
    }

    $arquivo = $_FILES[$campo];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Não foi possível receber a imagem. Tente novamente.');
    }

    if ((int) $arquivo['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('A imagem deve ter no máximo 2 MB.');
    }

    $informacoesImagem = @getimagesize($arquivo['tmp_name']);

    if (!$informacoesImagem) {
        throw new RuntimeException('O arquivo enviado não é uma imagem válida.');
    }

    $formatosPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    $tipoMime = $informacoesImagem['mime'] ?? '';

    if (!isset($formatosPermitidos[$tipoMime])) {
        throw new RuntimeException('Use uma imagem JPG, PNG, WEBP ou GIF.');
    }

    $pasta = dirname(__DIR__) . '/arquivos/' . $tipo;

    if (!is_dir($pasta) && !mkdir($pasta, 0755, true) && !is_dir($pasta)) {
        throw new RuntimeException('Não foi possível preparar a pasta de imagens.');
    }

    $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $formatosPermitidos[$tipoMime];
    $caminhoDestino = $pasta . '/' . $nomeArquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
        throw new RuntimeException('Não foi possível salvar a imagem enviada.');
    }

    return 'arquivos/' . $tipo . '/' . $nomeArquivo;
}

function remover_imagem_enviada(string $caminho, string $tipo): void
{
    $caminho = caminho_imagem($caminho, $tipo);

    if ($caminho === '') {
        return;
    }

    $arquivo = dirname(__DIR__) . '/' . $caminho;

    if (is_file($arquivo)) {
        unlink($arquivo);
    }
}

// ===== Criaturas e documentos =====
function imagem_padrao_criatura(string $nome): string
{
    $nomeNormalizado = strtolower(strtr($nome, [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a',
        'é' => 'e', 'ê' => 'e', 'í' => 'i',
        'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c',
    ]));

    if (str_contains($nomeNormalizado, 'goblin')) {
        return 'https://upload.wikimedia.org/wikipedia/commons/e/eb/Goblins.png';
    }

    if (str_contains($nomeNormalizado, 'lobo')) {
        return 'https://upload.wikimedia.org/wikipedia/commons/f/f6/Canis_rufus_walking_in_forest_cropped.jpg';
    }

    if (str_contains($nomeNormalizado, 'esqueleto')) {
        return 'https://upload.wikimedia.org/wikipedia/commons/9/90/Skeleton-warrior.jpg';
    }

    return 'imagens/textura-pergaminho.webp';
}

function processar_imagens_do_documento(string $conteudoHtml): string
{
    $quantidadeImagens = 0;

    $conteudoHtml = preg_replace_callback(
        '~src=(["\'])data:image/(jpeg|png|webp|gif);base64,([^"\']+)\1~i',
        function (array $trechos) use (&$quantidadeImagens): string {
            if ($quantidadeImagens >= 3) {
                return 'src=""';
            }

            $dadosImagem = base64_decode($trechos[3], true);

            if ($dadosImagem === false || strlen($dadosImagem) > 1000000) {
                return 'src=""';
            }

            $informacoesImagem = @getimagesizefromstring($dadosImagem);

            if (!$informacoesImagem) {
                return 'src=""';
            }

            $formatosPermitidos = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];

            $tipoMime = $informacoesImagem['mime'] ?? '';

            if (!isset($formatosPermitidos[$tipoMime])) {
                return 'src=""';
            }

            $pasta = dirname(__DIR__) . '/arquivos/documentos';

            if (!is_dir($pasta) && !mkdir($pasta, 0755, true) && !is_dir($pasta)) {
                return 'src=""';
            }

            $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $formatosPermitidos[$tipoMime];

            if (file_put_contents($pasta . '/' . $nomeArquivo, $dadosImagem) === false) {
                return 'src=""';
            }

            $quantidadeImagens++;

            return 'src="arquivos/documentos/' . $nomeArquivo . '"';
        },
        $conteudoHtml
    );

    return $conteudoHtml ?? '';
}

function limpar_html_documento(string $conteudoHtml): string
{
    if (trim($conteudoHtml) === '') {
        return '';
    }

    $conteudoHtml = preg_replace(
        '~<(script|style|iframe|object|embed|svg|math|form|video|audio)\\b[^>]*>.*?</\\1\\s*>~is',
        '',
        $conteudoHtml
    ) ?? '';

    $conteudoHtml = preg_replace(
        '~<(script|style|iframe|object|embed|svg|math|form|video|audio)\\b[^>]*/?>~is',
        '',
        $conteudoHtml
    ) ?? '';

    $tagsAceitas = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'h2', 'h3', 'h4',
        'ul', 'ol', 'li', 'blockquote', 'hr', 'img', 'a',
    ];

    if (!class_exists(DOMDocument::class)) {
        return preg_replace_callback(
            '~<!--.*?-->|</?[a-zA-Z][^>]*>~s',
            function (array $trecho) use ($tagsAceitas): string {
                $tagAtual = $trecho[0];

                if (str_starts_with($tagAtual, '<!--')) {
                    return '';
                }

                if (!preg_match('~^<\\s*(/?)\\s*([a-zA-Z0-9]+)\\b([^>]*)>~s', $tagAtual, $partes)) {
                    return '';
                }

                $fechamento = $partes[1] === '/';
                $nomeTag = strtolower($partes[2]);
                $atributos = $partes[3];

                if (!in_array($nomeTag, $tagsAceitas, true)) {
                    return '';
                }

                if ($fechamento) {
                    return in_array($nomeTag, ['br', 'hr', 'img'], true)
                        ? ''
                        : '</' . $nomeTag . '>';
                }

                if ($nomeTag === 'img') {
                    preg_match("~\\bsrc\\s*=\\s*(?:\"([^\"]*)\"|\\'([^\\']*)\\'|([^\\s>]+))~i", $atributos, $origemImagem);
                    preg_match("~\\balt\\s*=\\s*(?:\"([^\"]*)\"|\\'([^\\']*)\\'|([^\\s>]+))~i", $atributos, $textoAlternativo);

                    $caminho = $origemImagem[1] ?? $origemImagem[2] ?? $origemImagem[3] ?? '';
                    $descricao = $textoAlternativo[1] ?? $textoAlternativo[2] ?? $textoAlternativo[3] ?? '';

                    if (!preg_match('~^arquivos/documentos/[a-zA-Z0-9._-]+$~', $caminho)) {
                        return '';
                    }

                    return '<img src="' . escapar_html($caminho)
                        . '" alt="' . escapar_html($descricao) . '">';
                }

                if ($nomeTag === 'a') {
                    preg_match("~\\bhref\\s*=\\s*(?:\"([^\"]*)\"|\\'([^\\']*)\\'|([^\\s>]+))~i", $atributos, $destinoLink);
                    $endereco = $destinoLink[1] ?? $destinoLink[2] ?? $destinoLink[3] ?? '';

                    if (preg_match('~^(https?://|mailto:|/)~i', $endereco)) {
return '<a href="' . escapar_html($endereco) . '">';
                    }

    return '<a>';
                }

                return '<' . $nomeTag . '>';
            },
            $conteudoHtml
        ) ?? '';
    }

    $estadoAnterior = libxml_use_internal_errors(true);
    $documento = new DOMDocument('1.0', 'UTF-8');
    $documento->loadHTML(
        '<?xml encoding="UTF-8">
        <html>
            <body>
                <div>'
        . $conteudoHtml
                . '</div>
            </body>
        </html>'
    );

    $raiz = $documento->getElementsByTagName('div')->item(0);

    if (!$raiz) {
        libxml_clear_errors();
        libxml_use_internal_errors($estadoAnterior);

        return nl2br(escapar_html(strip_tags($conteudoHtml)));
    }

    $reconstruir = function (DOMNode $no) use (&$reconstruir, $tagsAceitas): string {
        $saida = '';

        foreach ($no->childNodes as $filho) {
            if ($filho instanceof DOMText) {
                $saida .= escapar_html($filho->nodeValue ?? '');
                continue;
            }

            if (!$filho instanceof DOMElement) {
                continue;
            }

            $nomeTag = strtolower($filho->tagName);

            if (!in_array($nomeTag, $tagsAceitas, true)) {
                $saida .= $reconstruir($filho);
                continue;
            }

            if ($nomeTag === 'img') {
                $caminho = trim($filho->getAttribute('src'));
                $descricao = trim($filho->getAttribute('alt'));

                if (preg_match('~^arquivos/documentos/[a-zA-Z0-9._-]+$~', $caminho)) {
                    $saida .= '<img src="' . escapar_html($caminho)
                        . '" alt="' . escapar_html($descricao) . '">';
                }

                continue;
            }

            if ($nomeTag === 'a') {
                $endereco = trim($filho->getAttribute('href'));
                $conteudo = $reconstruir($filho);

                if (preg_match('~^(https?://|mailto:|/)~i', $endereco)) {
        $saida .= '<a href="' . escapar_html($endereco) . '">' . $conteudo . '</a>';
                } else {
                    $saida .= $conteudo;
                }

                continue;
            }

            $conteudo = $reconstruir($filho);

            if (in_array($nomeTag, ['br', 'hr'], true)) {
                $saida .= '<' . $nomeTag . '>';
            } else {
                $saida .= '<' . $nomeTag . '>' . $conteudo . '</' . $nomeTag . '>';
            }
        }

        return $saida;
    };

    $resultado = $reconstruir($raiz);
    libxml_clear_errors();
    libxml_use_internal_errors($estadoAnterior);

    return $resultado;
}

function exibir_conteudo_documento(string $conteudo): string
{
    if (strip_tags($conteudo) === $conteudo) {
        return nl2br(escapar_html($conteudo));
    }

    return limpar_html_documento($conteudo);
}
