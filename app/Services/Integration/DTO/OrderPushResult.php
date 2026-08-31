<?php

namespace App\Services\Integration\DTO;

final readonly class OrderPushResult
{
    public function __construct(
        public bool $success,
        public ?string $reference = null,   // شماره فاکتور/سند بازگشتی از باران
        public ?string $message = null,
        public ?int $httpStatus = null,
        public array $request = [],
        public array $response = [],
    ) {}

    public static function ok(?string $reference, array $request = [], array $response = [], ?int $http = 200): self
    {
        return new self(true, $reference, null, $http, $request, $response);
    }

    public static function fail(string $message, array $request = [], array $response = [], ?int $http = null): self
    {
        return new self(false, null, $message, $http, $request, $response);
    }
}
