<?php

return [
    'enabled' => env('OPENGREP_ENABLED', false),
    'binary'  => env('OPENGREP_BINARY', 'opengrep'),
    'timeout' => 30,
];