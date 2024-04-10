/* jshint esversion: 6 */
const useWallet = (cards, cardIndex, setSelectCard, setBrand) => {
    const filterHandler = (inputValue) => {
        cards.filter((option) =>
            option.label.toLowerCase().startsWith(inputValue.toLowerCase()),
        );
    };

    const cardChangeHandler = (value) => {
        setSelectCard(cardIndex, value);
        if (!cards) {
            return;
        }
        const foundedCard = cards.find((card) => card.value === value);
        if (foundedCard) {
            setBrand(cardIndex, foundedCard.brand);
            return;
        }
        setBrand(cardIndex, "");
    };

    return {
        filterHandler,
        cardChangeHandler,
    };
};

export default useWallet;
