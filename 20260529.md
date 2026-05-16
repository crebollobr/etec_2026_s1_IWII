# Aula 2 — GET de listas e renderização dinâmica

> ETEC — Sexta à noite — 4 horas
> Hoje você vai sair sabendo: pegar arrays inteiros do servidor, varrer com `$.each`, montar dezenas de cards/tabelas/imagens dinamicamente, e fazer **busca instantânea** no que está na tela.
> Pasta de trabalho: `lab22/`
> API: continuamos com **JSONPlaceholder** (só GET, sem persistir nada).

---

## Recap rápido da Aula 1

Você sabe:

```javascript
$('#elem').text('x')                 // mexer no DOM
$('#btn').on('click', function(){})  // reagir a clique
$.get(url, function(resposta){})     // pedir UM dado
```

Hoje a virada é: **pedir muitos dados de uma vez e desenhar tudo na tela**.

---

## 2.1 — Teoria: `$.getJSON` e arrays (15 min)

`$.get` devolve **o que o servidor mandou**. Se for JSON, o jQuery tenta adivinhar — mas pra garantir, use o atalho:

```javascript
$.getJSON('url', function(dados) {
    // 'dados' já é um objeto JS pronto (parse feito)
});
```

Quando a URL devolve **um array**, o callback recebe um array. Por exemplo:

```
https://jsonplaceholder.typicode.com/users
```

Devolve **10 usuários** em um array de 10 objetos `{id, name, email, address, company, ...}`.

### Iterando com `$.each`

JavaScript puro: `for`, `for..of`, `.forEach`. jQuery tem o utilitário **`$.each`**, que funciona com **arrays e objetos**:

```javascript
$.each(usuarios, function(i, u) {
    console.log(i, u.name);
});
```

- `i` é o índice (0, 1, 2…)
- `u` é o item atual

Dentro do callback, `this` também aponta pro item — mas usar o parâmetro é mais claro.

---

## 2.2 — Laboratório: Lista de 100 posts (25 min)

Crie **`lab22/ex01-lista-posts.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex01 — Lista de posts</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 30px auto; }
        .post {
            border-left: 4px solid #06f; padding: 10px 14px;
            margin: 10px 0; background: #f7f9ff;
        }
        .post h3 { margin: 0 0 6px; }
        .post p { margin: 0; color: #444; }
        .post small { color: #999; }
        #status { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <h1>Mural de posts</h1>
    <p id="status">Carregando 100 posts...</p>
    <div id="mural"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $.getJSON('https://jsonplaceholder.typicode.com/posts', function(posts) {
            $('#status').text('Mostrando ' + posts.length + ' posts:');

            $.each(posts, function(i, p) {
                $('#mural').append(
                    '<div class="post">' +
                        '<h3>#' + p.id + ' — ' + p.title + '</h3>' +
                        '<p>' + p.body + '</p>' +
                        '<small>Autor: usuário ' + p.userId + '</small>' +
                    '</div>'
                );
            });
        });
    </script>
</body>
</html>
```

**Abra.** Você vê brevemente "Carregando 100 posts..." e em seguida **100 cards** descendo a tela. Role o mouse — todos foram criados pelo seu JavaScript, na hora.

**Veja na Network (F12):** **uma só** requisição trouxe os 100. Imagine se fossem 100 requisições.

---

## 2.3 — Teoria: Criando elementos com `$('<tag>')` (10 min)

Concatenar string é prático, mas tem dois problemas:

1. **HTML injection:** se um dado vier com `<script>` ou aspas, quebra tudo.
2. **Fica difícil de ler** quando o card é grande.

Forma mais robusta: criar o elemento e popular método a método.

```javascript
var $bloco = $('<div>').addClass('post');
$bloco.append($('<h3>').text(p.title));
$bloco.append($('<p>').text(p.body));
$('#mural').append($bloco);
```

`.text()` **escapa** o conteúdo (não interpreta HTML), então é seguro com dado vindo do servidor.

Convenção: variáveis que guardam objeto jQuery começam com `$` (`$bloco`, `$item`) — ajuda a lembrar que ali tem métodos jQuery.

---

## 2.4 — Laboratório: Lista de usuários "bonita" (25 min)

Crie **`lab22/ex02-usuarios.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex02 — Usuários</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 30px auto; }
        .usuario {
            display: flex; gap: 14px; align-items: center;
            padding: 12px; border-bottom: 1px solid #eee;
        }
        .avatar {
            width: 56px; height: 56px; border-radius: 50%;
            background: #06f; color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; font-weight: bold;
        }
        .nome { font-weight: bold; font-size: 18px; }
        .email { color: #06f; font-size: 13px; }
        .meta { color: #666; font-size: 13px; }
    </style>
</head>
<body>
    <h1>Diretório de usuários</h1>
    <div id="lista">Carregando...</div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $.getJSON('https://jsonplaceholder.typicode.com/users', function(usuarios) {
            $('#lista').empty();   // tira o "Carregando..."

            $.each(usuarios, function(i, u) {
                // Avatar feito com a inicial do nome
                var inicial = u.name.charAt(0);

                var $card = $('<div>').addClass('usuario');
                $card.append($('<div>').addClass('avatar').text(inicial));

                var $info = $('<div>');
                $info.append($('<div>').addClass('nome').text(u.name));
                $info.append($('<div>').addClass('email').text(u.email));
                $info.append($('<div>').addClass('meta')
                    .text(u.address.city + ' • ' + u.company.name));

                $card.append($info);
                $('#lista').append($card);
            });
        });
    </script>
</body>
</html>
```

**Abra.** Você verá 10 cartões de usuário com "avatares" de cor sólida com a inicial. Nada disso veio pronto do servidor — o JSON tem só os dados, **você** desenhou.

---

## 2.5 — Teoria: Estados de UI (10 min)

Toda interface AJAX passa por **3 estados**:

| Estado          | O que mostrar                                        |
|-----------------|------------------------------------------------------|
| **Carregando**  | "Carregando..." em cinza, ou um spinner              |
| **Sucesso**     | os dados                                             |
| **Vazio**       | "Nenhum resultado." em cinza (não pode ficar branco) |

O aluno **precisa ver** que algo está acontecendo, principalmente quando a conexão demora.

Truque rápido pra testar feedback de loading em local rápido demais:

```javascript
// Atrasar a resposta artificialmente
setTimeout(function() {
    $.getJSON(url, callback);
}, 1500);
```

Aí dá tempo de ver o "Carregando..." na tela.

---

## 2.6 — Laboratório: Galeria de fotos com loading (25 min)

Crie **`lab22/ex03-galeria.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex03 — Galeria</title>
    <style>
        body { font-family: sans-serif; max-width: 1000px; margin: 30px auto; }
        #galeria {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .foto {
            border: 1px solid #ddd; padding: 6px;
            text-align: center; font-size: 12px;
        }
        .foto img { width: 100%; display: block; }
        .foto .titulo { padding: 6px 2px 2px; }

        .loader {
            text-align: center; padding: 40px;
            color: #888; font-style: italic;
        }
    </style>
</head>
<body>
    <h1>Galeria de 20 fotos</h1>
    <div id="galeria">
        <div class="loader">Carregando galeria...</div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var url = 'https://jsonplaceholder.typicode.com/photos?_limit=20';

        $.getJSON(url, function(fotos) {
            $('#galeria').empty();

            $.each(fotos, function(i, f) {
                var $card = $('<div>').addClass('foto');
                $card.append($('<img>').attr('src', f.thumbnailUrl).attr('alt', f.title));
                $card.append($('<div>').addClass('titulo').text(f.title));
                $('#galeria').append($card);
            });
        });
    </script>
</body>
</html>
```

**Abra.** Veja:

1. Por um instante, "Carregando galeria..." aparece.
2. As 20 imagens aparecem no grid 4x5.

**Force o loader durar mais:** acrescente um `setTimeout` em volta do `$.getJSON` (1500ms) e veja o "Carregando..." de verdade.

---

## 2.7 — Teoria: Busca client-side (10 min)

Já que os 100 posts (ou 20 fotos) **já estão na tela**, dá pra filtrar **sem nova requisição** — só escondendo o que não bate.

Padrão:

```javascript
$('#busca').on('input', function() {
    var termo = $(this).val().toLowerCase();
    $('.item').each(function() {
        var texto = $(this).text().toLowerCase();
        $(this).toggle(texto.indexOf(termo) !== -1);
    });
});
```

- `.on('input', ...)` — dispara a cada tecla.
- `$(this)` — o input atual.
- `.toggle(boolean)` — mostra se `true`, esconde se `false`.
- `.each` — varre coleção do DOM (não confundir com `$.each` para arrays).

---

## 2.8 — Laboratório: Galeria com busca instantânea (25 min)

Volte ao **`lab22/ex03-galeria.html`** e modifique para ter campo de busca. Salve como **`lab22/ex04-busca.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex04 — Busca</title>
    <style>
        body { font-family: sans-serif; max-width: 1000px; margin: 30px auto; }
        .barra { display: flex; gap: 10px; align-items: center; margin-bottom: 14px; }
        #busca { flex: 1; padding: 8px; font-size: 16px; }
        #contador { color: #06f; font-weight: bold; }

        #galeria {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .foto {
            border: 1px solid #ddd; padding: 6px;
            text-align: center; font-size: 12px;
        }
        .foto img { width: 100%; display: block; }
        .foto .titulo { padding: 6px 2px 2px; }
        .loader { text-align: center; padding: 40px; color: #888; }
    </style>
</head>
<body>
    <h1>Galeria com busca</h1>

    <div class="barra">
        <input type="text" id="busca" placeholder="Digite para filtrar...">
        <div><span id="contador">0</span> visíveis</div>
    </div>

    <div id="galeria"><div class="loader">Carregando...</div></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var url = 'https://jsonplaceholder.typicode.com/photos?_limit=50';

        $.getJSON(url, function(fotos) {
            $('#galeria').empty();

            $.each(fotos, function(i, f) {
                var $card = $('<div>').addClass('foto');
                $card.append($('<img>').attr('src', f.thumbnailUrl));
                $card.append($('<div>').addClass('titulo').text(f.title));
                $('#galeria').append($card);
            });

            atualizarContador();
        });

        // Busca instantânea (a cada tecla)
        $('#busca').on('input', function() {
            var termo = $(this).val().toLowerCase();

            $('.foto').each(function() {
                var t = $(this).find('.titulo').text().toLowerCase();
                $(this).toggle(t.indexOf(termo) !== -1);
            });

            atualizarContador();
        });

        function atualizarContador() {
            $('#contador').text($('.foto:visible').length);
        }
    </script>
</body>
</html>
```

**Abra. Digite no campo.** Cada tecla filtra na hora — sem pedir nada ao servidor. O contador atualiza junto.

Note `$('.foto:visible')` — outro filtro jQuery útil pra contar quem está aparecendo.

---

## 2.9 — Teoria: Encadeando duas requisições (10 min)

Às vezes você precisa do **post** + **autor**. Duas chamadas:

```javascript
$.getJSON('https://jsonplaceholder.typicode.com/posts/1', function(post) {
    // Quando o post chega, pede o autor
    $.getJSON('https://jsonplaceholder.typicode.com/users/' + post.userId, function(autor) {
        // Aqui você tem post E autor
        $('#tela').html(
            '<h2>' + post.title + '</h2>' +
            '<p>' + post.body + '</p>' +
            '<small>por ' + autor.name + '</small>'
        );
    });
});
```

**Atenção:** quanto mais aninhamento, pior de ler ("callback hell"). Na Aula 4 a gente vai aprender a usar `.done()` para encurtar isso.

---

## 2.10 — Laboratório: Post com nome do autor (15 min)

Crie **`lab22/ex05-encadeado.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex05 — Encadeado</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 30px auto; }
        .post { padding: 14px; border: 1px solid #ddd; border-radius: 6px; }
        .autor { color: #06f; font-weight: bold; }
        .loader { color: #888; font-style: italic; }
    </style>
</head>
<body>
    <h1>Post + autor</h1>

    <label>Post (1 a 100):</label>
    <input type="number" id="num" value="1" min="1" max="100">
    <button id="buscar">Buscar</button>

    <div id="resultado" class="post loader">Aguardando...</div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $('#buscar').on('click', function() {
            var id = $('#num').val();
            var $res = $('#resultado').addClass('loader').text('Buscando post...');

            $.getJSON('https://jsonplaceholder.typicode.com/posts/' + id, function(post) {
                $res.text('Post carregado, buscando autor...');

                $.getJSON('https://jsonplaceholder.typicode.com/users/' + post.userId, function(autor) {
                    $res.removeClass('loader').html(
                        '<h2>' + post.title + '</h2>' +
                        '<p>' + post.body + '</p>' +
                        '<small>por <span class="autor">' + autor.name + '</span> (' + autor.email + ')</small>'
                    );
                });
            });
        });

        $('#buscar').trigger('click');
    </script>
</body>
</html>
```

**Abra. Observe o texto mudando:**

1. "Buscando post..."
2. "Post carregado, buscando autor..."
3. Resultado final.

Na aba **Network**, são **duas** requisições.

---

## Cheatsheet da Aula 2

```javascript
// Pegar array do servidor
$.getJSON(url, function(dados) {
    $.each(dados, function(i, item) { ... });
});

// Criar elemento com segurança
var $bloco = $('<div>').addClass('x');
$bloco.append($('<h3>').text(item.titulo));

// Limpar lista antes de re-renderizar
$('#lista').empty();

// Mostrar / esconder
$('.item').toggle(true);   // ou false

// Contar visíveis
$('.item:visible').length

// Busca instantânea
$('#campo').on('input', function() {
    var termo = $(this).val().toLowerCase();
    $('.item').each(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(termo) !== -1);
    });
});
```

---

## 🏆 Desafio da Aula 2

Crie **`lab22/desafio.html`** — **catálogo de álbuns**.

### Requisitos

1. Ao abrir, busque os 10 usuários: `https://jsonplaceholder.typicode.com/users`
2. Mostre como **lista lateral** (uma coluna estreita à esquerda) com nome e cidade.
3. Quando o usuário clicar num nome, **buscar os álbuns dele**:
   `https://jsonplaceholder.typicode.com/albums?userId=ID`
4. Mostrar os álbuns como cards na área principal (à direita).
5. Acima da lista de álbuns, mostre: "X álbuns de [Nome do usuário]".

### Visual sugerido

```
+----------------------------------------------+
|  Catálogo de álbuns                          |
+--------+-------------------------------------+
| Ana    |  X álbuns de Diego                  |
| Bruno  |  ┌─────────┐ ┌─────────┐ ┌────────┐ |
| Carla  |  │ Album 1 │ │ Album 2 │ │ Album..│ |
| Diego ←|  └─────────┘ └─────────┘ └────────┘ |
| Elisa  |                                     |
+--------+-------------------------------------+
```

### Bônus

- O usuário clicado fica destacado (fundo amarelo).
- Mostre "Selecione um usuário à esquerda" enquanto ninguém foi clicado.
- Adicione um campo de busca acima da lista de álbuns que filtra **só os álbuns visíveis na tela** (estilo do Ex04).

---

Na **Aula 3** a gente vai **enviar dados de verdade** ao servidor. Você cria a tarefa, o servidor salva no SQLite, e quando recarrega ela continua lá. Saímos do JSONPlaceholder e migramos para o **`api.php`** do professor em `curso.chr.eti.br`.
