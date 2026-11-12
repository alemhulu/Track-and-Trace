{{-- Any other 4xx status without its own page --}}
@include('errors.partials.page', [
    'code' => method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 400,
    'title' => 'This request couldn\'t be completed',
    'status' => 'Request problem',
    'message' => 'Something about the request wasn\'t right. Go back and try again, or start from the dashboard.',
    'tone' => 'info',
    'icon' => 'alert',
    'actions' => ['back', 'home'],
])
