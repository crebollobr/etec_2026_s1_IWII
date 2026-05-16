# Aula 4 — `$.ajax` completo, erros e UX

> ETEC — Sexta à noite — 4 horas
> Hoje você vai sair sabendo: usar `$.ajax({...})` com **todas** as opções, lidar com **erros de verdade** (404, 500, internet caída), aplicar `timeout`, organizar callbacks com `.done/.fail/.always`, componentizar páginas com `.load()`, e montar **vários** loadings ao mesmo tempo.
> Pasta de trabalho: `lab24/`
> API: continua a `curso.chr.eti.br/ajax/api.php`.

---

## O problema dos atalhos

`$.get`, `$.post`, `$.getJSON` são **atalhos**. Funcionam ótimo no caminho feliz. Mas você não tem como:

- Definir `timeout` (e se a rede demorar 30s?).
- Reagir a erro de forma diferente do sucesso.
- Mandar JSON puro no corpo (a `api.php` aceita, mas APIs REST modernas exigem).
- Mandar `PUT` ou `DELETE` (atalhos só fazem GET/POST).
- Adicionar headers customizados.

Pra tudo isso: **`$.ajax({...})`**.

---

## 4.1 — Teoria: A forma completa (15 min)

```javascript
$.ajax({
    url: 'https://curso.chr.eti.br/ajax/api.php',
    method: 'GET',             // ou POST, PUT, DELETE
    data: { aluno_id: 'ana123' },
    dataType: 'json',          // o que esperar de volta
    timeout: 5000,             // 5 segundos no máximo
    success: function(resp) {
        // chamado se deu certo
    },
    error: function(xhr, status, erro) {
        // chamado se deu errado
    },
    complete: function() {
        // chamado sempre, no fim (sucesso ou erro)
    }
});
```

### As opções principais

| Opção         | Pra que serve                                                  |
|---------------|----------------------------------------------------------------|
| `url`         | endereço                                                        |
| `method`      | `GET`, `POST`, `PUT`, `DELETE`                                  |
| `data`        | objeto que vira `?a=1&b=2` (GET) ou corpo (POST)                |
| `dataType`    | `'json'`, `'html'`, `'text'`                                    |
| `contentType` | tipo do corpo enviado (`'application/json'` etc.)               |
| `timeout`     | milissegundos antes de desistir                                 |
| `headers`     | objeto com headers extras                                       |
| `success`     | callback de sucesso                                             |
| `error`       | callback de erro                                                |
| `complete`    | callback que sempre roda                                        |

### Os 3 callbacks

Em ordem de chamada:

1. **success** OU **error** (um dos dois)
2. **complete** (sempre)

`complete` é útil pra esconder loading ("acabou, com sucesso ou não").

---

## 4.2 — Laboratório: GET completo com loading e erro (25 min)

Crie **`lab24/ex01-ajax-completo.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex01 — $.ajax</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 30px auto; }
        button { padding: 8px 14px; font-size: 16px; }
        #estado {
            margin: 14px 0; padding: 10px 14px;
            border-radius: 4px; font-style: italic;
        }
        .info { background: #eef; color: #06f; }
        .ok   { background: #efe; color: #060; font-style: normal; }
        .err  { background: #fee; color: crimson; font-style: normal; }
        .tarefa { padding: 8px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <h1>$.ajax completo</h1>
    <p>Logado como: <strong id="quem"></strong></p>

    <button id="carregar">Carregar minhas tarefas</button>
    <button id="erroFake">Forçar erro 404</button>
    <button id="timeoutFake">Forçar timeout</button>

    <div id="estado"></div>
    <div id="lista"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var ALUNO_ID = localStorage.getItem('aluno_id');
        var API = 'https://curso.chr.eti.br/ajax/api.php';
        $('#quem').text(ALUNO_ID);

        function setEstado(classe, msg) {
            $('#estado').attr('class', classe).text(msg);
        }

        $('#carregar').on('click', function() {
            setEstado('info', 'Carregando...');
            $('#lista').empty();

            $.ajax({
                url: API,
                method: 'GET',
                data: { aluno_id: ALUNO_ID },
                dataType: 'json',
                timeout: 5000,
                success: function(tarefas) {
                    setEstado('ok', 'Recebido ' + tarefas.length + ' tarefa(s).');
                    $.each(tarefas, function(i, t) {
                        $('#lista').append(
                            $('<div>').addClass('tarefa').text('#' + t.id + ' — ' + t.titulo)
                        );
                    });
                },
                error: function(xhr, status, erro) {
                    setEstado('err', 'Falhou: ' + status + ' — ' + (erro || 'sem detalhes'));
                },
                complete: function() {
                    console.log('complete: terminou (com sucesso ou erro)');
                }
            });
        });

        // URL inexistente — força 404
        $('#erroFake').on('click', function() {
            setEstado('info', 'Tentando URL inexistente...');
            $('#lista').empty();

            $.ajax({
                url: 'https://curso.chr.eti.br/ajax/QUE-NAO-EXISTE.php',
                method: 'GET',
                dataType: 'json',
                timeout: 5000,
                success: function() {
                    setEstado('ok', 'Era pra dar erro...');
                },
                error: function(xhr, status, erro) {
                    setEstado('err', 'Erro esperado! status HTTP: ' + xhr.status + ' — ' + erro);
                }
            });
        });

        // Timeout artificial: 1ms
        $('#timeoutFake').on('click', function() {
            setEstado('info', 'Pedindo com timeout absurdo de 1ms...');
            $('#lista').empty();

            $.ajax({
                url: API,
                method: 'GET',
                data: { aluno_id: ALUNO_ID },
                dataType: 'json',
                timeout: 1,
                error: function(xhr, status, erro) {
                    setEstado('err', 'Erro: ' + status);   // status = "timeout"
                }
            });
        });
    </script>
</body>
</html>
```

**Teste os 3 botões:**

1. **Carregar** → vê "Carregando..." (azul) → "Recebido N tarefas" (verde) → lista aparece.
2. **Forçar 404** → "Erro esperado! status HTTP: 404 — Not Found" (vermelho).
3. **Forçar timeout** → "Erro: timeout" (vermelho), quase imediato.

Cada caso fica em **cor diferente**. O aluno vê.

### O parâmetro `xhr` no error

`xhr` é o **objeto da requisição**. Os campos úteis:

```javascript
xhr.status         // 404, 500, 0 (se rede caiu)…
xhr.statusText     // "Not Found", "Internal Server Error"…
xhr.responseText   // o que veio no corpo (texto)
xhr.responseJSON   // se veio JSON, já parseado
```

---

## 4.3 — Teoria: `.done`, `.fail`, `.always` (15 min)

`$.ajax` devolve um **objeto promise**. Em vez de passar `success/error/complete` dentro do objeto de configuração, dá pra **encadear**:

```javascript
$.ajax({ url: API, dataType: 'json' })
    .done(function(resp) {
        // sucesso
    })
    .fail(function(xhr) {
        // erro
    })
    .always(function() {
        // sempre
    });
```

Funciona com `$.get`, `$.post`, `$.getJSON` também:

```javascript
$.getJSON(url)
    .done(function(dados) { ... })
    .fail(function(xhr) { ... });
```

### Por que isso é melhor

- Lê de cima pra baixo, sem aninhar.
- Pode adicionar mais `.done` depois (em outros pontos do código).
- Combina bem com várias requisições em paralelo (próximo bloco).

---

## 4.4 — Laboratório: Reescrevendo com `.done/.fail` (15 min)

Crie **`lab24/ex02-promises.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex02 — .done .fail</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 30px auto; }
        button { padding: 8px 14px; }
        #log { font-family: monospace; background: #111; color: #0f0;
               padding: 12px; min-height: 100px; border-radius: 4px; }
        #log div.err { color: #f66; }
    </style>
</head>
<body>
    <h1>.done / .fail / .always</h1>

    <button id="ok">Tentar URL boa</button>
    <button id="ruim">Tentar URL ruim</button>

    <h3>Log:</h3>
    <div id="log"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var ALUNO_ID = localStorage.getItem('aluno_id');
        var API = 'https://curso.chr.eti.br/ajax/api.php';

        function log(msg, erro) {
            var $linha = $('<div>').text('[' + new Date().toLocaleTimeString() + '] ' + msg);
            if (erro) $linha.addClass('err');
            $('#log').append($linha);
        }

        $('#ok').on('click', function() {
            log('iniciando GET...');

            $.getJSON(API + '?aluno_id=' + ALUNO_ID)
                .done(function(tarefas) {
                    log('✓ recebido ' + tarefas.length + ' tarefa(s)');
                })
                .fail(function(xhr) {
                    log('✗ falhou: ' + xhr.status, true);
                })
                .always(function() {
                    log('— fim —');
                });
        });

        $('#ruim').on('click', function() {
            log('tentando URL inexistente...');

            $.getJSON('https://curso.chr.eti.br/ajax/NAO-EXISTE.php')
                .done(function() {
                    log('era pra ter dado errado');
                })
                .fail(function(xhr) {
                    log('✗ status ' + xhr.status + ' — esperado', true);
                })
                .always(function() {
                    log('— fim —');
                });
        });
    </script>
</body>
</html>
```

**Aperte os botões.** O log preto-verde mostra cada passo. Falhas em vermelho. Note a ordem: `done` OU `fail` aparece primeiro, **e depois sempre `always`**.

---

## 4.5 — Teoria: Várias requisições em paralelo (10 min)

Quando você precisa de dados de **dois lugares** ao mesmo tempo, **não encadeie** (lento). Lance os dois **juntos**.

```javascript
var req1 = $.getJSON('https://jsonplaceholder.typicode.com/users');
var req2 = $.getJSON('https://jsonplaceholder.typicode.com/posts');

$.when(req1, req2).done(function(resp1, resp2) {
    var usuarios = resp1[0];   // ← cuidado: vem como [dados, status, xhr]
    var posts    = resp2[0];
    // tudo chegou
});
```

Ou trate cada um separadamente:

```javascript
$.getJSON('url-a').done(function(a) { renderA(a); }).fail(mostraErroA);
$.getJSON('url-b').done(function(b) { renderB(b); }).fail(mostraErroB);
```

Esse segundo jeito é mais simples e dá feedback **independente** (uma falha não derruba a outra). É o que a gente vai usar.

---

## 4.6 — Laboratório: Mini-dashboard com 3 chamadas paralelas (35 min)

Crie **`lab24/ex03-dashboard.html`**. Vamos buscar de 3 fontes diferentes — tarefas (api do curso), usuários (JSONPlaceholder) e posts (JSONPlaceholder) — e mostrar cada um num "widget" próprio:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex03 — Dashboard</title>
    <style>
        body { font-family: sans-serif; max-width: 1100px; margin: 30px auto; }
        h1 { margin-bottom: 16px; }
        #grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .widget {
            background: #fff; border: 1px solid #ddd;
            border-radius: 6px; padding: 14px;
            min-height: 200px;
        }
        .widget h2 { margin: 0 0 10px; font-size: 18px; }
        .estado {
            font-style: italic;
            padding: 8px 0;
        }
        .info { color: #06f; }
        .err  { color: crimson; font-style: normal; }
        ul { margin: 0; padding: 0 0 0 18px; }
        li { padding: 3px 0; }
    </style>
</head>
<body>
    <h1>Painel de controle</h1>

    <div id="grid">
        <div class="widget">
            <h2>Minhas tarefas</h2>
            <div class="estado info" id="estado-t">Carregando...</div>
            <ul id="lista-t"></ul>
        </div>

        <div class="widget">
            <h2>Usuários (placeholder)</h2>
            <div class="estado info" id="estado-u">Carregando...</div>
            <ul id="lista-u"></ul>
        </div>

        <div class="widget">
            <h2>Posts (placeholder)</h2>
            <div class="estado info" id="estado-p">Carregando...</div>
            <ul id="lista-p"></ul>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var ALUNO_ID = localStorage.getItem('aluno_id');
        var API = 'https://curso.chr.eti.br/ajax/api.php';

        // ===== TAREFAS =====
        $.ajax({
            url: API,
            data: { aluno_id: ALUNO_ID },
            dataType: 'json',
            timeout: 5000
        })
        .done(function(tarefas) {
            $('#estado-t').removeClass('info').text(tarefas.length + ' tarefa(s)');
            $.each(tarefas, function(i, t) {
                $('#lista-t').append($('<li>').text(t.titulo));
            });
        })
        .fail(function(xhr) {
            $('#estado-t').removeClass('info').addClass('err')
                .text('Erro: ' + (xhr.status || 'rede'));
        });

        // ===== USUÁRIOS =====
        $.ajax({
            url: 'https://jsonplaceholder.typicode.com/users',
            dataType: 'json',
            timeout: 5000
        })
        .done(function(usuarios) {
            $('#estado-u').removeClass('info').text(usuarios.length + ' usuários');
            $.each(usuarios, function(i, u) {
                $('#lista-u').append($('<li>').text(u.name));
            });
        })
        .fail(function(xhr) {
            $('#estado-u').removeClass('info').addClass('err')
                .text('Erro: ' + xhr.status);
        });

        // ===== POSTS =====
        $.ajax({
            url: 'https://jsonplaceholder.typicode.com/posts?_limit=5',
            dataType: 'json',
            timeout: 5000
        })
        .done(function(posts) {
            $('#estado-p').removeClass('info').text(posts.length + ' posts');
            $.each(posts, function(i, p) {
                $('#lista-p').append($('<li>').text(p.title));
            });
        })
        .fail(function(xhr) {
            $('#estado-p').removeClass('info').addClass('err')
                .text('Erro: ' + xhr.status);
        });
    </script>
</body>
</html>
```

**Abra.** Os 3 widgets dizem "Carregando..." em azul, e — em paralelo — vão sendo preenchidos. Se um falhar, ele fica em **vermelho**, mas **os outros continuam** funcionando. Isso é o **isolamento** que a UI precisa ter.

**Teste a robustez:** troque uma das URLs por algo errado. Só um widget pega vermelho.

---

## 4.7 — Teoria: `.load()` — o atalho preguiçoso (10 min)

`.load(url)` baixa **HTML** de uma URL e enfia direto dentro de um elemento. Útil pra "componentizar" — mesmo header, mesmo menu, mesmo rodapé em várias páginas.

```javascript
$('#topo').load('header.html');
$('#rodape').load('footer.html');
```

> **Atenção:** `.load()` chama o servidor — então precisa **abrir via http**, não `file://`. Use Live Server do VS Code, ou rode `python3 -m http.server` na pasta e acesse `http://localhost:8000/...`.

Você pode até pegar **só um pedaço** da página remota usando seletor:

```javascript
$('#topo').load('header.html #nav');   // só o elemento #nav do arquivo
```

---

## 4.8 — Laboratório: Componentizando com `.load()` (20 min)

Crie 3 arquivos:

**`lab24/header.html`:**

```html
<nav style="background:#222; color:#fff; padding:12px 20px;">
    <strong>ETEC AJAX 2026</strong> &nbsp;|&nbsp; Home &nbsp;|&nbsp; Aulas &nbsp;|&nbsp; Contato
</nav>
```

**`lab24/footer.html`:**

```html
<footer style="background:#eee; padding:10px 20px; margin-top:30px; color:#666; font-size:13px;">
    © 2026 — Curso AJAX da ETEC
</footer>
```

**`lab24/ex04-componentes.html`:**

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex04 — .load()</title>
    <style>
        body { margin: 0; font-family: sans-serif; }
        main { max-width: 800px; margin: 0 auto; padding: 20px; }
    </style>
</head>
<body>
    <div id="topo"></div>

    <main>
        <h1>Página com header/footer carregados</h1>
        <p>Esta página tem 5 linhas de conteúdo. O resto vem de fora.</p>
        <p>Veja a aba <strong>Network</strong> (F12): você verá 3 requisições — esta página, o header.html e o footer.html.</p>
    </main>

    <div id="rodape"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $('#topo').load('header.html');
        $('#rodape').load('footer.html');
    </script>
</body>
</html>
```

**Rode via servidor** (não `file://`!):

```bash
cd lab24
python3 -m http.server 8000
```

Abra `http://localhost:8000/ex04-componentes.html`. Você verá o header preto em cima, conteúdo no meio, footer cinza embaixo.

**Mude o `header.html`** (adicione um link), salve, F5 — todas as páginas que usam header.html mudam juntas. **Esse é o ganho.**

---

## 4.9 — Teoria: Bug clássico de retry (10 min)

Erro de rede acontece. Boa UI: **mostrar o erro + botão "Tentar de novo"**.

```javascript
function carregar() {
    $('#estado').text('Carregando...').removeClass('err');
    $('#tentar').hide();

    $.ajax({ url: API, dataType: 'json', timeout: 5000 })
        .done(function(d) {
            // mostra dados
        })
        .fail(function() {
            $('#estado').text('Falhou. Tente de novo.').addClass('err');
            $('#tentar').show();
        });
}

$('#tentar').on('click', carregar);
carregar();
```

O usuário vê "Falhou" → aperta "Tentar de novo" → roda de novo. Sem F5.

---

## Cheatsheet da Aula 4

```javascript
// $.ajax completo
$.ajax({
    url: ..., method: 'GET', data: {...},
    dataType: 'json', timeout: 5000,
    headers: { 'X-Custom': 'valor' }
})
.done(function(resp){ ... })
.fail(function(xhr){ console.log(xhr.status, xhr.statusText); })
.always(function(){ ... });

// xhr no error
xhr.status          // 200, 404, 500, 0 (rede caiu)
xhr.statusText      // "Not Found"
xhr.responseText
xhr.responseJSON

// Várias em paralelo
$.getJSON(u1).done(renderA).fail(mostraErroA);
$.getJSON(u2).done(renderB).fail(mostraErroB);

// Componentizar
$('#topo').load('header.html');     // requer servidor (não file://)
```

---

## 🏆 Desafio da Aula 4

Crie **`lab24/desafio.html`** — **monitor de saúde da API**.

### Requisitos

Página com **3 botões grandes**, um abaixo do outro, cada um representando um teste:

1. **Testar API do curso** — `GET api.php?aluno_id=...`
2. **Testar JSONPlaceholder** — `GET https://jsonplaceholder.typicode.com/posts/1`
3. **Testar URL inexistente** — `GET https://curso.chr.eti.br/ajax/NAO-EXISTE.php`

Para cada teste, ao clicar:

- Mostrar "⏳ Testando..." (amarelo) ao lado do botão.
- Quando voltar: **status HTTP** + **tempo em ms** + **✓ ou ✗**.
- Verde se sucesso, vermelho se erro.

### Dica: medir tempo

```javascript
var inicio = Date.now();
$.ajax({...})
    .always(function() {
        var ms = Date.now() - inicio;
        // mostra ms na tela
    });
```

### Bônus

- Botão "**Testar todos**" que dispara os 3 ao mesmo tempo (em paralelo).
- Mostra o status de cada um em **vermelho/verde** conforme retorna.
- Quando todos voltarem, mostra "✓ Bateria completa" embaixo.

### Visual sugerido

```
+----------------------------------------------+
| Monitor de saúde                             |
+----------------------------------------------+
| [ Testar API do curso     ]  ⏳ Testando...   |
| [ Testar JSONPlaceholder  ]  ✓ 200 — 142ms   |
| [ Testar URL inexistente  ]  ✗ 404 — 89ms    |
|                                              |
| [ Testar todos ]                             |
+----------------------------------------------+
```

---

Na **Aula 5** vamos completar o ciclo: **PUT** (editar), **DELETE** (apagar), e montar o **projeto final** — um gerenciador de tarefas com CRUD completo.
