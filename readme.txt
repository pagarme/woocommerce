=== Pagar.me módulo para WooCommerce ===
Contributors: Pagar.me
Tags: payments, pagarme, ecommerce, e-commerce, store, sales, sell, shop, cart, checkout, woocommerce, creditcard
Requires at least: 4.1
Tested up to: 6.2.2
Requires PHP: 7.1
Stable tag: 3.1.0
License: MIT
License URI: https://github.com/pagarme/woocommerce/blob/master/LICENSE

Desenvolvemos um módulo que integra o Woocomerce a Pagar.me de forma prática e segura, assim não é preciso que seu time de tecnologia desenvolva nenhuma linha de código. Basta instalar e configurar o módulo para usar!

== Installation ==
Nosso processo de instalação é simples e bem detalhado:

* [Visão geral sobre nosso plugin](https://docs.pagar.me/docs/woocommerce-introdu%C3%A7%C3%A3o);
* [Requisitos de Instalação](https://docs.pagar.me/docs/requisitos-de-instala%C3%A7%C3%A3o-woocommerce);
* [Instalando o plugin](https://docs.pagar.me/docs/instalando-o-plugin-woocommerce-1);
* [Configurando os meios de pagamento](https://docs.pagar.me/docs/configurando-os-meios-de-pagamento-woocommerce);
* [Configurando a dashboard](https://docs.pagar.me/docs/configurando-a-dashboard-woocommerce).

== Changelog ==
Lançamos versões regularmente com melhorias, correções e atualizações.
= 3.1.0 (05/07/2023) =
Você pode conferir essas atualizações aqui: [Github](https://github.com/pagarme/woocommerce/releases/tag/3.1.0)

* **Novas implementações:**
  * Compatibilidade com [Woocommerce Subscriptions](https://woocommerce.com/pt-br/products/woocommerce-subscriptions/);
  * Adicionar informações de pagamento nos emails enviados pelo Woocommerce;
  * Validações de configurações;
  * Validação se os campos obrigatórios estão ativos;

* **Correções:**
  * Valor divergente em multi-meios com a casa do milhar;
  * Finalizar pagamento sem uma bandeira de cartão valida;

* **Melhorias:**
  * Compatibilização com plugins;
  * Guardar número de parcelas do pedido;
  * Recebimento de webhooks chargeback e payment_failed.

= 3.0.0 (05/06/2023) =
Você pode conferir essas atualizações aqui: [Github](https://github.com/pagarme/woocommerce/releases/tag/3.0.0)

* **Novas implementações:**
  * Os métodos de pagamento agora são independentes;
  * Possibilidade de aplicar descontos para cada método de pagamento.

* **Correções:**
  * Permitir plugins de pedidos sequenciais.

* **Melhorias:**
  * As funções da carteira foram revisadas;
  * O design do front-end foi refinado.

Para consultar versões anteriores, acesse nosso [Github](https://github.com/pagarme/woocommerce/releases).

== Upgrade Notice ==
Nosso plugin agora é compatível com Woocommerce Subscriptions
