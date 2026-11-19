<?php
declare(strict_types=1);

return [
    'name' => 'ORIENTATION-BJ',
    'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Porto-Novo',
    'debug' => filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOL),
];
