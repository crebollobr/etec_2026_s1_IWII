# Aula 01 — Tecnologias JavaScript Modernas para Frontend e Backend

> Guia simples para alunos iniciantes que querem entender **o que aprender agora** e **o que o futuro pede** de um programador web.

---

## 1. Por que JavaScript?

JavaScript (JS) é a linguagem que roda no **navegador** (Chrome, Firefox, Edge) e hoje também roda no **servidor**, em **celulares** e até em **geladeiras inteligentes**.

Com JS você pode construir:

- Sites e lojas virtuais
- Aplicativos para celular
- Jogos
- Sistemas de empresa
- Inteligência artificial (IA)

**Em resumo:** aprender JS é aprender a linguagem mais usada do mundo hoje.

---

## 2. A Base que Todo Programador Precisa Saber

Antes de sair aprendendo "ferramentas da moda", você precisa dominar o básico. Sem isso, nada faz sentido.

### 2.1. Os três pilares do site

| Tecnologia | Para que serve | Analogia |
|------------|----------------|----------|
| **HTML** | Estrutura da página | O esqueleto do corpo |
| **CSS** | Aparência (cores, tamanho) | A pele e as roupas |
| **JavaScript** | Comportamento (cliques, animações) | Os músculos e o cérebro |

### 2.2. JavaScript Moderno (ES6+)

JS evoluiu muito. Hoje usamos uma versão moderna chamada **ES6+** (ECMAScript 2015 em diante). Os conceitos mais importantes:

- `let` e `const` (em vez do antigo `var`)
- **Arrow functions** → `() => {}`
- **Template strings** → `` `Olá, ${nome}` ``
- **Destructuring** → `const { nome, idade } = pessoa`
- **Promises** e **async/await** (para esperar dados da internet)
- **Módulos** → `import` e `export`

### 2.3. TypeScript (MUITO importante para o futuro)

**TypeScript** é o JavaScript com "super poderes". Ele avisa erros **antes** do código rodar.

Exemplo simples:

```ts
function somar(a: number, b: number): number {
  return a + b;
}
```

Aqui o programa sabe que `a` e `b` são números. Se você tentar passar texto, ele avisa.

**Hoje, a maioria das empresas exige TypeScript.** Aprenda depois de se sentir confortável com JS.

---

## 3. Tecnologias de **Frontend** (o que o usuário vê)

Frontend é tudo que aparece na tela do navegador.

### 3.1. Frameworks e Bibliotecas

Um **framework** é como um kit pronto de ferramentas para construir sites mais rápido.

| Nome | O que é | Quando usar |
|------|---------|-------------|
| **React** | Biblioteca criada pela Meta (Facebook). A mais popular do mundo. | Aprenda primeiro — é a porta de entrada do mercado. |
| **Vue.js** | Mais fácil de aprender que React. Muito usado no Brasil e na Ásia. | Bom para quem está começando e quer algo simples. |
| **Svelte** | Moderno, leve e rápido. Vem ganhando força. | Para quem quer algo enxuto e performático. |
| **Angular** | Feito pelo Google. Grande e completo. | Mais usado em empresas grandes. |

👉 **Recomendação para 2026+:** comece com **React** (ou Vue). São as mais pedidas em vagas.

### 3.2. Meta-frameworks (o nível acima)

São frameworks "em cima" dos frameworks, que resolvem problemas que o React sozinho não resolve bem (como SEO e velocidade).

- **Next.js** (em cima do React) → hoje é praticamente padrão do mercado.
- **Nuxt.js** (em cima do Vue)
- **SvelteKit** (em cima do Svelte)
- **Astro** → ótimo para sites de conteúdo e blogs.

### 3.3. Estilização (deixar bonito)

- **Tailwind CSS** → a forma mais moderna e rápida de estilizar. Praticamente obrigatório hoje.
- **shadcn/ui** → coleção de componentes prontos (botões, menus) muito usada com React + Tailwind.

### 3.4. Ferramentas do dia a dia

- **Vite** → substitui ferramentas antigas (como Webpack). Super rápido.
- **ESLint** e **Prettier** → ajudam a manter o código limpo e padronizado.
- **Git** e **GitHub** → controle de versão (salvar histórico do seu código). **ESSENCIAL.**

---

## 4. Tecnologias de **Backend** (o que fica "por trás")

Backend é a parte invisível: banco de dados, login, pagamentos, etc.

### 4.1. Ambientes (onde o JS roda no servidor)

- **Node.js** → o mais tradicional e popular. Comece por ele.
- **Deno** → criado pelo mesmo autor do Node, mais moderno e seguro.
- **Bun** → o mais novo e mais rápido. Vem crescendo muito.

### 4.2. Frameworks de Backend

| Nome | Para que serve |
|------|----------------|
| **Express** | O mais famoso. Simples e fácil. Ótimo para começar. |
| **Fastify** | Moderno e rápido. Substituto natural do Express. |
| **NestJS** | Organizado, parecido com Angular. Usado em empresas grandes. |
| **Hono** | Leve, moderno, roda em qualquer lugar (incluindo "edge"). |

### 4.3. Banco de Dados

Você precisa aprender pelo menos **um de cada tipo**:

- **SQL (relacional):** PostgreSQL (mais recomendado), MySQL
- **NoSQL (documentos):** MongoDB
- **Cache/memória:** Redis

E também ferramentas que facilitam o uso:

- **Prisma** ou **Drizzle ORM** → ajudam a conversar com o banco usando JavaScript.

### 4.4. APIs (como o frontend conversa com o backend)

- **REST** → o jeito clássico (aprenda primeiro).
- **GraphQL** → você pede só os dados que precisa.
- **tRPC** → moderno, para projetos 100% TypeScript.

---

## 5. Tecnologias que estão **em alta** (e vão crescer mais)

Essas são as "apostas" para os próximos anos:

1. **IA no código** — ferramentas como **Claude Code**, **Cursor** e **GitHub Copilot**. Saber usar bem é diferencial.
2. **Edge Computing** — rodar código perto do usuário (Cloudflare Workers, Vercel Edge).
3. **Serverless** — você escreve a função, a nuvem cuida do resto (AWS Lambda, Vercel Functions).
4. **WebAssembly (Wasm)** — rodar linguagens como Rust e C++ no navegador, na velocidade do nativo.
5. **React Server Components (RSC)** — novo jeito de montar páginas no servidor, reaproveitando código.
6. **PWA (Progressive Web Apps)** — sites que funcionam como apps no celular.
7. **React Native / Expo** — criar aplicativos de celular usando JavaScript.

---

## 6. Ordem Sugerida de Estudo (passo a passo)

> Não tente aprender tudo ao mesmo tempo. Siga uma ordem.

1. **HTML + CSS** → monte páginas simples.
2. **JavaScript moderno (ES6+)** → entenda variáveis, funções, arrays, objetos.
3. **Git e GitHub** → salve tudo que você faz.
4. **React** → seu primeiro framework.
5. **Tailwind CSS** → para estilizar rápido.
6. **TypeScript** → adicione "super poderes" ao seu JS.
7. **Next.js** → leve o React para o próximo nível.
8. **Node.js + Express** → seu primeiro backend.
9. **PostgreSQL + Prisma** → seu primeiro banco de dados.
10. **Deploy** → coloque seu projeto no ar (Vercel, Netlify, Railway).

Depois disso, vá para áreas especializadas: testes, mobile (React Native), IA, etc.

---

## 7. Dicas Finais para o Aluno Iniciante

- **Não pule o básico.** Quem não sabe JS puro sofre em qualquer framework.
- **Construa projetos pequenos.** Uma calculadora, uma lista de tarefas, um clone de um site simples.
- **Erre muito.** Erro é parte do aprendizado.
- **Leia a documentação oficial** (em inglês, se possível). É sempre a melhor fonte.
- **Siga comunidades** (YouTube, Discord, Twitter/X, Dev.to).
- **Não corra atrás de toda moda.** Domine o fundamental, as modas passam.

---

## 8. Resumo em Uma Frase

> **Aprenda bem HTML, CSS e JavaScript. Adicione React, TypeScript e Node.js. Com isso, você já está pronto para trabalhar como dev web em 2026 e além.**

---

*Fim da Aula 01 — próxima aula: instalando o ambiente de desenvolvimento (Node, VS Code, Git).*
