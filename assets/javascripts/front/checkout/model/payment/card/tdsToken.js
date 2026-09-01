const pagarmeTdsToken = {
    FAIL_GET_TOKEN: "fail_get_token",

    getToken: () => {
        try {
            const response = jQuery.ajax({
                type: "GET",
                dataType: "json",
                url: "/wc-api/pagarme-tds-token",
                async: false,
                cache: false,
            }).responseText;

            if (response.length === 0) {
                return {
                    error: pagarmeTdsToken.FAIL_GET_TOKEN,
                };
            }

            const parsedResponse = JSON.parse(response);

            return {
                token: parsedResponse?.data?.token,
                engine: parsedResponse?.data?.engine,
            };
        } catch (e) {
            return {
                error: pagarmeTdsToken.FAIL_GET_TOKEN,
            };
        }
    },
};
