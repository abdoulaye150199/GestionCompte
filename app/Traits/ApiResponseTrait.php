<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    public function successResponse($data, $message = null, $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    public function errorResponse($message = null, $code = 400): JsonResponse
    {
        return response()->json([
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
            $baseUrl = request()->url();
            $query = request()->query();

            $query['page'] = $currentPage;
            $links['self'] = url($baseUrl) . '?' . http_build_query(array_merge($query, ['page' => $currentPage]));
            if ($currentPage > 1) {
                $links['prev'] = url($baseUrl) . '?' . http_build_query(array_merge($query, ['page' => $currentPage - 1]));
                $links['first'] = url($baseUrl) . '?' . http_build_query(array_merge($query, ['page' => 1]));
            }
            if ($totalPages && $currentPage < $totalPages) {
                $links['next'] = url($baseUrl) . '?' . http_build_query(array_merge($query, ['page' => $currentPage + 1]));
                $links['last'] = url($baseUrl) . '?' . http_build_query(array_merge($query, ['page' => $totalPages]));
            }
        } catch (\Exception $e) {
            $links = [];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'pagination' => $pagination,
            '_links' => $links,
        ], $code);
    }

    public function notFoundResponse($message = 'Resource not found'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => $message,
        ], 404);
    }
}
