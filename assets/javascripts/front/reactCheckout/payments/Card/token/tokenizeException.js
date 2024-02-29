export default class TokenizeException extends Error {
    constructor(message) {
        super(message);
        this.name = this.constructor.name;
    }
}
