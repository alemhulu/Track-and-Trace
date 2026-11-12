@include('errors.partials.page', [
    'code' => 405,
    'title' => 'That action isn\'t available here',
    'status' => 'Not allowed',
    'message' => 'The page was opened in a way it doesn\'t support, often by refreshing after submitting a form. Go back and try again.',
    'tone' => 'info',
    'icon' => 'alert',
    'actions' => ['back', 'home'],
])
