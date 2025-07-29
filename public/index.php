<?php

declare(strict_types=1);

use Gsu\SyllabusPortal\Kernel;

date_default_timezone_set(
    is_string($_SERVER['APP_TIMEZONE'] ?? null)
        ? $_SERVER['APP_TIMEZONE']
        : 'America/New_York'
);

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return fn (array $context) => new Kernel(
    is_string($context['APP_ENV'] ?? null) ? $context['APP_ENV'] : 'dev',
    (bool) $context['APP_DEBUG']
);
