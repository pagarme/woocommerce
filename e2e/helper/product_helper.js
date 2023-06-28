const locators =  {
    product_search_field: '#woocommerce-product-search-field-0',
    role_button: 'button',
    role_link: 'link'
}

const searchProduct = async (page, productName) => {
    await page.click(locators.product_search_field)
    await page.type(locators.product_search_field, productName)
    await page.locator(locators.product_search_field).press('Enter');

}

const selectProduct = async (productName) => {
    await page.getByRole(locators.role_link, { name: productName }).click();
}

const addToCart = async page =>  {
    await page.getByRole(locators.role_button, { name: 'Add to cart' }).click()
}

const viewCart = async page => {
    await page.locator('#content').getByRole(locators.role_link, { name: 'View cart ' }).click();
}

const proceedCheckout = async page => {
    await page.getByRole(locators.role_link, { name: 'Proceed to checkout ' }).click();
}

module.exports = {
    searchProduct,
    selectProduct,
    addToCart,
    viewCart,
    proceedCheckout

}
