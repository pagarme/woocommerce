(function ($) {
        const invalidFieldClass = 'invalid-field';
        const fieldErrorMessageClass = 'field-error-message';
        const fieldValidateDataAttribute = 'data-field-validate';
        const maxLengthDataAttribute = 'max-length';
        const minDataAttribute = 'min';
        const errorMessageDataAttribute = 'error-message';

        const validateRequiredField = (element, value) => {
            const isMultipleSelect = element.is('select') && element.is('[multiple]');
            return (!$.trim(value) && !isMultipleSelect) || (value.length === 0 && isMultipleSelect);
        };

        const validateMaxLengthField = (element, value) => {
            const maxLength = parseInt($(element).data(maxLengthDataAttribute));
            return  value.length > maxLength;
        };

        const validateMinValueField = (element, value) => {
            const minValue = parseFloat($(element).data(minDataAttribute));
            return value < minValue;
        }

        const validateAlphanumericAndSpacesAndPunctuation = (element, value) => {
            const regex = /^[A-Za-z0-9À-ú \-:()%@*_.,!?$;]+$/;
            return value?.length > 0 && !value.match(regex);
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
                        case 'alphanumeric-spaces-punctuation':
                            fieldHasError = validateAlphanumericAndSpacesAndPunctuation(element, fieldValue);
                            break;
                    }

                    if (fieldHasError) {
                        element.addClass(invalidFieldClass);
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
