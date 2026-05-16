# Aula 1 — Fundamentos do jQuery e primeiro AJAX

> ETEC — Sexta à noite — 4 horas
> Hoje você vai sair sabendo: o que é jQuery, como selecionar e mexer em qualquer coisa da página, e como pedir dados para um servidor sem recarregar.
> Pasta de trabalho: `lab21/`

---

## Regra de ouro

**Se você não viu funcionar no navegador, não passou.** Toda hipótese é testada agora, ao vivo. A cada `lab/` aberto, F12 sempre aberto também.

---

## 1.1 — Teoria: História do AJAX (20 min)

A web nasceu em 1991 **síncrona**: você clicava num link, o servidor mandava a página **inteira**, o navegador apagava tudo e desenhava de novo. Tudo piscava. Cadastro com erro? Tela branca, voltava e você redigitava tudo.

Em 1999 a Microsoft inventou o `XMLHttpRequest` (XHR) pro Outlook Web — JavaScript falando com servidor **sem recarregar**. Ninguém usou direito.

Em **2004 / 2005**, Gmail e Google Maps mostraram pra todo mundo: dá pra fazer site se comportar como aplicativo de desktop.

Em **18 de fevereiro de 2005**, Jesse James Garrett publica o artigo *"Ajax: A New Approach to Web Applications"* e batiza a técnica: **A**synchronous **J**avaScript **a**nd **X**ML.

Em **2006** John Resig lança o **jQuery**. Antes, fazer AJAX dava 25 linhas e funcionava diferente em cada navegador. Com jQuery viraram 3 linhas que funcionam em todo lugar. Em 2010, mais da metade dos sites do mundo usavam jQuery.

**Hoje (2026):** React/Vue/Angular dominam, mas **~70% dos sites ainda têm jQuery** (WordPress, Bootstrap, sistemas legados). Aprender jQuery vale porque:

1. É o jeito mais **didático** de entender AJAX.
2. Você vai pegar manutenção de sistema antigo cedo ou tarde.
3. Os conceitos (seletor, encadeamento, callback) são os mesmos de qualquer framework.

### AJAX em uma frase

> **Pedir dados ao servidor em segundo plano e atualizar só uma parte da página.**

Não é linguagem, não é produto. É **técnica**.

---

## 1.2 — Teoria: Instalação (10 min)

jQuery é **um arquivo `.js`**. Carregou, funcionou.

```html
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
```

**Coloque sempre antes do `</body>`.** Se estiver no `<head>`, seu script tenta mexer em elementos que ainda nem existem — bug silencioso.

### Conferir se carregou

Console do navegador (F12 → Console):

```javascript
$              // ƒ (e,t){...}   ← carregou
$.fn.jquery    // "3.7.1"
```

Se aparecer `$ is not defined`, o caminho do `<script>` está errado.

---

## 1.3 — Laboratório: Primeiro arquivo (10 min)

Crie a pasta `lab21/` e dentro o arquivo **`ex01-primeiro.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex01 — Primeiro jQuery</title>
</head>
<body>
    <h1 id="titulo">Página comum</h1>
    <p>Se o jQuery carregou, o título lá em cima vai mudar e ficar vermelho sozinho.</p>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $('#titulo').text('Funcionou!').css('color', 'crimson');
    </script>
</body>
</html>
```

**Abra no navegador.** O título deve aparecer **já em vermelho** dizendo "Funcionou!". Você viu? Então o jQuery está vivo.

---

## 1.4 — Teoria: A função `$()` (15 min)

`$` é só **apelido** para `jQuery`. Estas duas linhas são idênticas:

```javascript
$('#titulo').text('Olá');
jQuery('#titulo').text('Olá');
```

`$()` recebe **algo** e devolve uma **coleção de elementos** com um monte de métodos.

| Você passa            | Ela faz                                       |
|-----------------------|-----------------------------------------------|
| `'#titulo'`           | seleciona o elemento de id `titulo`           |
| `'p'`                 | seleciona **todos** os parágrafos             |
| `'.aviso'`            | seleciona todos com classe `aviso`            |
| `'<div>oi</div>'`     | **cria** um div novo (na memória)             |
| `document.body`       | encapsula um elemento DOM existente           |

**Importante:** mesmo quando seleciona 1 elemento, ela devolve uma **coleção**. Quando você chama um método, ele age em **todos**:

```javascript
$('p').css('color', 'blue');   // pinta TODOS os <p> de azul
```

Sem `for`. Esse é o superpoder do jQuery.

---

## 1.5 — Laboratório: Brincando no console (15 min)

Abra `ex01-primeiro.html` no navegador. Aperte **F12**. Aba **Console**. Vá digitando uma linha por vez e veja a página mudar:

```javascript
$('h1').text('Hackeado!')
$('h1').css('background', 'yellow')
$('p').css('color', 'red')
$('p').css('font-size', '24px')
$('body').css('background', 'lavender')
$('body').append('<h2>Eu apareci do nada</h2>')
$('h1').fadeOut(2000)
$('h1').fadeIn(2000)
```

**Recarregue.** Tudo volta. **Brinque até cansar.** Console é laboratório.

---

## 1.6 — Teoria: Seletores (25 min)

**Toda sintaxe CSS funciona em `$()`.** E jQuery adiciona ainda mais.

### Básicos

| Seletor          | Pega                                |
|------------------|-------------------------------------|
| `*`              | tudo                                |
| `p`              | todos os `<p>`                      |
| `#menu`          | id `menu`                           |
| `.aviso`         | classe `aviso`                      |
| `p.aviso`        | `<p>` que tem classe `aviso`        |
| `h1, h2, h3`     | união (qualquer um dos três)        |

### Hierárquicos

| Seletor    | Significa                                       |
|------------|-------------------------------------------------|
| `ul li`    | `<li>` descendente de `<ul>` (qualquer nível)   |
| `ul > li`  | `<li>` **filho direto** de `<ul>`               |
| `h1 + p`   | `<p>` imediatamente depois de `<h1>`            |
| `h1 ~ p`   | qualquer `<p>` irmão depois de `<h1>`           |

### Atributo

```javascript
$('input[type="text"]')      // só inputs de texto
$('a[href^="https"]')        // links que começam com https
$('img[src$=".png"]')        // imagens PNG
$('a[href*="etec"]')         // links que contêm "etec"
```

### Filtros jQuery (não são CSS!)

| Filtro            | Pega                                      |
|-------------------|-------------------------------------------|
| `:first` / `:last`| primeiro / último                         |
| `:even` / `:odd`  | índice par / ímpar (zebra-striping!)      |
| `:eq(n)`          | índice `n`                                |
| `:contains("x")`  | que contém o texto "x"                    |
| `:has(sel)`       | que tem `sel` dentro                      |
| `:not(sel)`       | que **não** bate com `sel`                |

### Travessia (navegar na árvore)

```javascript
.find(s)   .children(s)   .parent()   .parents(s)
.closest(s)   .siblings()   .next()   .prev()
.first()   .last()   .eq(n)   .filter(s)
```

---

## 1.7 — Laboratório: Seletores no mundo real (25 min)

Crie **`lab21/ex02-seletores.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex02 — Seletores</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 30px auto; }
        table { border-collapse: collapse; margin: 10px 0; }
        td, th { border: 1px solid #999; padding: 6px 14px; }
    </style>
</head>
<body>
    <h1 id="titulo">Notas da turma</h1>

    <table id="notas">
        <tr><th>Aluno</th><th>Nota</th></tr>
        <tr><td>Ana</td>    <td>7.5</td></tr>
        <tr><td>Bruno</td>  <td>9.0</td></tr>
        <tr><td>Carla</td>  <td>5.5</td></tr>
        <tr><td>Diego</td>  <td>8.0</td></tr>
        <tr><td>Elisa</td>  <td>6.0</td></tr>
        <tr><td>Felipe</td> <td>4.0</td></tr>
    </table>

    <h2>Links</h2>
    <ul id="links">
        <li><a href="https://jquery.com">jQuery oficial</a></li>
        <li><a href="https://etec.sp.gov.br">ETEC</a></li>
        <li><a href="/local.html">página local</a></li>
        <li><a href="https://google.com">Google</a></li>
    </ul>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // 1. Zebra-striping — linhas ímpares cinzas
        $('#notas tr:odd').css('background', '#eee');

        // 2. Cabeçalho destacado
        $('#notas tr:first').css({ background: '#cfe', fontWeight: 'bold' });

        // 3. Quem tirou abaixo de 7 (manual por enquanto)
        $('#notas tr:contains("4.0"), #notas tr:contains("5.5"), #notas tr:contains("6.0")')
            .css('color', 'crimson');

        // 4. Links externos em azul
        $('a[href^="https"]').css('color', 'navy');

        // 5. Quantos links externos existem?
        var n = $('a[href^="https"]').length;
        $('#links').after('<p><strong>' + n + ' links externos</strong> nesta página.</p>');
    </script>
</body>
</html>
```

**Abra.** Você deve ver a tabela com zebra, cabeçalho azul, três alunos em vermelho, links em azul, e um aviso no final dizendo "3 links externos".

**Quebre de propósito** (e veja o que vira):
- Troque `:odd` por `:even`.
- Troque `:contains("4.0")` por `:contains("a")` — quantas linhas pegam?
- Troque `[href^="https"]` por `[href*="etec"]` — quem fica vermelho?

---

## 1.8 — Teoria: Manipulando o DOM (15 min)

### Conteúdo

| Método              | Faz                                  |
|---------------------|--------------------------------------|
| `.text()` / `.text("x")`         | lê / escreve texto puro |
| `.html()` / `.html("<b>x</b>")`  | lê / escreve HTML       |
| `.val()` / `.val("x")`           | lê / escreve valor de input |

### Aparência

| Método                                  | Faz                          |
|-----------------------------------------|------------------------------|
| `.css("color", "red")`                  | um CSS                       |
| `.css({color:"red", fontSize:"20px"})`  | vários CSS                   |
| `.addClass("ativo")`                    | adiciona classe              |
| `.removeClass("ativo")`                 | remove classe                |
| `.toggleClass("ativo")`                 | inverte                      |

### Estrutura

| Método              | Faz                                       |
|---------------------|-------------------------------------------|
| `.append(x)`        | insere `x` no **final** do elemento       |
| `.prepend(x)`       | insere `x` no **início**                  |
| `.after(x)`         | insere `x` **depois** (como irmão)        |
| `.before(x)`        | insere `x` **antes**                      |
| `.remove()`         | apaga                                     |
| `.empty()`          | esvazia o conteúdo                        |
| `.replaceWith(x)`   | substitui o elemento inteiro              |

### Efeitos (úteis pra mostrar loading depois)

```javascript
.show()  .hide()
.fadeIn(ms)  .fadeOut(ms)
.slideUp(ms)  .slideDown(ms)
```

### Encadeamento

Quase todo método devolve a coleção, então dá pra encadear:

```javascript
$('#t').text('Novo').css('color', 'crimson').addClass('grande').fadeIn(1000);
```

---

## 1.9 — Laboratório: Mexendo no DOM com botões (20 min)

Vocês já viram eventos em JS puro (`addEventListener`). No jQuery é `.on('click', função)` — mesma ideia, sintaxe mais curta.

Crie **`lab21/ex03-manipulacao.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex03 — Manipulação</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 30px auto; }
        button { padding: 8px 14px; margin: 4px; }
        .caixa { padding: 12px; margin: 8px 0; border: 1px solid #999; border-radius: 4px; }
        .ativo { background: #cfe; }
        .alerta { background: #fdd; color: #900; font-weight: bold; }
    </style>
</head>
<body>
    <h1 id="t">Painel de controle</h1>

    <div>
        <button id="btn-mudar">Mudar título</button>
        <button id="btn-add">Adicionar caixa</button>
        <button id="btn-ativar">Ativar todas</button>
        <button id="btn-limpar">Limpar caixas</button>
        <button id="btn-sumir">Sumir título</button>
        <button id="btn-voltar">Voltar título</button>
    </div>

    <div id="container">
        <div class="caixa">Caixa 1</div>
        <div class="caixa">Caixa 2</div>
        <div class="caixa">Caixa 3</div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $('#btn-mudar').on('click', function() {
            $('#t').text('Título mudado em ' + new Date().toLocaleTimeString())
                   .css('color', 'crimson');
        });

        var contador = 3;
        $('#btn-add').on('click', function() {
            contador++;
            $('#container').append(
                '<div class="caixa">Caixa ' + contador + ' (criada agora)</div>'
            );
        });

        $('#btn-ativar').on('click', function() {
            $('.caixa').toggleClass('ativo');
        });

        $('#btn-limpar').on('click', function() {
            $('#container').empty();
            contador = 0;
        });

        $('#btn-sumir').on('click', function() {
            $('#t').fadeOut(800);
        });

        $('#btn-voltar').on('click', function() {
            $('#t').fadeIn(800);
        });
    </script>
</body>
</html>
```

**Aperte cada botão.** Tudo é **manipulação de DOM** disparada por clique. Ainda **nenhum AJAX** — você está dominando o navegador no cliente.

---

## 1.10 — Teoria: AJAX e callback (15 min)

Hora do troféu da noite.

### A ideia

```javascript
$.get('url-do-servidor', function(resposta) {
    // este código roda DEPOIS, quando a resposta chega
});
```

A função no 2º argumento é o **callback**. jQuery a chama quando a resposta volta.

### Atenção à ordem (assincronia)

```javascript
console.log('1');
$.get('https://jsonplaceholder.typicode.com/posts/1', function(post) {
    console.log('3 — chegou:', post);
});
console.log('2');
```

No console aparece:

```
1
2
3 — chegou: {userId: 1, id: 1, title: "..."}
```

A linha 3 imprime **depois** porque foi rede. **Se você precisa do dado, só use dentro do callback.**

### A API de teste — JSONPlaceholder

API pública, sem cadastro, dados falsos. Mãozinha pra estudar:

| URL                                                  | Devolve            |
|------------------------------------------------------|--------------------|
| `https://jsonplaceholder.typicode.com/posts`         | 100 posts          |
| `https://jsonplaceholder.typicode.com/posts/1`       | post #1            |
| `https://jsonplaceholder.typicode.com/users`         | 10 usuários        |
| `https://jsonplaceholder.typicode.com/users/3`       | usuário #3         |

---

## 1.11 — Laboratório: Primeiro AJAX (30 min)

Crie **`lab21/ex04-primeiro-ajax.html`**:

```html
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ex04 — Primeiro AJAX</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 30px auto; }
        .barra { display: flex; gap: 8px; align-items: center; margin-bottom: 14px; }
        input { padding: 6px; width: 80px; }
        button { padding: 6px 14px; }
        #resultado {
            border: 1px solid #ccc; padding: 14px;
            min-height: 100px; border-radius: 4px;
        }
        .carregando { color: #999; font-style: italic; }
        .erro { color: crimson; }
    </style>
</head>
<body>
    <h1>Buscador de posts</h1>

    <div class="barra">
        <label>Número do post (1–100):</label>
        <input type="number" id="num" value="1" min="1" max="100">
        <button id="buscar">Buscar</button>
    </div>

    <div id="resultado">Clique em "Buscar" para começar.</div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $('#buscar').on('click', function() {
            var id = $('#num').val();
            var url = 'https://jsonplaceholder.typicode.com/posts/' + id;

            // Estado "carregando" — o aluno PRECISA ver isso acontecer
            $('#resultado')
                .removeClass('erro')
                .addClass('carregando')
                .text('Carregando post #' + id + '...');

            $.get(url, function(post) {
                $('#resultado')
                    .removeClass('carregando')
                    .html(
                        '<h2>' + post.title + '</h2>' +
                        '<p>' + post.body + '</p>' +
                        '<small>Post #' + post.id + ' • Usuário ' + post.userId + '</small>'
                    );
            });
        });

        // Dispara o primeiro automaticamente
        $('#buscar').trigger('click');
    </script>
</body>
</html>
```

**Abra. Apareceu o post #1?** Mude para 42, clique. Para 99, clique.

**Confira no F12 → aba Network:** você vê cada requisição saindo, o status 200 OK, e a resposta JSON.

### O que aconteceu, passo a passo

1. Clicou no botão.
2. `.val()` leu o número.
3. `$.get` saiu pela rede **em segundo plano**.
4. A página **continuou respondendo** — sem tela branca.
5. Quando o servidor respondeu, jQuery chamou o callback com o JSON virado em objeto.
6. Atualizamos só o `<div id="resultado">`.

**Isso é AJAX.** Tudo o que vamos ver nas próximas 4 noites é variação disso.

---

## Cheatsheet da Aula 1

```javascript
// SELEÇÃO
$('tag')   $('#id')   $('.classe')   $('a[href^="https"]')
$('li:first')   $('tr:odd')   $('p:contains("x")')

// MANIPULAÇÃO
.text()  .html()  .val()
.css(k, v)  .addClass(c)  .removeClass(c)  .toggleClass(c)
.append(x)  .prepend(x)  .after(x)  .before(x)
.remove()  .empty()
.fadeIn(ms)  .fadeOut(ms)

// EVENTOS (revisão jQuery — vocês já viram em JS puro)
$('#btn').on('click', function() { ... });

// AJAX
$.get(url, function(resposta) { ... });
```

---

## 🏆 Desafio da Aula 1

Crie **`lab21/desafio.html`** — um **navegador de usuários**.

### Requisitos visuais

- Um título "Usuários da API".
- Um cartão grande no centro mostrando: nome, email, cidade e nome da empresa.
- Três botões abaixo do cartão: **« Anterior**, **Buscar #** (com input ao lado), **Próximo »**.

### Comportamento

- Ao abrir, mostra o usuário **#1** automaticamente.
- "Anterior" / "Próximo" mudam o ID (1 a 10) e buscam.
- O input ao lado de "Buscar #" também muda o ID.
- Enquanto a requisição estiver em curso, o cartão mostra "Carregando..." em cinza-itálico.

### Dica de URL

```
https://jsonplaceholder.typicode.com/users/3
```

Devolve um objeto com `name`, `email`, `address.city`, `company.name`.

### Bônus para nota alta

- Desabilite "Anterior" quando ID=1 e "Próximo" quando ID=10:

  ```javascript
  $('#btn-ant').prop('disabled', id <= 1);
  $('#btn-prox').prop('disabled', id >= 10);
  ```

- Use `.fadeOut` no cartão antes de carregar e `.fadeIn` depois que chegar — vira um pequeno efeito de transição entre usuários.

---

Na **Aula 2** a gente para de buscar **um** e passa a buscar **listas inteiras** — 100 posts, 10 usuários, 500 comentários — e renderizar tudo dinamicamente.
