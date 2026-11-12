{{-- Any other 5xx status without its own page --}}
@include('errors.partials.page', [
    'code' => method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500,
    'title' => 'The service is having trouble',
    'status' => 'Server error',
    'message' => 'Track & Trace couldn\'t complete this request. Try again in a moment; if it keeps happening, contact your system administrator.',
    'tone' => 'danger',
    'icon' => 'alert',
    'actions' => ['reload', 'home'],
])
