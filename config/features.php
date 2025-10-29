<?php

return [
    // Feature flags for optional behaviors
    // Enable HATEOAS links in resources and paginated responses
    'hateoas' => env('FEATURE_HATEOAS', true),
];
