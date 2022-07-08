<?php

namespace SultanovSolutions\LaravelBase\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class JsonException extends Exception
{
    const JSON_RESOLVE_STRING = '- json';

    public function __construct(mixed $message = "", $code = 500, Throwable $previous = null)
    {
        if (is_array($message))
            $message = json_encode($message) . self::JSON_RESOLVE_STRING;

        parent::__construct($message, $code, $previous);
    }

    public function render(): JsonResponse
    {
        $message = $this->getMessage();

        $response = ['status' => false, 'message' => $message];

        if (str($message)->contains(self::JSON_RESOLVE_STRING))
            $response = json_decode(str($message)->replace(self::JSON_RESOLVE_STRING, ''), 1);

        if (!isset($response['status']))
            $response['status'] = false;

        if ($response['status'] === 'none')
            unset($response['status']);

        return response()->json($response, $this->getCode());
    }
}
