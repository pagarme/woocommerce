Caso você deseje adicionar um split de pagamento basta você seguir os seguintes passos:

### 1 - Adicionando as configurações de Marketplace 
Adicione o filtro `pagarme_marketplace_config` para configurar os parâmetros de marketplace:

```php
add_filter("pagarme_marketplace_config", 'realizarConfiguracaoMarketplace', 10, 1);

function realizarConfiguracaoMarketplace($marketplaceConfig)
{
	/**
	 *	Você também pode configurar outros parâmetros como:
	 *	- responsibilityForProcessingFees
	 *	- responsibilityForChargebacks
	 *	- responsibilityForReceivingSplitRemainder
	 *	- responsibilityForReceivingExtrasAndDiscounts
	 *	
	 *	Eles recebem um dos seguintes parâmetros: 
	 *	- marketplace_sellers
	 *	- marketplace
	 *	- sellers
	 */
	$marketplaceConfig->mainRecipientId = "re_xxxxxxxxx0x00000xxxx000xx"; // Obrigatório | Valor do recipientId do Marketplace
	return $marketplaceConfig;
}
```

### 2 - Adicionando as regras de Split
Adicione o filtro `pagarme_split_order` para editar as regras de Split :

```php
add_filter("pagarme_split_order", 'alimentarSplit', 10, 1);

function alimentarSplit($splitArray)
{
	/**
	 * Todos os valores monetários precisam ser inteiros e em centavos. 
	 * Exemplo: R$ 4,00 você deve passar 400;
	 */
	$valorTotalDeComissaoMarketplace = 400;
	$splitArray['marketplace']['totalCommission'] = $valorTotalDeComissaoMarketplace; // Valor total de comissão destinado ao Marketplace
	
	/**
	 * Vocês pode adicionar mais de um recebedor, mas a soma de todos os marketplaceCommission 
	 * deve ser igual ao $valorTotalDeComissaoMarketplace;
	 * O nó de sellers deve receber um ou mais arrays com a seguinte estrutura de campos:
	 *  - marketplaceCommission; 
	 *  - commission; 
	 *  - pagarmeId.
	 */
	$splitArray['sellers'][] = [
		'marketplaceCommission' => 400, 				// Comissão do Marketplace
		'commission' => 800, 							// Comissão do recebedor
		'pagarmeId' => 're_xxxxxxxxx0x00000xxxx000xx' 	// Id do recebedor
	];
	return $splitArray;
}
```