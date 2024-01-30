=== Pagar.me módulo para WooCommerce ===
Contributors: Pagar.me
Tags: payments, pagarme, ecommerce, e-commerce, store, sales, sell, shop, cart, checkout, woocommerce, creditcard
Requires at least: 4.1
Tested up to: 6.3
Requires PHP: 7.1
Stable tag: 3.2.1
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

= 3.2.1 (30/01/2024) =
Você pode conferir essas atualizações aqui: [Github](https://github.com/pagarme/woocommerce/releases/tag/3.2.1)

* **Correções:**
  * Parcelamento de cartão de crédito

= 3.2.0 (29/01/2024) =
Você pode conferir essas atualizações aqui: [Github](https://github.com/pagarme/woocommerce/releases/tag/3.2.0)

* **Novas implementações:**
  * Autonomia na escolha dos métodos de pagamento para Subscriptions 
  * Parcelamento em até 12x no cartão de crédito para Subscriptions

* **Correções:**
  * Erro ao acessar a página de edição de produtos
  * Compatibilização com PHP 8.2/8.3
  * Erro ao finalizar compra de custo 0
  * Campos primeiro nome e sobrenome não obrigatórios
  * Falha ao gerar o pedido recorrente por falta de CustomerId
  * Redução duplicada de estoque - @tiagopapile

== Upgrade Notice ==
Nosso plugin agora é compatível com Woocommerce Subscriptions
