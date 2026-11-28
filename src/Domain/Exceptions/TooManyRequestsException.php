<?php

namespace Voyanara\LaravelApiClient\Domain\Exceptions;

/**
 * Thrown on 429: the Avito rate limit for the method is exceeded.
 * GET requests are repeated automatically while Retry-After fits avito.retry.max_wait;
 * use $retryAfter to delay a queued job, e.g. $this->release($e->retryAfter ?? 60).
 * Extends ClientResponseException, so `catch (ClientResponseException)` handles it too.
 */
class TooManyRequestsException extends ClientResponseException {}
