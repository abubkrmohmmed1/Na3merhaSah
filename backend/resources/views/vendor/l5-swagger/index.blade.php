<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentationTitle }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ l5_swagger_asset($documentation, 'swagger-ui.css') }}">
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-32x32.png') }}" sizes="32x32"/>
    <link rel="icon" type="image/png" href="{{ l5_swagger_asset($documentation, 'favicon-16x16.png') }}" sizes="16x16"/>
    <style>
    /* Namerha Sah Full Theme Overrides */
    body {
        margin: 0;
        background: #F5F7FB !important; /* light main bg */
        font-family: 'Cairo', sans-serif !important;
    }
    
    .swagger-ui * {
        font-family: 'Cairo', sans-serif !important;
    }

    /* Topbar */
    .swagger-ui .topbar { background-color: #1A284D !important; border-bottom: 3px solid #FACC6B; }
    .swagger-ui .topbar-wrapper .link span { display: none; } /* Hide swagger word */
    
    /* Text Colors */
    .swagger-ui .info .title { color: #1A284D !important; font-weight: 800; }
    .swagger-ui .info p, .swagger-ui .info li, .swagger-ui .info table { color: #2B3674 !important; }
    .swagger-ui a { color: #4B7DF3 !important; font-weight: 600; }
    .swagger-ui a:hover { color: #1A284D !important; }
    
    /* Buttons */
    .swagger-ui .btn.authorize { color: #FACC6B !important; border-color: #FACC6B !important; background: transparent !important; }
    .swagger-ui .btn.authorize svg { fill: #FACC6B !important; }
    .swagger-ui .btn.execute { background-color: #4B7DF3 !important; color: #FFFFFF !important; border-color: #4B7DF3 !important; }
    .swagger-ui .btn { box-shadow: none !important; font-weight: 600 !important; border-radius: 8px !important; }

    /* Blocks */
    .swagger-ui .opblock { border-radius: 12px !important; box-shadow: 0 2px 10px rgba(0,0,0,0.05) !important; margin-bottom: 15px !important; border: 1px solid #e2e8f0 !important; background: #FFFFFF !important; }
    
    .swagger-ui .opblock.opblock-post .opblock-summary-method { background: #4B7DF3 !important; border-radius: 8px !important; }
    .swagger-ui .opblock.opblock-get .opblock-summary-method { background: #1A284D !important; border-radius: 8px !important; }
    
    .swagger-ui .opblock-summary-path { font-size: 16px !important; color: #1A284D !important; font-weight: 700 !important; direction: ltr; text-align: left; }
    .swagger-ui .opblock-summary-description { color: #4B7DF3 !important; font-weight: 600 !important; }

    /* Tables & Inputs */
    .swagger-ui table thead tr td, .swagger-ui table thead tr th { color: #1A284D !important; border-bottom-color: #4B7DF3 !important; }
    .swagger-ui input[type=text], .swagger-ui input[type=password], .swagger-ui input[type=search], .swagger-ui input[type=email], .swagger-ui input[type=file], .swagger-ui textarea, .swagger-ui select {
        border: 1px solid #cbd5e1 !important; border-radius: 8px !important; padding: 8px 12px !important;
    }
    
    .swagger-ui .responses-inner h4, .swagger-ui .responses-inner h5 { color: #1A284D !important; }
    .swagger-ui .response-col_status { color: #4B7DF3 !important; font-weight: bold; }
    .swagger-ui .scheme-container { background: #FFFFFF !important; box-shadow: 0 2px 10px rgba(0,0,0,0.05) !important; border-bottom: 1px solid #e2e8f0 !important;}
    
    </style>
</head>

<body>
<div id="swagger-ui"></div>

<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-bundle.js') }}"></script>
<script src="{{ l5_swagger_asset($documentation, 'swagger-ui-standalone-preset.js') }}"></script>
<script>
    window.onload = function() {
        const urls = [];

        @foreach($urlsToDocs as $title => $url)
            urls.push({name: "{{ $title }}", url: "{{ $url }}"});
        @endforeach

        const ui = SwaggerUIBundle({
            dom_id: '#swagger-ui',
            urls: urls,
            "urls.primaryName": "{{ $documentationTitle }}",
            operationsSorter: {!! isset($operationsSorter) ? '"' . $operationsSorter . '"' : 'null' !!},
            configUrl: {!! isset($configUrl) ? '"' . $configUrl . '"' : 'null' !!},
            validatorUrl: {!! isset($validatorUrl) ? '"' . $validatorUrl . '"' : 'null' !!},
            oauth2RedirectUrl: "{{ route('l5-swagger.'.$documentation.'.oauth2_callback', [], $useAbsolutePath) }}",

            requestInterceptor: function(request) {
                request.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
                return request;
            },

            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],

            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],

            layout: "StandaloneLayout",
            docExpansion : "{!! config('l5-swagger.defaults.ui.display.doc_expansion', 'none') !!}",
            deepLinking: true,
            filter: {!! config('l5-swagger.defaults.ui.display.filter') ? 'true' : 'false' !!},
            persistAuthorization: "{!! config('l5-swagger.defaults.ui.authorization.persist_authorization') ? 'true' : 'false' !!}",

        })

        window.ui = ui

        @if(in_array('oauth2', array_column(config('l5-swagger.defaults.securityDefinitions.securitySchemes'), 'type')))
        ui.initOAuth({
            usePkceWithAuthorizationCodeGrant: "{!! (bool)config('l5-swagger.defaults.ui.authorization.oauth2.use_pkce_with_authorization_code_grant') !!}"
        })
        @endif
    }
</script>
</body>
</html>
