<?php

namespace App\Http\Middleware;

use App\Enums\ErrorCode;
use App\Enums\HttpStatusCode;
use App\Exceptions\CustomApiException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiErrorHandlerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (CustomApiException $e) {
            Log::info('CustomApiException caught in middleware: ' . $e->getMessage());
            return $this->handleCustomApiException($e);
        } catch (Throwable $e) {
            Log::info('Throwable caught in middleware: ' . get_class($e) . ' - ' . $e->getMessage());
            return $this->handleGenericException($e);
        }
    }

    public function terminate($request, $response)
    {
        // This method is called after the response is sent
        Log::info('ApiErrorHandlerMiddleware terminated');
    }

    /**
     * Handle CustomApiException
     */
    protected function handleCustomApiException(CustomApiException $e): Response
    {
        // Log the error with context
        Log::error('Custom API Exception', [
            'error_code' => $e->getErrorCode()->value,
            'message' => $e->getMessage(),
            'context' => $e->getContext(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->json($e->toArray(), $e->getHttpStatusCode()->value);
    }

    /**
     * Handle generic exceptions
     */
    protected function handleGenericException(Throwable $e): Response
    {
        // Log the error
        Log::error('Unhandled Exception in API', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        // Create a generic error response
        $errorResponse = [
            'success' => false,
            'error' => [
                'code' => ErrorCode::INTERNAL_SERVER_ERROR->value,
                'message' => $this->getUserFriendlyMessage($e),
            ],
            'timestamp' => now()->toISOString(),
        ];

        return response()->json($errorResponse, HttpStatusCode::INTERNAL_SERVER_ERROR->value);
    }

    /**
     * Get user-friendly error message based on exception type
     */
    protected function getUserFriendlyMessage(Throwable $e): string
    {
        // In production, don't expose internal error details
        if (app()->environment('production')) {
            return 'Une erreur inattendue s\'est produite. Veuillez réessayer plus tard.';
        }

        // In development, show the actual error for debugging
        return $e->getMessage();
    }

    /**
     * Create a standardized error response
     */
    public static function createErrorResponse(
        ErrorCode $errorCode,
        HttpStatusCode $httpStatusCode,
        string $customMessage = null,
        array $context = []
    ): Response {
        $exception = new CustomApiException($errorCode, $httpStatusCode, $customMessage, $context);
        return response()->json($exception->toArray(), $httpStatusCode->value);
    }

    /**
     * Helper method to throw CustomApiException
     */
    public static function throwError(
        ErrorCode $errorCode,
        HttpStatusCode $httpStatusCode,
        string $customMessage = null,
        array $context = []
    ): void {
        throw new CustomApiException($errorCode, $httpStatusCode, $customMessage, $context);
    }
}