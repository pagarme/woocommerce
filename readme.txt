=== Pagar.me para WooCommerce ===
Contributors: pagarme
Tags: payment, pagarme, ecommerce, brasil, woocommerce
Requires at least: 4.1
Tested up to: 6.3
Requires PHP: 7.1
Stable tag: 3.2.2
License: MIT
License URI: https://github.com/pagarme/woocommerce/blob/master/LICENSE

Aceite diversos métodos de pagamento de forma simples e segura utilizando o Pagar.me!

== Description ==
Desenvolvemos um plugin que integra o Woocomerce a Pagar.me de forma prática e segura, assim não é preciso que seu time de tecnologia desenvolva nenhuma linha de código. Basta instalar e configurar o módulo para usar!

== Installation ==
Nosso processo de instalação é simples e bem detalhado:

* [Visão geral sobre nosso plugin](https://docs.pagar.me/docs/woocommerce-introdu%C3%A7%C3%A3o);
* [Requisitos de Instalação](https://docs.pagar.me/docs/requisitos-de-instala%C3%A7%C3%A3o-woocommerce);
* [Instalando o plugin](https://docs.pagar.me/docs/instalando-o-plugin-woocommerce-1);
* [Configurando os meios de pagamento](https://docs.pagar.me/docs/configurando-os-meios-de-pagamento-woocommerce);
* [Configurando a dashboard](https://docs.pagar.me/docs/configurando-a-dashboard-woocommerce).

== Changelog ==
Lançamos versões regularmente com melhorias, correções e atualizações.

= 3.3.0 (20/06/2024) =
Você pode conferir essas atualizações aqui: [Github](https://github.com/pagarme/woocommerce/releases/tag/3.3.0)

* **Novas funcionalidades:**
  *  [3DS 2.0](https://pagar.me/blog/3ds-2-0/);
  *  [Pedido Manual](https://woocommerce.com/document/como-gerenciar-pedidos/#section-16);
  *  Compatibilização com o [Checkout Blocks](https://docs.pagar.me/docs/requisitos-de-instala%C3%A7%C3%A3o-woocommerce#campos-do-checkout). 

* **Melhorias:**
  *  [Estorno automático](https://woocommerce.com/document/woocommerce-refunds/#automatic-refunds) (fluxo padrão do Woocommerce);
  *  Status de pedidos não autorizados que antes iriam para cancelado, agora vão para Malsucedido conforme o [fluxo padrão do Woocommerce](https://woocommerce.com/document/como-gerenciar-pedidos/#section-2).
  *  Adição de um [filter para extensão](https://github.com/pagarme/woocommerce/blob/master/docs/filters-actions/split.md) de Split.

* **Correções:**
  *  Traduções do cartão de crédito;
  *  Remoção de readonly em configurações no painel administrativo;
  *  Não acessar mais variáveis diretamente.

= 3.2.2 (16/04/2024) =
Você pode conferir essas atualizações aqui: [Github](https://github.com/pagarme/woocommerce/releases/tag/3.2.2)

* **Correções:**
  * Atualização do SweetAlert para 11.10.1
  * Compra com número 0 no endereço
  * Remoção de campo vazio no admin para Pix e Boleto
  * Webhook URL com o path da loja
  * Remoção da modal de preencha dados obrigatórios


== Upgrade Notice ==
Nosso plugin agora é compatível com Woocommerce Subscriptions
