#!/bin/sh
# Aplica os fixes de PHP 8.4 (implicitly nullable params -> explicit ?Type)
# nos pacotes do vendor/ que nao podemos atualizar via composer update.
#
# Reexecute este script apos `composer install` para reaplicar as patches:
#   make patch-vendor

set -e

PLUGIN_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
TARGETS="
${PLUGIN_ROOT}/vendor/pagarme/ecommerce-module-core/src
"

REGEX='s/(?<![?\\])((?:\\\\)?[A-Z][A-Za-z_0-9]*(?:\\\\[A-Z][A-Za-z_0-9]*)*|string|int|float|bool|array|callable|iterable|object|mixed)([ \t]+\$[A-Za-z_][A-Za-z0-9_]*\s*=\s*null\b)/?$1$2/g'

for target in $TARGETS; do
    if [ ! -d "$target" ]; then
        echo "[patch-vendor] AVISO: $target nao existe (rode 'make install' primeiro?)"
        continue
    fi
    echo "[patch-vendor] Aplicando em $target ..."
    find "$target" -name '*.php' -type f -exec perl -i -pe "$REGEX" {} +
done

# Confere se sobrou alguma ocorrencia (excluindo FQN com leading \, que o regex acima nao pega)
REMAINING=$(grep -rn -E '(string|int|float|bool|array|callable|iterable|object|mixed|[A-Z][a-zA-Z_]*)[ \t]+\$[a-zA-Z_]+[ \t]*=[ \t]*null\b' \
    $TARGETS \
    --include='*.php' 2>/dev/null | grep -v -E '\?[A-Za-z]' | wc -l | tr -d ' ')

echo "[patch-vendor] Ocorrencias restantes: $REMAINING"
echo "[patch-vendor] OK."
