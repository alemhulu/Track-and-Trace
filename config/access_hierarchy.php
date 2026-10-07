<?php

return [
    'role_scope' => [
        'Super-Admin' => 'national',
        'Admin' => 'national',
        'National Admin' => 'national',
        'Region Officer' => 'region',
        'Zone Officer' => 'zone',
        'Woreda Officer' => 'woreda',
        'Organization User' => 'organization',
        'School User' => 'organization',
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
