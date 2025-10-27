<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait RestResponse
{
    /**
     * Return a standardized success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Opération réussie', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a standardized error response
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Erreur', int $statusCode = 400, $errors = null): JsonResponse
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
     * Return a standardized paginated response
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
        $response = [
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
        ];

        // Add HATEOAS links for REST Level 3 compliance
        if (!empty($links)) {
            $response['_links'] = $links;
        }

        return response()->json($response);
    }

    /**
     * Validate request data
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return array
     */
    protected function validateRequest(Request $request, array $rules, array $messages = [], array $customAttributes = []): array
    {
        return $request->validate($rules, $messages, $customAttributes);
    }
}