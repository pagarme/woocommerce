(function ($) {
        const invalidFieldClass = 'invalid-field';
        const fieldErrorMessageClass = 'field-error-message';
        const fieldValidateDataAttribute = 'data-field-validate';
        const maxLengthDataAttribute = 'max-length';
        const minDataAttribute = 'min';
        const errorMessageDataAttribute = 'error-message';

        const validateRequiredField = (element, value) => {
            const isMultipleSelect = element.is('select') && element.is('[multiple]');
            const isEmptyField = (!$.trim(value) && !isMultipleSelect) || (value.length === 0 && isMultipleSelect);
            if (isEmptyField) {
                element.addClass(invalidFieldClass);
                return true;
            }

            return false;
        };

        const validateMaxLengthField = (element, value) => {
            const maxLength = parseInt($(element).data(maxLengthDataAttribute));
            const isValueSizeGreaterThanMaxLength = value.length > maxLength;
            if (isValueSizeGreaterThanMaxLength) {
                element.addClass(invalidFieldClass);
                return true;
            }

            return false;
        };

        const validateMinValueField = (element, value) => {
            const minValue = parseFloat($(element).data(minDataAttribute));
            const isValueLesserThanMinValue = value < minValue;
            if (isValueLesserThanMinValue) {
                element.addClass(invalidFieldClass);
                return true;
            }

            return false;
        }

        const showErrorMessage = (element, errorMessage) => {
            const newParagraph = document.createElement('p');
            $(newParagraph).text(errorMessage);
            $(newParagraph).addClass(fieldErrorMessageClass)

            $(element).after($(newParagraph));
        }

        const getErrorMessage = (element, errorType) => {
            return $(element).data(`${errorMessageDataAttribute}-${errorType}`);
        }

        const resetErrorsInfo = () => {
            $(`.${invalidFieldClass}`).removeClass(invalidFieldClass)
            $(`.${fieldErrorMessageClass}`).remove();
        }

        const initializeFormErrorsVariables = (element) => {
            const isMultipleSelect = element.is('select') && element.is('[multiple]');
            if (isMultipleSelect) {
                const multipleSelect = $(element).closest('fieldset').find('.select2-selection--multiple');
                return {
                    element: multipleSelect,
                    errorMessageElement: $(multipleSelect).closest('.select2-container'),
                    fieldValue: $(element).val()
                }
            }

            return {
                element,
                errorMessageElement: element,
                fieldValue: $(element).val()
            };
        }

        function formHasErrors() {
            const validationFields = $('[data-field-validate]');
            let hasErrors = false;
            resetErrorsInfo();

            function validateField() {
                const originalElement = $(this);
                const {element, errorMessageElement, fieldValue} = initializeFormErrorsVariables(originalElement)
                const validationsType = $(this).attr(fieldValidateDataAttribute)
                    .split('|');
                validationsType.forEach(function (validationType) {
                    let fieldHasError = false;
                    switch (validationType) {
                        case 'required':
                            fieldHasError = validateRequiredField(element, fieldValue);
                            break;
                        case 'max-length':
                            fieldHasError = validateMaxLengthField(element, fieldValue);
                            break;
                        case 'min':
                            fieldHasError = validateMinValueField(element, parseFloat(fieldValue));
                            break;
                    }

                    if (fieldHasError) {

                        console.log(getErrorMessage(originalElement, validationType))
                        showErrorMessage(errorMessageElement, getErrorMessage(originalElement, validationType));
                        hasErrors = fieldHasError;
                    }
                });
            }

            validationFields.each(validateField);

            return hasErrors;
        }


        const addEventListener = () => {
            $('form button[type=submit]').on('click', function (event) {
                if (formHasErrors()) {
                    event.preventDefault();
                }
            });
        };

        addEventListener();
    }(jQuery)
);
