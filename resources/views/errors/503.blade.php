@include('errors.layout', [
    'code'    => 503,
    'title'   => 'Service Unavailable',
    'message' => 'Greplens is currently down for maintenance. We\'ll be back shortly.',
])
