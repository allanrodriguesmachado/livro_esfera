# Livro Esfera - Documentação do Projeto

## 📄 Objetivo

O sistema **Livro Esfera** foi desenvolvido como parte de um **desafio técnico**, com o objetivo de aplicar boas práticas de desenvolvimento Web na criação de um sistema de **cadastro de livros** com funcionalidades completas de CRUD, geração de relatórios e arquitetura moderna.

---

## 🚀 Tecnologias Utilizadas

| Camada         | Tecnologia                   |
| -------------- | ---------------------------- |
| Backend        | PHP 8+, Laminas Framework    |
| Frontend       | jQuery, Bootstrap 5          |
| Empacotamento  | Webpack                      |
| Banco de Dados | PostgreSQL                   |
| Ambiente       | Docker, Docker Compose       |
| Relatórios     | View SQL + PDF (mpdf/dompdf) |
| UX             | SweetAlert2, DataTables      |

---

## ⚖️ Funcionalidades

* [x] CRUD completo para **Livro**, **Autor** e **Assunto**
* [x] Cadastro inline de autor e assunto
* [x] Validação de campos obrigatórios (ex: ano com 4 dígitos, valor > 0)
* [x] Formatação automática de moeda (R\$) e ano
* [x] Interface responsiva com Bootstrap
* [x] Manipulação dinâmica via jQuery e AJAX
* [x] SweetAlert2 para confirmações e edições
* [x] Relatório PDF agrupado por autor (via view)
* [x] TDD

---

## 🌐 Arquitetura do Projeto

### Estrutura Modular (Laminas Framework)

```
/module
  /Application
    /config
    /src
      /Controller
      /Form
      /Model
      /Repository
    /view
      /application
        /livro
        /autor
        /assunto
```

### Webpack e Frontend

```
/assets
  /js
    registro.js
    controle.js
    relatorio.js
  /scss
```

### Docker

* `docker-compose.yml` define serviços PHP, PostgreSQL e Nginx
* Scripts prontos para `build` e `up`

---

## 📊 Banco de Dados

### Tabelas Principais

* `livros` (id, titulo, editora, ano\_publicacao, valor)
* `autores` (id, nome)
* `assuntos` (id, nome)
* `livro_autor` (livro\_id, autor\_id)
* `livro_assunto` (livro\_id, assunto\_id)

### View para Relatório

```sql
CREATE OR REPLACE VIEW view_livros_autores_assuntos AS
SELECT
    l.id             AS livro_id,
    l.titulo,
    l.editora,
    l.ano_publicacao,
    l.valor,
    a.nome           AS autor,
    s.nome           AS assunto
FROM livros l
    JOIN livro_autor la ON la.livro_id = l.id
    JOIN autores a ON a.id = la.autor_id
    JOIN livro_assunto ls ON ls.livro_id = l.id
    JOIN assuntos s ON s.id = ls.assunto_id
WHERE l.ts_cancelado = FALSE
ORDER BY a.nome, l.titulo;
```

---

## 📅 Fluxo de Navegação

1. Tela inicial com listagem de livros (DataTables)
2. Botão "Novo Livro" alterna para formulário
3. Preenchimento com validação (campos obrigatórios, valor, ano)
4. Autor e assunto podem ser cadastrados inline (modais)
5. Após salvar, lista é atualizada automaticamente
6. Relatório PDF gerado com botão destacado

---

## 🔄 Comandos para Execução (Docker)

```bash
docker-compose up -d
composer install
./vendor/bin/laminas development-enable
```

Acesse via: `http://localhost:8080`

---

## 🔢 Relatório PDF

* Rota: `/relatorio/livros`
* Método: `POST`
* Saída: PDF gerado com base na view `view_livros_autores_assuntos`
* Agrupado por autor, com dados de livro e assunto
* Geração via biblioteca backend `mpdf` ou `dompdf`

---

## 🔚 Dependências de Projeto

### composer.json

* laminas/laminas-mvc
* laminas/laminas-db
* laminas/laminas-view
* dompdf/dompdf ^3.1
* mpdf/mpdf ^6.1
* phpunit/phpunit ^10.4 (dev)
* squizlabs/php\_codesniffer, psalm, symfony/var-dumper (dev)

### package.json (via Webpack)

*(Presumido como padrão para compilação de JS/CSS com Bootstrap e jQuery)*

---

## ⚠️ Considerações Finais

* Sistema completo e funcional
* Organização modular com foco em boas práticas
* Componentização JS com Webpack
* Integração dinâmica e responsiva
* Código pronto para expansão e integração futura
---

## 📆 Apresentação

Este projeto será apresentado tecnicamente durante a entrevista, onde será possível discutir:

* Arquitetura
* Modelagem
* Decisões de projeto
* Melhorias futuras (ex: testes, cache, autenticação)

---

**Desenvolvido por:** Allan Rodrigues

**Projeto:** Livro Esfera - Desafio Técnico
