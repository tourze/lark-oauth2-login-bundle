<?php

namespace Tourze\LarkOAuth2LoginBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\LarkOAuth2LoginBundle\Repository\LarkOAuth2ConfigRepository;

#[ORM\Entity(repositoryClass: LarkOAuth2ConfigRepository::class)]
#[ORM\Table(name: 'lark_oauth2_config', options: ['comment' => '飞书OAuth2应用配置表'])]
#[ORM\UniqueConstraint(name: 'UNIQ_lark_oauth2_config_app_id', columns: ['app_id'])]
class LarkOAuth2Config implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '飞书应用ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $appId;

    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '飞书应用密钥'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $appSecret;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '配置名称'])]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '授权范围'])]
    #[Assert\Length(max: 65535)]
    private ?string $scope = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注信息'])]
    #[Assert\Length(max: 65535)]
    private ?string $remark = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否有效'])]
    #[Assert\Type(type: 'bool')]
    private bool $isValid = true;

    public function __construct()
    {
        $this->setCreateTime(new \DateTimeImmutable());
        $this->setUpdateTime(new \DateTimeImmutable());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    public function setAppSecret(string $appSecret): void
    {
        $this->appSecret = $appSecret;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): void
    {
        $this->scope = $scope;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function setValid(bool $valid): void
    {
        $this->isValid = $valid;
    }

    public function __toString(): string
    {
        return sprintf('LarkOAuth2Config[%s]:%s', $this->id, $this->appId);
    }
}
