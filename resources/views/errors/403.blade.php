@include('errors.partials.page', [
    'code' => 403,
    'title' => 'You don\'t have access to this page',
    'status' => 'Restricted',
    'message' => 'Your account\'s role or location doesn\'t include it. If you need it for your work, ask your administrator to adjust your permissions.',
    'tone' => 'warn',
    'icon' => 'lock',
    'actions' => ['back', 'home'],
])
