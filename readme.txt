=== Pagar.me módulo para WooCommerce ===
Contributors: Pagar.me
Tags: payments, pagarme, ecommerce, e-commerce, store, sales, sell, shop, cart, checkout, woocommerce, creditcard
Requires at least: 4.1
Tested up to: 6.3
Requires PHP: 7.1
Stable tag: 3.1.5
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

= 3.1.5 (04/09/2023) =
Você pode conferir essas atualizações aqui: [Github](https://github.com/pagarme/woocommerce/releases/tag/3.1.5)

* **Correções:**
  * Incompatibilidade de seletores css de alguns temas para arquivos js.
  * Quando atualiza algum dado de checkout o cartão perde a bandeira já consultada.
  * Incompatibilidade com métodos de pagamento que utilizam o evento checkout_place_order.
  * Recorrência não finaliza compra com Boleto ou PIX.
  * Incompatibilidade com caracteres especiais nas instruções de pagamento do boleto.

= 3.1.4 (17/08/2023) =
Você pode conferir essas atualizações aqui: [Github](https://github.com/pagarme/woocommerce/releases/tag/3.1.4)

* **Correções:**
  * Problema ao criar assinatura com pix e boleto.

* **Melhorias:**
  * Valor padrão para bandeiras de Cartão de Crédito e Voucher.

Para consultar versões anteriores, acesse nosso [Github](https://github.com/pagarme/woocommerce/releases).

== Upgrade Notice ==
Nosso plugin agora é compatível com Woocommerce Subscriptions
