<?php

declare(strict_types=1);

namespace Tourze\LarkOAuth2LoginBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User;

#[AdminCrud(routePath: '/lark-oauth2/user', routeName: 'lark_oauth2_user')]
final class LarkOAuth2UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LarkOAuth2User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('飞书OAuth2用户')
            ->setEntityLabelInPlural('飞书OAuth2用户管理')
            ->setPageTitle('index', '飞书OAuth2用户列表')
            ->setPageTitle('detail', '飞书OAuth2用户详情')
            ->setPageTitle('new', '新建飞书OAuth2用户')
            ->setPageTitle('edit', '编辑飞书OAuth2用户')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setHelp('index', '管理飞书OAuth2用户信息，包括用户基本信息、令牌状态和授权数据')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('config', '应用配置')
                ->setFormTypeOption('class', LarkOAuth2Config::class)
                ->setFormTypeOption('choice_label', 'name')
            )
            ->add(TextFilter::new('openId', 'OpenID'))
            ->add(TextFilter::new('unionId', 'UnionID'))
            ->add(TextFilter::new('userId', '用户ID'))
            ->add(TextFilter::new('name', '用户姓名'))
            ->add(TextFilter::new('email', '邮箱地址'))
            ->add(TextFilter::new('mobile', '手机号码'))
            ->add(TextFilter::new('employeeNo', '员工编号'))
            ->add(BooleanFilter::new('tokenExpired', '令牌是否过期')->setFormTypeOption('mapped', false))
            ->add(DateTimeFilter::new('tokenExpiresTime', '令牌过期时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = $this->buildAllFields($pageName);

        return match ($pageName) {
            Crud::PAGE_INDEX => $this->getIndexFields($fields),
            Crud::PAGE_DETAIL => $fields,
            default => $this->getFormFields($fields),
        };
    }

    /**
     * @return list<\EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface>
     */
    private function buildAllFields(string $pageName): array
    {
        return [
            IdField::new('id', 'ID')
                ->onlyOnIndex(),

            AssociationField::new('config', '应用配置')
                ->setFormTypeOptions([
                    'class' => LarkOAuth2Config::class,
                    'choice_label' => function (LarkOAuth2Config $config) {
                        return sprintf('%s (%s)', $config->getName() ?? $config->getAppId(), $config->getAppId());
                    },
                ])
                ->setHelp('选择对应的飞书应用配置')
                ->setRequired(true),

            TextField::new('openId', 'OpenID')
                ->setHelp('用户的唯一开放标识符')
                ->setRequired(true)
                ->setFormTypeOption('attr', ['readonly' => Crud::PAGE_EDIT === $pageName]),

            TextField::new('unionId', 'UnionID')
                ->setHelp('用户的联合标识符，可用于跨应用识别用户')
                ->hideOnIndex(),

            TextField::new('userId', '用户ID')
                ->setHelp('飞书内部用户ID')
                ->hideOnIndex(),

            TextField::new('name', '用户姓名')
                ->setHelp('用户的中文姓名'),

            TextField::new('enName', '英文姓名')
                ->setHelp('用户的英文姓名')
                ->hideOnIndex(),

            EmailField::new('email', '邮箱地址')
                ->setHelp('用户的邮箱地址'),

            TelephoneField::new('mobile', '手机号码')
                ->setHelp('用户的手机号码')
                ->hideOnIndex(),

            ImageField::new('avatarUrl', '头像')
                ->setBasePath('')
                ->setUploadDir('')
                ->onlyOnDetail()
                ->setHelp('用户头像图片'),

            TextField::new('avatarThumb', '头像缩略图')
                ->hideOnIndex()
                ->onlyOnDetail()
                ->setHelp('头像缩略图URL'),

            TextField::new('avatarMiddle', '头像中等尺寸')
                ->hideOnIndex()
                ->onlyOnDetail()
                ->setHelp('头像中等尺寸URL'),

            TextField::new('avatarBig', '头像大尺寸')
                ->hideOnIndex()
                ->onlyOnDetail()
                ->setHelp('头像大尺寸URL'),

            TextField::new('tenantKey', '租户密钥')
                ->setHelp('飞书租户密钥')
                ->hideOnIndex(),

            TextField::new('employeeNo', '员工编号')
                ->setHelp('企业内部员工编号')
                ->hideOnIndex(),

            $this->buildAccessTokenField(),

            $this->buildRefreshTokenField(),

            IntegerField::new('expiresIn', '令牌有效期')
                ->setHelp('令牌有效期（秒）')
                ->onlyOnDetail(),

            $this->buildTokenExpiresTimeField(),

            $this->buildRefreshTokenExpiresTimeField(),

            TextField::new('scope', '授权范围')
                ->setHelp('OAuth2授权范围')
                ->onlyOnDetail(),

            $this->buildTokenStatusField(),

            $this->buildRawDataField(),

            DateTimeField::new('createTime', '创建时间')
                ->setFormat('yyyy-MM-dd HH:mm:ss')
                ->setHelp('记录创建时间')
                ->hideOnForm(),

            DateTimeField::new('updateTime', '更新时间')
                ->setFormat('yyyy-MM-dd HH:mm:ss')
                ->setHelp('记录最后更新时间')
                ->hideOnForm()
                ->hideOnIndex(),
        ];
    }

    private function buildAccessTokenField(): TextareaField
    {
        return TextareaField::new('accessToken', '访问令牌')
            ->setHelp('OAuth2访问令牌（敏感信息）')
            ->onlyOnDetail()
            ->setFormTypeOption('attr', [
                'readonly' => true,
                'style' => 'font-family: monospace; font-size: 12px;',
            ])
            ->formatValue(function ($value) {
                if ('' === $value || null === $value || !is_string($value)) {
                    return '';
                }

                return substr($value, 0, 20) . '***' . substr($value, -10);
            });
    }

    private function buildRefreshTokenField(): TextareaField
    {
        return TextareaField::new('refreshToken', '刷新令牌')
            ->setHelp('OAuth2刷新令牌（敏感信息）')
            ->onlyOnDetail()
            ->setFormTypeOption('attr', [
                'readonly' => true,
                'style' => 'font-family: monospace; font-size: 12px;',
            ])
            ->formatValue(function ($value) {
                if ('' === $value || null === $value || !is_string($value)) {
                    return '无';
                }

                return substr($value, 0, 20) . '***' . substr($value, -10);
            });
    }

    private function buildTokenExpiresTimeField(): DateTimeField
    {
        return DateTimeField::new('tokenExpiresTime', '令牌过期时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('访问令牌的过期时间')
            ->formatValue(function ($value, $entity) {
                if (!$value instanceof \DateTimeInterface || !$entity instanceof LarkOAuth2User) {
                    return '';
                }

                $isExpired = $entity->isTokenExpired();
                $formatted = $value->format('Y-m-d H:i:s');

                return $isExpired ? "⚠️ {$formatted} (已过期)" : "✅ {$formatted}";
            });
    }

    private function buildRefreshTokenExpiresTimeField(): DateTimeField
    {
        return DateTimeField::new('refreshTokenExpiresTime', '刷新令牌过期时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->setHelp('刷新令牌的过期时间')
            ->onlyOnDetail()
            ->formatValue(function ($value, $entity) {
                if (!$value instanceof \DateTimeInterface || !$entity instanceof LarkOAuth2User) {
                    return '无';
                }

                $isExpired = $entity->isRefreshTokenExpired();
                $formatted = $value->format('Y-m-d H:i:s');

                return $isExpired ? "⚠️ {$formatted} (已过期)" : "✅ {$formatted}";
            });
    }

    private function buildTokenStatusField(): BooleanField
    {
        return BooleanField::new('tokenExpired', '令牌状态')
            ->onlyOnIndex()
            ->renderAsSwitch(false)
            ->formatValue(function ($value, $entity) {
                if (!$entity instanceof LarkOAuth2User) {
                    return false;
                }

                return !$entity->isTokenExpired();
            })
            ->setHelp('令牌是否有效');
    }

    private function buildRawDataField(): TextareaField
    {
        return TextareaField::new('rawData', '原始数据')
            ->setHelp('从飞书API获取的原始用户数据')
            ->onlyOnDetail()
            ->setFormTypeOption('attr', [
                'readonly' => true,
                'rows' => 10,
                'style' => 'font-family: monospace; font-size: 12px;',
            ])
            ->formatValue(function ($value) {
                if (null === $value || [] === $value) {
                    return '{}';
                }

                return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            });
    }

    /**
     * @param list<\EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface> $fields
     * @return list<\EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface>
     */
    private function getIndexFields(array $fields): array
    {
        return [
            $fields[0],  // id
            $fields[1],  // config
            $fields[2],  // openId
            $fields[5],  // name
            $fields[7],  // email
            $fields[19], // tokenExpired
            $fields[16], // tokenExpiresTime
            $fields[21], // createTime
        ];
    }

    /**
     * @param list<\EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface> $fields
     * @return list<\EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface>
     */
    private function getFormFields(array $fields): array
    {
        return [
            $fields[1],  // config
            $fields[2],  // openId
            $fields[3],  // unionId
            $fields[4],  // userId
            $fields[5],  // name
            $fields[6],  // enName
            $fields[7],  // email
            $fields[8],  // mobile
            $fields[13], // tenantKey
            $fields[14], // employeeNo
        ];
    }
}
