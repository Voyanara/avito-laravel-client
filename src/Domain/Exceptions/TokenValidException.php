<?php

namespace Voyanara\LaravelApiClient\Domain\Exceptions;

/**
 * Thrown when Avito rejects the access token (401/403) even after a refresh.
 * Extends ClientResponseException, so `catch (ClientResponseException)` handles it too.
 */
class TokenValidException extends ClientResponseException {}
