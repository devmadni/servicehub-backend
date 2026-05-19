<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    /**
     * 200 — Generic success with data payload.
     *
     * @param  mixed  $data
     */
    protected function success(mixed $data, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data instanceof JsonResource ? $data->resolve() : $data,
        ], $status);
    }

    /**
     * 201 — Resource created.
     *
     * @param  mixed  $data
     */
    protected function created(mixed $data, string $message = 'Created successfully'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * 200 — Paginated collection with standard meta block.
     */
    protected function paginated(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
        ], 200);
    }

    /**
     * 200 — Auth token response (login / register).
     *
     * @param  mixed  $user
     */
    protected function withToken(string $token, mixed $user, string $message = 'Authenticated'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => [
                'token' => $token,
                'user'  => $user,
            ],
        ], 200);
    }

    /**
     * 204 — No content (delete / logout).
     */
    protected function noContent(string $message = 'Done'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => null,
        ], 200); // 200 instead of 204 so mobile clients can read the message body
    }

    /**
     * 400 — Bad request.
     */
    protected function badRequest(string $message = 'Bad request', array $errors = []): JsonResponse
    {
        return $this->error($message, 400, $errors);
    }

    /**
     * 401 — Unauthenticated.
     */
    protected function unauthorized(string $message = 'Unauthenticated'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * 403 — Forbidden.
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * 404 — Resource not found.
     */
    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * 409 — Conflict (e.g. slot already taken).
     */
    protected function conflict(string $message = 'Conflict'): JsonResponse
    {
        return $this->error($message, 409);
    }

    /**
     * 422 — Validation failed.
     *
     * @param  array<string, string[]>  $errors
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], 422);
    }

    /**
     * 405 — Method not allowed.
     */
    protected function methodNotAllowed(string $message = 'Method not allowed'): JsonResponse
    {
        return $this->error($message, 405);
    }

    /**
     * 500 — Internal server error.
     */
    protected function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return $this->error($message, 500);
    }

    /**
     * Generic error envelope — used by all named error helpers above.
     *
     * @param  array<string, mixed>  $errors
     */
    protected function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $body = [
            'success' => false,
            'message' => $message,
            'data'    => null,
        ];

        if (! empty($errors)) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }
}
