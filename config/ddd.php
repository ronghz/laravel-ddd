<?php

return [
    'router' => [
        'use_auto_router' => true,
        'use_project_prefix' => true,
        'route_middleware' => ['system', 'auth'],
        'header_version_key' => 'Release-Version',
    ],

    'generator' => [
        'ports' => ['Platform', 'Customer']
    ],
];
