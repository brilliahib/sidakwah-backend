<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function success($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => $code,
                'message' => $message,
            ],
            'data' => $data,
        ], $code);
    }

    protected function error(string $message = 'Error', int $code = 400, $data = null)
    {
        return response()->json([
            'meta' => [
                'status' => 'error',
                'statusCode' => $code,
                'message' => $message,
            ],
            'data' => $data,
        ], $code);
    }
}
