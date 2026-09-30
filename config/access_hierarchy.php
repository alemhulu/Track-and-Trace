<?php

return [
    'role_scope' => [
        'Super-Admin' => 'national',
        'Admin' => 'national',
        'Org-Manager' => 'organization',
    ],

    'default_role_scope' => 'organization',

    'levels' => [
        'national',
        'region',
        'zone',
        'woreda',
        'organization',
    ],
];
