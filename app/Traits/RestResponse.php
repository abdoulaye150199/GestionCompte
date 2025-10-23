<?php

namespace App\Traits;

trait RestResponse
{
    /**
     * Format a success response
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, string $message = '', int $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Format an error response
     *
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message, int $code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $code);
    }

    /**
     * Format a collection response
     *
     * @param \Illuminate\Database\Eloquent\Collection $collection
     * @param callable|null $transformer
     * @return array
     */
    protected function formatCollection($collection, callable $transformer = null)
    {
        if ($transformer) {
            return $collection->map($transformer)->all();
        }
        return $collection->all();
    }
}