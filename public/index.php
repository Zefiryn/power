<?php

try {
    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';
    // use App\Kernel;

    return function (array $context) {
        return new App\Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    };
} catch (Throwable $exception) {
    echo $exception->getMessage();
}
