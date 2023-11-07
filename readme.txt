=== Pagar.me módulo para WooCommerce ===
Contributors: Pagar.me
Tags: payments, pagarme, ecommerce, e-commerce, store, sales, sell, shop, cart, checkout, woocommerce, creditcard
Requires at least: 4.1
Tested up to: 6.3
Requires PHP: 7.1
Stable tag: 3.1.7
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

= 3.1.7 (07/11/2023) =
Você pode conferir essas atualizações aqui: [Github](https://github.com/pagarme/woocommerce/releases/tag/3.1.7)

* **Correções:**
  * Falha ao renovar pedido manualmente com cartão de crédito.
  * Falha na validação do cartão de crédito ao trocar meio de entrega.
  * Alto uso de memória no recebimento de webhook de Charge.
  * Falha na visualização do parcelamento para 2 cartões de crédito
  * Pedidos com reembolso na charge exibem erro no admin

= 3.1.6 (04/10/2023) =
Você pode conferir essas atualizações aqui: [Github](https://github.com/pagarme/woocommerce/releases/tag/3.1.6)

* **Melhorias:**
  * Validador de configurações da Dash da Pagar.me.
  * Compatibilização com o HPOS.
  * Receber apenas webhooks do Woocommerce.
  * Compatibilização com mod_pagespeed.

* **Correções:**
  * Envio de informações do boleto na criação de pedido.
  * Modal de dados obrigatórios para número de endereço.

== Upgrade Notice ==
Nosso plugin agora é compatível com Woocommerce Subscriptions
