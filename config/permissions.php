<?php

/*
|--------------------------------------------------------------------------
| Permission catalog
|--------------------------------------------------------------------------
|
| Every permission the app checks, grouped the way the sidebar is: section →
| module → permissions, and for modules made of several screens (Locations,
| book settings, …) module → sub-module → permissions. Names follow
| "module.action" or "module.submodule.action"; the first permission of a module
| opens it, and 'route' is its sidebar page. The seeder creates these, the role
| editor groups by them, and routes, navigation and Livewire actions check them.
|
*/

// View / create / edit / delete for one screen, e.g. $crud('locations.regions', 'regions')
$crud = fn (string $prefix, string $noun) => [
    "$prefix.view" => "View $noun",
    "$prefix.create" => "Create $noun",
    "$prefix.edit" => "Edit $noun",
    "$prefix.delete" => "Delete $noun",
];

return [
    'sections' => [
        'Overview' => [
            'dashboard' => [
                'route' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'fi-rr-dashboard',
                'permissions' => [
                    'dashboard.view' => 'View dashboard',
                ],
            ],
            'dashboard.inventory' => [
                'route' => 'dashboard.inventory',
                'label' => 'Inventory',
                'icon' => 'fi-rr-chart-histogram',
                'permissions' => [
                    'dashboard.inventory.view' => 'View inventory insights',
                ],
            ],
            'dashboard.distribution' => [
                'route' => 'dashboard.distribution',
                'label' => 'Distribution Insights',
                'icon' => 'fi-rr-chart-connected',
                'permissions' => [
                    'dashboard.distribution.view' => 'View distribution insights',
                ],
            ],
        ],

        'Supply Chain' => [
            'books' => [
                'route' => 'book',
                'label' => 'Books',
                'icon' => 'fi-rr-book',
                'permissions' => $crud('books', 'books'),
                'submodules' => [
                    'books.subjects' => ['label' => 'Subjects', 'permissions' => $crud('books.subjects', 'subjects')],
                    'books.grades' => ['label' => 'Grades', 'permissions' => $crud('books.grades', 'grades')],
                    'books.types' => ['label' => 'Book types', 'permissions' => $crud('books.types', 'book types')],
                    'books.print-types' => ['label' => 'Print types', 'permissions' => $crud('books.print-types', 'print types')],
                    'books.paper-sizes' => ['label' => 'Paper sizes', 'permissions' => $crud('books.paper-sizes', 'paper sizes')],
                ],
            ],
            'print-orders' => [
                'route' => 'print-order',
                'label' => 'Print Orders',
                'icon' => 'fi-rr-print',
                'permissions' => [
                    'print-orders.view' => 'View print orders',
                    'print-orders.create' => 'Place print orders',
                    'print-orders.edit' => 'Change print order status',
                ],
            ],
            'packages' => [
                'route' => 'book-packages',
                'label' => 'Packages',
                'icon' => 'fi-rr-box',
                'permissions' => [
                    'packages.view' => 'View packages',
                    'packages.request' => 'Open package requests',
                    'packages.send' => 'Open package sending',
                ],
            ],
            'warehouses' => [
                'route' => 'warehouse',
                'label' => 'Warehouses',
                'icon' => 'fi-rr-home-location-alt',
                'permissions' => $crud('warehouses', 'warehouses & stores'),
            ],
        ],

        'Logistics' => [
            'routes' => [
                'route' => 'route',
                'label' => 'Routes',
                'icon' => 'fi-rr-chart-tree',
                'permissions' => $crud('routes', 'routes'),
            ],
            'distributions' => [
                'route' => 'distribution',
                'label' => 'Distribution',
                'icon' => 'fi-rr-truck-side',
                'permissions' => $crud('distributions', 'distributions'),
            ],
            'trace' => [
                'route' => 'trace',
                'label' => 'Trace',
                'icon' => 'fi-rr-search',
                'permissions' => [
                    'trace.view' => 'Trace books and packages',
                ],
            ],
        ],

        'Field Operations' => [
            'manual-tracking' => [
                'route' => 'manual-tracking',
                'label' => 'Manual Tracking',
                'icon' => 'fi-rr-cursor-finger',
                'permissions' => [
                    'manual-tracking.view' => 'Open manual tracking',
                    'manual-tracking.reports.view' => 'View reports & exports',
                ],
                'submodules' => [
                    'manual-tracking.titles' => ['label' => 'Titles', 'permissions' => [
                        'manual-tracking.titles.create' => 'Register titles',
                        'manual-tracking.titles.edit' => 'Edit titles',
                        'manual-tracking.titles.delete' => 'Delete titles',
                    ]],
                    'manual-tracking.stock' => ['label' => 'Stock', 'permissions' => [
                        'manual-tracking.stock.manage' => 'Record opening stock',
                    ]],
                    'manual-tracking.shipments' => ['label' => 'Shipments', 'permissions' => [
                        'manual-tracking.shipments.dispatch' => 'Dispatch shipments',
                        'manual-tracking.shipments.receive' => 'Receive shipments',
                    ]],
                    'manual-tracking.scan' => ['label' => 'Sort & Scan', 'permissions' => [
                        'manual-tracking.scan.sort' => 'Sort & scan at hubs',
                        'manual-tracking.alerts.resolve' => 'Resolve misroute alerts',
                    ]],
                    'manual-tracking.discrepancies' => ['label' => 'Discrepancies', 'permissions' => [
                        'manual-tracking.discrepancies.resolve' => 'Resolve discrepancies',
                    ]],
                    'manual-tracking.schools' => ['label' => 'Schools', 'permissions' => [
                        'manual-tracking.schools.view' => 'Open school records',
                        'manual-tracking.schools.enrol' => 'Register students & enrolment',
                        'manual-tracking.schools.issue' => 'Issue & return books',
                    ]],
                    'manual-tracking.visits' => ['label' => 'Visits', 'permissions' => [
                        'manual-tracking.visits.record' => 'Record verification visits',
                    ]],
                ],
            ],
        ],

        'Administration' => [
            'organizations' => [
                'route' => 'organization',
                'label' => 'Organizations',
                'icon' => 'fi-rr-building',
                'permissions' => [
                    'organizations.view' => 'View organizations',
                    'organizations.create' => 'Create organizations',
                    'organizations.delete' => 'Delete organizations',
                ],
                'submodules' => [
                    'organizations.types' => ['label' => 'Organization types', 'permissions' => $crud('organizations.types', 'organization types')],
                ],
            ],
            'locations' => [
                'route' => 'location',
                'label' => 'Locations',
                'icon' => 'fi-rr-map-marker-home',
                'permissions' => [
                    'locations.view' => 'Open locations',
                ],
                'submodules' => [
                    'locations.countries' => ['label' => 'Countries', 'permissions' => $crud('locations.countries', 'countries')],
                    'locations.regions' => ['label' => 'Regions / Cities', 'permissions' => $crud('locations.regions', 'regions')],
                    'locations.zones' => ['label' => 'Zones / Sub-cities', 'permissions' => $crud('locations.zones', 'zones')],
                    'locations.woredas' => ['label' => 'Woredas', 'permissions' => $crud('locations.woredas', 'woredas')],
                    'locations.kebeles' => ['label' => 'Kebeles', 'permissions' => $crud('locations.kebeles', 'kebeles')],
                ],
            ],
            'users' => [
                'route' => 'users.index',
                'label' => 'Users',
                'icon' => 'fi-rr-user',
                'permissions' => $crud('users', 'users'),
            ],
            'roles' => [
                'route' => 'roles.index',
                'label' => 'Roles & Permissions',
                'icon' => 'fi-rr-key',
                'permissions' => $crud('roles', 'roles'),
            ],
            'logs' => [
                'label' => 'System Logs',
                'icon' => 'fi-rr-info',
                'permissions' => [
                    'logs.view' => 'View system logs',
                ],
            ],
        ],
    ],
];
