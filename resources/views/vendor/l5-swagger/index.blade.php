<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{config('l5-swagger.documentations.'.$documentation.'.api.title')}}</title>
    {{-- Use direct public assets to avoid route-based redirects in dev environments --}}
    <link rel="stylesheet" type="text/css" href="{{ config('app.url') }}/swagger-assets/swagger-ui.css">
    <link rel="icon" type="image/png" href="{{ config('app.url') }}/swagger-assets/favicon-32x32.png" sizes="32x32"/>
    <link rel="icon" type="image/png" href="{{ config('app.url') }}/swagger-assets/favicon-16x16.png" sizes="16x16"/>
    <style>
    html
    {
        box-sizing: border-box;
        overflow: -moz-scrollbars-vertical;
        overflow-y: scroll;
    }
    *,
    *:before,
    *:after
    {
        box-sizing: inherit;
    }

    body {
      margin:0;
      background: #fafafa;
    }
    </style>
    @if(config('l5-swagger.defaults.ui.display.dark_mode'))
        <style>
            body#dark-mode,
            #dark-mode .scheme-container {
                background: #1b1b1b;
            }
            #dark-mode .scheme-container,
            #dark-mode .opblock .opblock-section-header{
                box-shadow: 0 1px 2px 0 rgba(255, 255, 255, 0.15);
            }
            #dark-mode .operation-filter-input,
            #dark-mode .dialog-ux .modal-ux,
            #dark-mode input[type=email],
            #dark-mode input[type=file],
            #dark-mode input[type=password],
            #dark-mode input[type=search],
            #dark-mode input[type=text],
            #dark-mode textarea{
                background: #343434;
                color: #e7e7e7;
            }
            #dark-mode .title,
            #dark-mode li,
            #dark-mode p,
            #dark-mode table,
            #dark-mode label,
            #dark-mode .opblock-tag,
            #dark-mode .opblock .opblock-summary-operation-id,
            #dark-mode .opblock .opblock-summary-path,
            #dark-mode .opblock .opblock-summary-path__deprecated,
            #dark-mode h1,
            #dark-mode h2,
            #dark-mode h3,
            #dark-mode h4,
            #dark-mode h5,
            #dark-mode .btn,
            #dark-mode .tab li,
            #dark-mode .parameter__name,
            #dark-mode .parameter__type,
            #dark-mode .prop-format,
            #dark-mode .loading-container .loading:after{
                color: #e7e7e7;
            }
            #dark-mode .opblock-description-wrapper p,
            #dark-mode .opblock-external-docs-wrapper p,
            #dark-mode .opblock-title_normal p,
            #dark-mode .response-col_status,
            #dark-mode table thead tr td,
            #dark-mode table thead tr th,
            #dark-mode .response-col_links,
            #dark-mode .swagger-ui{
                color: wheat;
            }
            #dark-mode .parameter__extension,
            #dark-mode .parameter__in,
            #dark-mode .model-title{
                color: #949494;
            }
            #dark-mode table thead tr td,
            #dark-mode table thead tr th{
                border-color: rgba(120,120,120,.2);
            }
            #dark-mode .opblock .opblock-section-header{
                background: transparent;
            }
            #dark-mode .opblock.opblock-post{
                background: rgba(73,204,144,.25);
            }
            #dark-mode .opblock.opblock-get{
                background: rgba(97,175,254,.25);
            }
            #dark-mode .opblock.opblock-put{
                background: rgba(252,161,48,.25);
            }
            #dark-mode .opblock.opblock-delete{
                background: rgba(249,62,62,.25);
            }
            #dark-mode .loading-container .loading:before{
                border-color: rgba(255,255,255,10%);
                border-top-color: rgba(255,255,255,.6);
            }
            #dark-mode svg:not(:root){
                fill: #e7e7e7;
            }
            #dark-mode .opblock-summary-description {
                color: #fafafa;
            }
        </style>
    @endif
</head>

<body @if(config('l5-swagger.defaults.ui.display.dark_mode')) id="dark-mode" @endif>
<div id="swagger-ui"></div>

{{-- Load scripts from public docs assets (pre-copied) to avoid controller routing and redirect loops --}}
<script src="{{ config('app.url') }}/swagger-assets/swagger-ui-bundle.js"></script>
<script src="{{ config('app.url') }}/swagger-assets/swagger-ui-standalone-preset.js"></script>

<?php $oauth2RedirectUrlValue = route('l5-swagger.'.$documentation.'.oauth2_callback', [], config('l5-swagger.documentations.'.$documentation.'.paths.use_absolute_path', false)); ?>
<script>
    // Prefer public copy if available (symlinked to storage) to avoid routing redirects
    const urlToDocs = <?php echo json_encode(url('/api-docs.json')); ?>;
    const operationsSorter = <?php echo json_encode($operationsSorter ?? null); ?>;
    const configUrl = <?php echo json_encode($configUrl ?? null); ?>;
    const validatorUrl = <?php echo json_encode($validatorUrl ?? null); ?>;
    const oauth2RedirectUrl = <?php echo json_encode($oauth2RedirectUrlValue); ?>;
    const docExpansion = <?php echo json_encode(config('l5-swagger.defaults.ui.display.doc_expansion', 'none')); ?>;
    const filterEnabled = <?php echo json_encode((bool) config('l5-swagger.defaults.ui.display.filter')); ?>;
    const persistAuth = <?php echo json_encode((bool) config('l5-swagger.defaults.ui.authorization.persist_authorization')); ?>;
    const usePkce = <?php echo json_encode((bool) config('l5-swagger.defaults.ui.authorization.oauth2.use_pkce_with_authorization_code_grant')); ?>;

    // Precompute values server-side and inject them as safe JS literals to avoid editor parsing issues
    const csrfToken = <?php echo json_encode(csrf_token()); ?>;
    const shouldInitOAuth = <?php echo json_encode(in_array('oauth2', array_column(config('l5-swagger.defaults.securityDefinitions.securitySchemes'), 'type'))); ?>;

    window.onload = function() {
        const ui = SwaggerUIBundle({
            dom_id: '#swagger-ui',
            url: urlToDocs,
            operationsSorter: operationsSorter,
            configUrl: configUrl,
            validatorUrl: validatorUrl,
            oauth2RedirectUrl: oauth2RedirectUrl,

            requestInterceptor: function(request) {
                if (csrfToken) {
                    request.headers['X-CSRF-TOKEN'] = csrfToken;
                }
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
            docExpansion: docExpansion,
            deepLinking: true,
            filter: filterEnabled,
            persistAuthorization: persistAuth
        });

        window.ui = ui;

        if (shouldInitOAuth) {
            ui.initOAuth({ usePkceWithAuthorizationCodeGrant: usePkce });
        }
    };
</script>

</body>
</html>
