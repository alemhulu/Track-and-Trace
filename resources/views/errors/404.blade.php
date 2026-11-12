@include('errors.partials.page', [
    'code' => 404,
    'title' => 'We couldn\'t find that page',
    'status' => 'Not found',
    'message' => 'The link may be out of date, or the record was moved or removed. Check the address, or start again from the dashboard.',
    'tone' => 'info',
    'icon' => 'search',
    'actions' => ['home', 'back'],
])
