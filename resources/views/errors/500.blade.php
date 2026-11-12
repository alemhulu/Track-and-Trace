@include('errors.partials.page', [
    'code' => 500,
    'title' => 'Something went wrong on our side',
    'status' => 'Server error',
    'message' => 'The problem has been logged. Try again in a moment. If it keeps happening, tell your system administrator what you were doing when it occurred.',
    'tone' => 'danger',
    'icon' => 'alert',
    'actions' => ['reload', 'home'],
])
