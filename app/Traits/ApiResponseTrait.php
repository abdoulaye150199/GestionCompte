<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    public function successResponse($data, $message = null, $code = 200): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    public function errorResponse($message = null, $code = 400): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'data' => null,
            'message' => $message,
        ], $code);
    }

    public function paginatedResponse($data, $pagination, $message = null, $code = 200): JsonResponse
    {
        // Build HATEOAS links for pagination (non-breaking)
        $links = [];
        try {
            $currentPage = $pagination['currentPage'] ?? 1;
            $perPage = $pagination['itemsPerPage'] ?? ($pagination['perPage'] ?? 15);
            $totalPages = $pagination['totalPages'] ?? null;

            // Safe request/url fallbacks so this method is test-friendly (no request bound)
            $req = null;
            try {
                $req = request();
            } catch (\Exception $e) {
                $req = null;
            }

            if ($req && method_exists($req, 'url')) {
                $baseUrl = $req->url();
                $query = $req->query();
            } else {
                $baseUrl = config('app.url') ?? '';
                $query = [];
            }

            // ensure page is defined
            $query['page'] = $currentPage;
            $base = rtrim($baseUrl, '/');
            $links['self'] = $base . '?' . http_build_query(array_merge($query, ['page' => $currentPage]));
            if ($currentPage > 1) {
                $links['prev'] = $base . '?' . http_build_query(array_merge($query, ['page' => $currentPage - 1]));
                $links['first'] = $base . '?' . http_build_query(array_merge($query, ['page' => 1]));
            }
            if ($totalPages && $currentPage < $totalPages) {
                $links['next'] = $base . '?' . http_build_query(array_merge($query, ['page' => $currentPage + 1]));
                $links['last'] = $base . '?' . http_build_query(array_merge($query, ['page' => $totalPages]));
            }
        } catch (\Exception $e) {
            // On any failure, preserve an empty links array (non-breaking)
            $links = [];
        }

        return new JsonResponse([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'pagination' => $pagination,
            '_links' => $links,
        ], $code);
    }

    public function notFoundResponse($message = 'Resource not found'): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'data' => null,
            'message' => $message,
        ], 404);
    }
}
