const { test, expect } = require('@playwright/test')
const { searchProduct,
        addToCart,
        viewCart,
        proceedCheckout } = require('../helper/product_helper')

const { informFirstAndLastName,
        selectPersonType,
        selectPersonTypeCnpj,
        informCpf,
        selectCountry,
        informZipCode,
        informAddress,
        informNeighborhoodAndCity,
        selectState,
        informPhone,
        informEmail,
        selectPlaceOrder,
        informCnpj} = require('../helper/checkout_helper')

const { informCreditCardInfo,
        informCvvAndExpirationDate } = require('../helper/pagarme_helper')

const { user_information,
        personal_legal_information,
        address_information,
        credit_card_information_valid } = require('../helper/data_helper')


test.describe('Cartão de Crédito', () => {
    test.beforeEach(async ({ page}) => {
        await page.goto('/')
    })

    test('Criar pedido com CPF', async ({page}) => {
        const user = user_information();
        const person = personal_legal_information();
        const address = address_information();
        const credit_card = credit_card_information_valid();
        await searchProduct(page, process.env.PRODUCT);
        await addToCart(page);
        await viewCart(page);
        await proceedCheckout(page);
        await informFirstAndLastName(page, user.first_name, user.last_name);
        await selectPersonType(page, person.cpf_type);
        await informCpf(page,person.valid_cpf);
        await selectCountry(page, address.country);
        await informZipCode(page, address.zip_code);
        await informAddress(page, address.address_line, address.address_number);
        await informNeighborhoodAndCity(page, address.state, address.city);
        await selectState(page, address.state);
        await informPhone(page, user.phone_number);
        await informEmail(page, user.email);
        await informCreditCardInfo(page, credit_card.credit_card_holder_name, credit_card.credit_card_number);
        await informCvvAndExpirationDate(page, credit_card.credit_card_date, credit_card.credit_card_cvv);
        await selectPlaceOrder(page);
        await expect(page.getByText('Thank you. Your order has been received.')).toBeVisible();
        await expect(page.getByText('The status of your transaction is PAID.')).toBeVisible();

    })

    test('Criar pedido com CNPJ', async ({page}) => {
        const user = user_information();
        const person = personal_legal_information();
        const address = address_information();
        const credit_card = credit_card_information_valid();
        await searchProduct(page, process.env.PRODUCT);
        await addToCart(page);
        await viewCart(page);
        await proceedCheckout(page);
        await informFirstAndLastName(page, user.first_name, user.last_name);
        await selectPersonTypeCnpj(page, person.cnpj_type);
        await informCnpj(page, person.cnpj_name, person.valid_cnpj);
        await selectCountry(page, address.country);
        await informZipCode(page, address.zip_code);
        await informAddress(page, address.address_line, address.address_number);
        await informNeighborhoodAndCity(page, address.state, address.city);
        await selectState(page, address.state);
        await informPhone(page, user.phone_number);
        await informEmail(page, user.email);
        await informCreditCardInfo(page, credit_card.credit_card_holder_name, credit_card.credit_card_number);
        await informCvvAndExpirationDate(page, credit_card.credit_card_date, credit_card.credit_card_cvv);
        await selectPlaceOrder(page);
        await expect(page.getByText('Thank you. Your order has been received.')).toBeVisible();
        await expect(page.getByText('The status of your transaction is PAID.')).toBeVisible();

    })
})
