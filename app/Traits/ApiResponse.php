<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return an error response
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Error', int $statusCode = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a paginated response
     *
     * @param mixed $data
     * @param int $currentPage
     * @param int $totalPages
     * @param int $totalItems
     * @param int $itemsPerPage
     * @param array $links
     * @return JsonResponse
     */
    protected function paginatedResponse(
        $data,
        int $currentPage,
        int $totalPages,
        int $totalItems,
        int $itemsPerPage,
        array $links = []
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'itemsPerPage' => $itemsPerPage,
                'hasNext' => $currentPage < $totalPages,
                'hasPrevious' => $currentPage > 1,
            ],
            'links' => $links,
        ]);
    }
}