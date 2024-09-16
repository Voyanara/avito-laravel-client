<?php

namespace Voyanara\LaravelApiClient\Application\DTO\UserInfo;

use Spatie\LaravelData\Data;

class UserOperationItemDTO extends Data
{
    public function __construct(
        public readonly float $amountBonus,
        public readonly float $amountRub,
        public readonly float $amountTotal,
        public readonly int $itemId,
        public readonly string $operationName,
        public readonly string $operationType,
        public readonly string $paidAt,
        public readonly int $serviceId,
        public readonly string $serviceName,
        public readonly string $serviceType,
        public readonly string $updatedAt,
    ) {}
}
