@include('errors.partials.page', [
    'code' => 401,
    'title' => 'Sign in to continue',
    'status' => 'Sign-in required',
    'message' => 'This page is for Ministry of Education accounts. Sign in and you can carry on where you were going.',
    'tone' => 'info',
    'icon' => 'lock',
    'actions' => ['login', 'back'],
])
