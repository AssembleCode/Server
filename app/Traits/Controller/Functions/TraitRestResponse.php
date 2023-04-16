<?php

namespace App\Traits\Controller\Functions;

use App\Exceptions\ErrorException;

trait TraitRestResponse
{
    // protected function successResponse($data)
    // {
    //     $response = [
    //         'code'         => 200,
    //         'status'     => 'success',
    //         'data'         => $data
    //     ];

    //     return response()->json($response['data'], $response['code']);
    // }

    // protected function errorResponse($data = null)
    // {
    //     $response = [
    //         'code'         => 422,
    //         'status'     => 'error',
    //         'data'         => $data,
    //         'message'     => 'Unprocessable Entity'
    //     ];

    //     throw new ErrorException($response['data']);
    // }

    public function successResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }

    public function errorResponse($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];


        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }
}
