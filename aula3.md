# Aula 3 — POST e envio de formulários

> ETEC — Sexta à noite — 4 horas
> Hoje você vai sair sabendo: **enviar dados ao servidor** com `$.post`, ler um formulário inteiro com `.serialize()`, validar antes de enviar e — pela primeira vez — **ver o dado salvar de verdade**.
> Pasta de trabalho: `lab23/`
> API: estreia do **`curso.chr.eti.br/ajax/api.php`** (professor mantém, SQLite por trás).

---

## Mudança importante: backend de verdade

Até agora a gente usou **JSONPlaceholder**. Você até podia fazer POST nela, mas ela **fingia** — devolvia status 201 e um objeto com id, sem salvar nada. Hoje a gente troca por uma API **que salva mesmo**.

### A API do curso

URL: **`https://curso.chr.eti.br/ajax/api.php`**

Recurso único: **tarefas** (id, aluno_id, titulo, feito, criado_em).

| O que faz             | Como                                                       |
|-----------------------|------------------------------------------------------------|
| Listar suas tarefas   | `GET    api.php?aluno_id=ana123`                           |
| Pegar uma tarefa      | `GET    api.php?aluno_id=ana123&id=7`                      |
| Criar tarefa          | `POST   api.php` + corpo `{aluno_id, titulo}`              |
| Atualizar (aula 5)    | `PUT    api.php?id=7` + corpo `{aluno_id, titulo, feito}`  |
| Apagar (aula 5)       | `DELETE api.php?id=7&aluno_id=ana123`                      |

### `aluno_id` — o que é

Como a turma toda usa o mesmo servidor, a API **filtra tudo** por `aluno_id`. **Cada um escolhe a sua identificação** e usa em todas as chamadas. Sem login, sem senha — não é segurança, é organização.

Sugestão de formato: **`primeironome + 3 dígitos`** (ex: `ana123`, `bruno007`, `gustavo542`).

Hoje você digita seu `aluno_id` uma vez, ele vai pro `localStorage`, e fica salvo no navegador para sempre. Toda chamada AJAX hoje (e nas próximas 2 aulas) vai mandar esse `aluno_id` junto.

---

## 3.1 — Teoria: `localStorage` em uma página (10 min)

`localStorage` é uma caixa de chave-valor **dentro do navegador** que persiste mesmo depois de fechar o navegador. Vocês já viram em JS puro:

```javascript
localStorage.setItem('aluno_id', 'ana123');
var id = localStorage.getItem('aluno_id');   // 'ana123'
localStorage.removeItem('aluno_id');         // apaga
```

É **separado por site** (chr.eti.br não vê o que google.com guarda). Só guarda **string**.

---

## 3.2 — Laboratório: Tela de identificação (15 min)

Crie **`lab23/ex01-identidade.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex01 — Quem é você?</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 60px auto; text-align: center; }
        input { padding: 10px; font-size: 18px; width: 80%; }
        button { padding: 10px 20px; font-size: 16px; }
        #atual { color: #06f; font-weight: bold; font-size: 22px; }
        .box { padding: 20px; border: 1px solid #ddd; border-radius: 6px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Quem é você na turma?</h1>

    <div class="box">
        <p>Seu ID atual: <span id="atual">(nenhum)</span></p>

        <input type="text" id="novo" placeholder="ex: ana123" maxlength="20">
        <br><br>
        <button id="salvar">Salvar</button>
        <button id="esquecer">Esquecer</button>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        function mostrarAtual() {
            var id = localStorage.getItem('aluno_id');
            $('#atual').text(id ? id : '(nenhum)');
        }

        mostrarAtual();

        $('#salvar').on('click', function() {
            var novo = $('#novo').val().trim();
            if (!novo) {
                alert('Digite alguma coisa.');
                return;
            }
            localStorage.setItem('aluno_id', novo);
            $('#novo').val('');
            mostrarAtual();
        });

        $('#esquecer').on('click', function() {
            localStorage.removeItem('aluno_id');
            mostrarAtual();
        });
    </script>
</body>
</html>
```

**Abra. Digite seu id (algo como `seunome123`). Clique Salvar.** O id aparece em azul. Feche o navegador, abra de novo, abra o arquivo — **continua lá**.

Isso vale pra todos os arquivos do `lab23/` em diante: a gente lê do `localStorage` sem perguntar de novo.

---

## 3.3 — Teoria: POST com `$.post` (10 min)

`$.post` é o atalho para enviar dados:

```javascript
$.post(url, dados, function(resposta) {
    // resposta = o que o servidor mandou de volta
});
```

- `dados` é um objeto JS. jQuery transforma em `chave=valor&chave=valor` (form URL-encoded).
- O servidor recebe, faz o que tem que fazer, e devolve algo (geralmente JSON com o que salvou).

Mas pra **API REST moderna**, é comum mandar **JSON** no corpo. Aí o atalho `$.post` não dá conta — precisa do `$.ajax` completo (que vamos ver na Aula 4). **Hoje a gente envia como objeto, e a `api.php` foi feita pra aceitar das duas formas.**

### O fluxo mental

```
[Página]                   [Servidor api.php]              [SQLite]
   |                            |                              |
   |--POST com {aluno_id, titulo}->                            |
   |                            |--INSERT INTO tarefas-------->|
   |                            |<-id novo--------------------|
   |<--JSON com {id, aluno_id, titulo, feito, criado_em}------|
   |                            |                              |
[atualiza UI]
```

---

## 3.4 — Laboratório: Criar uma tarefa (25 min)

Crie **`lab23/ex02-criar.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex02 — Criar tarefa</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 30px auto; }
        input { padding: 8px; font-size: 16px; width: 70%; }
        button { padding: 8px 16px; font-size: 16px; }
        #resultado {
            margin-top: 20px; padding: 12px;
            border-left: 4px solid #06f; background: #f0f6ff;
        }
        .erro { color: crimson; border-color: crimson !important; background: #fff0f0; }
    </style>
</head>
<body>
    <h1>Criar tarefa</h1>
    <p>Logado como: <strong id="quem"></strong></p>

    <input type="text" id="titulo" placeholder="Ex: Estudar AJAX">
    <button id="criar">Criar</button>

    <div id="resultado">A resposta do servidor aparece aqui.</div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var ALUNO_ID = localStorage.getItem('aluno_id');

        if (!ALUNO_ID) {
            alert('Vá no ex01-identidade.html primeiro e cadastre seu id!');
        }
        $('#quem').text(ALUNO_ID || '(sem id)');

        $('#criar').on('click', function() {
            var titulo = $('#titulo').val().trim();
            if (!titulo) {
                $('#resultado').addClass('erro').text('Digite um título!');
                return;
            }

            var dados = { aluno_id: ALUNO_ID, titulo: titulo };

            $.post('https://curso.chr.eti.br/ajax/api.php', dados, function(tarefa) {
                $('#resultado').removeClass('erro').html(
                    '<strong>Tarefa criada!</strong><br>' +
                    'id: ' + tarefa.id + '<br>' +
                    'título: ' + tarefa.titulo + '<br>' +
                    'criada em: ' + tarefa.criado_em
                );
                $('#titulo').val('');
            }, 'json');   // 'json' = espera resposta em JSON
        });
    </script>
</body>
</html>
```

**Abra. Digite "Estudar AJAX". Clique Criar.** Você verá:

```
Tarefa criada!
id: 14
título: Estudar AJAX
criada em: 2026-05-16 19:42:08
```

**Crie 3 tarefas.** Cada uma ganha um id novo.

Abra a **Network (F12)**: na requisição você vê o corpo `aluno_id=ana123&titulo=Estudar+AJAX`. Na resposta, JSON.

---

## 3.5 — Teoria: Ler formulários (10 min)

Em vez de pegar campo por campo com `.val()`:

```javascript
var dados = {
    aluno_id: ALUNO_ID,
    titulo: $('#titulo').val(),
    prioridade: $('#prio').val(),
    prazo: $('#prazo').val()
};
```

Você pode usar **`.serialize()`** num `<form>` inteiro — ele monta a string `name1=val1&name2=val2` automaticamente, **usando o atributo `name`** de cada campo.

```html
<form id="f">
    <input name="titulo">
    <input name="prazo" type="date">
</form>
```

```javascript
$('#f').serialize();
// "titulo=Estudar+AJAX&prazo=2026-05-20"
```

Ou ainda **`.serializeArray()`** que devolve um array de `{name, value}` — útil para validar.

**A `api.php` aceita os dois jeitos.**

---

## 3.6 — Laboratório: Formulário com `.serialize()` + lista que atualiza (35 min)

Aqui o pulo do gato: depois de criar, **buscar a lista atualizada** e re-renderizar. Você cria → vê na lista, sem F5.

Crie **`lab23/ex03-form-lista.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex03 — Form + lista</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 30px auto; }
        form {
            background: #f5f8ff; padding: 14px; border-radius: 6px;
            display: flex; gap: 8px; align-items: center;
        }
        form input { padding: 8px; font-size: 16px; flex: 1; }
        form button { padding: 8px 16px; font-size: 16px; }
        .tarefa {
            display: flex; gap: 10px; padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .tarefa.feita { text-decoration: line-through; color: #888; }
        .vazio { color: #888; font-style: italic; padding: 20px 0; }
        .erro { color: crimson; }
    </style>
</head>
<body>
    <h1>Minhas tarefas</h1>
    <p>Logado como: <strong id="quem"></strong></p>

    <form id="f">
        <input type="text" name="titulo" placeholder="Nova tarefa..." autofocus>
        <button type="submit">Adicionar</button>
    </form>

    <h2>Lista</h2>
    <div id="lista" class="vazio">Carregando...</div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var ALUNO_ID = localStorage.getItem('aluno_id');
        var API = 'https://curso.chr.eti.br/ajax/api.php';
        $('#quem').text(ALUNO_ID || '(sem id — passe pelo ex01)');

        // ===== LISTAR =====
        function carregarTarefas() {
            $('#lista').removeClass('erro').text('Carregando...');

            $.getJSON(API + '?aluno_id=' + encodeURIComponent(ALUNO_ID), function(tarefas) {
                if (tarefas.length === 0) {
                    $('#lista').addClass('vazio').text('Nenhuma tarefa ainda.');
                    return;
                }

                $('#lista').removeClass('vazio').empty();

                $.each(tarefas, function(i, t) {
                    var $row = $('<div>').addClass('tarefa');
                    if (t.feito == 1) $row.addClass('feita');
                    $row.append($('<span>').text('#' + t.id + ' — ' + t.titulo));
                    $('#lista').append($row);
                });
            });
        }

        // ===== CRIAR =====
        $('#f').on('submit', function(e) {
            e.preventDefault();   // não recarrega a página!

            var titulo = $('input[name="titulo"]', this).val().trim();
            if (!titulo) return;

            // Monta dados manualmente (precisa juntar o aluno_id com o form)
            var dados = $(this).serialize() + '&aluno_id=' + encodeURIComponent(ALUNO_ID);

            $.post(API, dados, function(tarefa) {
                $('input[name="titulo"]', '#f').val('').focus();
                carregarTarefas();   // recarrega a lista
            }, 'json');
        });

        // Ao abrir, já busca
        carregarTarefas();
    </script>
</body>
</html>
```

**Abra.** Em um navegador limpo (sem tarefas suas ainda), você verá "Nenhuma tarefa ainda."

**Crie "Estudar AJAX", "Fazer compras", "Ligar pro Pedro".** Cada submit:

1. Envia POST.
2. Limpa o campo.
3. Recarrega a lista.

A tarefa aparece na lista **instantaneamente**, sem F5. E se você fechar o navegador e abrir de novo: ainda lá. **Salvou de verdade.**

### O detalhe do `e.preventDefault()`

Form HTML, por padrão, **recarrega a página** no submit. Isso destrói tudo que o AJAX fez. `e.preventDefault()` segura esse comportamento.

---

## 3.7 — Teoria: Validação antes de enviar (10 min)

Quanto mais cedo você impedir um envio inútil, melhor. Regras típicas:

- Campo obrigatório vazio? Não envia.
- Tamanho mínimo / máximo? Confere.
- Formato (email, número)? Confere.

```javascript
var titulo = $('#titulo').val().trim();

if (titulo.length < 3) {
    $('#erro').text('Mínimo 3 caracteres.');
    return;
}

if (titulo.length > 100) {
    $('#erro').text('Máximo 100 caracteres.');
    return;
}
```

Visualmente: **pinte a borda do campo de vermelho** e mostre a mensagem perto, para o aluno **ver** o que está errado.

---

## 3.8 — Laboratório: Form com validação visual (25 min)

Crie **`lab23/ex04-validacao.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex04 — Validação</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 30px auto; }
        .campo { margin: 12px 0; }
        .campo label { display: block; margin-bottom: 4px; font-size: 14px; }
        .campo input {
            padding: 8px; font-size: 16px; width: 100%;
            border: 2px solid #ccc; border-radius: 4px;
        }
        .campo.erro input { border-color: crimson; background: #fff0f0; }
        .campo.erro .msg { color: crimson; font-size: 13px; margin-top: 4px; }
        .campo .msg { display: none; }
        .campo.erro .msg { display: block; }

        button { padding: 10px 20px; font-size: 16px; }
        #ok { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Cadastrar tarefa (com validação)</h1>
    <p>Logado como: <strong id="quem"></strong></p>

    <div class="campo" id="campoTitulo">
        <label>Título da tarefa:</label>
        <input type="text" id="titulo">
        <div class="msg" id="msgTitulo"></div>
    </div>

    <button id="enviar">Enviar</button>
    <span id="ok"></span>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var ALUNO_ID = localStorage.getItem('aluno_id');
        var API = 'https://curso.chr.eti.br/ajax/api.php';
        $('#quem').text(ALUNO_ID || '(sem id)');

        $('#enviar').on('click', function() {
            $('#ok').text('');
            $('#campoTitulo').removeClass('erro');

            var titulo = $('#titulo').val().trim();

            if (titulo.length < 3) {
                $('#campoTitulo').addClass('erro');
                $('#msgTitulo').text('Mínimo 3 caracteres.');
                return;
            }
            if (titulo.length > 80) {
                $('#campoTitulo').addClass('erro');
                $('#msgTitulo').text('Máximo 80 caracteres.');
                return;
            }

            // Validou. Envia.
            var dados = { aluno_id: ALUNO_ID, titulo: titulo };

            $.post(API, dados, function(tarefa) {
                $('#ok').text('✓ Salva — id ' + tarefa.id);
                $('#titulo').val('');
            }, 'json');
        });

        // Limpa erro assim que o aluno volta a digitar
        $('#titulo').on('input', function() {
            $('#campoTitulo').removeClass('erro');
        });
    </script>
</body>
</html>
```

**Teste:**

1. Clique "Enviar" com o campo vazio → borda vermelha, "Mínimo 3 caracteres".
2. Digite "ab" → ainda 3 caracteres faltando.
3. Comece a digitar → o erro some.
4. Digite "Comprar pão" → "✓ Salva — id N".

**Bug clássico que evitamos:** o aluno tenta enviar vazio, vê o erro, digita certo, e o erro continua na tela. O `.on('input', ...)` limpa o erro assim que ele mexe.

---

## Cheatsheet da Aula 3

```javascript
// IDENTIDADE
localStorage.setItem('aluno_id', 'ana123');
var id = localStorage.getItem('aluno_id');

// POST
$.post(url, { campo1: 'x', campo2: 'y' }, function(resp){ ... }, 'json');

// FORM
$('#form').on('submit', function(e) {
    e.preventDefault();    // evita recarregar
    var dados = $(this).serialize();
    $.post(url, dados, callback, 'json');
});

// VALIDAÇÃO VISUAL
$('#campo').addClass('erro');
$('#campo').removeClass('erro');
$('#campo').on('input', function(){ $(this).removeClass('erro'); });

// URL com aluno_id
var url = API + '?aluno_id=' + encodeURIComponent(ALUNO_ID);
```

---

## 🏆 Desafio da Aula 3

Crie **`lab23/desafio.html`** — **agenda de contatos**.

A `api.php` só conhece o recurso "tarefas" — mas a gente **abusa do campo `titulo`** pra guardar tudo de um contato como uma string concatenada:

```
"Pedro Silva | pedro@email.com | (11) 99999-0000"
```

### Requisitos

1. Formulário com 3 campos: **nome**, **email**, **telefone**.
2. Validação:
   - nome ≥ 2 caracteres
   - email contém `@`
   - telefone ≥ 8 caracteres
3. Ao enviar, junta os 3 numa string `"nome | email | telefone"` e cria via `$.post`.
4. Lista abaixo mostra os contatos, **separando os campos de novo** (use `.split(' | ')`).
5. A lista atualiza sozinha depois de cada cadastro.

### Visual

```
+----------------------------------+
| Agenda                           |
+----------------------------------+
| Nome:    [_________________]     |
| Email:   [_________________]     |
| Telefone:[_________________]     |
| [ Adicionar contato ]            |
+----------------------------------+
| Contatos:                        |
|  ─ Pedro Silva                   |
|     pedro@email.com — (11) 9999 |
|  ─ Ana Costa                     |
|     ana@email.com — (21) 8888   |
+----------------------------------+
```

### Bônus

- Conte quantos contatos a lista tem ("3 contatos").
- Use 3 `<div class="campo">` com estilo de erro igual ao Ex04.

---

Na **Aula 4** vamos abandonar o `$.post` e ir pra forma **completa** `$.ajax({...})` — controlando timeout, headers, e principalmente **o que fazer quando dá erro** (404, 500, internet caída).
