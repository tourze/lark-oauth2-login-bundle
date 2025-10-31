<?php

namespace Tourze\LarkOAuth2LoginBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\LarkOAuth2LoginBundle\Repository\LarkOAuth2StateRepository;

#[ORM\Entity(repositoryClass: LarkOAuth2StateRepository::class)]
#[ORM\Table(name: 'lark_oauth2_state', options: ['comment' => '飞书OAuth2授权状态表'])]
class LarkOAuth2State implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '会话ID'])]
    #[Assert\Length(max: 255)]
    private ?string $sessionId = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否已使用'])]
    #[Assert\Type(type: 'bool')]
    private bool $isUsed = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'create_time', options: ['comment' => '创建时间'])]
    #[IndexColumn]
    private \DateTimeImmutable $createTime;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'used_time', nullable: true, options: ['comment' => '使用时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $usedTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'expires_time', options: ['comment' => '过期时间'])]
    #[Assert\NotNull]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private \DateTimeImmutable $expiresTime;

    #[ORM\Column(type: Types::STRING, length: 32, options: ['comment' => '状态值'])]
    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(exactly: 32)]
    #[Assert\Regex(pattern: '/^[a-fA-F0-9]{32}$/')]
    private string $state;

    #[ORM\ManyToOne(targetEntity: LarkOAuth2Config::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private LarkOAuth2Config $config;

    public function __construct()
    {
        $this->createTime = new \DateTimeImmutable();
        $this->expiresTime = $this->createTime->modify('+5 minutes');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getConfig(): LarkOAuth2Config
    {
        return $this->config;
    }

    public function setConfig(LarkOAuth2Config $config): void
    {
        $this->config = $config;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function isUsed(): bool
    {
        return $this->isUsed;
    }

    public function markAsUsed(): void
    {
        $this->isUsed = true;
        $this->usedTime = new \DateTimeImmutable();
    }

    public function getCreateTime(): \DateTimeImmutable
    {
        return $this->createTime;
    }

    public function getUsedTime(): ?\DateTimeImmutable
    {
        return $this->usedTime;
    }

    public function getExpiresTime(): \DateTimeImmutable
    {
        return $this->expiresTime;
    }

    public function isValid(): bool
    {
        return !$this->isUsed && !$this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expiresTime < new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf('LarkOAuth2State[%s]:%s', $this->id, $this->state);
    }
}
