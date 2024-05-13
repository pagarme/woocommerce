export function getMonthAndYearFromExpirationDate(date) {
    return date.replace(/\s/g, "").split("/");
}
