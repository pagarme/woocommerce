
function validateBilling(billing) {

    const requiredFields = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address_1',
        'city',
        'postcode',
        'state',
        'country'
    ];
    for (const field of requiredFields) {
        if (!billing[field]) {
            return false;
        }
    }
    return true;
}

export default validateBilling;