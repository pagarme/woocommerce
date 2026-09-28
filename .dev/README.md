# Ambiente de Desenvolvimento — woo-pagarme-payments

Stack Docker com WordPress + WooCommerce + plugin pré-ativado, MariaDB, phpMyAdmin, WP-CLI e Xdebug.

## Pré-requisitos

| Ferramenta | Versão mínima |
|------------|---------------|
| Docker Engine | 24+ |
| Docker Compose v2 | 2.20+ |
| GNU Make | qualquer |

> **macOS / Windows:** Docker Desktop já inclui Compose v2. No Linux instale via `apt install docker-compose-plugin`.

---

## Quick start

```bash
# 1. sobe tudo e inicializa WordPress + WooCommerce (idempotente)
make up

# 2. instala dependências PHP do plugin
make install
```

Na primeira execução o `wp-cli` baixa o WordPress, instala o WooCommerce, importa produtos de exemplo e ativa o plugin. Aguarde o log `[init-wp] OK.` antes de abrir o browser.

---

## Serviços e URLs

| Serviço | URL / endereço | Credenciais |
|---------|---------------|-------------|
| WordPress | http://woo.localhost | — |
| wp-admin | http://woo.localhost/wp-admin | `admin` / `admin` |
| phpMyAdmin | http://localhost:8081 | `root` / `root` |
| MariaDB | `localhost:3306` | usuário `wordpress` / senha `wordpress` |

> Altere as portas via variáveis de ambiente antes de subir (veja seção abaixo).

---

## Comandos disponíveis

```bash
make help          # lista todos os targets com descrição
```

| Target | O que faz |
|--------|-----------|
| `make up` | Sobe o ambiente e inicializa WordPress |
| `make down` | Para e remove containers (volumes preservados) |
| `make restart` | `down` + `up` |
| `make clean` | Para containers **e apaga volumes** (reset completo) |
| `make build` | Reconstrói a imagem Docker |
| `make install` | `composer install` dentro do container + patches de vendor |
| `make patch-vendor` | Reaaplica patches PHP 8.4 em pacotes de vendor/ |
| `make seed` | Roda o `init-wp.sh` novamente (idempotente) |
| `make test` | Executa PHPUnit dentro do container |
| `make phpcs` | Executa PHP_CodeSniffer dentro do container |
| `make shell` | Abre bash no container do WordPress |
| `make shell-db` | Abre cliente MySQL no container do banco |
| `make logs` | Acompanha logs de todos os serviços |
| `make logs-wp` | Acompanha apenas logs do WordPress/Apache |
| `make ps` | Lista status dos containers |
| `make xdebug-log` | Acompanha o log do Xdebug em tempo real |

---

## Variáveis de ambiente

Crie um arquivo `.env` na raiz do projeto (ao lado do `Makefile`) para sobrescrever os padrões:

```dotenv
# URL base do WordPress (deve corresponder ao seu /etc/hosts ou resolver via DNS)
WP_URL=http://woo.localhost

# Porta exposta do Apache (padrão: 80)
WP_PORT=80

# Porta exposta do phpMyAdmin (padrão: 8081)
PMA_PORT=8081

# Credenciais do admin WordPress
WP_ADMIN_USER=admin
WP_ADMIN_PASSWORD=admin
WP_ADMIN_EMAIL=admin@example.com

# Locale e estrutura de permalinks
WP_LOCALE=pt_BR
WP_PERMALINK_STRUCTURE=/%postname%/
```

> O arquivo `.env` é lido automaticamente pelo Docker Compose. Não o comite se contiver segredos.

---

## Resolução de nomes (`woo.localhost`)

Adicione ao `/etc/hosts` (ou equivalente no seu OS):

```
127.0.0.1  woo.localhost
```

---

## Rede corporativa com proxy Netskope

Use os targets `*-netskope` que carregam o `docker-compose.netskope.yml` sobre o compose base:

```bash
make build-netskope
make up-netskope
```

O certificado Netskope é injetado no bundle de CAs do WordPress em tempo de execução pelo `init-wp.sh` — sem necessidade de rebuildar a imagem.

---

## Xdebug

O Xdebug está habilitado por padrão na imagem de desenvolvimento (porta `9003`).

**VS Code** — instale a extensão [PHP Debug](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug) e adicione ao `launch.json`:

```json
{
  "name": "Listen for Xdebug",
  "type": "php",
  "request": "launch",
  "port": 9003,
  "pathMappings": {
    "/var/www/html/wp-content/plugins/woo-pagarme-payments": "${workspaceFolder}"
  }
}
```

**Linux:** descomente a linha `extra_hosts` no `docker-compose.yml` para que o container alcance a IDE no host:

```yaml
extra_hosts:
  - "host.docker.internal:host-gateway"
```

> No macOS/Windows com Docker Desktop essa linha **não é necessária** e quebra a resolução — deixe comentada.

Acompanhe o log do Xdebug:

```bash
make xdebug-log
```

---

## Banco de dados

Acesse via phpMyAdmin em http://localhost:8081 ou diretamente:

```bash
make shell-db
```

Conexão externa (DBeaver, TablePlus, etc.):

| Campo | Valor |
|-------|-------|
| Host | `127.0.0.1` |
| Port | `3306` |
| Database | `wordpress` |
| User | `wordpress` |
| Password | `wordpress` |

---

## Fluxo de desenvolvimento típico

```bash
# primeira vez
make up
make install

# após mudar dependências no composer.json
make install

# rodar testes
make test

# lint
make phpcs

# reset completo (apaga banco e WordPress)
make clean
make up
make install
```

---

## Estrutura dos arquivos nesta pasta

```
.dev/
├── Dockerfile              # imagem WordPress + Xdebug + Composer
├── Dockerfile.netskope     # variante com suporte ao proxy Netskope
├── docker-compose.yml      # stack principal
├── docker-compose.netskope.yml  # override para proxy corporativo
├── init-wp.sh              # inicialização idempotente via WP-CLI
├── php-dev.ini             # configurações PHP para desenvolvimento
├── php-cli.ini             # configurações PHP para WP-CLI
├── xdebug.ini              # configuração do Xdebug
└── patch-vendor.sh         # patches de compatibilidade PHP 8.4
```
