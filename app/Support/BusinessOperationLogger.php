<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Throwable;

final class BusinessOperationLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public static function failure(string $operationName, Throwable $exception, array $context = []): void
    {
        Log::error('Business operation failed.', array_merge($context, [
            'operation_name' => $operationName,
            'exception_class' => $exception::class,
            'exception_message' => $exception->getMessage(),
        ]));
    }
}
