<?php

namespace Tourze\LarkOAuth2LoginBundle\Exception;

class LarkOAuth2ApiException extends LarkOAuth2Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        private readonly ?string $endpoint = null,
        /** @var array<string, mixed>|null */
        private readonly ?array $responseData = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }
}
