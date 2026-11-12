@include('errors.partials.page', [
    'code' => 419,
    'title' => 'Your session expired',
    'status' => 'Session expired',
    'message' => 'For your security, forms stop working after a while without activity. Refresh the page, then fill in and submit the form again.',
    'tone' => 'warn',
    'icon' => 'clock',
    'actions' => ['reload', 'login'],
])
