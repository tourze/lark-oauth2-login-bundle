<?php

namespace Tourze\LarkOAuth2LoginBundle\Exception;

abstract class LarkOAuth2Exception extends \Exception
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, private readonly array $context = [])
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
