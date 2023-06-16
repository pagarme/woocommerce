const locators =  {
    first_name_id: '#billing_first_name',
    last_name_id: '#billing_last_name',
    person_id: '#select2-billing_persontype-container',
    cpf_id: '#billing_cpf',
    cnpj_id: '#billing_cnpj',
    company_name_id: '#billing_company',
    country_id: '#select2-billing_country-container',
    zipcode_id: '#billing_postcode',
    street_address_name: '#billing_address_1',
    number_address_id: '#billing_number',
    neighborhood_id: '#billing_neighborhood',
    city_id: '#billing_city',
    state_id: '#select2-billing_state-container',
    phone_id: '#billing_phone',
    email_id: '#billing_email',
    place_order_id: '#place_order'
}

const informFirstAndLastName = async (page, firstName, lastName) => {
    await page.click(locators.first_name_id)
    await page.type(locators.first_name_id, firstName);
    await page.click(locators.last_name_id)
    await page.type(locators.last_name_id, lastName);

}

const selectPersonType = async (page, personType) => {
    await page.click(locators.person_id);
    await page.getByRole('textbox', { name: personType }).click();

}

const informCpf = async (page, cpfNumber) => {
    await page.click(locators.cpf_id)
    await page.type(locators.cpf_id, '81007396091');
}

const informCnpj = async (page, cnpjCompanyName, cnpjNumber) => {
    await page.click(locators.company_name_id)
    await page.type(locators.company_name_id, cnpjCompanyName);
    await page.click(locators.cnpj_id)
    await page.type(locators.cnpj_id, cnpjNumber);
}

const selectCountry = async (page, countryInfo) => {
    await page.click(locators.country_id);
    await page.getByRole('option', { name: countryInfo }).click();
}

const informZipCode = async (page, zipCode) => {
    await page.click(locators.zipcode_id);
    await page.type(locators.zipcode_id, zipCode);
}

const informAddress = async (page, streetAddress, numberAddress) => {
    await page.click(locators.street_address_name);
    await page.type(locators.street_address_name, streetAddress);
    await page.click(locators.number_address_id);
    await page.type(locators.number_address_id, numberAddress);

}

const informNeighborhoodAndCity = async (page, neighborhoodName, cityName) => {
    await page.click(locators.neighborhood_id);
    await page.type(locators.neighborhood_id, neighborhoodName);
    await page.click(locators.city_id);
    await page.type(locators.city_id, cityName);
}

const selectState = async (page, stateName) => {
    await page.click(locators.state_id);
    await page.getByRole('option', { name: stateName }).click();
}

const informPhone = async (page, phoneNumber) => {
    await page.click(locators.phone_id);
    await page.type(locators.phone_id, phoneNumber);
}

const informEmail = async (page, emailData) => {
    await page.click(locators.email_id);
    await page.type(locators.email_id, emailData);
}

const selectPlaceOrder = async (page) => {
    await page.click(locators.place_order_id);
}

module.exports = {
    informFirstAndLastName,
    selectPersonType,
    informCpf,
    informCnpj,
    selectCountry,
    informZipCode,
    informAddress,
    informNeighborhoodAndCity,
    selectState,
    informPhone,
    informEmail,
    selectPlaceOrder
}
