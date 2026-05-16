# Aula 5 — CRUD completo e projeto integrado

> ETEC — Sexta à noite — 4 horas
> Hoje você fecha o ciclo: **PUT** (editar), **DELETE** (apagar), organização do código em funções, e o projeto final — um **gerenciador de tarefas** com CRUD completo, lista, edição inline, marcar como feito, apagar, e busca.
> Pasta de trabalho: `lab25/`
> API: `curso.chr.eti.br/ajax/api.php`.

---

## O que falta do CRUD

CRUD = **C**reate, **R**ead, **U**pdate, **D**elete. Até agora:

| Sigla     | Método HTTP | Aula      | Onde                          |
|-----------|-------------|-----------|-------------------------------|
| **C**reate | POST       | Aula 3    | criar tarefa                  |
| **R**ead   | GET        | Aulas 1–3 | listar / buscar tarefa        |
| **U**pdate | PUT        | **Aula 5** | editar título, marcar feito  |
| **D**elete | DELETE     | **Aula 5** | apagar                       |

PUT e DELETE não têm atalho no jQuery (não existe `$.put` nem `$.delete`). Vai ser tudo `$.ajax({ method: 'PUT', ... })`.

---

## 5.1 — Teoria: PUT (15 min)

PUT = "**substituir** o recurso de id X pelos dados que estou enviando".

```javascript
$.ajax({
    url: 'https://curso.chr.eti.br/ajax/api.php?id=7',
    method: 'PUT',
    data: JSON.stringify({ aluno_id: 'ana123', titulo: 'Comprar leite', feito: 1 }),
    contentType: 'application/json',
    dataType: 'json'
})
.done(function(tarefa) {
    // 'tarefa' = a versão atualizada que o servidor devolveu
});
```

### Detalhes que mudam em relação ao POST

- O id da tarefa vai **na query string** (`?id=7`), não no corpo.
- O corpo é **JSON** (`JSON.stringify(...)` + `contentType: 'application/json'`) — a `api.php` do curso espera assim para PUT.
- A resposta é a tarefa **inteira atualizada**.

### A versão "marcar como feito"

A `api.php` aceita atualização parcial:

```javascript
$.ajax({
    url: API + '?id=7',
    method: 'PUT',
    data: JSON.stringify({ aluno_id: ALUNO_ID, feito: 1 }),
    contentType: 'application/json',
    dataType: 'json'
});
```

Mandou só `feito: 1` → marca como feito sem mudar título.

---

## 5.2 — Laboratório: Editor de tarefa (30 min)

Crie **`lab25/ex01-editar.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex01 — Editar tarefa</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 30px auto; }
        .linha { display: flex; gap: 8px; margin: 6px 0; align-items: center; }
        input { padding: 6px; font-size: 15px; }
        button { padding: 6px 14px; }
        .feita { text-decoration: line-through; color: #888; }
        #msg { color: green; }
    </style>
</head>
<body>
    <h1>Editar tarefa</h1>
    <p>Logado como: <strong id="quem"></strong></p>

    <div class="linha">
        <label>ID da tarefa:</label>
        <input type="number" id="id">
        <button id="carregar">Carregar</button>
    </div>

    <div class="linha">
        <label>Título:</label>
        <input type="text" id="titulo" style="flex:1">
    </div>

    <div class="linha">
        <label><input type="checkbox" id="feito"> feita</label>
    </div>

    <div class="linha">
        <button id="salvar">Salvar alterações</button>
        <span id="msg"></span>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var ALUNO_ID = localStorage.getItem('aluno_id');
        var API = 'https://curso.chr.eti.br/ajax/api.php';
        $('#quem').text(ALUNO_ID);

        $('#carregar').on('click', function() {
            var id = $('#id').val();
            if (!id) return;

            $.getJSON(API + '?aluno_id=' + ALUNO_ID + '&id=' + id)
                .done(function(t) {
                    $('#titulo').val(t.titulo);
                    $('#feito').prop('checked', t.feito == 1);
                    $('#msg').text('Carregada.');
                })
                .fail(function(xhr) {
                    $('#msg').css('color', 'crimson').text('Não achei essa tarefa (id ' + id + ').');
                });
        });

        $('#salvar').on('click', function() {
            var id = $('#id').val();
            if (!id) return;

            var dados = {
                aluno_id: ALUNO_ID,
                titulo: $('#titulo').val(),
                feito: $('#feito').is(':checked') ? 1 : 0
            };

            $.ajax({
                url: API + '?id=' + id,
                method: 'PUT',
                data: JSON.stringify(dados),
                contentType: 'application/json',
                dataType: 'json'
            })
            .done(function(t) {
                $('#msg').css('color', 'green').text('✓ Salvo: "' + t.titulo + '"');
            })
            .fail(function(xhr) {
                $('#msg').css('color', 'crimson').text('Erro: ' + xhr.status);
            });
        });
    </script>
</body>
</html>
```

**Teste:** se você criou tarefas na Aula 3, digite um id existente, clique **Carregar** — campos preenchem. Mude o título, marque "feita", clique **Salvar**.

**Confira** que salvou de verdade: vá no ex03 da Aula 3 (a lista), recarregue, e veja a tarefa com o novo título e riscada (porque o CSS `.feita` aparece quando `feito == 1`).

---

## 5.3 — Teoria: DELETE (10 min)

```javascript
$.ajax({
    url: API + '?id=7&aluno_id=' + ALUNO_ID,
    method: 'DELETE',
    dataType: 'json'
})
.done(function(resp) {
    // resp = { ok: true, id: 7 } (algo assim — a api.php manda confirmação)
});
```

**Confirmação SEMPRE:** apagar é destrutivo. Use `confirm()` do navegador, ou um modal próprio. Nunca apague sem perguntar.

```javascript
if (!confirm('Apagar essa tarefa?')) return;
```

---

## 5.4 — Laboratório: Apagar com confirmação (20 min)

Crie **`lab25/ex02-apagar.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex02 — Apagar</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 30px auto; }
        .tarefa {
            display: flex; gap: 10px; align-items: center;
            padding: 8px; border-bottom: 1px solid #eee;
        }
        .tarefa .titulo { flex: 1; }
        .tarefa.removendo {
            background: #fee; opacity: 0.5; transition: 0.3s;
        }
        button.del {
            padding: 4px 10px; background: crimson; color: #fff;
            border: 0; border-radius: 4px; cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Apagador de tarefas</h1>
    <p>Logado como: <strong id="quem"></strong></p>
    <div id="lista">Carregando...</div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var ALUNO_ID = localStorage.getItem('aluno_id');
        var API = 'https://curso.chr.eti.br/ajax/api.php';
        $('#quem').text(ALUNO_ID);

        function carregar() {
            $('#lista').text('Carregando...');

            $.getJSON(API + '?aluno_id=' + ALUNO_ID)
                .done(function(tarefas) {
                    if (tarefas.length === 0) {
                        $('#lista').text('Sem tarefas. Use o lab23 pra criar algumas.');
                        return;
                    }

                    $('#lista').empty();

                    $.each(tarefas, function(i, t) {
                        var $row = $('<div>').addClass('tarefa').attr('data-id', t.id);
                        $row.append($('<span>').addClass('titulo')
                            .text('#' + t.id + ' — ' + t.titulo));

                        var $del = $('<button>').addClass('del').text('Apagar');
                        $del.on('click', function() {
                            if (!confirm('Apagar "' + t.titulo + '"?')) return;

                            $row.addClass('removendo');

                            $.ajax({
                                url: API + '?id=' + t.id + '&aluno_id=' + ALUNO_ID,
                                method: 'DELETE',
                                dataType: 'json'
                            })
                            .done(function() {
                                $row.slideUp(300, function() { $(this).remove(); });
                            })
                            .fail(function(xhr) {
                                $row.removeClass('removendo');
                                alert('Erro ao apagar: ' + xhr.status);
                            });
                        });

                        $row.append($del);
                        $('#lista').append($row);
                    });
                });
        }

        carregar();
    </script>
</body>
</html>
```

**Teste.** Crie tarefas (use o lab23 ex03 se faltar), abra esta página, aperte **Apagar**, confirme. A linha:

1. Fica com fundo vermelho-claro (visual de "removendo").
2. Recolhe com `.slideUp`.
3. Some.

Sem F5. Se você abrir de novo, ela não está mais.

---

## 5.5 — Teoria: Organizando o código (15 min)

Até agora o JavaScript ficou tudo numa pilha dentro do `<script>`. Pra apps maiores, isso vira caos. Separe em **funções**:

```javascript
// Configurações
var API = 'https://curso.chr.eti.br/ajax/api.php';
var ALUNO_ID = localStorage.getItem('aluno_id');

// Funções de API (só falam com servidor)
function listarTarefas() { return $.getJSON(API + '?aluno_id=' + ALUNO_ID); }
function criarTarefa(titulo) { return $.post(API, {aluno_id: ALUNO_ID, titulo: titulo}, null, 'json'); }
function atualizarTarefa(id, dados) {
    return $.ajax({
        url: API + '?id=' + id, method: 'PUT',
        data: JSON.stringify($.extend({aluno_id: ALUNO_ID}, dados)),
        contentType: 'application/json', dataType: 'json'
    });
}
function apagarTarefa(id) {
    return $.ajax({
        url: API + '?id=' + id + '&aluno_id=' + ALUNO_ID,
        method: 'DELETE', dataType: 'json'
    });
}

// Funções de UI (só desenham)
function renderizar(tarefas) { ... }
function mostrarErro(msg) { ... }

// Lógica que liga uma coisa na outra (eventos + chamadas)
function recarregar() {
    listarTarefas().done(renderizar).fail(mostrarErro);
}

$('#form').on('submit', function(e) {
    e.preventDefault();
    var titulo = $('#novo').val().trim();
    if (!titulo) return;
    criarTarefa(titulo).done(function() {
        $('#novo').val('');
        recarregar();
    });
});

// Boot
recarregar();
```

**3 camadas:**

1. **API** — fala com servidor.
2. **UI** — desenha na tela.
3. **Eventos** — conecta as duas.

Cada função faz **uma coisa**. Quando der bug, você sabe onde olhar.

---

## 5.6 — Projeto final: Gerenciador de tarefas (~90 min)

Crie **`lab25/app.html`** — o app de verdade.

### O que ele faz

- Lista todas as suas tarefas, com checkbox e botão de apagar.
- Marcar/desmarcar checkbox → `PUT feito`.
- Botão apagar → confirmação + `DELETE`.
- Form de cima para criar nova tarefa.
- Contador "X feitas de Y" no topo.
- Filtros "Todas / Pendentes / Feitas".

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Tarefas — Projeto Final</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 30px auto; padding: 0 14px; }
        h1 { margin: 0 0 4px; }
        .topo { color: #666; margin-bottom: 20px; }

        form.novo {
            display: flex; gap: 8px; margin-bottom: 14px;
            background: #f3f7ff; padding: 10px; border-radius: 6px;
        }
        form.novo input { flex: 1; padding: 8px; font-size: 16px; }
        form.novo button { padding: 8px 16px; font-size: 16px; cursor: pointer; }

        .filtros { display: flex; gap: 6px; margin: 14px 0; }
        .filtros button {
            padding: 6px 12px; border: 1px solid #ccc; background: #fff; cursor: pointer;
        }
        .filtros button.ativo { background: #06f; color: #fff; border-color: #06f; }

        .tarefa {
            display: flex; gap: 10px; align-items: center;
            padding: 10px; border-bottom: 1px solid #eee;
        }
        .tarefa .titulo { flex: 1; cursor: pointer; }
        .tarefa.feita .titulo { text-decoration: line-through; color: #888; }
        .tarefa .del {
            border: 0; background: transparent; cursor: pointer;
            font-size: 18px; color: crimson;
        }

        #estado { color: #888; font-style: italic; padding: 14px 0; }
        .err { color: crimson; }
    </style>
</head>
<body>
    <h1>📋 Minhas tarefas</h1>
    <div class="topo">
        <span id="quem"></span> &nbsp;•&nbsp;
        <strong id="contador">0 feitas de 0</strong>
    </div>

    <form class="novo" id="formNovo">
        <input type="text" id="novoTitulo" placeholder="Nova tarefa..." autofocus>
        <button type="submit">Adicionar</button>
    </form>

    <div class="filtros">
        <button data-filtro="todas" class="ativo">Todas</button>
        <button data-filtro="pendentes">Pendentes</button>
        <button data-filtro="feitas">Feitas</button>
    </div>

    <div id="lista"></div>
    <div id="estado"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // ===== CONFIG =====
        var API = 'https://curso.chr.eti.br/ajax/api.php';
        var ALUNO_ID = localStorage.getItem('aluno_id');
        var filtroAtual = 'todas';

        if (!ALUNO_ID) {
            $('body').prepend('<div style="background:#fdd; padding:10px; color:crimson;">Defina seu aluno_id no lab23/ex01 primeiro!</div>');
        }
        $('#quem').text(ALUNO_ID || '(sem id)');

        // ===== API =====
        function listarTarefas() {
            return $.getJSON(API + '?aluno_id=' + encodeURIComponent(ALUNO_ID));
        }
        function criarTarefa(titulo) {
            return $.post(API, { aluno_id: ALUNO_ID, titulo: titulo }, null, 'json');
        }
        function atualizarTarefa(id, dados) {
            return $.ajax({
                url: API + '?id=' + id, method: 'PUT',
                data: JSON.stringify($.extend({ aluno_id: ALUNO_ID }, dados)),
                contentType: 'application/json', dataType: 'json'
            });
        }
        function apagarTarefa(id) {
            return $.ajax({
                url: API + '?id=' + id + '&aluno_id=' + ALUNO_ID,
                method: 'DELETE', dataType: 'json'
            });
        }

        // ===== UI =====
        function renderizar(tarefas) {
            $('#lista').empty();

            var visiveis = tarefas.filter(function(t) {
                if (filtroAtual === 'pendentes') return t.feito == 0;
                if (filtroAtual === 'feitas')    return t.feito == 1;
                return true;
            });

            if (visiveis.length === 0) {
                $('#estado').text('Nada para mostrar neste filtro.');
            } else {
                $('#estado').text('');
            }

            $.each(visiveis, function(i, t) {
                var $row = $('<div>').addClass('tarefa').attr('data-id', t.id);
                if (t.feito == 1) $row.addClass('feita');

                var $chk = $('<input type="checkbox">').prop('checked', t.feito == 1);
                $chk.on('change', function() {
                    var novo = $(this).is(':checked') ? 1 : 0;
                    atualizarTarefa(t.id, { feito: novo }).done(carregar).fail(function() {
                        alert('Erro ao salvar.');
                        $(this).prop('checked', !novo);
                    });
                });

                var $tit = $('<span>').addClass('titulo').text(t.titulo);

                // Edição inline: dois cliques pra editar
                $tit.on('dblclick', function() {
                    var novo = prompt('Novo título:', t.titulo);
                    if (novo && novo.trim() !== '' && novo !== t.titulo) {
                        atualizarTarefa(t.id, { titulo: novo.trim() }).done(carregar);
                    }
                });

                var $del = $('<button>').addClass('del').html('&times;').attr('title', 'Apagar');
                $del.on('click', function() {
                    if (!confirm('Apagar "' + t.titulo + '"?')) return;
                    $row.fadeOut(200, function() {
                        apagarTarefa(t.id).done(carregar);
                    });
                });

                $row.append($chk, $tit, $del);
                $('#lista').append($row);
            });

            atualizarContador(tarefas);
        }

        function atualizarContador(tarefas) {
            var feitas = tarefas.filter(function(t){ return t.feito == 1; }).length;
            $('#contador').text(feitas + ' feitas de ' + tarefas.length);
        }

        function mostrarErro(xhr) {
            $('#estado').addClass('err').text('Erro: ' + (xhr.status || 'rede caiu'));
        }

        // ===== LIGAÇÃO =====
        function carregar() {
            $('#estado').removeClass('err').text('Carregando...');
            listarTarefas().done(renderizar).fail(mostrarErro);
        }

        $('#formNovo').on('submit', function(e) {
            e.preventDefault();
            var titulo = $('#novoTitulo').val().trim();
            if (!titulo) return;

            criarTarefa(titulo).done(function() {
                $('#novoTitulo').val('').focus();
                carregar();
            }).fail(mostrarErro);
        });

        $('.filtros button').on('click', function() {
            $('.filtros button').removeClass('ativo');
            $(this).addClass('ativo');
            filtroAtual = $(this).data('filtro');
            carregar();
        });

        // ===== BOOT =====
        if (ALUNO_ID) carregar();
    </script>
</body>
</html>
```

**Abra e use de verdade.** Crie 5 tarefas. Marque 2 como feitas. Mude o filtro. Apague uma. Dê **duplo-clique** num título — abre `prompt()` pra editar. Tudo persiste — feche, reabra, está lá.

### O que esse arquivo tem dentro

- **API** (4 funções) — falar com servidor.
- **UI** (3 funções) — desenhar tela.
- **Ligação** — eventos e boot.

Esse padrão é o mesmo de qualquer SPA moderna, só que sem framework. Quando você for ver React/Vue, vai reconhecer essas camadas — só muda o jeito de escrever.

---

## Cheatsheet da Aula 5

```javascript
// PUT
$.ajax({
    url: API + '?id=' + id, method: 'PUT',
    data: JSON.stringify({ aluno_id, titulo, feito }),
    contentType: 'application/json', dataType: 'json'
}).done(...).fail(...);

// DELETE
$.ajax({
    url: API + '?id=' + id + '&aluno_id=' + ALUNO_ID,
    method: 'DELETE', dataType: 'json'
}).done(...).fail(...);

// Confirmação destrutiva
if (!confirm('Apagar?')) return;

// $.extend — junta objetos
$.extend({ a: 1 }, { b: 2 })   // { a: 1, b: 2 }

// Atributo de dados
<button data-id="7">
$(this).data('id')   // 7

// filter de array
var feitas = tarefas.filter(function(t){ return t.feito == 1; });
```

---

## 🏆 Desafio final do curso

Extenda o `app.html` para virar **`lab25/desafio.html`** com **3 funcionalidades extras**:

### 1. Busca em tempo real

Adicione um campo de busca acima da lista. A cada tecla, **esconde** as tarefas cujo título não bate. (Use o padrão da Aula 2 — `.on('input', ...)` + `.toggle()`.)

### 2. Contagem ao vivo

No topo, em vez de só "X feitas de Y", mostre 3 números:
- **Total**
- **Pendentes**
- **Feitas**

Cada um colorido (preto / laranja / verde).

### 3. Botão "Apagar todas as feitas"

Adicione um botão "Limpar concluídas" perto dos filtros. Ao clicar:
- Pede confirmação ("Apagar N tarefas concluídas?").
- Apaga em sequência: para cada tarefa feita, chama `apagarTarefa(id)`.
- Após todas voltarem, recarrega a lista.

### Bônus pra nota máxima

- Quando criar/editar/apagar dá erro, mostre uma **toast** vermelha no canto da tela por 3 segundos, em vez de `alert`.
- Mostre "Última atualização: HH:MM:SS" no topo, atualizada a cada `carregar()`.

---

## E agora?

Você sabe:

- Selecionar e manipular **qualquer coisa** em uma página com jQuery.
- Buscar, criar, editar e apagar dados em um servidor real via AJAX.
- Tratar erros, mostrar estados de loading, dar feedback visual.
- Organizar código em camadas (API / UI / eventos).

Isso é a base de **qualquer aplicação web**, com ou sem framework moderno. React, Vue e Angular fazem essas mesmas coisas — só com mais ferramentas em volta.

Se você for estudar mais por conta:

- **`fetch()` nativo** — a versão moderna do `$.ajax`, sem precisar de jQuery.
- **async/await** — sintaxe nova pra escrever AJAX em vez de `.done().fail()`.
- **Frameworks** — React/Vue/Angular usam tudo isso por baixo, com componentes e estado reativo.

Boa sorte. 🚀
