const pagarmeTdsToken = {
    FAIL_GET_TOKEN: "fail_get_token",

    /**
     * O `engine` acompanha o token porque só o backend sabe qual emissor o
     * gerou, e cada emissor é aceito por apenas um SDK de 3DS.
     *
     * `engine` é opcional e serve ao fallback: o checkout pede um token do
     * legado quando o NX não está disponível. Sem argumento, o backend decide
     * (preferindo o NX).
     */
    getToken: (engine) => {
        try {
            const response = jQuery.ajax({
                type: "GET",
                dataType: "json",
                url: "/wc-api/pagarme-tds-token",
                data: engine ? { engine } : {},
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
