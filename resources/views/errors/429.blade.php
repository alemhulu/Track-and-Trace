@include('errors.partials.page', [
    'code' => 429,
    'title' => 'Too many requests',
    'status' => 'Slow down',
    'message' => 'A lot of requests came from your account in a short time. Wait a minute, then try again.',
    'tone' => 'warn',
    'icon' => 'gauge',
    'actions' => ['back', 'home'],
])
