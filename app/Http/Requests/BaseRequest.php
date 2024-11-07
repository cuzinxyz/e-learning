<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    //override this method in your FormRequest
    protected function failedValidation(Validator $validator)
    {
        $response = [
            'code' => 422,
            'message' => [$validator->errors()],
            'data' => null,
        ];

        throw new HttpResponseException(
            response()->json($response, 422)
        );
    }
}
