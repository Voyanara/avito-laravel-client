<?php

namespace Voyanara\LaravelApiClient\Domain\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Throwable;

/**
 * An Avito API error. The message is the raw response body, as before;
 * the parsed details are available as properties.
 *
 * The constructor is final: fromResponse() creates subclasses with `new static`,
 * so they may add methods but cannot change the constructor.
 */
class ClientResponseException extends Exception
{
    /**
     * @param  int|null  $status  HTTP status code (also returned by getCode())
     * @param  int|null  $errorCode  Avito error code from the body: error.code or code (e.g. 1009 in applyVas)
     * @param  string|null  $errorMessage  Avito error message from the body: error.message, message or result.message
     * @param  int|null  $retryAfter  Seconds from the Retry-After header
     */
    final public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?int $status = null,
        public readonly ?int $errorCode = null,
        public readonly ?string $errorMessage = null,
        public readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function fromResponse(Response $response): static
    {
        $errorCode = $response->json('error.code') ?? $response->json('code');
        $errorMessage = $response->json('error.message') ?? $response->json('message') ?? $response->json('result.message');

        return new static(
            $response->body(),
            $response->status(),
            null,
            $response->status(),
            is_numeric($errorCode) ? (int) $errorCode : null,
            is_string($errorMessage) ? $errorMessage : null,
            self::parseRetryAfter($response->header('Retry-After')),
        );
    }

    /**
     * Retry-After is either a number of seconds or an HTTP date.
     */
    private static function parseRetryAfter(string $value): ?int
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : max(0, $timestamp - now()->getTimestamp());
    }
}
