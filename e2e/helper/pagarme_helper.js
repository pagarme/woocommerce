const informCreditCardInfo = async (page, creditCardName, creditCardNumber) => {
    await page.getByLabel('Card Holder Name *').fill(creditCardName);
    await page.getByPlaceholder('•••• •••• •••• ••••').fill(creditCardNumber);
}

const informCvvAndExpirationDate = async (page, expirationDate, cvvNumber) => {
    await page.getByPlaceholder('MM / YY').fill(expirationDate);
    await page.getByPlaceholder('CVV').fill(cvvNumber);
}

module.exports = {
    informCreditCardInfo,
    informCvvAndExpirationDate
}
