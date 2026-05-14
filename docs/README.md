![Pagar.me e WooCommerce logo](https://github.com/pagarme/woocommerce/blob/master/docs/images/pagarme+woocommerce-white.png#gh-dark-mode-only)
![Pagar.me e WooCommerce logo](https://github.com/pagarme/woocommerce/blob/master/docs/images/pagarme+woocommerce.png#gh-light-mode-only)

# Pagar.me módulo para WooCommerce

**Tags:** ecommerce, e-commerce, store, sales, sell, shop, cart, checkout, woocommerce, pagarme, payments, creditcard

**License:** MIT

**License URI:** https://github.com/pagarme/woocommerce/blob/master/LICENSE

Módulo de integração da Pagar.me com o WooCommerce. Aceite pagamentos de cartão de crédito, pix, boleto, voucher e multimeios, e aumente a sua conversão.

## Descrição

A inteligência do seu pagamento - Soluções focadas em aumentar sua conversão!

Pagamentos tem que ser fáceis. São muitos passos por trás de uma simples transação financeira. Mas o seu consumidor não precisa saber disso. Nossas soluções estão disponíveis para ajuda-lo a aumentar sua conversão e oferecer a melhor experiência no momento do pagamento para o seu cliente.

## Contribuição

Se você está interessado em contribuir para o desenvolvimento deste projeto, ficamos felizes em receber sua ajuda! No [contributing.md](https://github.com/pagarme/woocommerce/blob/master/.github/contributing.md) está o guia de como contribuir com o projeto.

## Documentação de Filtros e Actions

Descubra como personalizar o comportamento do nosso plugin! Acesse nossas documentações abaixo e comece a realizar as modificações que deseja:
- [Split](https://github.com/pagarme/woocommerce/blob/master/docs/filters-actions/split.md)

## Compatibilidade

- Requer Wordpress 4.1 ou posterior para funcionar.
- Requer WooCommerce 3.9 ou posterior para funcionar.
- Requer versão do PHP maior ou igual a 7.1.

## Instalação do plugin

- Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
- Ative o plugin.

## Requerimentos

- [Conta na Pagar.me](http://www.pagar.me/)
- [WooCommerce](https://wordpress.org/plugins/woocommerce/)

## Desenvolvimento local

Este repositório inclui um ambiente Docker pronto para desenvolvimento (PHP 8.4 + Apache + XDebug 3, MariaDB 11 e phpMyAdmin) orquestrado via Makefile. Os arquivos do ambiente ficam em `.dev/`.

### Pré-requisitos
- Docker (com `docker compose` v2)
- GNU Make

### Subindo o ambiente pela primeira vez

```bash
make build      # build das imagens
make up         # sobe WP, MariaDB, phpMyAdmin + instala WooCommerce (latest) e ativa o plugin
make install    # composer install dentro do container (deps PHP do plugin)
```

`make up` invoca um container `wp-cli` que automaticamente:
- instala o WordPress (se ainda não estiver instalado);
- instala e ativa a versão **latest** do WooCommerce;
- ativa o plugin `woo-pagarme-payments`.

O script é idempotente — pode ser reexecutado a qualquer momento com `make seed`.

Acessos após o boot:

| Serviço | URL | Credenciais |
|---|---|---|
| WordPress | <http://localhost:8080> | — |
| wp-admin | <http://localhost:8080/wp-admin> | `admin` / `admin` |
| phpMyAdmin | <http://localhost:8081> | `root` / `root` |

As portas podem ser sobrescritas via variáveis de ambiente: `WP_PORT=8090 PMA_PORT=8091 make up`. Defaults do WordPress admin (`WP_ADMIN_USER`, `WP_ADMIN_PASSWORD`, `WP_ADMIN_EMAIL`, `WP_TITLE`) também podem ser sobrescritos via env vars na hora do `make up` / `make seed`.

### Comandos do Makefile

Rode `make` (ou `make help`) para listar todos os comandos.

| Comando | Descrição |
|---|---|
| `make build` | Build (ou rebuild) das imagens |
| `make up` | Sobe o ambiente + instala/ativa WooCommerce automaticamente |
| `make seed` | Reexecuta o init do WP (instala/ativa WordPress + WooCommerce, idempotente) |
| `make down` | Para os containers (volumes preservados) |
| `make restart` | `down` + `up` |
| `make clean` | Remove containers **e volumes** (apaga o WP instalado) |
| `make ps` | Status dos containers |
| `make logs` | Acompanha logs de todos os serviços |
| `make logs-wp` | Logs apenas do WordPress/Apache |
| `make shell` | Bash no container do WordPress |
| `make shell-db` | Cliente mysql no container do banco |
| `make install` | Roda `composer install` no plugin |
| `make test` | Roda PHPUnit (apenas os testes do plugin) |
| `make phpcs` | Roda PHPCS com WordPress Coding Standards |
| `make xdebug-log` | `tail -f` no log do XDebug |

### XDebug

Configurado em modo `trigger` (XDebug 3, porta `9003`, idekey `PHPSTORM`). Só ativa quando você dispara explicitamente, evitando overhead em todas as requests:

- Cookie `XDEBUG_TRIGGER` (extensões como *Xdebug Helper* fazem isso)
- Query string: `?XDEBUG_TRIGGER=1`
- Header HTTP: `X-XDEBUG-TRIGGER: 1`

Na sua IDE, configure para escutar na porta `9003` com path mapping:
- Remoto: `/var/www/html/wp-content/plugins/woo-pagarme-payments`
- Local: raiz deste repositório

## Contribuidores

| ![eduardobattisti avatar](https://avatars.githubusercontent.com/u/56602897?s=60&v=4) | ![tiagopapile avatar](https://avatars.githubusercontent.com/u/82596706?s=60&v=4) | ![gutobenn avatar](https://avatars.githubusercontent.com/u/607762?s=60&v=4) |
|---|---|---|
| [eduardobattisti](https://github.com/eduardobattisti) | [tiagopapile](https://github.com/tiagopapile) | [gutobenn](https://github.com/gutobenn) |
