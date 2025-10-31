<?php

namespace Tourze\LarkOAuth2LoginBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\LarkOAuth2LoginBundle\Repository\LarkOAuth2UserRepository;

#[ORM\Entity(repositoryClass: LarkOAuth2UserRepository::class)]
#[ORM\Table(name: 'lark_oauth2_user', options: ['comment' => '飞书OAuth2用户信息表'])]
#[ORM\UniqueConstraint(name: 'UNIQ_lark_oauth2_user_open_id', columns: ['open_id'])]
#[ORM\UniqueConstraint(name: 'UNIQ_lark_oauth2_user_union_id', columns: ['union_id'])]
class LarkOAuth2User implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: LarkOAuth2Config::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private LarkOAuth2Config $config;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '用户开放ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $openId;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '用户联合ID'])]
    #[Assert\Length(max: 255)]
    private ?string $unionId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '用户ID'])]
    #[IndexColumn]
    #[Assert\Length(max: 255)]
    private ?string $userId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '用户姓名'])]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '用户英文名'])]
    #[Assert\Length(max: 255)]
    private ?string $enName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '头像URL'])]
    #[Assert\Length(max: 65535)]
    #[Assert\Url]
    private ?string $avatarUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '头像缩略图'])]
    #[Assert\Length(max: 65535)]
    private ?string $avatarThumb = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '头像中等尺寸'])]
    #[Assert\Length(max: 65535)]
    private ?string $avatarMiddle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '头像大尺寸'])]
    #[Assert\Length(max: 65535)]
    private ?string $avatarBig = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '邮箱地址'])]
    #[Assert\Length(max: 50)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true, options: ['comment' => '手机号码'])]
    #[Assert\Length(max: 50)]
    #[Assert\Regex(pattern: '/^\+?[1-9]\d{1,14}$/')]
    private ?string $mobile = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '租户密钥'])]
    #[Assert\Length(max: 255)]
    private ?string $tenantKey = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '员工编号'])]
    #[Assert\Length(max: 255)]
    private ?string $employeeNo = null;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '访问令牌'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    private string $accessToken;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '刷新令牌'])]
    #[Assert\Length(max: 65535)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '令牌过期时间(秒)'])]
    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    private int $expiresIn;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'token_expires_time', options: ['comment' => '令牌过期时间'])]
    #[Assert\NotNull]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private \DateTimeImmutable $tokenExpiresTime;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'refresh_token_expires_time', nullable: true, options: ['comment' => '刷新令牌过期时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $refreshTokenExpiresTime = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '授权范围'])]
    #[Assert\Length(max: 65535)]
    private ?string $scope = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '原始数据'])]
    #[Assert\Type(type: 'array')]
    private ?array $rawData = null;

    public function __construct()
    {
        $this->setCreateTime(new \DateTimeImmutable());
        $this->setUpdateTime(new \DateTimeImmutable());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConfig(): LarkOAuth2Config
    {
        return $this->config;
    }

    public function setConfig(LarkOAuth2Config $config): void
    {
        $this->config = $config;
    }

    public function getOpenId(): string
    {
        return $this->openId;
    }

    public function setOpenId(string $openId): void
    {
        $this->openId = $openId;
    }

    public function getUnionId(): ?string
    {
        return $this->unionId;
    }

    public function setUnionId(?string $unionId): void
    {
        $this->unionId = $unionId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getEnName(): ?string
    {
        return $this->enName;
    }

    public function setEnName(?string $enName): void
    {
        $this->enName = $enName;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): void
    {
        $this->avatarUrl = $avatarUrl;
    }

    public function getAvatarThumb(): ?string
    {
        return $this->avatarThumb;
    }

    public function setAvatarThumb(?string $avatarThumb): void
    {
        $this->avatarThumb = $avatarThumb;
    }

    public function getAvatarMiddle(): ?string
    {
        return $this->avatarMiddle;
    }

    public function setAvatarMiddle(?string $avatarMiddle): void
    {
        $this->avatarMiddle = $avatarMiddle;
    }

    public function getAvatarBig(): ?string
    {
        return $this->avatarBig;
    }

    public function setAvatarBig(?string $avatarBig): void
    {
        $this->avatarBig = $avatarBig;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): void
    {
        $this->mobile = $mobile;
    }

    public function getTenantKey(): ?string
    {
        return $this->tenantKey;
    }

    public function setTenantKey(?string $tenantKey): void
    {
        $this->tenantKey = $tenantKey;
    }

    public function getEmployeeNo(): ?string
    {
        return $this->employeeNo;
    }

    public function setEmployeeNo(?string $employeeNo): void
    {
        $this->employeeNo = $employeeNo;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    public function setExpiresIn(int $expiresIn): void
    {
        $this->expiresIn = $expiresIn;
        $this->tokenExpiresTime = (new \DateTimeImmutable())->modify("+{$expiresIn} seconds");
    }

    public function getTokenExpiresTime(): \DateTimeImmutable
    {
        return $this->tokenExpiresTime;
    }

    public function getRefreshTokenExpiresTime(): ?\DateTimeImmutable
    {
        return $this->refreshTokenExpiresTime;
    }

    public function setRefreshTokenExpiresIn(int $expiresIn): void
    {
        $this->refreshTokenExpiresTime = (new \DateTimeImmutable())->modify("+{$expiresIn} seconds");
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    /**
     * @param array<string, mixed>|null $rawData
     */
    public function setRawData(?array $rawData): void
    {
        $this->rawData = $rawData;
    }

    public function isTokenExpired(): bool
    {
        return $this->tokenExpiresTime < new \DateTimeImmutable();
    }

    public function isRefreshTokenExpired(): bool
    {
        if (null === $this->refreshTokenExpiresTime) {
            return true;
        }

        return $this->refreshTokenExpiresTime < new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf('LarkOAuth2User[%s]:%s', $this->id, $this->openId);
    }
}
