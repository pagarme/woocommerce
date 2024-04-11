/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/index.js":
/*!***********************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/index.js ***!
  \***********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _inputs_components_Installments__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./inputs/components/Installments */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Installments/index.js");
/* harmony import */ var _inputs_components_InputHolderName__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./inputs/components/InputHolderName */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputHolderName/index.js");
/* harmony import */ var _inputs_components_InputNumber__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./inputs/components/InputNumber */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputNumber/index.js");
/* harmony import */ var _inputs_components_InputExpiry__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./inputs/components/InputExpiry */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputExpiry/index.js");
/* harmony import */ var _inputs_components_InputCvv__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./inputs/components/InputCvv */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputCvv/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_10___default = /*#__PURE__*/__webpack_require__.n(prop_types__WEBPACK_IMPORTED_MODULE_10__);
/* harmony import */ var _inputs_components_Wallet__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./inputs/components/Wallet */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Wallet/index.js");
/* harmony import */ var _useCard__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./useCard */ "./assets/javascripts/front/reactCheckout/payments/Card/useCard.js");
/* harmony import */ var _useCardValidation__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./useCardValidation */ "./assets/javascripts/front/reactCheckout/payments/Card/useCardValidation.js");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_9__);

/* jshint esversion: 9 */










const {
  CheckboxControl
} = window.wc.blocksComponents;
const Card = ({
  billing,
  components,
  backendConfig,
  cardIndex,
  eventRegistration
}) => {
  const {
    LoadingMask
  } = components;
  const {
    holderNameLabel,
    numberLabel,
    expiryLabel,
    cvvLabel,
    installmentsLabel,
    saveCardLabel,
    walletLabel
  } = backendConfig.fieldsLabels;
  const {
    isLoading,
    setIsLoading,
    setHolderName,
    setNumber,
    setExpirationDate,
    setInstallment,
    setBrand,
    setCvv,
    setWalletId,
    setErrors,
    saveCardChangeHandler,
    formatFieldId,
    holderName,
    number,
    expirationDate,
    selectedInstallment,
    brand,
    cvv,
    saveCard,
    walletId,
    errors
  } = (0,_useCard__WEBPACK_IMPORTED_MODULE_7__["default"])(cardIndex);
  const {
    validateAllFields
  } = (0,_useCardValidation__WEBPACK_IMPORTED_MODULE_8__["default"])(cardIndex, errors, setErrors, backendConfig.fieldErrors);
  const {
    onCheckoutValidation
  } = eventRegistration;
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_9__.useEffect)(() => {
    return onCheckoutValidation(() => {
      validateAllFields(holderName, number, expirationDate, cvv);
      return true;
    });
  }, [onCheckoutValidation, holderName, number, expirationDate, cvv, backendConfig]);
  console.log(0);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(LoadingMask, {
    isLoading: isLoading
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-components-form"
  }, backendConfig.walletEnabled && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_inputs_components_Wallet__WEBPACK_IMPORTED_MODULE_6__["default"], {
    label: walletLabel,
    selectedCard: walletId,
    cards: backendConfig.cards,
    cardIndex: cardIndex,
    setSelectCard: setWalletId,
    setBrand: setBrand
  }), walletId.length === 0 && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_inputs_components_InputHolderName__WEBPACK_IMPORTED_MODULE_2__["default"], {
    id: formatFieldId("holder_name"),
    label: holderNameLabel,
    inputValue: holderName,
    setInputValue: setHolderName,
    cardIndex: cardIndex,
    errors: errors,
    setErrors: setErrors,
    fieldErrors: backendConfig.fieldErrors
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_inputs_components_InputNumber__WEBPACK_IMPORTED_MODULE_3__["default"], {
    id: formatFieldId("number"),
    label: numberLabel,
    inputValue: number,
    setInputValue: setNumber,
    brand: brand,
    setBrand: setBrand,
    brands: backendConfig.brands,
    setIsLoading: setIsLoading,
    cardIndex: cardIndex,
    errors: errors,
    setErrors: setErrors,
    fieldErrors: backendConfig.fieldErrors
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_inputs_components_InputExpiry__WEBPACK_IMPORTED_MODULE_4__["default"], {
    id: formatFieldId("expiry"),
    label: expiryLabel,
    inputValue: expirationDate,
    setInputValue: setExpirationDate,
    cardIndex: cardIndex,
    errors: errors,
    setErrors: setErrors,
    fieldErrors: backendConfig.fieldErrors
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_inputs_components_InputCvv__WEBPACK_IMPORTED_MODULE_5__["default"], {
    id: formatFieldId("cvv"),
    label: cvvLabel,
    inputValue: cvv,
    setInputValue: setCvv,
    cardIndex: cardIndex,
    errors: errors,
    setErrors: setErrors,
    fieldErrors: backendConfig.fieldErrors
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_inputs_components_Installments__WEBPACK_IMPORTED_MODULE_1__["default"], {
    label: installmentsLabel,
    installments: backendConfig.installments,
    installmentsType: backendConfig.installmentsType,
    selectedInstallment: selectedInstallment,
    setSelectedInstallment: setInstallment,
    brand: brand,
    cartTotal: billing.cartTotal.value,
    setIsLoading: setIsLoading,
    cardIndex: cardIndex
  }), walletId.length === 0 && backendConfig.walletEnabled && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(CheckboxControl, {
    label: saveCardLabel,
    checked: saveCard,
    onChange: saveCardChangeHandler
  })));
};
Card.propType = {
  billing: (prop_types__WEBPACK_IMPORTED_MODULE_10___default().object).isRequired,
  components: (prop_types__WEBPACK_IMPORTED_MODULE_10___default().object).isRequired,
  backendConfig: (prop_types__WEBPACK_IMPORTED_MODULE_10___default().object).isRequired,
  cardIndex: (prop_types__WEBPACK_IMPORTED_MODULE_10___default().number).isRequired,
  eventRegistration: (prop_types__WEBPACK_IMPORTED_MODULE_10___default().object).isRequired
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Card);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputCvv/index.js":
/*!**************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputCvv/index.js ***!
  \**************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(prop_types__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _MaskedInput__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../MaskedInput */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/MaskedInput/index.js");
/* harmony import */ var _useCardValidation__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../useCardValidation */ "./assets/javascripts/front/reactCheckout/payments/Card/useCardValidation.js");

/* jshint esversion: 9 */



const InputCvv = ({
  id,
  label,
  cardIndex,
  inputValue,
  setInputValue,
  errors,
  setErrors,
  fieldErrors
}) => {
  const {
    validateInputCvv
  } = (0,_useCardValidation__WEBPACK_IMPORTED_MODULE_2__["default"])(cardIndex, errors, setErrors, fieldErrors);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_MaskedInput__WEBPACK_IMPORTED_MODULE_1__["default"], {
    id: id,
    label: label,
    mask: "9999",
    inputValue: inputValue,
    setInputValue: setInputValue,
    cardIndex: cardIndex,
    validate: validateInputCvv,
    validateIndex: "inputCvv",
    errors: errors
  }), errors.inputCvv && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-components-validation-error",
    role: "alert"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, errors.inputCvv)));
};
InputCvv.propTypes = {
  id: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  label: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  cardIndex: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().number).isRequired,
  inputValue: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  setInputValue: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().func).isRequired,
  errors: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().object).isRequired,
  setErrors: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().func).isRequired
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (InputCvv);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputExpiry/index.js":
/*!*****************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputExpiry/index.js ***!
  \*****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(prop_types__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _MaskedInput__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../MaskedInput */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/MaskedInput/index.js");
/* harmony import */ var _useCardValidation__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../useCardValidation */ "./assets/javascripts/front/reactCheckout/payments/Card/useCardValidation.js");

/* jshint esversion: 9 */



const InputExpiry = ({
  id,
  label,
  cardIndex,
  inputValue,
  setInputValue,
  errors,
  setErrors,
  fieldErrors
}) => {
  const {
    validateInputExpiry
  } = (0,_useCardValidation__WEBPACK_IMPORTED_MODULE_2__["default"])(cardIndex, errors, setErrors, fieldErrors);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_MaskedInput__WEBPACK_IMPORTED_MODULE_1__["default"], {
    id: id,
    label: label,
    mask: "99/99",
    maskChar: "_",
    inputValue: inputValue,
    setInputValue: setInputValue,
    cardIndex: cardIndex,
    validate: validateInputExpiry,
    validateIndex: "inputExpiry",
    errors: errors
  }), errors.inputExpiry && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-components-validation-error",
    role: "alert"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, errors.inputExpiry)));
};
InputExpiry.propTypes = {
  id: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  label: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  cardIndex: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().number).isRequired,
  inputValue: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  setInputValue: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().func).isRequired,
  errors: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().object).isRequired,
  setErrors: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().func).isRequired
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (InputExpiry);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputHolderName/index.js":
/*!*********************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputHolderName/index.js ***!
  \*********************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(prop_types__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _useInputHolderName__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./useInputHolderName */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputHolderName/useInputHolderName.js");

/* jshint esversion: 8 */


const InputHolderName = ({
  id,
  label,
  inputValue,
  setInputValue,
  cardIndex,
  errors,
  setErrors,
  fieldErrors
}) => {
  const {
    setIsActive,
    cssClasses,
    inputChangeHandler,
    inputBlurHandler
  } = (0,_useInputHolderName__WEBPACK_IMPORTED_MODULE_1__["default"])(inputValue, setInputValue, cardIndex, errors, setErrors, fieldErrors);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: cssClasses
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: id
  }, label), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    id: id,
    value: inputValue,
    onChange: inputChangeHandler,
    onFocus: () => setIsActive(true),
    onBlur: inputBlurHandler
  }), errors.inputHolderName && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-components-validation-error",
    role: "alert"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, errors.inputHolderName)));
};
InputHolderName.propTypes = {
  id: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().string).isRequired,
  label: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().string).isRequired,
  inputValue: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().string).isRequired,
  setInputValue: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().func).isRequired,
  cardIndex: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().number).isRequired,
  errors: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().object).isRequired,
  setErrors: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().func).isRequired
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (InputHolderName);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputHolderName/useInputHolderName.js":
/*!**********************************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputHolderName/useInputHolderName.js ***!
  \**********************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _useCardValidation__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../../useCardValidation */ "./assets/javascripts/front/reactCheckout/payments/Card/useCardValidation.js");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* jshint esversion: 9 */


const useInputHolderName = (inputValue, setInputValue, cardIndex, errors, setErrors, fieldErrors) => {
  const {
    validateInputHolderName
  } = (0,_useCardValidation__WEBPACK_IMPORTED_MODULE_0__["default"])(cardIndex, errors, setErrors, fieldErrors);
  let cssClasses = "wc-block-components-text-input";
  const [isActive, setIsActive] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  if (isActive || inputValue.length) {
    cssClasses += " is-active";
  }
  if (errors.hasOwnProperty('inputHolderName')) {
    cssClasses += " has-error";
  }
  const inputChangeHandler = event => {
    const result = event.target.value.replace(/[^a-z ]/gi, "");
    setInputValue(cardIndex, result);
  };
  const inputBlurHandler = event => {
    validateInputHolderName(event.target.value);
    setIsActive(false);
  };
  return {
    setIsActive,
    cssClasses,
    inputChangeHandler,
    inputBlurHandler
  };
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (useInputHolderName);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputNumber/index.js":
/*!*****************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputNumber/index.js ***!
  \*****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(prop_types__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var react_input_mask__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-input-mask */ "./node_modules/react-input-mask/index.js");
/* harmony import */ var react_input_mask__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_input_mask__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _useInputNumber__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./useInputNumber */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputNumber/useInputNumber.js");

/* jshint esversion: 8 */



const InputNumber = ({
  id,
  label,
  inputValue,
  setInputValue,
  brand,
  setBrand,
  brands,
  setIsLoading,
  cardIndex,
  errors,
  setErrors,
  fieldErrors
}) => {
  const {
    setIsActive,
    cssClasses,
    brandImageSrc,
    inputChangeHandler,
    inputBlurHandler
  } = (0,_useInputNumber__WEBPACK_IMPORTED_MODULE_2__["default"])(inputValue, brands, setInputValue, setBrand, setIsLoading, cardIndex, errors, setErrors, fieldErrors);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: cssClasses
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: id
  }, label), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)((react_input_mask__WEBPACK_IMPORTED_MODULE_1___default()), {
    className: "pagarme-card-form-card-number",
    type: "text",
    id: id,
    mask: "9999 9999 9999 9999",
    maskChar: "\u2022",
    onFocus: () => setIsActive(true),
    onChange: inputChangeHandler,
    value: inputValue,
    onBlur: inputBlurHandler
  }), brandImageSrc && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    src: brandImageSrc,
    alt: brand
  }), errors.inputNumber && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-components-validation-error",
    role: "alert"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, errors.inputNumber)));
};
InputNumber.propTypes = {
  id: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  label: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  inputValue: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  setInputValue: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().func).isRequired,
  brand: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  setBrand: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().func).isRequired,
  brands: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().object).isRequired,
  setIsLoading: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().func).isRequired,
  cardIndex: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().number).isRequired,
  errors: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().object).isRequired,
  setErrors: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().func).isRequired
  // onCheckoutValidation: PropTypes.bool.isRequired,
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (InputNumber);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputNumber/useInputNumber.js":
/*!**************************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/InputNumber/useInputNumber.js ***!
  \**************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils_cardNumberFormatter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../utils/cardNumberFormatter */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/utils/cardNumberFormatter.js");
/* harmony import */ var _useCardValidation__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../useCardValidation */ "./assets/javascripts/front/reactCheckout/payments/Card/useCardValidation.js");
/* jshint esversion: 9 */



const binUrl = "https://api.pagar.me/bin/v1/";
const mundipaggCdn = "https://cdn.mundipagg.com/assets/images/logos/brands/png/";
const useInputNumber = (inputValue, brands, setInputValue, setBrand, setIsLoading, cardIndex, errors, setErrors, fieldErrors) => {
  const {
    validateInputNumber
  } = (0,_useCardValidation__WEBPACK_IMPORTED_MODULE_2__["default"])(cardIndex, errors, setErrors, fieldErrors);
  const [brandImageSrc, setBrandImageSrc] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)("");
  let cssClasses = "wc-block-components-text-input pagarme-credit-card-number-container";
  const [isActive, setIsActive] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  if (isActive || inputValue.length) {
    cssClasses += " is-active";
  }
  if (errors.hasOwnProperty('inputNumber')) {
    cssClasses += " has-error";
  }
  const inputChangeHandler = event => {
    setInputValue(cardIndex, event.target.value);
  };
  const getBrandContengency = bin => {
    let oldPrefix = "";
    let brand = null;
    for (const [currentBrandKey, currentBrand] of Object.entries(brands)) {
      for (const prefix of currentBrand.prefixes) {
        const prefixText = prefix.toString();
        if (bin.indexOf(prefixText) === 0 && oldPrefix.length < prefixText.length) {
          oldPrefix = prefixText;
          brand = currentBrandKey;
        }
      }
    }
    return brand;
  };
  const resetBrand = () => {
    setBrand(cardIndex, "");
    setBrandImageSrc("");
  };
  const changeBrand = async () => {
    const cardNumber = (0,_utils_cardNumberFormatter__WEBPACK_IMPORTED_MODULE_1__.formatCardNumber)(inputValue);
    if (cardNumber.length !== 16) {
      resetBrand();
      return;
    }
    setIsLoading(true);
    const bin = cardNumber.substring(0, 6);
    const binFormattedUrl = `${binUrl}${bin}`;
    try {
      const response = await fetch(binFormattedUrl);
      const result = await response.json();
      let brand = result.brand;
      if (!response.ok || typeof result.brandName == "undefined") {
        brand = getBrandContengency(bin);
      }
      if (brand === null) {
        resetBrand();
        setIsLoading(false);
        return;
      }
      if (result.brandImage) {
        setBrandImageSrc(result.brandImage);
        setIsLoading(false);
        setBrand(cardIndex, brand);
        return;
      }
      const brandImage = `${mundipaggCdn}${brand}.png`;
      setBrandImageSrc(brandImage);
      setIsLoading(false);
      setBrand(cardIndex, brand);
    } catch (e) {
      resetBrand();
      setIsLoading(false);
    }
  };
  const inputBlurHandler = event => {
    validateInputNumber(event.target.value);
    changeBrand();
    setIsActive(false);
  };
  return {
    setIsActive,
    cssClasses,
    brandImageSrc,
    inputChangeHandler,
    inputBlurHandler
  };
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (useInputNumber);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Installments/index.js":
/*!******************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Installments/index.js ***!
  \******************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(prop_types__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _useInstallments__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./useInstallments */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Installments/useInstallments.js");

/* jshint esversion: 8 */


const {
  ComboboxControl
} = wp.components;
const Installments = ({
  label,
  installments,
  installmentsType,
  selectedInstallment,
  setSelectedInstallment,
  brand,
  cartTotal,
  setIsLoading,
  cardIndex
}) => {
  const {
    installmentsOptions,
    filterHandler,
    installmentsChangeHandler
  } = (0,_useInstallments__WEBPACK_IMPORTED_MODULE_1__["default"])(installments, installmentsType, brand, cartTotal, setSelectedInstallment, setIsLoading, cardIndex);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-components-select-input pagarme-installments-combobox"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-components-combobox is-active"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(ComboboxControl, {
    className: "wc-block-components-combobox-control",
    label: label,
    onChange: installmentsChangeHandler,
    value: selectedInstallment,
    options: installmentsOptions,
    onFilterValueChange: filterHandler,
    allowReset: false,
    autoComplete: "off"
  })));
};
Installments.propTypes = {
  label: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().string).isRequired,
  installments: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().array).isRequired,
  installmentsType: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().number).isRequired,
  selectedInstallment: prop_types__WEBPACK_IMPORTED_MODULE_2___default().oneOfType([(prop_types__WEBPACK_IMPORTED_MODULE_2___default().string), (prop_types__WEBPACK_IMPORTED_MODULE_2___default().number)]).isRequired,
  setSelectedInstallment: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().func).isRequired,
  brand: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().string).isRequired,
  cartTotal: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().number).isRequired,
  setIsLoading: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().func).isRequired,
  cardIndex: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().number).isRequired
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Installments);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Installments/installmentsTypeEnum.js":
/*!*********************************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Installments/installmentsTypeEnum.js ***!
  \*********************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   installmentsTypesEnum: () => (/* binding */ installmentsTypesEnum)
/* harmony export */ });
const installmentsTypesEnum = {
  FOR_ALL_CARD_BRANDS: 1,
  BY_CARD_BRAND: 2
};

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Installments/useInstallments.js":
/*!****************************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Installments/useInstallments.js ***!
  \****************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _utils_installmentsFormatter__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../utils/installmentsFormatter */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/utils/installmentsFormatter.js");
/* harmony import */ var _Common_hooks_usePrevious__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../Common/hooks/usePrevious */ "./assets/javascripts/front/reactCheckout/payments/Common/hooks/usePrevious.js");
/* harmony import */ var _installmentsTypeEnum__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./installmentsTypeEnum */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Installments/installmentsTypeEnum.js");




const useInstallments = (installments, installmentsType, brand, cartTotal, setSelectedInstallment, setIsLoading, cardIndex) => {
  const [installmentsOptions, setInstallmentsOptions] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)((0,_utils_installmentsFormatter__WEBPACK_IMPORTED_MODULE_1__["default"])(installments));
  const previousCartTotal = (0,_Common_hooks_usePrevious__WEBPACK_IMPORTED_MODULE_2__["default"])(cartTotal);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    (async () => {
      const canNotUpdateInstallments = installmentsType === _installmentsTypeEnum__WEBPACK_IMPORTED_MODULE_3__.installmentsTypesEnum.BY_CARD_BRAND && !brand || installmentsType === _installmentsTypeEnum__WEBPACK_IMPORTED_MODULE_3__.installmentsTypesEnum.FOR_ALL_CARD_BRANDS && (!previousCartTotal || previousCartTotal === cartTotal);
      if (canNotUpdateInstallments) {
        return;
      }
      setIsLoading(true);
      const formatedCartTotal = parseFloat(cartTotal / 100).toFixed(2).replace(".", ",");
      try {
        const response = await fetch("/wp-admin/admin-ajax.php?" + new URLSearchParams({
          action: "xqRhBHJ5sW",
          flag: brand,
          total: formatedCartTotal
        }), {
          headers: {
            "X-Request-Type": "Ajax"
          }
        });
        if (!response.ok) {
          setIsLoading(false);
          return;
        }
        const result = await response.json();
        if (!result?.installments?.length) {
          setIsLoading(false);
          return;
        }
        setInstallmentsOptions((0,_utils_installmentsFormatter__WEBPACK_IMPORTED_MODULE_1__["default"])(result.installments));
        setSelectedInstallment(cardIndex, 1);
        setIsLoading(false);
      } catch (e) {
        setIsLoading(false);
        return;
      }
    })();
  }, [brand, cartTotal, installmentsType, setInstallmentsOptions, _utils_installmentsFormatter__WEBPACK_IMPORTED_MODULE_1__["default"], setSelectedInstallment, cardIndex]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (installmentsType === _installmentsTypeEnum__WEBPACK_IMPORTED_MODULE_3__.installmentsTypesEnum.BY_CARD_BRAND) {
      setInstallmentsOptions([{
        label: "...",
        value: ""
      }]);
    }
  }, [installmentsType, setInstallmentsOptions]);
  const filterHandler = inputValue => {
    installmentsOptions.filter(option => option.label.toLowerCase().startsWith(inputValue.toLowerCase()));
  };
  const installmentsChangeHandler = value => {
    setSelectedInstallment(cardIndex, value);
  };
  return {
    installmentsOptions,
    filterHandler,
    installmentsChangeHandler
  };
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (useInstallments);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/MaskedInput/index.js":
/*!*****************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/MaskedInput/index.js ***!
  \*****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_input_mask__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react-input-mask */ "./node_modules/react-input-mask/index.js");
/* harmony import */ var react_input_mask__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_input_mask__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(prop_types__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _useMaskedInput__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./useMaskedInput */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/MaskedInput/useMaskedInput.js");

/* jshint esversion: 8 */



const MaskedInput = ({
  id,
  label,
  inputValue,
  setInputValue,
  cardIndex,
  validate,
  validateIndex,
  mask,
  maskChar = null,
  errors
}) => {
  const {
    setIsActive,
    cssClasses,
    inputChangeHandler,
    inputBlurHandler
  } = (0,_useMaskedInput__WEBPACK_IMPORTED_MODULE_2__["default"])(inputValue, setInputValue, cardIndex, validate, validateIndex, errors);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: cssClasses
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: id
  }, label), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)((react_input_mask__WEBPACK_IMPORTED_MODULE_1___default()), {
    type: "text",
    id: id,
    mask: mask,
    maskChar: maskChar,
    onChange: inputChangeHandler,
    value: inputValue,
    onFocus: () => setIsActive(true),
    onBlur: inputBlurHandler
  }));
};
MaskedInput.propTypes = {
  id: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  label: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  inputValue: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  setInputValue: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().func).isRequired,
  cardIndex: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().number).isRequired,
  mask: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string).isRequired,
  maskChar: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().string)
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (MaskedInput);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/MaskedInput/useMaskedInput.js":
/*!**************************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/MaskedInput/useMaskedInput.js ***!
  \**************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* jshint esversion: 8 */

const useMaskedInput = (inputValue, setInputValue, cardIndex, validate, validateIndex, errors) => {
  let cssClasses = "wc-block-components-text-input";
  const [isActive, setIsActive] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  if (isActive || inputValue.length) {
    cssClasses += " is-active";
  }
  if (errors.hasOwnProperty(validateIndex)) {
    cssClasses += " has-error";
  }
  const inputChangeHandler = event => {
    setInputValue(cardIndex, event.target.value);
  };
  const inputBlurHandler = event => {
    validate(event.target.value);
    setIsActive(false);
  };
  return {
    setIsActive,
    cssClasses,
    inputChangeHandler,
    inputBlurHandler
  };
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (useMaskedInput);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Wallet/index.js":
/*!************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Wallet/index.js ***!
  \************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(prop_types__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _useWallet__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./useWallet */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Wallet/useWallet.js");

/* jshint esversion: 8 */


const {
  ComboboxControl
} = wp.components;
const Wallet = ({
  cards,
  label,
  cardIndex,
  selectedCard,
  setSelectCard,
  setBrand
}) => {
  const {
    filterHandler,
    cardChangeHandler
  } = (0,_useWallet__WEBPACK_IMPORTED_MODULE_1__["default"])(cards, cardIndex, setSelectCard, setBrand);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-components-select-input pagarme-installments-combobox"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-components-combobox is-active"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(ComboboxControl, {
    className: "wc-block-components-combobox-control",
    label: label,
    onChange: cardChangeHandler,
    value: selectedCard,
    options: cards,
    onFilterValueChange: filterHandler,
    allowReset: false,
    autoComplete: "off"
  })));
};
Wallet.propTypes = {
  cards: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().array).isRequired,
  label: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().string).isRequired,
  cardIndex: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().number).isRequired,
  selectedCard: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().string).isRequired,
  setSelectCard: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().func).isRequired,
  setBrand: (prop_types__WEBPACK_IMPORTED_MODULE_2___default().func).isRequired
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Wallet);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Wallet/useWallet.js":
/*!****************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/components/Wallet/useWallet.js ***!
  \****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* jshint esversion: 6 */
const useWallet = (cards, cardIndex, setSelectCard, setBrand) => {
  const filterHandler = inputValue => {
    cards.filter(option => option.label.toLowerCase().startsWith(inputValue.toLowerCase()));
  };
  const cardChangeHandler = value => {
    setSelectCard(cardIndex, value);
    if (!cards) {
      return;
    }
    const foundedCard = cards.find(card => card.value === value);
    if (foundedCard) {
      setBrand(cardIndex, foundedCard.brand);
      return;
    }
    setBrand(cardIndex, "");
  };
  return {
    filterHandler,
    cardChangeHandler
  };
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (useWallet);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/utils/cardNumberFormatter.js":
/*!**************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/utils/cardNumberFormatter.js ***!
  \**************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   formatCardNumber: () => (/* binding */ formatCardNumber)
/* harmony export */ });
function formatCardNumber(number) {
  return number.replace(/\s|•/g, "");
}

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/utils/expirationDate.js":
/*!*********************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/utils/expirationDate.js ***!
  \*********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getMonthAndYearFromExpirationDate: () => (/* binding */ getMonthAndYearFromExpirationDate)
/* harmony export */ });
function getMonthAndYearFromExpirationDate(date) {
  return date.replace(/\s/g, "").split("/");
}

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/utils/installmentsFormatter.js":
/*!****************************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/inputs/utils/installmentsFormatter.js ***!
  \****************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
const {
  formatPrice
} = window.wc.priceFormat;
const formatInstallmentLabel = ({
  optionLabel,
  finalPrice,
  value,
  extraText,
  installmentPrice
}) => {
  const formatedPrice = formatPrice(installmentPrice);
  const formatedFinalPrice = formatPrice(finalPrice);
  if (value === 1) {
    return `${optionLabel} (${formatedPrice})`;
  }
  return `${value}x ${optionLabel} ${formatedPrice} (${formatedFinalPrice}) ${extraText}`.trim();
};
const formatInstallmentsOptions = installments => {
  return installments.map(installment => {
    return {
      label: formatInstallmentLabel(installment),
      value: installment.value
    };
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (formatInstallmentsOptions);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/token/token.js":
/*!*****************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/token/token.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   tokenize: () => (/* binding */ tokenize)
/* harmony export */ });
/* harmony import */ var _inputs_utils_cardNumberFormatter__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../inputs/utils/cardNumberFormatter */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/utils/cardNumberFormatter.js");
/* harmony import */ var _inputs_utils_expirationDate__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../inputs/utils/expirationDate */ "./assets/javascripts/front/reactCheckout/payments/Card/inputs/utils/expirationDate.js");


const tranlasteErrorMessage = (errorIndex, message, errorMessages) => {
  const error = errorIndex.replace("request.", "").replace("card.", "");
  const output = `${error}: ${message}`;
  if (errorMessages.hasOwnProperty(output)) {
    return errorMessages[output];
  }
  return "";
};
const buildErrorMessage = (response, errorMessages) => {
  let errorMessage = "";
  for (const errorIndex in response.errors) {
    for (const error of response.errors[errorIndex] || []) {
      const message = tranlasteErrorMessage(errorIndex, error, errorMessages);
      if (message.length === 0) {
        continue;
      }
      errorMessage += `${message}<br/>`;
    }
  }
  return errorMessage;
};
async function tokenize(cardNumber, cardHolderName, cardExpirationDate, cardCvv, appId, errorMessages) {
  const [month, year] = (0,_inputs_utils_expirationDate__WEBPACK_IMPORTED_MODULE_1__.getMonthAndYearFromExpirationDate)(cardExpirationDate);
  const data = {
    card: {
      holder_name: cardHolderName,
      number: (0,_inputs_utils_cardNumberFormatter__WEBPACK_IMPORTED_MODULE_0__.formatCardNumber)(cardNumber),
      exp_month: month,
      exp_year: year,
      cvv: cardCvv
    }
  };
  try {
    const tokenUrl = `https://api.pagar.me/core/v5/tokens?appId=${appId}`;
    const response = await fetch(tokenUrl, {
      method: "POST",
      body: JSON.stringify(data)
    });
    if (!response.ok) {
      const responseBody = await response.text();
      if (responseBody.length === 0) {
        return {
          errorMessage: errorMessages.serviceUnavailable
        };
      }
      const jsonReponse = JSON.parse(responseBody);
      const errorMessage = buildErrorMessage(jsonReponse, errorMessages);
      return {
        errorMessage
      };
    }
    const jsonReponse = await response.json();
    return {
      token: jsonReponse.id
    };
  } catch (e) {
    return {
      errorMessage: errorMessages.serviceUnavailable
    };
  }
}

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/token/tokenizeException.js":
/*!*****************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/token/tokenizeException.js ***!
  \*****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ TokenizeException)
/* harmony export */ });
class TokenizeException extends Error {
  constructor(message) {
    super(message);
    this.name = this.constructor.name;
  }
}

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/token/tokenizeMultiCards.js":
/*!******************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/token/tokenizeMultiCards.js ***!
  \******************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _token__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./token */ "./assets/javascripts/front/reactCheckout/payments/Card/token/token.js");
/* harmony import */ var _tokenizeException__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./tokenizeException */ "./assets/javascripts/front/reactCheckout/payments/Card/token/tokenizeException.js");
/* jshint esversion: 9 */


const tokenizeMultiCards = async (cards, cardsNumber, backendConfig) => {
  const dataCards = [];
  for (let cardIndex = 1; cardIndex < cardsNumber + 1; ++cardIndex) {
    const {
      holderName,
      number,
      expirationDate,
      cvv,
      brand,
      installment,
      saveCard,
      walletId
    } = cards[cardIndex];
    if (walletId.length > 0) {
      dataCards[cardIndex] = {
        "wallet-id": walletId,
        brand: brand,
        installment: installment
      };
      continue;
    }
    const result = await (0,_token__WEBPACK_IMPORTED_MODULE_0__.tokenize)(number, holderName, expirationDate, cvv, backendConfig.appId, backendConfig.errorMessages);
    if (result.errorMessage) {
      throw new _tokenizeException__WEBPACK_IMPORTED_MODULE_1__["default"](result.errorMessage);
    }
    dataCards[cardIndex] = {
      token: result.token,
      brand: brand,
      installment: installment
    };
    if (saveCard) {
      dataCards[cardIndex]["save-card"] = saveCard;
    }
  }
  return dataCards;
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (tokenizeMultiCards);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/useCard.js":
/*!*************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/useCard.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _store_cards__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../store/cards */ "./assets/javascripts/front/reactCheckout/payments/store/cards.js");
/* jshint esversion: 8 */



const useCard = cardIndex => {
  const [isLoading, setIsLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const {
    setHolderName,
    setNumber,
    setExpirationDate,
    setInstallment,
    setBrand,
    setCvv,
    setSaveCard,
    setWalletId,
    setErrors
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.useDispatch)(_store_cards__WEBPACK_IMPORTED_MODULE_2__["default"]);
  const holderName = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.useSelect)(select => {
    return select(_store_cards__WEBPACK_IMPORTED_MODULE_2__["default"]).getHolderName(cardIndex);
  }, [cardIndex]);
  const number = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.useSelect)(select => {
    return select(_store_cards__WEBPACK_IMPORTED_MODULE_2__["default"]).getNumber(cardIndex);
  }, [cardIndex]);
  const expirationDate = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.useSelect)(select => {
    return select(_store_cards__WEBPACK_IMPORTED_MODULE_2__["default"]).getExpirationDate(cardIndex);
  }, [cardIndex]);
  const selectedInstallment = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.useSelect)(select => {
    return select(_store_cards__WEBPACK_IMPORTED_MODULE_2__["default"]).getInstallment(cardIndex);
  }, [cardIndex]);
  const brand = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.useSelect)(select => {
    return select(_store_cards__WEBPACK_IMPORTED_MODULE_2__["default"]).getBrand(cardIndex);
  }, [cardIndex]);
  const cvv = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.useSelect)(select => {
    return select(_store_cards__WEBPACK_IMPORTED_MODULE_2__["default"]).getCvv(cardIndex);
  }, [cardIndex]);
  const saveCard = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.useSelect)(select => {
    return select(_store_cards__WEBPACK_IMPORTED_MODULE_2__["default"]).getSaveCard(cardIndex);
  }, [cardIndex]);
  const walletId = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.useSelect)(select => {
    return select(_store_cards__WEBPACK_IMPORTED_MODULE_2__["default"]).getWalletId(cardIndex);
  }, [cardIndex]);
  const errors = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.useSelect)(select => {
    return select(_store_cards__WEBPACK_IMPORTED_MODULE_2__["default"]).getErrors(cardIndex);
  }, [cardIndex]);
  const saveCardChangeHandler = value => {
    setSaveCard(cardIndex, value);
  };
  const formatFieldId = id => `pagarme_credit_card_${cardIndex}_${id}`;
  return {
    isLoading,
    setIsLoading,
    setHolderName,
    setNumber,
    setExpirationDate,
    setInstallment,
    setBrand,
    setCvv,
    setWalletId,
    setErrors,
    saveCardChangeHandler,
    formatFieldId,
    holderName,
    number,
    expirationDate,
    selectedInstallment,
    brand,
    cvv,
    saveCard,
    walletId,
    errors
  };
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (useCard);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Card/useCardValidation.js":
/*!***********************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Card/useCardValidation.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* jshint esversion: 9 */

const useCardValidation = (cardIndex, errors, setErrors, fieldErrors) => {
  const validateHolderName = (value, errors) => {
    delete errors['inputHolderName'];
    const valid = value.length > 0;
    if (!valid) {
      errors.inputHolderName = fieldErrors.holderName;
    }
    return errors;
  };
  const validateInputHolderName = value => {
    const updatedErrors = validateHolderName(value, errors);
    setErrors(cardIndex, {
      ...updatedErrors
    });
    return !!updatedErrors.inputHolderName;
  };
  const validateNumber = (value, errors) => {
    delete errors['inputNumber'];
    value = value.replace(/(\D)/g, '');
    const valid = value.length === 16;
    if (!valid) {
      errors.inputNumber = fieldErrors.cardNumber;
    }
    return errors;
  };
  const validateInputNumber = value => {
    const updatedErrors = validateNumber(value, errors);
    setErrors(cardIndex, {
      ...updatedErrors
    });
    return !!updatedErrors.inputNumber;
  };
  const validateExpiry = (value, errors) => {
    delete errors['inputExpiry'];
    const empty = value.length === 0;
    if (empty) {
      errors.inputExpiry = fieldErrors.emptyExpiry;
      return errors;
    }
    const splitDate = value.split('/');
    const expiryMonth = splitDate[0].replace(/(\D)/g, '');
    const validMonth = expiryMonth >= 1 && expiryMonth <= 12;
    if (!validMonth) {
      errors.inputExpiry = fieldErrors.invalidExpiryMonth;
    }
    return errors;
  };
  const validateInputExpiry = value => {
    const updatedErrors = validateExpiry(value, errors);
    setErrors(cardIndex, {
      ...updatedErrors
    });
    return !!updatedErrors.inputExpiry;
  };
  const validateCvv = (value, errors) => {
    delete errors['inputCvv'];
    value = value.replace(/(\D)/g, '');
    const empty = value.length === 0;
    if (empty) {
      errors.inputCvv = fieldErrors.emptyCvv;
      return errors;
    }
    const valid = value.length === 3 || value.length === 4;
    if (!valid) {
      errors.inputCvv = fieldErrors.invalidCvv;
    }
    return errors;
  };
  const validateInputCvv = value => {
    const updatedErrors = validateCvv(value, errors);
    setErrors(cardIndex, {
      ...updatedErrors
    });
    return !!updatedErrors.inputCvv;
  };
  const validateAllFields = (holderName, number, expiry, cvv) => {
    let updatedErrors = {
      ...errors
    };
    updatedErrors = validateHolderName(holderName, updatedErrors);
    updatedErrors = validateNumber(number, updatedErrors);
    updatedErrors = validateExpiry(expiry, updatedErrors);
    updatedErrors = validateCvv(cvv, updatedErrors);
    setErrors(cardIndex, {
      ...updatedErrors
    });
    return Object.keys(updatedErrors).length === 0;
  };
  return {
    validateInputHolderName,
    validateInputNumber,
    validateInputExpiry,
    validateInputCvv,
    validateAllFields
  };
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (useCardValidation);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/Common/hooks/usePrevious.js":
/*!*************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/Common/hooks/usePrevious.js ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);

const usePrevious = value => {
  const ref = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)();
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    ref.current = value;
  });
  return ref.current;
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (usePrevious);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/creditCard/useCreditCard.js":
/*!*************************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/creditCard/useCreditCard.js ***!
  \*************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _store_cards__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../store/cards */ "./assets/javascripts/front/reactCheckout/payments/store/cards.js");
/* harmony import */ var _Card_token_tokenizeException__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../Card/token/tokenizeException */ "./assets/javascripts/front/reactCheckout/payments/Card/token/tokenizeException.js");
/* harmony import */ var _Card_token_tokenizeMultiCards__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../Card/token/tokenizeMultiCards */ "./assets/javascripts/front/reactCheckout/payments/Card/token/tokenizeMultiCards.js");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_4__);
/* jshint esversion: 9 */





const useCreditCard = (backendConfig, emitResponse, eventRegistration) => {
  const {
    reset
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useDispatch)(_store_cards__WEBPACK_IMPORTED_MODULE_0__["default"]);
  const {
    onPaymentSetup
  } = eventRegistration;
  const cards = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useSelect)(select => {
    return select(_store_cards__WEBPACK_IMPORTED_MODULE_0__["default"]).getCards();
  });
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useEffect)(() => {
    reset();
  }, []);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useEffect)(() => {
    const unsubscribe = onPaymentSetup(async () => {
      try {
        let hasErrors = false;
        if (typeof cards === 'object') {
          hasErrors = Object.values(cards).some(card => {
            return Object.keys(card.errors).length > 0;
          });
        }
        if (hasErrors) {
          return {
            type: emitResponse.responseTypes.ERROR,
            message: backendConfig.errorMessages.creditCardFormHasErrors
          };
        }
        const formatedCards = await (0,_Card_token_tokenizeMultiCards__WEBPACK_IMPORTED_MODULE_2__["default"])(cards, 1, backendConfig);
        return {
          type: emitResponse.responseTypes.SUCCESS,
          meta: {
            paymentMethodData: {
              pagarme: JSON.stringify({
                [backendConfig.key]: {
                  cards: {
                    ...formatedCards
                  }
                }
              }),
              payment_method: backendConfig.key
            }
          }
        };
      } catch (e) {
        let errorMesage = backendConfig.errorMessages.serviceUnavailable;
        if (e instanceof _Card_token_tokenizeException__WEBPACK_IMPORTED_MODULE_1__["default"]) {
          errorMesage = e.message;
        }
        return {
          type: emitResponse.responseTypes.ERROR,
          message: errorMesage
        };
      }
    });
    return unsubscribe;
  }, [onPaymentSetup, cards, backendConfig]);
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (useCreditCard);

/***/ }),

/***/ "./assets/javascripts/front/reactCheckout/payments/store/cards.js":
/*!************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/store/cards.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_0__);

const DEFAULT_CARD = {
  holderName: "",
  number: "",
  expirationDate: "",
  installment: 1,
  brand: "",
  cvv: "",
  saveCard: false,
  walletId: "",
  errors: {}
};
const DEFAULT_STATE = {
  cards: {
    1: {
      ...DEFAULT_CARD
    },
    2: {
      ...DEFAULT_CARD
    }
  }
};
const actions = {
  setHolderName(cardIndex, holderName) {
    return {
      type: "SET_PROPERTY_VALUE",
      cardIndex,
      value: holderName,
      propertyName: "holderName"
    };
  },
  setNumber(cardIndex, number) {
    return {
      type: "SET_PROPERTY_VALUE",
      cardIndex,
      value: number,
      propertyName: "number"
    };
  },
  setExpirationDate(cardIndex, expirationDate) {
    return {
      type: "SET_PROPERTY_VALUE",
      cardIndex,
      value: expirationDate,
      propertyName: "expirationDate"
    };
  },
  setInstallment(cardIndex, installment) {
    return {
      type: "SET_PROPERTY_VALUE",
      cardIndex,
      value: installment,
      propertyName: "installment"
    };
  },
  setBrand(cardIndex, brand) {
    return {
      type: "SET_PROPERTY_VALUE",
      cardIndex,
      value: brand,
      propertyName: "brand"
    };
  },
  setCvv(cardIndex, cvv) {
    return {
      type: "SET_PROPERTY_VALUE",
      cardIndex,
      value: cvv,
      propertyName: "cvv"
    };
  },
  setSaveCard(cardIndex, saveCard) {
    return {
      type: "SET_PROPERTY_VALUE",
      cardIndex,
      value: saveCard,
      propertyName: "saveCard"
    };
  },
  setWalletId(cardIndex, walletId) {
    return {
      type: "SET_PROPERTY_VALUE",
      cardIndex,
      value: walletId,
      propertyName: "walletId"
    };
  },
  setErrors(cardIndex, errors) {
    return {
      type: "SET_PROPERTY_VALUE",
      cardIndex,
      value: errors,
      propertyName: "errors"
    };
  },
  reset() {
    return {
      type: "RESET"
    };
  }
};
const pagarmeCardsStore = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.createReduxStore)("pagarme-cards", {
  reducer(state = DEFAULT_STATE, action) {
    switch (action.type) {
      case "SET_PROPERTY_VALUE":
        if (action.propertyName?.length === 0) {
          return state;
        }
        return {
          ...state,
          cards: {
            ...state.cards,
            [action.cardIndex]: {
              ...state.cards[action.cardIndex],
              [action.propertyName]: action.value
            }
          }
        };
      case "RESET":
        return DEFAULT_STATE;
    }
    return state;
  },
  actions,
  selectors: {
    getHolderName(state, cardIndex) {
      return state.cards[cardIndex].holderName;
    },
    getNumber(state, cardIndex) {
      return state.cards[cardIndex].number;
    },
    getExpirationDate(state, cardIndex) {
      return state.cards[cardIndex].expirationDate;
    },
    getInstallment(state, cardIndex) {
      return state.cards[cardIndex].installment;
    },
    getBrand(state, cardIndex) {
      return state.cards[cardIndex].brand;
    },
    getCvv(state, cardIndex) {
      return state.cards[cardIndex].cvv;
    },
    getSaveCard(state, cardIndex) {
      return state.cards[cardIndex].saveCard;
    },
    getWalletId(state, cardIndex) {
      return state.cards[cardIndex].walletId;
    },
    getCards(state) {
      return state.cards;
    },
    getErrors(state, cardIndex) {
      return state.cards[cardIndex].errors;
    }
  }
});
(0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.register)(pagarmeCardsStore);
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (pagarmeCardsStore);

/***/ }),

/***/ "./node_modules/invariant/browser.js":
/*!*******************************************!*\
  !*** ./node_modules/invariant/browser.js ***!
  \*******************************************/
/***/ ((module) => {

"use strict";
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



/**
 * Use invariant() to assert state which your program assumes to be true.
 *
 * Provide sprintf-style format (only %s is supported) and arguments
 * to provide information about what broke and what you were
 * expecting.
 *
 * The invariant message will be stripped in production, but the invariant
 * will remain to ensure logic does not differ in production.
 */

var invariant = function(condition, format, a, b, c, d, e, f) {
  if (true) {
    if (format === undefined) {
      throw new Error('invariant requires an error message argument');
    }
  }

  if (!condition) {
    var error;
    if (format === undefined) {
      error = new Error(
        'Minified exception occurred; use the non-minified dev environment ' +
        'for the full error message and additional helpful warnings.'
      );
    } else {
      var args = [a, b, c, d, e, f];
      var argIndex = 0;
      error = new Error(
        format.replace(/%s/g, function() { return args[argIndex++]; })
      );
      error.name = 'Invariant Violation';
    }

    error.framesToPop = 1; // we don't care about invariant's own frame
    throw error;
  }
};

module.exports = invariant;


/***/ }),

/***/ "./node_modules/object-assign/index.js":
/*!*********************************************!*\
  !*** ./node_modules/object-assign/index.js ***!
  \*********************************************/
/***/ ((module) => {

"use strict";
/*
object-assign
(c) Sindre Sorhus
@license MIT
*/


/* eslint-disable no-unused-vars */
var getOwnPropertySymbols = Object.getOwnPropertySymbols;
var hasOwnProperty = Object.prototype.hasOwnProperty;
var propIsEnumerable = Object.prototype.propertyIsEnumerable;

function toObject(val) {
	if (val === null || val === undefined) {
		throw new TypeError('Object.assign cannot be called with null or undefined');
	}

	return Object(val);
}

function shouldUseNative() {
	try {
		if (!Object.assign) {
			return false;
		}

		// Detect buggy property enumeration order in older V8 versions.

		// https://bugs.chromium.org/p/v8/issues/detail?id=4118
		var test1 = new String('abc');  // eslint-disable-line no-new-wrappers
		test1[5] = 'de';
		if (Object.getOwnPropertyNames(test1)[0] === '5') {
			return false;
		}

		// https://bugs.chromium.org/p/v8/issues/detail?id=3056
		var test2 = {};
		for (var i = 0; i < 10; i++) {
			test2['_' + String.fromCharCode(i)] = i;
		}
		var order2 = Object.getOwnPropertyNames(test2).map(function (n) {
			return test2[n];
		});
		if (order2.join('') !== '0123456789') {
			return false;
		}

		// https://bugs.chromium.org/p/v8/issues/detail?id=3056
		var test3 = {};
		'abcdefghijklmnopqrst'.split('').forEach(function (letter) {
			test3[letter] = letter;
		});
		if (Object.keys(Object.assign({}, test3)).join('') !==
				'abcdefghijklmnopqrst') {
			return false;
		}

		return true;
	} catch (err) {
		// We don't expect any of the above to throw, but better to be safe.
		return false;
	}
}

module.exports = shouldUseNative() ? Object.assign : function (target, source) {
	var from;
	var to = toObject(target);
	var symbols;

	for (var s = 1; s < arguments.length; s++) {
		from = Object(arguments[s]);

		for (var key in from) {
			if (hasOwnProperty.call(from, key)) {
				to[key] = from[key];
			}
		}

		if (getOwnPropertySymbols) {
			symbols = getOwnPropertySymbols(from);
			for (var i = 0; i < symbols.length; i++) {
				if (propIsEnumerable.call(from, symbols[i])) {
					to[symbols[i]] = from[symbols[i]];
				}
			}
		}
	}

	return to;
};


/***/ }),

/***/ "./node_modules/prop-types/checkPropTypes.js":
/*!***************************************************!*\
  !*** ./node_modules/prop-types/checkPropTypes.js ***!
  \***************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



var printWarning = function() {};

if (true) {
  var ReactPropTypesSecret = __webpack_require__(/*! ./lib/ReactPropTypesSecret */ "./node_modules/prop-types/lib/ReactPropTypesSecret.js");
  var loggedTypeFailures = {};
  var has = __webpack_require__(/*! ./lib/has */ "./node_modules/prop-types/lib/has.js");

  printWarning = function(text) {
    var message = 'Warning: ' + text;
    if (typeof console !== 'undefined') {
      console.error(message);
    }
    try {
      // --- Welcome to debugging React ---
      // This error was thrown as a convenience so that you can use this stack
      // to find the callsite that caused this warning to fire.
      throw new Error(message);
    } catch (x) { /**/ }
  };
}

/**
 * Assert that the values match with the type specs.
 * Error messages are memorized and will only be shown once.
 *
 * @param {object} typeSpecs Map of name to a ReactPropType
 * @param {object} values Runtime values that need to be type-checked
 * @param {string} location e.g. "prop", "context", "child context"
 * @param {string} componentName Name of the component for error messages.
 * @param {?Function} getStack Returns the component stack.
 * @private
 */
function checkPropTypes(typeSpecs, values, location, componentName, getStack) {
  if (true) {
    for (var typeSpecName in typeSpecs) {
      if (has(typeSpecs, typeSpecName)) {
        var error;
        // Prop type validation may throw. In case they do, we don't want to
        // fail the render phase where it didn't fail before. So we log it.
        // After these have been cleaned up, we'll let them throw.
        try {
          // This is intentionally an invariant that gets caught. It's the same
          // behavior as without this statement except with a better message.
          if (typeof typeSpecs[typeSpecName] !== 'function') {
            var err = Error(
              (componentName || 'React class') + ': ' + location + ' type `' + typeSpecName + '` is invalid; ' +
              'it must be a function, usually from the `prop-types` package, but received `' + typeof typeSpecs[typeSpecName] + '`.' +
              'This often happens because of typos such as `PropTypes.function` instead of `PropTypes.func`.'
            );
            err.name = 'Invariant Violation';
            throw err;
          }
          error = typeSpecs[typeSpecName](values, typeSpecName, componentName, location, null, ReactPropTypesSecret);
        } catch (ex) {
          error = ex;
        }
        if (error && !(error instanceof Error)) {
          printWarning(
            (componentName || 'React class') + ': type specification of ' +
            location + ' `' + typeSpecName + '` is invalid; the type checker ' +
            'function must return `null` or an `Error` but returned a ' + typeof error + '. ' +
            'You may have forgotten to pass an argument to the type checker ' +
            'creator (arrayOf, instanceOf, objectOf, oneOf, oneOfType, and ' +
            'shape all require an argument).'
          );
        }
        if (error instanceof Error && !(error.message in loggedTypeFailures)) {
          // Only monitor this failure once because there tends to be a lot of the
          // same error.
          loggedTypeFailures[error.message] = true;

          var stack = getStack ? getStack() : '';

          printWarning(
            'Failed ' + location + ' type: ' + error.message + (stack != null ? stack : '')
          );
        }
      }
    }
  }
}

/**
 * Resets warning cache when testing.
 *
 * @private
 */
checkPropTypes.resetWarningCache = function() {
  if (true) {
    loggedTypeFailures = {};
  }
}

module.exports = checkPropTypes;


/***/ }),

/***/ "./node_modules/prop-types/factoryWithTypeCheckers.js":
/*!************************************************************!*\
  !*** ./node_modules/prop-types/factoryWithTypeCheckers.js ***!
  \************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



var ReactIs = __webpack_require__(/*! react-is */ "./node_modules/prop-types/node_modules/react-is/index.js");
var assign = __webpack_require__(/*! object-assign */ "./node_modules/object-assign/index.js");

var ReactPropTypesSecret = __webpack_require__(/*! ./lib/ReactPropTypesSecret */ "./node_modules/prop-types/lib/ReactPropTypesSecret.js");
var has = __webpack_require__(/*! ./lib/has */ "./node_modules/prop-types/lib/has.js");
var checkPropTypes = __webpack_require__(/*! ./checkPropTypes */ "./node_modules/prop-types/checkPropTypes.js");

var printWarning = function() {};

if (true) {
  printWarning = function(text) {
    var message = 'Warning: ' + text;
    if (typeof console !== 'undefined') {
      console.error(message);
    }
    try {
      // --- Welcome to debugging React ---
      // This error was thrown as a convenience so that you can use this stack
      // to find the callsite that caused this warning to fire.
      throw new Error(message);
    } catch (x) {}
  };
}

function emptyFunctionThatReturnsNull() {
  return null;
}

module.exports = function(isValidElement, throwOnDirectAccess) {
  /* global Symbol */
  var ITERATOR_SYMBOL = typeof Symbol === 'function' && Symbol.iterator;
  var FAUX_ITERATOR_SYMBOL = '@@iterator'; // Before Symbol spec.

  /**
   * Returns the iterator method function contained on the iterable object.
   *
   * Be sure to invoke the function with the iterable as context:
   *
   *     var iteratorFn = getIteratorFn(myIterable);
   *     if (iteratorFn) {
   *       var iterator = iteratorFn.call(myIterable);
   *       ...
   *     }
   *
   * @param {?object} maybeIterable
   * @return {?function}
   */
  function getIteratorFn(maybeIterable) {
    var iteratorFn = maybeIterable && (ITERATOR_SYMBOL && maybeIterable[ITERATOR_SYMBOL] || maybeIterable[FAUX_ITERATOR_SYMBOL]);
    if (typeof iteratorFn === 'function') {
      return iteratorFn;
    }
  }

  /**
   * Collection of methods that allow declaration and validation of props that are
   * supplied to React components. Example usage:
   *
   *   var Props = require('ReactPropTypes');
   *   var MyArticle = React.createClass({
   *     propTypes: {
   *       // An optional string prop named "description".
   *       description: Props.string,
   *
   *       // A required enum prop named "category".
   *       category: Props.oneOf(['News','Photos']).isRequired,
   *
   *       // A prop named "dialog" that requires an instance of Dialog.
   *       dialog: Props.instanceOf(Dialog).isRequired
   *     },
   *     render: function() { ... }
   *   });
   *
   * A more formal specification of how these methods are used:
   *
   *   type := array|bool|func|object|number|string|oneOf([...])|instanceOf(...)
   *   decl := ReactPropTypes.{type}(.isRequired)?
   *
   * Each and every declaration produces a function with the same signature. This
   * allows the creation of custom validation functions. For example:
   *
   *  var MyLink = React.createClass({
   *    propTypes: {
   *      // An optional string or URI prop named "href".
   *      href: function(props, propName, componentName) {
   *        var propValue = props[propName];
   *        if (propValue != null && typeof propValue !== 'string' &&
   *            !(propValue instanceof URI)) {
   *          return new Error(
   *            'Expected a string or an URI for ' + propName + ' in ' +
   *            componentName
   *          );
   *        }
   *      }
   *    },
   *    render: function() {...}
   *  });
   *
   * @internal
   */

  var ANONYMOUS = '<<anonymous>>';

  // Important!
  // Keep this list in sync with production version in `./factoryWithThrowingShims.js`.
  var ReactPropTypes = {
    array: createPrimitiveTypeChecker('array'),
    bigint: createPrimitiveTypeChecker('bigint'),
    bool: createPrimitiveTypeChecker('boolean'),
    func: createPrimitiveTypeChecker('function'),
    number: createPrimitiveTypeChecker('number'),
    object: createPrimitiveTypeChecker('object'),
    string: createPrimitiveTypeChecker('string'),
    symbol: createPrimitiveTypeChecker('symbol'),

    any: createAnyTypeChecker(),
    arrayOf: createArrayOfTypeChecker,
    element: createElementTypeChecker(),
    elementType: createElementTypeTypeChecker(),
    instanceOf: createInstanceTypeChecker,
    node: createNodeChecker(),
    objectOf: createObjectOfTypeChecker,
    oneOf: createEnumTypeChecker,
    oneOfType: createUnionTypeChecker,
    shape: createShapeTypeChecker,
    exact: createStrictShapeTypeChecker,
  };

  /**
   * inlined Object.is polyfill to avoid requiring consumers ship their own
   * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/is
   */
  /*eslint-disable no-self-compare*/
  function is(x, y) {
    // SameValue algorithm
    if (x === y) {
      // Steps 1-5, 7-10
      // Steps 6.b-6.e: +0 != -0
      return x !== 0 || 1 / x === 1 / y;
    } else {
      // Step 6.a: NaN == NaN
      return x !== x && y !== y;
    }
  }
  /*eslint-enable no-self-compare*/

  /**
   * We use an Error-like object for backward compatibility as people may call
   * PropTypes directly and inspect their output. However, we don't use real
   * Errors anymore. We don't inspect their stack anyway, and creating them
   * is prohibitively expensive if they are created too often, such as what
   * happens in oneOfType() for any type before the one that matched.
   */
  function PropTypeError(message, data) {
    this.message = message;
    this.data = data && typeof data === 'object' ? data: {};
    this.stack = '';
  }
  // Make `instanceof Error` still work for returned errors.
  PropTypeError.prototype = Error.prototype;

  function createChainableTypeChecker(validate) {
    if (true) {
      var manualPropTypeCallCache = {};
      var manualPropTypeWarningCount = 0;
    }
    function checkType(isRequired, props, propName, componentName, location, propFullName, secret) {
      componentName = componentName || ANONYMOUS;
      propFullName = propFullName || propName;

      if (secret !== ReactPropTypesSecret) {
        if (throwOnDirectAccess) {
          // New behavior only for users of `prop-types` package
          var err = new Error(
            'Calling PropTypes validators directly is not supported by the `prop-types` package. ' +
            'Use `PropTypes.checkPropTypes()` to call them. ' +
            'Read more at http://fb.me/use-check-prop-types'
          );
          err.name = 'Invariant Violation';
          throw err;
        } else if ( true && typeof console !== 'undefined') {
          // Old behavior for people using React.PropTypes
          var cacheKey = componentName + ':' + propName;
          if (
            !manualPropTypeCallCache[cacheKey] &&
            // Avoid spamming the console because they are often not actionable except for lib authors
            manualPropTypeWarningCount < 3
          ) {
            printWarning(
              'You are manually calling a React.PropTypes validation ' +
              'function for the `' + propFullName + '` prop on `' + componentName + '`. This is deprecated ' +
              'and will throw in the standalone `prop-types` package. ' +
              'You may be seeing this warning due to a third-party PropTypes ' +
              'library. See https://fb.me/react-warning-dont-call-proptypes ' + 'for details.'
            );
            manualPropTypeCallCache[cacheKey] = true;
            manualPropTypeWarningCount++;
          }
        }
      }
      if (props[propName] == null) {
        if (isRequired) {
          if (props[propName] === null) {
            return new PropTypeError('The ' + location + ' `' + propFullName + '` is marked as required ' + ('in `' + componentName + '`, but its value is `null`.'));
          }
          return new PropTypeError('The ' + location + ' `' + propFullName + '` is marked as required in ' + ('`' + componentName + '`, but its value is `undefined`.'));
        }
        return null;
      } else {
        return validate(props, propName, componentName, location, propFullName);
      }
    }

    var chainedCheckType = checkType.bind(null, false);
    chainedCheckType.isRequired = checkType.bind(null, true);

    return chainedCheckType;
  }

  function createPrimitiveTypeChecker(expectedType) {
    function validate(props, propName, componentName, location, propFullName, secret) {
      var propValue = props[propName];
      var propType = getPropType(propValue);
      if (propType !== expectedType) {
        // `propValue` being instance of, say, date/regexp, pass the 'object'
        // check, but we can offer a more precise error message here rather than
        // 'of type `object`'.
        var preciseType = getPreciseType(propValue);

        return new PropTypeError(
          'Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + preciseType + '` supplied to `' + componentName + '`, expected ') + ('`' + expectedType + '`.'),
          {expectedType: expectedType}
        );
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createAnyTypeChecker() {
    return createChainableTypeChecker(emptyFunctionThatReturnsNull);
  }

  function createArrayOfTypeChecker(typeChecker) {
    function validate(props, propName, componentName, location, propFullName) {
      if (typeof typeChecker !== 'function') {
        return new PropTypeError('Property `' + propFullName + '` of component `' + componentName + '` has invalid PropType notation inside arrayOf.');
      }
      var propValue = props[propName];
      if (!Array.isArray(propValue)) {
        var propType = getPropType(propValue);
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + propType + '` supplied to `' + componentName + '`, expected an array.'));
      }
      for (var i = 0; i < propValue.length; i++) {
        var error = typeChecker(propValue, i, componentName, location, propFullName + '[' + i + ']', ReactPropTypesSecret);
        if (error instanceof Error) {
          return error;
        }
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createElementTypeChecker() {
    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      if (!isValidElement(propValue)) {
        var propType = getPropType(propValue);
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + propType + '` supplied to `' + componentName + '`, expected a single ReactElement.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createElementTypeTypeChecker() {
    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      if (!ReactIs.isValidElementType(propValue)) {
        var propType = getPropType(propValue);
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + propType + '` supplied to `' + componentName + '`, expected a single ReactElement type.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createInstanceTypeChecker(expectedClass) {
    function validate(props, propName, componentName, location, propFullName) {
      if (!(props[propName] instanceof expectedClass)) {
        var expectedClassName = expectedClass.name || ANONYMOUS;
        var actualClassName = getClassName(props[propName]);
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + actualClassName + '` supplied to `' + componentName + '`, expected ') + ('instance of `' + expectedClassName + '`.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createEnumTypeChecker(expectedValues) {
    if (!Array.isArray(expectedValues)) {
      if (true) {
        if (arguments.length > 1) {
          printWarning(
            'Invalid arguments supplied to oneOf, expected an array, got ' + arguments.length + ' arguments. ' +
            'A common mistake is to write oneOf(x, y, z) instead of oneOf([x, y, z]).'
          );
        } else {
          printWarning('Invalid argument supplied to oneOf, expected an array.');
        }
      }
      return emptyFunctionThatReturnsNull;
    }

    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      for (var i = 0; i < expectedValues.length; i++) {
        if (is(propValue, expectedValues[i])) {
          return null;
        }
      }

      var valuesString = JSON.stringify(expectedValues, function replacer(key, value) {
        var type = getPreciseType(value);
        if (type === 'symbol') {
          return String(value);
        }
        return value;
      });
      return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of value `' + String(propValue) + '` ' + ('supplied to `' + componentName + '`, expected one of ' + valuesString + '.'));
    }
    return createChainableTypeChecker(validate);
  }

  function createObjectOfTypeChecker(typeChecker) {
    function validate(props, propName, componentName, location, propFullName) {
      if (typeof typeChecker !== 'function') {
        return new PropTypeError('Property `' + propFullName + '` of component `' + componentName + '` has invalid PropType notation inside objectOf.');
      }
      var propValue = props[propName];
      var propType = getPropType(propValue);
      if (propType !== 'object') {
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type ' + ('`' + propType + '` supplied to `' + componentName + '`, expected an object.'));
      }
      for (var key in propValue) {
        if (has(propValue, key)) {
          var error = typeChecker(propValue, key, componentName, location, propFullName + '.' + key, ReactPropTypesSecret);
          if (error instanceof Error) {
            return error;
          }
        }
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createUnionTypeChecker(arrayOfTypeCheckers) {
    if (!Array.isArray(arrayOfTypeCheckers)) {
       true ? printWarning('Invalid argument supplied to oneOfType, expected an instance of array.') : 0;
      return emptyFunctionThatReturnsNull;
    }

    for (var i = 0; i < arrayOfTypeCheckers.length; i++) {
      var checker = arrayOfTypeCheckers[i];
      if (typeof checker !== 'function') {
        printWarning(
          'Invalid argument supplied to oneOfType. Expected an array of check functions, but ' +
          'received ' + getPostfixForTypeWarning(checker) + ' at index ' + i + '.'
        );
        return emptyFunctionThatReturnsNull;
      }
    }

    function validate(props, propName, componentName, location, propFullName) {
      var expectedTypes = [];
      for (var i = 0; i < arrayOfTypeCheckers.length; i++) {
        var checker = arrayOfTypeCheckers[i];
        var checkerResult = checker(props, propName, componentName, location, propFullName, ReactPropTypesSecret);
        if (checkerResult == null) {
          return null;
        }
        if (checkerResult.data && has(checkerResult.data, 'expectedType')) {
          expectedTypes.push(checkerResult.data.expectedType);
        }
      }
      var expectedTypesMessage = (expectedTypes.length > 0) ? ', expected one of type [' + expectedTypes.join(', ') + ']': '';
      return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` supplied to ' + ('`' + componentName + '`' + expectedTypesMessage + '.'));
    }
    return createChainableTypeChecker(validate);
  }

  function createNodeChecker() {
    function validate(props, propName, componentName, location, propFullName) {
      if (!isNode(props[propName])) {
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` supplied to ' + ('`' + componentName + '`, expected a ReactNode.'));
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function invalidValidatorError(componentName, location, propFullName, key, type) {
    return new PropTypeError(
      (componentName || 'React class') + ': ' + location + ' type `' + propFullName + '.' + key + '` is invalid; ' +
      'it must be a function, usually from the `prop-types` package, but received `' + type + '`.'
    );
  }

  function createShapeTypeChecker(shapeTypes) {
    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      var propType = getPropType(propValue);
      if (propType !== 'object') {
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type `' + propType + '` ' + ('supplied to `' + componentName + '`, expected `object`.'));
      }
      for (var key in shapeTypes) {
        var checker = shapeTypes[key];
        if (typeof checker !== 'function') {
          return invalidValidatorError(componentName, location, propFullName, key, getPreciseType(checker));
        }
        var error = checker(propValue, key, componentName, location, propFullName + '.' + key, ReactPropTypesSecret);
        if (error) {
          return error;
        }
      }
      return null;
    }
    return createChainableTypeChecker(validate);
  }

  function createStrictShapeTypeChecker(shapeTypes) {
    function validate(props, propName, componentName, location, propFullName) {
      var propValue = props[propName];
      var propType = getPropType(propValue);
      if (propType !== 'object') {
        return new PropTypeError('Invalid ' + location + ' `' + propFullName + '` of type `' + propType + '` ' + ('supplied to `' + componentName + '`, expected `object`.'));
      }
      // We need to check all keys in case some are required but missing from props.
      var allKeys = assign({}, props[propName], shapeTypes);
      for (var key in allKeys) {
        var checker = shapeTypes[key];
        if (has(shapeTypes, key) && typeof checker !== 'function') {
          return invalidValidatorError(componentName, location, propFullName, key, getPreciseType(checker));
        }
        if (!checker) {
          return new PropTypeError(
            'Invalid ' + location + ' `' + propFullName + '` key `' + key + '` supplied to `' + componentName + '`.' +
            '\nBad object: ' + JSON.stringify(props[propName], null, '  ') +
            '\nValid keys: ' + JSON.stringify(Object.keys(shapeTypes), null, '  ')
          );
        }
        var error = checker(propValue, key, componentName, location, propFullName + '.' + key, ReactPropTypesSecret);
        if (error) {
          return error;
        }
      }
      return null;
    }

    return createChainableTypeChecker(validate);
  }

  function isNode(propValue) {
    switch (typeof propValue) {
      case 'number':
      case 'string':
      case 'undefined':
        return true;
      case 'boolean':
        return !propValue;
      case 'object':
        if (Array.isArray(propValue)) {
          return propValue.every(isNode);
        }
        if (propValue === null || isValidElement(propValue)) {
          return true;
        }

        var iteratorFn = getIteratorFn(propValue);
        if (iteratorFn) {
          var iterator = iteratorFn.call(propValue);
          var step;
          if (iteratorFn !== propValue.entries) {
            while (!(step = iterator.next()).done) {
              if (!isNode(step.value)) {
                return false;
              }
            }
          } else {
            // Iterator will provide entry [k,v] tuples rather than values.
            while (!(step = iterator.next()).done) {
              var entry = step.value;
              if (entry) {
                if (!isNode(entry[1])) {
                  return false;
                }
              }
            }
          }
        } else {
          return false;
        }

        return true;
      default:
        return false;
    }
  }

  function isSymbol(propType, propValue) {
    // Native Symbol.
    if (propType === 'symbol') {
      return true;
    }

    // falsy value can't be a Symbol
    if (!propValue) {
      return false;
    }

    // 19.4.3.5 Symbol.prototype[@@toStringTag] === 'Symbol'
    if (propValue['@@toStringTag'] === 'Symbol') {
      return true;
    }

    // Fallback for non-spec compliant Symbols which are polyfilled.
    if (typeof Symbol === 'function' && propValue instanceof Symbol) {
      return true;
    }

    return false;
  }

  // Equivalent of `typeof` but with special handling for array and regexp.
  function getPropType(propValue) {
    var propType = typeof propValue;
    if (Array.isArray(propValue)) {
      return 'array';
    }
    if (propValue instanceof RegExp) {
      // Old webkits (at least until Android 4.0) return 'function' rather than
      // 'object' for typeof a RegExp. We'll normalize this here so that /bla/
      // passes PropTypes.object.
      return 'object';
    }
    if (isSymbol(propType, propValue)) {
      return 'symbol';
    }
    return propType;
  }

  // This handles more types than `getPropType`. Only used for error messages.
  // See `createPrimitiveTypeChecker`.
  function getPreciseType(propValue) {
    if (typeof propValue === 'undefined' || propValue === null) {
      return '' + propValue;
    }
    var propType = getPropType(propValue);
    if (propType === 'object') {
      if (propValue instanceof Date) {
        return 'date';
      } else if (propValue instanceof RegExp) {
        return 'regexp';
      }
    }
    return propType;
  }

  // Returns a string that is postfixed to a warning about an invalid type.
  // For example, "undefined" or "of type array"
  function getPostfixForTypeWarning(value) {
    var type = getPreciseType(value);
    switch (type) {
      case 'array':
      case 'object':
        return 'an ' + type;
      case 'boolean':
      case 'date':
      case 'regexp':
        return 'a ' + type;
      default:
        return type;
    }
  }

  // Returns class name of the object, if any.
  function getClassName(propValue) {
    if (!propValue.constructor || !propValue.constructor.name) {
      return ANONYMOUS;
    }
    return propValue.constructor.name;
  }

  ReactPropTypes.checkPropTypes = checkPropTypes;
  ReactPropTypes.resetWarningCache = checkPropTypes.resetWarningCache;
  ReactPropTypes.PropTypes = ReactPropTypes;

  return ReactPropTypes;
};


/***/ }),

/***/ "./node_modules/prop-types/index.js":
/*!******************************************!*\
  !*** ./node_modules/prop-types/index.js ***!
  \******************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */

if (true) {
  var ReactIs = __webpack_require__(/*! react-is */ "./node_modules/prop-types/node_modules/react-is/index.js");

  // By explicitly using `prop-types` you are opting into new development behavior.
  // http://fb.me/prop-types-in-prod
  var throwOnDirectAccess = true;
  module.exports = __webpack_require__(/*! ./factoryWithTypeCheckers */ "./node_modules/prop-types/factoryWithTypeCheckers.js")(ReactIs.isElement, throwOnDirectAccess);
} else {}


/***/ }),

/***/ "./node_modules/prop-types/lib/ReactPropTypesSecret.js":
/*!*************************************************************!*\
  !*** ./node_modules/prop-types/lib/ReactPropTypesSecret.js ***!
  \*************************************************************/
/***/ ((module) => {

"use strict";
/**
 * Copyright (c) 2013-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



var ReactPropTypesSecret = 'SECRET_DO_NOT_PASS_THIS_OR_YOU_WILL_BE_FIRED';

module.exports = ReactPropTypesSecret;


/***/ }),

/***/ "./node_modules/prop-types/lib/has.js":
/*!********************************************!*\
  !*** ./node_modules/prop-types/lib/has.js ***!
  \********************************************/
/***/ ((module) => {

module.exports = Function.call.bind(Object.prototype.hasOwnProperty);


/***/ }),

/***/ "./node_modules/prop-types/node_modules/react-is/cjs/react-is.development.js":
/*!***********************************************************************************!*\
  !*** ./node_modules/prop-types/node_modules/react-is/cjs/react-is.development.js ***!
  \***********************************************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";
/** @license React v16.13.1
 * react-is.development.js
 *
 * Copyright (c) Facebook, Inc. and its affiliates.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */





if (true) {
  (function() {
'use strict';

// The Symbol used to tag the ReactElement-like types. If there is no native Symbol
// nor polyfill, then a plain number is used for performance.
var hasSymbol = typeof Symbol === 'function' && Symbol.for;
var REACT_ELEMENT_TYPE = hasSymbol ? Symbol.for('react.element') : 0xeac7;
var REACT_PORTAL_TYPE = hasSymbol ? Symbol.for('react.portal') : 0xeaca;
var REACT_FRAGMENT_TYPE = hasSymbol ? Symbol.for('react.fragment') : 0xeacb;
var REACT_STRICT_MODE_TYPE = hasSymbol ? Symbol.for('react.strict_mode') : 0xeacc;
var REACT_PROFILER_TYPE = hasSymbol ? Symbol.for('react.profiler') : 0xead2;
var REACT_PROVIDER_TYPE = hasSymbol ? Symbol.for('react.provider') : 0xeacd;
var REACT_CONTEXT_TYPE = hasSymbol ? Symbol.for('react.context') : 0xeace; // TODO: We don't use AsyncMode or ConcurrentMode anymore. They were temporary
// (unstable) APIs that have been removed. Can we remove the symbols?

var REACT_ASYNC_MODE_TYPE = hasSymbol ? Symbol.for('react.async_mode') : 0xeacf;
var REACT_CONCURRENT_MODE_TYPE = hasSymbol ? Symbol.for('react.concurrent_mode') : 0xeacf;
var REACT_FORWARD_REF_TYPE = hasSymbol ? Symbol.for('react.forward_ref') : 0xead0;
var REACT_SUSPENSE_TYPE = hasSymbol ? Symbol.for('react.suspense') : 0xead1;
var REACT_SUSPENSE_LIST_TYPE = hasSymbol ? Symbol.for('react.suspense_list') : 0xead8;
var REACT_MEMO_TYPE = hasSymbol ? Symbol.for('react.memo') : 0xead3;
var REACT_LAZY_TYPE = hasSymbol ? Symbol.for('react.lazy') : 0xead4;
var REACT_BLOCK_TYPE = hasSymbol ? Symbol.for('react.block') : 0xead9;
var REACT_FUNDAMENTAL_TYPE = hasSymbol ? Symbol.for('react.fundamental') : 0xead5;
var REACT_RESPONDER_TYPE = hasSymbol ? Symbol.for('react.responder') : 0xead6;
var REACT_SCOPE_TYPE = hasSymbol ? Symbol.for('react.scope') : 0xead7;

function isValidElementType(type) {
  return typeof type === 'string' || typeof type === 'function' || // Note: its typeof might be other than 'symbol' or 'number' if it's a polyfill.
  type === REACT_FRAGMENT_TYPE || type === REACT_CONCURRENT_MODE_TYPE || type === REACT_PROFILER_TYPE || type === REACT_STRICT_MODE_TYPE || type === REACT_SUSPENSE_TYPE || type === REACT_SUSPENSE_LIST_TYPE || typeof type === 'object' && type !== null && (type.$$typeof === REACT_LAZY_TYPE || type.$$typeof === REACT_MEMO_TYPE || type.$$typeof === REACT_PROVIDER_TYPE || type.$$typeof === REACT_CONTEXT_TYPE || type.$$typeof === REACT_FORWARD_REF_TYPE || type.$$typeof === REACT_FUNDAMENTAL_TYPE || type.$$typeof === REACT_RESPONDER_TYPE || type.$$typeof === REACT_SCOPE_TYPE || type.$$typeof === REACT_BLOCK_TYPE);
}

function typeOf(object) {
  if (typeof object === 'object' && object !== null) {
    var $$typeof = object.$$typeof;

    switch ($$typeof) {
      case REACT_ELEMENT_TYPE:
        var type = object.type;

        switch (type) {
          case REACT_ASYNC_MODE_TYPE:
          case REACT_CONCURRENT_MODE_TYPE:
          case REACT_FRAGMENT_TYPE:
          case REACT_PROFILER_TYPE:
          case REACT_STRICT_MODE_TYPE:
          case REACT_SUSPENSE_TYPE:
            return type;

          default:
            var $$typeofType = type && type.$$typeof;

            switch ($$typeofType) {
              case REACT_CONTEXT_TYPE:
              case REACT_FORWARD_REF_TYPE:
              case REACT_LAZY_TYPE:
              case REACT_MEMO_TYPE:
              case REACT_PROVIDER_TYPE:
                return $$typeofType;

              default:
                return $$typeof;
            }

        }

      case REACT_PORTAL_TYPE:
        return $$typeof;
    }
  }

  return undefined;
} // AsyncMode is deprecated along with isAsyncMode

var AsyncMode = REACT_ASYNC_MODE_TYPE;
var ConcurrentMode = REACT_CONCURRENT_MODE_TYPE;
var ContextConsumer = REACT_CONTEXT_TYPE;
var ContextProvider = REACT_PROVIDER_TYPE;
var Element = REACT_ELEMENT_TYPE;
var ForwardRef = REACT_FORWARD_REF_TYPE;
var Fragment = REACT_FRAGMENT_TYPE;
var Lazy = REACT_LAZY_TYPE;
var Memo = REACT_MEMO_TYPE;
var Portal = REACT_PORTAL_TYPE;
var Profiler = REACT_PROFILER_TYPE;
var StrictMode = REACT_STRICT_MODE_TYPE;
var Suspense = REACT_SUSPENSE_TYPE;
var hasWarnedAboutDeprecatedIsAsyncMode = false; // AsyncMode should be deprecated

function isAsyncMode(object) {
  {
    if (!hasWarnedAboutDeprecatedIsAsyncMode) {
      hasWarnedAboutDeprecatedIsAsyncMode = true; // Using console['warn'] to evade Babel and ESLint

      console['warn']('The ReactIs.isAsyncMode() alias has been deprecated, ' + 'and will be removed in React 17+. Update your code to use ' + 'ReactIs.isConcurrentMode() instead. It has the exact same API.');
    }
  }

  return isConcurrentMode(object) || typeOf(object) === REACT_ASYNC_MODE_TYPE;
}
function isConcurrentMode(object) {
  return typeOf(object) === REACT_CONCURRENT_MODE_TYPE;
}
function isContextConsumer(object) {
  return typeOf(object) === REACT_CONTEXT_TYPE;
}
function isContextProvider(object) {
  return typeOf(object) === REACT_PROVIDER_TYPE;
}
function isElement(object) {
  return typeof object === 'object' && object !== null && object.$$typeof === REACT_ELEMENT_TYPE;
}
function isForwardRef(object) {
  return typeOf(object) === REACT_FORWARD_REF_TYPE;
}
function isFragment(object) {
  return typeOf(object) === REACT_FRAGMENT_TYPE;
}
function isLazy(object) {
  return typeOf(object) === REACT_LAZY_TYPE;
}
function isMemo(object) {
  return typeOf(object) === REACT_MEMO_TYPE;
}
function isPortal(object) {
  return typeOf(object) === REACT_PORTAL_TYPE;
}
function isProfiler(object) {
  return typeOf(object) === REACT_PROFILER_TYPE;
}
function isStrictMode(object) {
  return typeOf(object) === REACT_STRICT_MODE_TYPE;
}
function isSuspense(object) {
  return typeOf(object) === REACT_SUSPENSE_TYPE;
}

exports.AsyncMode = AsyncMode;
exports.ConcurrentMode = ConcurrentMode;
exports.ContextConsumer = ContextConsumer;
exports.ContextProvider = ContextProvider;
exports.Element = Element;
exports.ForwardRef = ForwardRef;
exports.Fragment = Fragment;
exports.Lazy = Lazy;
exports.Memo = Memo;
exports.Portal = Portal;
exports.Profiler = Profiler;
exports.StrictMode = StrictMode;
exports.Suspense = Suspense;
exports.isAsyncMode = isAsyncMode;
exports.isConcurrentMode = isConcurrentMode;
exports.isContextConsumer = isContextConsumer;
exports.isContextProvider = isContextProvider;
exports.isElement = isElement;
exports.isForwardRef = isForwardRef;
exports.isFragment = isFragment;
exports.isLazy = isLazy;
exports.isMemo = isMemo;
exports.isPortal = isPortal;
exports.isProfiler = isProfiler;
exports.isStrictMode = isStrictMode;
exports.isSuspense = isSuspense;
exports.isValidElementType = isValidElementType;
exports.typeOf = typeOf;
  })();
}


/***/ }),

/***/ "./node_modules/prop-types/node_modules/react-is/index.js":
/*!****************************************************************!*\
  !*** ./node_modules/prop-types/node_modules/react-is/index.js ***!
  \****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


if (false) {} else {
  module.exports = __webpack_require__(/*! ./cjs/react-is.development.js */ "./node_modules/prop-types/node_modules/react-is/cjs/react-is.development.js");
}


/***/ }),

/***/ "./node_modules/react-input-mask/index.js":
/*!************************************************!*\
  !*** ./node_modules/react-input-mask/index.js ***!
  \************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

if (false) {} else {
  module.exports = __webpack_require__(/*! ./lib/react-input-mask.development.js */ "./node_modules/react-input-mask/lib/react-input-mask.development.js");
}


/***/ }),

/***/ "./node_modules/react-input-mask/lib/react-input-mask.development.js":
/*!***************************************************************************!*\
  !*** ./node_modules/react-input-mask/lib/react-input-mask.development.js ***!
  \***************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


function _interopDefault (ex) { return (ex && (typeof ex === 'object') && 'default' in ex) ? ex['default'] : ex; }

var React = _interopDefault(__webpack_require__(/*! react */ "react"));
var reactDom = __webpack_require__(/*! react-dom */ "react-dom");
var invariant = _interopDefault(__webpack_require__(/*! invariant */ "./node_modules/invariant/browser.js"));
var warning = _interopDefault(__webpack_require__(/*! warning */ "./node_modules/warning/warning.js"));

function _defaults2(obj, defaults) { var keys = Object.getOwnPropertyNames(defaults); for (var i = 0; i < keys.length; i++) { var key = keys[i]; var value = Object.getOwnPropertyDescriptor(defaults, key); if (value && value.configurable && obj[key] === undefined) { Object.defineProperty(obj, key, value); } } return obj; }

function _extends() {
  _extends = Object.assign || function (target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i];

      for (var key in source) {
        if (Object.prototype.hasOwnProperty.call(source, key)) {
          target[key] = source[key];
        }
      }
    }

    return target;
  };

  return _extends.apply(this, arguments);
}

function _inheritsLoose(subClass, superClass) {
  subClass.prototype = Object.create(superClass.prototype);
  subClass.prototype.constructor = subClass;

  _defaults2(subClass, superClass);
}

function _objectWithoutPropertiesLoose(source, excluded) {
  if (source == null) return {};
  var target = {};
  var sourceKeys = Object.keys(source);
  var key, i;

  for (i = 0; i < sourceKeys.length; i++) {
    key = sourceKeys[i];
    if (excluded.indexOf(key) >= 0) continue;
    target[key] = source[key];
  }

  return target;
}

function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

function setInputSelection(input, start, end) {
  if ('selectionStart' in input && 'selectionEnd' in input) {
    input.selectionStart = start;
    input.selectionEnd = end;
  } else {
    var range = input.createTextRange();
    range.collapse(true);
    range.moveStart('character', start);
    range.moveEnd('character', end - start);
    range.select();
  }
}
function getInputSelection(input) {
  var start = 0;
  var end = 0;

  if ('selectionStart' in input && 'selectionEnd' in input) {
    start = input.selectionStart;
    end = input.selectionEnd;
  } else {
    var range = document.selection.createRange();

    if (range.parentElement() === input) {
      start = -range.moveStart('character', -input.value.length);
      end = -range.moveEnd('character', -input.value.length);
    }
  }

  return {
    start: start,
    end: end,
    length: end - start
  };
}

var defaultFormatChars = {
  '9': '[0-9]',
  'a': '[A-Za-z]',
  '*': '[A-Za-z0-9]'
};
var defaultMaskChar = '_';

function parseMask (mask, maskChar, formatChars) {
  var parsedMaskString = '';
  var prefix = '';
  var lastEditablePosition = null;
  var permanents = [];

  if (maskChar === undefined) {
    maskChar = defaultMaskChar;
  }

  if (formatChars == null) {
    formatChars = defaultFormatChars;
  }

  if (!mask || typeof mask !== 'string') {
    return {
      maskChar: maskChar,
      formatChars: formatChars,
      mask: null,
      prefix: null,
      lastEditablePosition: null,
      permanents: []
    };
  }

  var isPermanent = false;
  mask.split('').forEach(function (character) {
    if (!isPermanent && character === '\\') {
      isPermanent = true;
    } else {
      if (isPermanent || !formatChars[character]) {
        permanents.push(parsedMaskString.length);

        if (parsedMaskString.length === permanents.length - 1) {
          prefix += character;
        }
      } else {
        lastEditablePosition = parsedMaskString.length + 1;
      }

      parsedMaskString += character;
      isPermanent = false;
    }
  });
  return {
    maskChar: maskChar,
    formatChars: formatChars,
    prefix: prefix,
    mask: parsedMaskString,
    lastEditablePosition: lastEditablePosition,
    permanents: permanents
  };
}

/* eslint no-use-before-define: ["error", { functions: false }] */
function isPermanentCharacter(maskOptions, pos) {
  return maskOptions.permanents.indexOf(pos) !== -1;
}
function isAllowedCharacter(maskOptions, pos, character) {
  var mask = maskOptions.mask,
      formatChars = maskOptions.formatChars;

  if (!character) {
    return false;
  }

  if (isPermanentCharacter(maskOptions, pos)) {
    return mask[pos] === character;
  }

  var ruleChar = mask[pos];
  var charRule = formatChars[ruleChar];
  return new RegExp(charRule).test(character);
}
function isEmpty(maskOptions, value) {
  return value.split('').every(function (character, i) {
    return isPermanentCharacter(maskOptions, i) || !isAllowedCharacter(maskOptions, i, character);
  });
}
function getFilledLength(maskOptions, value) {
  var maskChar = maskOptions.maskChar,
      prefix = maskOptions.prefix;

  if (!maskChar) {
    while (value.length > prefix.length && isPermanentCharacter(maskOptions, value.length - 1)) {
      value = value.slice(0, value.length - 1);
    }

    return value.length;
  }

  var filledLength = prefix.length;

  for (var i = value.length; i >= prefix.length; i--) {
    var character = value[i];
    var isEnteredCharacter = !isPermanentCharacter(maskOptions, i) && isAllowedCharacter(maskOptions, i, character);

    if (isEnteredCharacter) {
      filledLength = i + 1;
      break;
    }
  }

  return filledLength;
}
function isFilled(maskOptions, value) {
  return getFilledLength(maskOptions, value) === maskOptions.mask.length;
}
function formatValue(maskOptions, value) {
  var maskChar = maskOptions.maskChar,
      mask = maskOptions.mask,
      prefix = maskOptions.prefix;

  if (!maskChar) {
    value = insertString(maskOptions, '', value, 0);

    if (value.length < prefix.length) {
      value = prefix;
    }

    while (value.length < mask.length && isPermanentCharacter(maskOptions, value.length)) {
      value += mask[value.length];
    }

    return value;
  }

  if (value) {
    var emptyValue = formatValue(maskOptions, '');
    return insertString(maskOptions, emptyValue, value, 0);
  }

  for (var i = 0; i < mask.length; i++) {
    if (isPermanentCharacter(maskOptions, i)) {
      value += mask[i];
    } else {
      value += maskChar;
    }
  }

  return value;
}
function clearRange(maskOptions, value, start, len) {
  var end = start + len;
  var maskChar = maskOptions.maskChar,
      mask = maskOptions.mask,
      prefix = maskOptions.prefix;
  var arrayValue = value.split('');

  if (!maskChar) {
    // remove any permanent chars after clear range, they will be added back by formatValue
    for (var i = end; i < arrayValue.length; i++) {
      if (isPermanentCharacter(maskOptions, i)) {
        arrayValue[i] = '';
      }
    }

    start = Math.max(prefix.length, start);
    arrayValue.splice(start, end - start);
    value = arrayValue.join('');
    return formatValue(maskOptions, value);
  }

  return arrayValue.map(function (character, i) {
    if (i < start || i >= end) {
      return character;
    }

    if (isPermanentCharacter(maskOptions, i)) {
      return mask[i];
    }

    return maskChar;
  }).join('');
}
function insertString(maskOptions, value, insertStr, insertPosition) {
  var mask = maskOptions.mask,
      maskChar = maskOptions.maskChar,
      prefix = maskOptions.prefix;
  var arrayInsertStr = insertStr.split('');
  var isInputFilled = isFilled(maskOptions, value);

  var isUsablePosition = function isUsablePosition(pos, character) {
    return !isPermanentCharacter(maskOptions, pos) || character === mask[pos];
  };

  var isUsableCharacter = function isUsableCharacter(character, pos) {
    return !maskChar || !isPermanentCharacter(maskOptions, pos) || character !== maskChar;
  };

  if (!maskChar && insertPosition > value.length) {
    value += mask.slice(value.length, insertPosition);
  }

  arrayInsertStr.every(function (insertCharacter) {
    while (!isUsablePosition(insertPosition, insertCharacter)) {
      if (insertPosition >= value.length) {
        value += mask[insertPosition];
      }

      if (!isUsableCharacter(insertCharacter, insertPosition)) {
        return true;
      }

      insertPosition++; // stop iteration if maximum value length reached

      if (insertPosition >= mask.length) {
        return false;
      }
    }

    var isAllowed = isAllowedCharacter(maskOptions, insertPosition, insertCharacter) || insertCharacter === maskChar;

    if (!isAllowed) {
      return true;
    }

    if (insertPosition < value.length) {
      if (maskChar || isInputFilled || insertPosition < prefix.length) {
        value = value.slice(0, insertPosition) + insertCharacter + value.slice(insertPosition + 1);
      } else {
        value = value.slice(0, insertPosition) + insertCharacter + value.slice(insertPosition);
        value = formatValue(maskOptions, value);
      }
    } else if (!maskChar) {
      value += insertCharacter;
    }

    insertPosition++; // stop iteration if maximum value length reached

    return insertPosition < mask.length;
  });
  return value;
}
function getInsertStringLength(maskOptions, value, insertStr, insertPosition) {
  var mask = maskOptions.mask,
      maskChar = maskOptions.maskChar;
  var arrayInsertStr = insertStr.split('');
  var initialInsertPosition = insertPosition;

  var isUsablePosition = function isUsablePosition(pos, character) {
    return !isPermanentCharacter(maskOptions, pos) || character === mask[pos];
  };

  arrayInsertStr.every(function (insertCharacter) {
    while (!isUsablePosition(insertPosition, insertCharacter)) {
      insertPosition++; // stop iteration if maximum value length reached

      if (insertPosition >= mask.length) {
        return false;
      }
    }

    var isAllowed = isAllowedCharacter(maskOptions, insertPosition, insertCharacter) || insertCharacter === maskChar;

    if (isAllowed) {
      insertPosition++;
    } // stop iteration if maximum value length reached


    return insertPosition < mask.length;
  });
  return insertPosition - initialInsertPosition;
}
function getLeftEditablePosition(maskOptions, pos) {
  for (var i = pos; i >= 0; --i) {
    if (!isPermanentCharacter(maskOptions, i)) {
      return i;
    }
  }

  return null;
}
function getRightEditablePosition(maskOptions, pos) {
  var mask = maskOptions.mask;

  for (var i = pos; i < mask.length; ++i) {
    if (!isPermanentCharacter(maskOptions, i)) {
      return i;
    }
  }

  return null;
}
function getStringValue(value) {
  return !value && value !== 0 ? '' : value + '';
}

function processChange(maskOptions, value, selection, previousValue, previousSelection) {
  var mask = maskOptions.mask,
      prefix = maskOptions.prefix,
      lastEditablePosition = maskOptions.lastEditablePosition;
  var newValue = value;
  var enteredString = '';
  var formattedEnteredStringLength = 0;
  var removedLength = 0;
  var cursorPosition = Math.min(previousSelection.start, selection.start);

  if (selection.end > previousSelection.start) {
    enteredString = newValue.slice(previousSelection.start, selection.end);
    formattedEnteredStringLength = getInsertStringLength(maskOptions, previousValue, enteredString, cursorPosition);

    if (!formattedEnteredStringLength) {
      removedLength = 0;
    } else {
      removedLength = previousSelection.length;
    }
  } else if (newValue.length < previousValue.length) {
    removedLength = previousValue.length - newValue.length;
  }

  newValue = previousValue;

  if (removedLength) {
    if (removedLength === 1 && !previousSelection.length) {
      var deleteFromRight = previousSelection.start === selection.start;
      cursorPosition = deleteFromRight ? getRightEditablePosition(maskOptions, selection.start) : getLeftEditablePosition(maskOptions, selection.start);
    }

    newValue = clearRange(maskOptions, newValue, cursorPosition, removedLength);
  }

  newValue = insertString(maskOptions, newValue, enteredString, cursorPosition);
  cursorPosition = cursorPosition + formattedEnteredStringLength;

  if (cursorPosition >= mask.length) {
    cursorPosition = mask.length;
  } else if (cursorPosition < prefix.length && !formattedEnteredStringLength) {
    cursorPosition = prefix.length;
  } else if (cursorPosition >= prefix.length && cursorPosition < lastEditablePosition && formattedEnteredStringLength) {
    cursorPosition = getRightEditablePosition(maskOptions, cursorPosition);
  }

  newValue = formatValue(maskOptions, newValue);

  if (!enteredString) {
    enteredString = null;
  }

  return {
    value: newValue,
    enteredString: enteredString,
    selection: {
      start: cursorPosition,
      end: cursorPosition
    }
  };
}

function isWindowsPhoneBrowser() {
  var windows = new RegExp('windows', 'i');
  var phone = new RegExp('phone', 'i');
  var ua = navigator.userAgent;
  return windows.test(ua) && phone.test(ua);
}

function isFunction(value) {
  return typeof value === 'function';
}

function getRequestAnimationFrame() {
  return window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame;
}

function getCancelAnimationFrame() {
  return window.cancelAnimationFrame || window.webkitCancelRequestAnimationFrame || window.webkitCancelAnimationFrame || window.mozCancelAnimationFrame;
}

function defer(fn) {
  var hasCancelAnimationFrame = !!getCancelAnimationFrame();
  var deferFn;

  if (hasCancelAnimationFrame) {
    deferFn = getRequestAnimationFrame();
  } else {
    deferFn = function deferFn() {
      return setTimeout(fn, 1000 / 60);
    };
  }

  return deferFn(fn);
}
function cancelDefer(deferId) {
  var cancelFn = getCancelAnimationFrame() || clearTimeout;
  cancelFn(deferId);
}

var InputElement =
/*#__PURE__*/
function (_React$Component) {
  _inheritsLoose(InputElement, _React$Component);

  function InputElement(props) {
    var _this;

    _this = _React$Component.call(this, props) || this;
    _this.focused = false;
    _this.mounted = false;
    _this.previousSelection = null;
    _this.selectionDeferId = null;
    _this.saveSelectionLoopDeferId = null;

    _this.saveSelectionLoop = function () {
      _this.previousSelection = _this.getSelection();
      _this.saveSelectionLoopDeferId = defer(_this.saveSelectionLoop);
    };

    _this.runSaveSelectionLoop = function () {
      if (_this.saveSelectionLoopDeferId === null) {
        _this.saveSelectionLoop();
      }
    };

    _this.stopSaveSelectionLoop = function () {
      if (_this.saveSelectionLoopDeferId !== null) {
        cancelDefer(_this.saveSelectionLoopDeferId);
        _this.saveSelectionLoopDeferId = null;
        _this.previousSelection = null;
      }
    };

    _this.getInputDOMNode = function () {
      if (!_this.mounted) {
        return null;
      }

      var input = reactDom.findDOMNode(_assertThisInitialized(_assertThisInitialized(_this)));
      var isDOMNode = typeof window !== 'undefined' && input instanceof window.Element; // workaround for react-test-renderer
      // https://github.com/sanniassin/react-input-mask/issues/147

      if (input && !isDOMNode) {
        return null;
      }

      if (input.nodeName !== 'INPUT') {
        input = input.querySelector('input');
      }

      if (!input) {
        throw new Error('react-input-mask: inputComponent doesn\'t contain input node');
      }

      return input;
    };

    _this.getInputValue = function () {
      var input = _this.getInputDOMNode();

      if (!input) {
        return null;
      }

      return input.value;
    };

    _this.setInputValue = function (value) {
      var input = _this.getInputDOMNode();

      if (!input) {
        return;
      }

      _this.value = value;
      input.value = value;
    };

    _this.setCursorToEnd = function () {
      var filledLength = getFilledLength(_this.maskOptions, _this.value);
      var pos = getRightEditablePosition(_this.maskOptions, filledLength);

      if (pos !== null) {
        _this.setCursorPosition(pos);
      }
    };

    _this.setSelection = function (start, end, options) {
      if (options === void 0) {
        options = {};
      }

      var input = _this.getInputDOMNode();

      var isFocused = _this.isFocused(); // don't change selection on unfocused input
      // because Safari sets focus on selection change (#154)


      if (!input || !isFocused) {
        return;
      }

      var _options = options,
          deferred = _options.deferred;

      if (!deferred) {
        setInputSelection(input, start, end);
      }

      if (_this.selectionDeferId !== null) {
        cancelDefer(_this.selectionDeferId);
      } // deferred selection update is required for pre-Lollipop Android browser,
      // but for consistent behavior we do it for all browsers


      _this.selectionDeferId = defer(function () {
        _this.selectionDeferId = null;
        setInputSelection(input, start, end);
      });
      _this.previousSelection = {
        start: start,
        end: end,
        length: Math.abs(end - start)
      };
    };

    _this.getSelection = function () {
      var input = _this.getInputDOMNode();

      return getInputSelection(input);
    };

    _this.getCursorPosition = function () {
      return _this.getSelection().start;
    };

    _this.setCursorPosition = function (pos) {
      _this.setSelection(pos, pos);
    };

    _this.isFocused = function () {
      return _this.focused;
    };

    _this.getBeforeMaskedValueChangeConfig = function () {
      var _this$maskOptions = _this.maskOptions,
          mask = _this$maskOptions.mask,
          maskChar = _this$maskOptions.maskChar,
          permanents = _this$maskOptions.permanents,
          formatChars = _this$maskOptions.formatChars;
      var alwaysShowMask = _this.props.alwaysShowMask;
      return {
        mask: mask,
        maskChar: maskChar,
        permanents: permanents,
        alwaysShowMask: !!alwaysShowMask,
        formatChars: formatChars
      };
    };

    _this.isInputAutofilled = function (value, selection, previousValue, previousSelection) {
      var input = _this.getInputDOMNode(); // only check for positive match because it will be false negative
      // in case of autofill simulation in tests
      //
      // input.matches throws an exception if selector isn't supported


      try {
        if (input.matches(':-webkit-autofill')) {
          return true;
        }
      } catch (e) {} // if input isn't focused then change event must have been triggered
      // either by autofill or event simulation in tests


      if (!_this.focused) {
        return true;
      } // if cursor has moved to the end while previousSelection forbids it
      // then it must be autofill


      return previousSelection.end < previousValue.length && selection.end === value.length;
    };

    _this.onChange = function (event) {
      var _assertThisInitialize = _assertThisInitialized(_assertThisInitialized(_this)),
          beforePasteState = _assertThisInitialize.beforePasteState;

      var _assertThisInitialize2 = _assertThisInitialized(_assertThisInitialized(_this)),
          previousSelection = _assertThisInitialize2.previousSelection;

      var beforeMaskedValueChange = _this.props.beforeMaskedValueChange;

      var value = _this.getInputValue();

      var previousValue = _this.value;

      var selection = _this.getSelection(); // autofill replaces entire value, ignore old one
      // https://github.com/sanniassin/react-input-mask/issues/113


      if (_this.isInputAutofilled(value, selection, previousValue, previousSelection)) {
        previousValue = formatValue(_this.maskOptions, '');
        previousSelection = {
          start: 0,
          end: 0,
          length: 0
        };
      } // set value and selection as if we haven't
      // cleared input in onPaste handler


      if (beforePasteState) {
        previousSelection = beforePasteState.selection;
        previousValue = beforePasteState.value;
        selection = {
          start: previousSelection.start + value.length,
          end: previousSelection.start + value.length,
          length: 0
        };
        value = previousValue.slice(0, previousSelection.start) + value + previousValue.slice(previousSelection.end);
        _this.beforePasteState = null;
      }

      var changedState = processChange(_this.maskOptions, value, selection, previousValue, previousSelection);
      var enteredString = changedState.enteredString;
      var newSelection = changedState.selection;
      var newValue = changedState.value;

      if (isFunction(beforeMaskedValueChange)) {
        var modifiedValue = beforeMaskedValueChange({
          value: newValue,
          selection: newSelection
        }, {
          value: previousValue,
          selection: previousSelection
        }, enteredString, _this.getBeforeMaskedValueChangeConfig());
        newValue = modifiedValue.value;
        newSelection = modifiedValue.selection;
      }

      _this.setInputValue(newValue);

      if (isFunction(_this.props.onChange)) {
        _this.props.onChange(event);
      }

      if (_this.isWindowsPhoneBrowser) {
        _this.setSelection(newSelection.start, newSelection.end, {
          deferred: true
        });
      } else {
        _this.setSelection(newSelection.start, newSelection.end);
      }
    };

    _this.onFocus = function (event) {
      var beforeMaskedValueChange = _this.props.beforeMaskedValueChange;
      var _this$maskOptions2 = _this.maskOptions,
          mask = _this$maskOptions2.mask,
          prefix = _this$maskOptions2.prefix;
      _this.focused = true; // if autoFocus is set, onFocus triggers before componentDidMount

      _this.mounted = true;

      if (mask) {
        if (!_this.value) {
          var emptyValue = formatValue(_this.maskOptions, prefix);
          var newValue = formatValue(_this.maskOptions, emptyValue);
          var filledLength = getFilledLength(_this.maskOptions, newValue);
          var cursorPosition = getRightEditablePosition(_this.maskOptions, filledLength);
          var newSelection = {
            start: cursorPosition,
            end: cursorPosition
          };

          if (isFunction(beforeMaskedValueChange)) {
            var modifiedValue = beforeMaskedValueChange({
              value: newValue,
              selection: newSelection
            }, {
              value: _this.value,
              selection: null
            }, null, _this.getBeforeMaskedValueChangeConfig());
            newValue = modifiedValue.value;
            newSelection = modifiedValue.selection;
          }

          var isInputValueChanged = newValue !== _this.getInputValue();

          if (isInputValueChanged) {
            _this.setInputValue(newValue);
          }

          if (isInputValueChanged && isFunction(_this.props.onChange)) {
            _this.props.onChange(event);
          }

          _this.setSelection(newSelection.start, newSelection.end);
        } else if (getFilledLength(_this.maskOptions, _this.value) < _this.maskOptions.mask.length) {
          _this.setCursorToEnd();
        }

        _this.runSaveSelectionLoop();
      }

      if (isFunction(_this.props.onFocus)) {
        _this.props.onFocus(event);
      }
    };

    _this.onBlur = function (event) {
      var beforeMaskedValueChange = _this.props.beforeMaskedValueChange;
      var mask = _this.maskOptions.mask;

      _this.stopSaveSelectionLoop();

      _this.focused = false;

      if (mask && !_this.props.alwaysShowMask && isEmpty(_this.maskOptions, _this.value)) {
        var newValue = '';

        if (isFunction(beforeMaskedValueChange)) {
          var modifiedValue = beforeMaskedValueChange({
            value: newValue,
            selection: null
          }, {
            value: _this.value,
            selection: _this.previousSelection
          }, null, _this.getBeforeMaskedValueChangeConfig());
          newValue = modifiedValue.value;
        }

        var isInputValueChanged = newValue !== _this.getInputValue();

        if (isInputValueChanged) {
          _this.setInputValue(newValue);
        }

        if (isInputValueChanged && isFunction(_this.props.onChange)) {
          _this.props.onChange(event);
        }
      }

      if (isFunction(_this.props.onBlur)) {
        _this.props.onBlur(event);
      }
    };

    _this.onMouseDown = function (event) {
      // tiny unintentional mouse movements can break cursor
      // position on focus, so we have to restore it in that case
      //
      // https://github.com/sanniassin/react-input-mask/issues/108
      if (!_this.focused && document.addEventListener) {
        _this.mouseDownX = event.clientX;
        _this.mouseDownY = event.clientY;
        _this.mouseDownTime = new Date().getTime();

        var mouseUpHandler = function mouseUpHandler(mouseUpEvent) {
          document.removeEventListener('mouseup', mouseUpHandler);

          if (!_this.focused) {
            return;
          }

          var deltaX = Math.abs(mouseUpEvent.clientX - _this.mouseDownX);
          var deltaY = Math.abs(mouseUpEvent.clientY - _this.mouseDownY);
          var axisDelta = Math.max(deltaX, deltaY);

          var timeDelta = new Date().getTime() - _this.mouseDownTime;

          if (axisDelta <= 10 && timeDelta <= 200 || axisDelta <= 5 && timeDelta <= 300) {
            _this.setCursorToEnd();
          }
        };

        document.addEventListener('mouseup', mouseUpHandler);
      }

      if (isFunction(_this.props.onMouseDown)) {
        _this.props.onMouseDown(event);
      }
    };

    _this.onPaste = function (event) {
      if (isFunction(_this.props.onPaste)) {
        _this.props.onPaste(event);
      } // event.clipboardData might not work in Android browser
      // cleaning input to get raw text inside onChange handler


      if (!event.defaultPrevented) {
        _this.beforePasteState = {
          value: _this.getInputValue(),
          selection: _this.getSelection()
        };

        _this.setInputValue('');
      }
    };

    _this.handleRef = function (ref) {
      if (_this.props.children == null && isFunction(_this.props.inputRef)) {
        _this.props.inputRef(ref);
      }
    };

    var _mask = props.mask,
        _maskChar = props.maskChar,
        _formatChars = props.formatChars,
        _alwaysShowMask = props.alwaysShowMask,
        _beforeMaskedValueChange = props.beforeMaskedValueChange;
    var defaultValue = props.defaultValue,
        _value = props.value;
    _this.maskOptions = parseMask(_mask, _maskChar, _formatChars);

    if (defaultValue == null) {
      defaultValue = '';
    }

    if (_value == null) {
      _value = defaultValue;
    }

    var _newValue = getStringValue(_value);

    if (_this.maskOptions.mask && (_alwaysShowMask || _newValue)) {
      _newValue = formatValue(_this.maskOptions, _newValue);

      if (isFunction(_beforeMaskedValueChange)) {
        var oldValue = props.value;

        if (props.value == null) {
          oldValue = defaultValue;
        }

        oldValue = getStringValue(oldValue);

        var modifiedValue = _beforeMaskedValueChange({
          value: _newValue,
          selection: null
        }, {
          value: oldValue,
          selection: null
        }, null, _this.getBeforeMaskedValueChangeConfig());

        _newValue = modifiedValue.value;
      }
    }

    _this.value = _newValue;
    return _this;
  }

  var _proto = InputElement.prototype;

  _proto.componentDidMount = function componentDidMount() {
    this.mounted = true; // workaround for react-test-renderer
    // https://github.com/sanniassin/react-input-mask/issues/147

    if (!this.getInputDOMNode()) {
      return;
    }

    this.isWindowsPhoneBrowser = isWindowsPhoneBrowser();

    if (this.maskOptions.mask && this.getInputValue() !== this.value) {
      this.setInputValue(this.value);
    }
  };

  _proto.componentDidUpdate = function componentDidUpdate() {
    var previousSelection = this.previousSelection;
    var _this$props = this.props,
        beforeMaskedValueChange = _this$props.beforeMaskedValueChange,
        alwaysShowMask = _this$props.alwaysShowMask,
        mask = _this$props.mask,
        maskChar = _this$props.maskChar,
        formatChars = _this$props.formatChars;
    var previousMaskOptions = this.maskOptions;
    var showEmpty = alwaysShowMask || this.isFocused();
    var hasValue = this.props.value != null;
    var newValue = hasValue ? getStringValue(this.props.value) : this.value;
    var cursorPosition = previousSelection ? previousSelection.start : null;
    this.maskOptions = parseMask(mask, maskChar, formatChars);

    if (!this.maskOptions.mask) {
      if (previousMaskOptions.mask) {
        this.stopSaveSelectionLoop(); // render depends on this.maskOptions and this.value,
        // call forceUpdate to keep it in sync

        this.forceUpdate();
      }

      return;
    } else if (!previousMaskOptions.mask && this.isFocused()) {
      this.runSaveSelectionLoop();
    }

    var isMaskChanged = this.maskOptions.mask && this.maskOptions.mask !== previousMaskOptions.mask;

    if (!previousMaskOptions.mask && !hasValue) {
      newValue = this.getInputValue();
    }

    if (isMaskChanged || this.maskOptions.mask && (newValue || showEmpty)) {
      newValue = formatValue(this.maskOptions, newValue);
    }

    if (isMaskChanged) {
      var filledLength = getFilledLength(this.maskOptions, newValue);

      if (cursorPosition === null || filledLength < cursorPosition) {
        if (isFilled(this.maskOptions, newValue)) {
          cursorPosition = filledLength;
        } else {
          cursorPosition = getRightEditablePosition(this.maskOptions, filledLength);
        }
      }
    }

    if (this.maskOptions.mask && isEmpty(this.maskOptions, newValue) && !showEmpty && (!hasValue || !this.props.value)) {
      newValue = '';
    }

    var newSelection = {
      start: cursorPosition,
      end: cursorPosition
    };

    if (isFunction(beforeMaskedValueChange)) {
      var modifiedValue = beforeMaskedValueChange({
        value: newValue,
        selection: newSelection
      }, {
        value: this.value,
        selection: this.previousSelection
      }, null, this.getBeforeMaskedValueChangeConfig());
      newValue = modifiedValue.value;
      newSelection = modifiedValue.selection;
    }

    this.value = newValue;
    var isValueChanged = this.getInputValue() !== this.value; // render depends on this.maskOptions and this.value,
    // call forceUpdate to keep it in sync

    if (isValueChanged) {
      this.setInputValue(this.value);
      this.forceUpdate();
    } else if (isMaskChanged) {
      this.forceUpdate();
    }

    var isSelectionChanged = false;

    if (newSelection.start != null && newSelection.end != null) {
      isSelectionChanged = !previousSelection || previousSelection.start !== newSelection.start || previousSelection.end !== newSelection.end;
    }

    if (isSelectionChanged || isValueChanged) {
      this.setSelection(newSelection.start, newSelection.end);
    }
  };

  _proto.componentWillUnmount = function componentWillUnmount() {
    this.mounted = false;

    if (this.selectionDeferId !== null) {
      cancelDefer(this.selectionDeferId);
    }

    this.stopSaveSelectionLoop();
  };

  _proto.render = function render() {
    var _this$props2 = this.props,
        mask = _this$props2.mask,
        alwaysShowMask = _this$props2.alwaysShowMask,
        maskChar = _this$props2.maskChar,
        formatChars = _this$props2.formatChars,
        inputRef = _this$props2.inputRef,
        beforeMaskedValueChange = _this$props2.beforeMaskedValueChange,
        children = _this$props2.children,
        restProps = _objectWithoutPropertiesLoose(_this$props2, ["mask", "alwaysShowMask", "maskChar", "formatChars", "inputRef", "beforeMaskedValueChange", "children"]);

    var inputElement;
     true ? warning( // parse mask to test against actual mask prop as this.maskOptions
    // will be updated later in componentDidUpdate
    !restProps.maxLength || !parseMask(mask, maskChar, formatChars).mask, 'react-input-mask: maxLength property shouldn\'t be passed to the masked input. It breaks masking and unnecessary because length is limited by the mask length.') : 0;

    if (children) {
      !isFunction(children) ?  true ? invariant(false, 'react-input-mask: children must be a function') : 0 : void 0;
      var controlledProps = ['onChange', 'onPaste', 'onMouseDown', 'onFocus', 'onBlur', 'value', 'disabled', 'readOnly'];

      var childrenProps = _extends({}, restProps);

      controlledProps.forEach(function (propId) {
        return delete childrenProps[propId];
      });
      inputElement = children(childrenProps);
      var conflictProps = controlledProps.filter(function (propId) {
        return inputElement.props[propId] != null && inputElement.props[propId] !== restProps[propId];
      });
      !!conflictProps.length ?  true ? invariant(false, "react-input-mask: the following props should be passed to the react-input-mask's component and should not be altered in children's function: " + conflictProps.join(', ')) : 0 : void 0;
       true ? warning(!inputRef, 'react-input-mask: inputRef is ignored when children is passed, attach ref to the children instead') : 0;
    } else {
      inputElement = React.createElement("input", _extends({
        ref: this.handleRef
      }, restProps));
    }

    var changedProps = {
      onFocus: this.onFocus,
      onBlur: this.onBlur
    };

    if (this.maskOptions.mask) {
      if (!restProps.disabled && !restProps.readOnly) {
        changedProps.onChange = this.onChange;
        changedProps.onPaste = this.onPaste;
        changedProps.onMouseDown = this.onMouseDown;
      }

      if (restProps.value != null) {
        changedProps.value = this.value;
      }
    }

    inputElement = React.cloneElement(inputElement, changedProps);
    return inputElement;
  };

  return InputElement;
}(React.Component);

module.exports = InputElement;


/***/ }),

/***/ "./node_modules/warning/warning.js":
/*!*****************************************!*\
  !*** ./node_modules/warning/warning.js ***!
  \*****************************************/
/***/ ((module) => {

"use strict";
/**
 * Copyright (c) 2014-present, Facebook, Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 */



/**
 * Similar to invariant but only logs a warning if the condition is not met.
 * This can be used to log issues in development environments in critical
 * paths. Removing the logging code for production environments will keep the
 * same logic and follow the same code paths.
 */

var __DEV__ = "development" !== 'production';

var warning = function() {};

if (__DEV__) {
  var printWarning = function printWarning(format, args) {
    var len = arguments.length;
    args = new Array(len > 1 ? len - 1 : 0);
    for (var key = 1; key < len; key++) {
      args[key - 1] = arguments[key];
    }
    var argIndex = 0;
    var message = 'Warning: ' +
      format.replace(/%s/g, function() {
        return args[argIndex++];
      });
    if (typeof console !== 'undefined') {
      console.error(message);
    }
    try {
      // --- Welcome to debugging React ---
      // This error was thrown as a convenience so that you can use this stack
      // to find the callsite that caused this warning to fire.
      throw new Error(message);
    } catch (x) {}
  }

  warning = function(condition, format, args) {
    var len = arguments.length;
    args = new Array(len > 2 ? len - 2 : 0);
    for (var key = 2; key < len; key++) {
      args[key - 2] = arguments[key];
    }
    if (format === undefined) {
      throw new Error(
          '`warning(condition, format, ...args)` requires a warning ' +
          'message argument'
      );
    }
    if (!condition) {
      printWarning.apply(null, [format].concat(args));
    }
  };
}

module.exports = warning;


/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

"use strict";
module.exports = window["React"];

/***/ }),

/***/ "react-dom":
/*!***************************!*\
  !*** external "ReactDOM" ***!
  \***************************/
/***/ ((module) => {

"use strict";
module.exports = window["ReactDOM"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!*****************************************************************************!*\
  !*** ./assets/javascripts/front/reactCheckout/payments/creditCard/index.js ***!
  \*****************************************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! prop-types */ "./node_modules/prop-types/index.js");
/* harmony import */ var prop_types__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(prop_types__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _Card__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../Card */ "./assets/javascripts/front/reactCheckout/payments/Card/index.js");
/* harmony import */ var _useCreditCard__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./useCreditCard */ "./assets/javascripts/front/reactCheckout/payments/creditCard/useCreditCard.js");

const {
  registerPaymentMethod
} = window.wc.wcBlocksRegistry;



const backendConfig = wc.wcSettings.getSetting("woo-pagarme-payments-credit_card_data");
const PagarmeCreditCardComponent = props => {
  const {
    emitResponse,
    eventRegistration
  } = props;
  (0,_useCreditCard__WEBPACK_IMPORTED_MODULE_2__["default"])(backendConfig, emitResponse, eventRegistration);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_Card__WEBPACK_IMPORTED_MODULE_1__["default"], {
    ...props,
    backendConfig: backendConfig,
    cardIndex: 1
  });
};
const PagarmeCreditCardLabel = ({
  components
}) => {
  const {
    PaymentMethodLabel
  } = components;
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(PaymentMethodLabel, {
    text: backendConfig.label
  });
};
PagarmeCreditCardComponent.propTypes = {
  emitResponse: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().object),
  eventRegistration: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().object)
};
PagarmeCreditCardLabel.propTypes = {
  components: (prop_types__WEBPACK_IMPORTED_MODULE_3___default().object)
};
const pagarmeCreditCardPaymentMethod = {
  name: backendConfig.name,
  label: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(PagarmeCreditCardLabel, null),
  content: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(PagarmeCreditCardComponent, null),
  edit: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(PagarmeCreditCardComponent, null),
  canMakePayment: () => true,
  ariaLabel: backendConfig.ariaLabel
};
registerPaymentMethod(pagarmeCreditCardPaymentMethod);
})();

/******/ })()
;
//# sourceMappingURL=credit_card.js.map