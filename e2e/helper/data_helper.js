const { faker } = require('@faker-js/faker')

const user_information = () => {
    return {
        first_name: faker.name.firstName(),
        last_name: faker.name.lastName(),
        email: faker.internet.email(),
        company: faker.company.name(),
        phone_number: faker.phone.number('+5551#########')
    }
}

const personal_legal_information = () => {
    return {
        cpf_type: 'Individuals',
        valid_cpf: '81007396091',
        cnpj_type: 'Legal Person',
        valid_cnpj: '07288037000106',
        cnpj_name: 'Woocommerce Tests Inc'
    }
}

const address_information = () => {
    return {
        address_line: faker.address.street(),
        country: 'Brazil',
        state: 'Rio de Janeiro',
        city: 'Rio de Janeiro',
        zip_code: faker.address.zipCode('########'),
        address_number: faker.address.buildingNumber()
    }
}

const credit_card_information_valid = () => {
    return {
        credit_card_holder_name: faker.name.firstName(),
        credit_card_number: '4000000000000010',
        credit_card_date: '03 / 32',
        credit_card_cvv: '997'
    }
}

module.exports = {
    user_information,
    personal_legal_information,
    address_information,
    credit_card_information_valid
}
