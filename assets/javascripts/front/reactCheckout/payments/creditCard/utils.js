export function getMonthAndYearFromExpirationDate(date) {
    return date.replace(/\s/g, '').split('/');
}

export function formatCardNumber(number) {
    return number.replace(/\s|\*/g, '')
}