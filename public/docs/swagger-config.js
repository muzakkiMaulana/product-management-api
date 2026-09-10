window.onload = function () {
    window.ui = SwaggerUIBundle({
        url: './openapi.json',
        dom_id: '#swagger-ui',
        deepLinking: true,
        persistAuthorization: true,
        validatorUrl: null,

        supportedSubmitMethods: [
            'get',
            'post',
            'put',
            'delete'
        ],

        presets: [
            SwaggerUIBundle.presets.apis
        ],

        layout: 'BaseLayout'
    });
};