@include('errors.partials.page', [
    'code' => 503,
    'title' => 'We\'ll be back shortly',
    'status' => 'Maintenance',
    'message' => 'Track & Trace is being updated. Your data is safe; please check back in a few minutes.',
    'tone' => 'info',
    'icon' => 'tool',
    'actions' => ['reload'],
])
