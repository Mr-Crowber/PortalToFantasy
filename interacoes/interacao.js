(() => {
    'use strict';

    // Editor de documentos
    const formularioDocumento = document.getElementById('formulario-documento');
    const editorDocumento = document.getElementById('editor-documento');
    const campoConteudo = document.getElementById('conteudo-documento');
    const seletorImagem = document.getElementById('arquivo-imagem-documento');
    const botaoImagem = document.getElementById('botao-inserir-imagem');
    let selecaoSalva = null;

    function guardarSelecao() {
        const selecao = window.getSelection();

        if (!selecao || !selecao.rangeCount || !editorDocumento?.contains(selecao.anchorNode)) {
            return;
        }

        selecaoSalva = selecao.getRangeAt(0).cloneRange();
    }

    function recuperarSelecao() {
        if (!selecaoSalva || !editorDocumento) return;

        editorDocumento.focus();
        const selecao = window.getSelection();
        selecao.removeAllRanges();
        selecao.addRange(selecaoSalva);
    }

    function aplicarFormatacao(botao) {
        if (!editorDocumento) return;

        editorDocumento.focus();
        document.execCommand(botao.dataset.comando, false, botao.dataset.valor || null);
        guardarSelecao();
    }

    function inserirImagem(arquivo) {
        const formatosPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!formatosPermitidos.includes(arquivo.type)) {
            window.alert('Escolha uma imagem JPG, PNG, WEBP ou GIF.');
            return;
        }

        if (arquivo.size > 1000000) {
            window.alert('A imagem deve ter no máximo 1 MB.');
            return;
        }

        if (editorDocumento.querySelectorAll('img').length >= 3) {
            window.alert('Cada documento pode conter até 3 imagens.');
            return;
        }

        const leitor = new FileReader();
        leitor.addEventListener('load', () => {
            recuperarSelecao();
            document.execCommand(
                'insertHTML',
                false,
                `<p><img src="${leitor.result}" alt="Imagem do documento"></p><p><br></p>`
            );
            guardarSelecao();
        });
        leitor.readAsDataURL(arquivo);
    }

    if (formularioDocumento && editorDocumento && campoConteudo) {
        document.querySelectorAll('[data-comando]').forEach((botao) => {
            botao.addEventListener('mousedown', (evento) => evento.preventDefault());
            botao.addEventListener('click', () => aplicarFormatacao(botao));
        });

        editorDocumento.addEventListener('keyup', guardarSelecao);
        editorDocumento.addEventListener('mouseup', guardarSelecao);

        if (botaoImagem && seletorImagem) {
            botaoImagem.addEventListener('mousedown', (evento) => evento.preventDefault());
            botaoImagem.addEventListener('click', () => {
                guardarSelecao();
                seletorImagem.click();
            });
            seletorImagem.addEventListener('change', () => {
                const arquivo = seletorImagem.files[0];
                if (arquivo) inserirImagem(arquivo);
                seletorImagem.value = '';
            });
        }

        formularioDocumento.addEventListener('submit', () => {
            campoConteudo.value = editorDocumento.innerHTML;
        });
    }

    // Vida e rolagens das fichas
    function esperar(tempo) {
        return new Promise((resolver) => window.setTimeout(resolver, tempo));
    }

    function encontrarAreaDeRolagem(formulario) {
        return formulario.closest('.painel-rolagem')
            || formulario.closest('.area-dados-criatura')
            || document;
    }

    async function animarDado(area) {
        const dado = area.querySelector('[data-dado-visual]');
        const estado = area.querySelector('[data-estado-dado]');

        if (!dado) return;

        dado.classList.remove('esta-rolando', 'esta-assentando');
        void dado.offsetWidth;
        dado.classList.add('esta-rolando');
        dado.setAttribute('aria-busy', 'true');

        if (estado) estado.textContent = 'ROLANDO...';

        await esperar(560);
        dado.classList.remove('esta-rolando');
        dado.classList.add('esta-assentando');
        await esperar(100);
        dado.classList.remove('esta-assentando');
        dado.removeAttribute('aria-busy');
    }

    function atualizarVida(dados) {
        document.querySelectorAll('[data-pv-atual]').forEach((elemento) => {
            elemento.textContent = dados.vida_atual;
        });

        document.querySelectorAll('[data-pv-maximo]').forEach((elemento) => {
            elemento.textContent = dados.vida_maxima;
        });

        document.querySelectorAll('[data-pv-bar]').forEach((elemento) => {
            elemento.style.width = `${dados.percentual}%`;
        });

        document.querySelectorAll('[data-pv-label]').forEach((elemento) => {
            elemento.textContent = `${dados.percentual}% da vida`;
        });

        document.querySelectorAll('[role="progressbar"]').forEach((elemento) => {
            elemento.setAttribute('aria-valuemax', dados.vida_maxima);
            elemento.setAttribute('aria-valuenow', dados.vida_atual);
        });

        document.querySelectorAll('[data-pv-feedback]').forEach((elemento) => {
            elemento.textContent = dados.mensagem;
            elemento.classList.add('esta-atualizado');
            window.setTimeout(() => elemento.classList.remove('esta-atualizado'), 1400);
        });
    }

    function atualizarResultado(area, dados) {
        const painel = area.querySelector('[data-resultado-rolagem]');
        if (!painel) return;

        const rotulo = document.createElement('span');
        rotulo.textContent = 'ÚLTIMO RESULTADO';

        const total = document.createElement('strong');
        total.textContent = dados.total;
        total.className = dados.critico
            ? 'resultado-critico'
            : (dados.falha ? 'resultado-falha-critica' : '');

        const detalhe = document.createElement('small');
        detalhe.textContent = dados.formula + (
            dados.critico ? ' · Sucesso crítico' : (dados.falha ? ' · Falha crítica' : '')
        );

        painel.replaceChildren(rotulo, total, detalhe);
    }

    function atualizarHistorico(area, historico) {
        const lista = area.querySelector('[data-historico-rolagens]');
        if (!lista) return;

        lista.replaceChildren();
        area.querySelector('[data-historico-vazio]')?.remove();

        const recentes = historico.slice(0, 5);
        const contador = area.querySelector('[data-quantidade-historico]');
        if (contador) contador.textContent = recentes.length;

        recentes.forEach((rolagem) => {
            const linha = document.createElement('li');
            const descricao = document.createElement('div');
            const nome = document.createElement('strong');
            const formula = document.createElement('small');
            const total = document.createElement('b');

            linha.className = 'item-historico-dados';
            if (rolagem.critico) linha.classList.add('esta-critico');
            else if (rolagem.falha) linha.classList.add('esta-falha-critica');

            descricao.className = 'conteudo-rolagem-historico';
            nome.textContent = rolagem.nome;
            formula.textContent = `${rolagem.hora} · ${rolagem.formula}`;
            total.textContent = rolagem.total;
            descricao.append(nome, formula);
            linha.append(descricao, total);
            lista.append(linha);
        });
    }

    function mostrarErro(area, mensagem) {
        const estado = area.querySelector('[data-estado-dado]');
        const painel = area.querySelector('[data-resultado-rolagem]');

        if (estado) estado.textContent = 'ROLAGEM NÃO CONCLUÍDA';
        if (!painel) return;

        const rotulo = document.createElement('span');
        rotulo.textContent = 'NÃO FOI POSSÍVEL CONCLUIR';
        const detalhe = document.createElement('small');
        detalhe.textContent = mensagem;
        painel.replaceChildren(rotulo, detalhe);
    }

    async function enviarAcao(evento) {
        const formulario = evento.target.closest('form[data-envio-assincrono]');
        if (!formulario) return;

        evento.preventDefault();
        if (formulario.dataset.enviando === '1') return;
        formulario.dataset.enviando = '1';

        const botao = evento.submitter || formulario.querySelector('button[type="submit"]');
        const dadosFormulario = new FormData(formulario);
        const acao = dadosFormulario.get('acao');
        const area = acao === 'rolar' ? encontrarAreaDeRolagem(formulario) : document;
        const animacao = acao === 'rolar' ? animarDado(area) : Promise.resolve();

        if (botao) botao.disabled = true;

        try {
            const resposta = await fetch(formulario.action, {
                method: 'POST',
                body: dadosFormulario,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const tipoResposta = resposta.headers.get('content-type') || '';
            const texto = await resposta.text();

            if (!tipoResposta.toLowerCase().includes('application/json')) {
                throw new Error(
                    resposta.status === 401
                        ? 'Sua sessão expirou. Entre novamente no PortalToFantasy.'
                        : `O servidor respondeu de forma inesperada (HTTP ${resposta.status}).`
                );
            }

            let dados;
            try {
                dados = JSON.parse(texto);
            } catch {
                throw new Error('O servidor enviou uma resposta inválida. Confira o log do PHP.');
            }

            await animacao;
            if (!resposta.ok || !dados.ok) {
                throw new Error(dados.mensagem || 'Não foi possível concluir a ação.');
            }

            if (dados.tipo === 'vida') {
                atualizarVida(dados);
                return;
            }

            if (dados.tipo === 'rolagem') {
                const face = area.querySelector('[data-valor-dado]');
                const estado = area.querySelector('[data-estado-dado]');
                const dado = area.querySelector('[data-dado-visual]');

                if (face) face.textContent = dados.dado;
                if (estado) {
                    estado.textContent = dados.critico
                        ? 'SUCESSO CRÍTICO'
                        : (dados.falha ? 'FALHA CRÍTICA' : 'ROLAGEM CONCLUÍDA');
                }

                dado?.classList.toggle('tem-critico', Boolean(dados.critico));
                dado?.classList.toggle('tem-falha-critica', Boolean(dados.falha));
                atualizarResultado(area, dados);
                atualizarHistorico(area, dados.historico || []);
            }
        } catch (erro) {
            await animacao;
            mostrarErro(area, erro.message);
        } finally {
            formulario.dataset.enviando = '0';
            if (botao) botao.disabled = false;
        }
    }

    document.addEventListener('submit', enviarAcao);
})();
