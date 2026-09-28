COMPOSE         := docker compose -f .dev/docker-compose.yml
COMPOSE_NETSKOPE := $(COMPOSE) -f .dev/docker-compose.netskope.yml
WP_SERVICE := wordpress
DB_SERVICE := db

.DEFAULT_GOAL := help

.PHONY: help
help: ## Lista todos os comandos disponíveis
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-25s\033[0m %s\n", $$1, $$2}'

# ---------------------------------------------------------------------------
# Ambiente de desenvolvimento (docker-compose.dev.yml)
# ---------------------------------------------------------------------------

.PHONY: build
build: ## Build (ou rebuild) da imagem sem proxy corporativo
	$(COMPOSE) build

.PHONY: build-netskope
build-netskope: ## Build (ou rebuild) da imagem com suporte ao proxy Netskope
	$(COMPOSE_NETSKOPE) build

.PHONY: up
up: ## Sobe o ambiente em background (sem proxy corporativo)
	$(COMPOSE) up -d
	@echo ""
	@echo "Inicializando WordPress + WooCommerce (pode demorar na 1a vez)..."
	@$(COMPOSE) run --rm wp-cli
	@echo ""
	@echo "WordPress:    $${WP_URL:-http://woo.localhost}"
	@echo "wp-admin:     $${WP_URL:-http://woo.localhost}/wp-admin  (user: admin / pass: admin)"
	@echo "phpMyAdmin:   http://localhost:$${PMA_PORT:-8081}          (user: root  / pass: root)"
	@echo ""

.PHONY: up-netskope
up-netskope: ## Sobe o ambiente em background (com proxy Netskope corporativo)
	$(COMPOSE_NETSKOPE) up -d
	@echo ""
	@echo "Inicializando WordPress + WooCommerce (pode demorar na 1a vez)..."
	@$(COMPOSE_NETSKOPE) run --rm wp-cli
	@echo ""
	@echo "WordPress:    $${WP_URL:-http://woo.localhost}"
	@echo "wp-admin:     $${WP_URL:-http://woo.localhost}/wp-admin  (user: admin / pass: admin)"
	@echo "phpMyAdmin:   http://localhost:$${PMA_PORT:-8081}          (user: root  / pass: root)"
	@echo ""

.PHONY: seed
seed: ## Reexecuta o init do WP sem proxy corporativo (idempotente)
	$(COMPOSE) run --rm wp-cli

.PHONY: seed-netskope
seed-netskope: ## Reexecuta o init do WP com proxy Netskope (idempotente)
	$(COMPOSE_NETSKOPE) run --rm wp-cli

.PHONY: down
down: ## Para e remove os containers (volumes preservados)
	$(COMPOSE) down

.PHONY: restart
restart: down up ## Reinicia o ambiente

.PHONY: clean
clean: ## Para e remove containers + volumes (apaga o WordPress instalado!)
	$(COMPOSE) down -v

.PHONY: logs
logs: ## Acompanha os logs de todos os serviços
	$(COMPOSE) logs -f

.PHONY: logs-wp
logs-wp: ## Acompanha apenas os logs do WordPress/Apache
	$(COMPOSE) logs -f $(WP_SERVICE)

.PHONY: ps
ps: ## Lista status dos containers
	$(COMPOSE) ps

.PHONY: shell
shell: ## Abre bash no container do WordPress
	$(COMPOSE) exec $(WP_SERVICE) bash

.PHONY: shell-db
shell-db: ## Abre o cliente mysql no container do banco
	$(COMPOSE) exec $(DB_SERVICE) mariadb -u wordpress -pwordpress wordpress

.PHONY: install
install: ## Roda composer install dentro do container + reaplica patches em vendor/
	$(COMPOSE) exec $(WP_SERVICE) bash -c "cd wp-content/plugins/woo-pagarme-payments && composer install"
	@$(MAKE) patch-vendor

.PHONY: patch-vendor
patch-vendor: ## (Re)aplica patches PHP 8.4 nos pacotes do vendor/ que nao atualizamos
	./.dev/patch-vendor.sh

.PHONY: test
test: ## Executa o phpunit dentro do container
	$(COMPOSE) exec $(WP_SERVICE) bash -c "cd wp-content/plugins/woo-pagarme-payments && vendor/bin/phpunit"

.PHONY: phpcs
phpcs: ## Executa o phpcs dentro do container
	$(COMPOSE) exec $(WP_SERVICE) bash -c "cd wp-content/plugins/woo-pagarme-payments && vendor/bin/phpcs ."

.PHONY: xdebug-log
xdebug-log: ## Mostra o log do Xdebug (tail -f)
	$(COMPOSE) exec $(WP_SERVICE) tail -f /tmp/xdebug.log
