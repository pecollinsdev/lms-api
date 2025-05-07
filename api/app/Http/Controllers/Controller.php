<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Return a standard JSON success response.
     */
    protected function respond($data = null, string $message = '', int $status = Response::HTTP_OK)
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Return a 201 Created JSON response.
     */
    protected function respondCreated($data = null, string $message = 'Resource created')
    {
        return $this->respond($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Return a 422 Unprocessable Entity (validation error).
     */
    protected function respondValidationError(array $errors, string $message = 'Validation error')
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Return a 401 Unauthorized JSON response.
     */
    protected function respondUnauthorized(string $message = 'Unauthorized')
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Validate the incoming request against given rules,
     * and return the validated data array.
     */
    protected function validated(Request $request, array $rules): array
    {
        return $request->validate($rules);
    }
}
