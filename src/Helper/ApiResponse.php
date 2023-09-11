<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse extends JsonResponse
{
    public static function createAndFormat(
        mixed $data,
        ?string $message = ApiMessages::MESSAGE_OK,
        ?string $key = ApiMessages::INDEX_SUCCESS,
        ?int $statusCode = Response::HTTP_OK
    ): self {
        return self::create([
            'data' => $data,
            'status' => $key,
            'message' => $message,
        ], $statusCode);
    }

    public static function create($data, $statusCode = self::HTTP_OK): self
    {
        return new self($data, $statusCode);
    }

    public static function createMessage($message, $statusCode = self::HTTP_OK): self
    {
        return new self(
            [
                ApiMessages::INDEX_STATUS => ApiMessages::INDEX_SUCCESS,
                ApiMessages::INDEX_MESSAGE => $message,
            ],
            $statusCode);
    }

    public static function createWarningMessage($message, $statusCode = self::HTTP_BAD_REQUEST): self
    {
        return new self(
            [
                ApiMessages::INDEX_STATUS => ApiMessages::INDEX_WARNING,
                ApiMessages::INDEX_MESSAGE => $message,
            ],
            $statusCode
        );
    }

    public static function createErrorMessage(
        string $message,
        int $statusCode = self::HTTP_INTERNAL_SERVER_ERROR,
        \Throwable $exception = null
    ): self {
        $data = [
            ApiMessages::INDEX_STATUS => ApiMessages::INDEX_ERROR,
            ApiMessages::INDEX_MESSAGE => $message,
        ];

        if ('dev' === $_ENV['APP_ENV']) {
            $data['debug'] = $exception?->getMessage();
        }

        return new self(
            $data,
            $statusCode
        );
    }
}
