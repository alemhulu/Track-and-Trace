{{-- End-to-end supply chain shown at the top of each module it spans, so users can see where they are in it. --}}
<x-ui.workflow {{ $attributes }} label="Supply chain" :steps="[
    ['title' => 'Print Orders', 'hint' => 'Order & print', 'link' => 'print-order.list', 'can' => 'print-orders.view', 'match' => 'print-order*', 'icon' => 'fi-rr-print'],
    ['title' => 'Packages', 'hint' => 'Request & send', 'link' => 'book-packages', 'can' => 'packages.view', 'match' => ['book-packages*', 'packages.*'], 'icon' => 'fi-rr-box'],
    ['title' => 'Routes', 'hint' => 'Plan delivery paths', 'link' => 'route.list', 'can' => 'routes.view', 'match' => ['route', 'route.*'], 'icon' => 'fi-rr-chart-tree'],
    ['title' => 'Distribution', 'hint' => 'Dispatch to destinations', 'link' => 'distribution.list', 'can' => 'distributions.view', 'match' => ['distribution', 'distribution.*'], 'icon' => 'fi-rr-truck-side'],
    ['title' => 'Trace', 'hint' => 'Follow every copy', 'link' => 'trace', 'can' => 'trace.view', 'match' => ['trace', 'trace.*'], 'icon' => 'fi-rr-search'],
]" />
