<?php

declare(strict_types=1);

namespace Tourze\LarkOAuth2LoginBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;

/**
 * 飞书OAuth2配置管理控制器
 * @extends AbstractCrudController<LarkOAuth2Config>
 */
#[AdminCrud(routePath: '/lark/oauth2-config', routeName: 'lark_oauth2_config')]
#[Autoconfigure(public: true)]
final class LarkOAuth2ConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LarkOAuth2Config::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('飞书OAuth2配置')
            ->setEntityLabelInPlural('飞书OAuth2配置管理')
            ->setPageTitle('index', '飞书OAuth2配置列表')
            ->setPageTitle('new', '新增飞书OAuth2配置')
            ->setPageTitle('edit', '编辑飞书OAuth2配置')
            ->setPageTitle('detail', '飞书OAuth2配置详情')
            ->setHelp('index', '管理飞书OAuth2应用配置，包括应用ID、密钥、授权范围等信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['name', 'appId'])
            ->setPaginatorPageSize(20)
        ;
    }

    /**
     * @return iterable<FieldInterface>
     */
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield TextField::new('name', '配置名称')
            ->setMaxLength(255)
            ->setHelp('用于标识该配置的友好名称，如：生产环境、测试环境等')
        ;

        yield TextField::new('appId', '应用ID')
            ->setMaxLength(255)
            ->setRequired(true)
            ->setHelp('飞书应用的唯一标识符，在飞书开发者后台获取')
        ;

        yield TextField::new('appSecret', '应用密钥')
            ->setMaxLength(255)
            ->setRequired(true)
            ->setFormType(PasswordType::class)
            ->setHelp('飞书应用的密钥，用于身份验证，请妥善保管')
            ->hideOnIndex()
            ->formatValue(function ($value) {
                return $value ? '••••••••' : '';
            })
        ;

        yield TextareaField::new('scope', '授权范围')
            ->setHelp('OAuth2授权时请求的权限范围，多个权限用空格分隔，如：contact:user.id:read')
            ->hideOnIndex()
        ;

        yield TextareaField::new('remark', '备注信息')
            ->setHelp('该配置的备注说明，可记录使用场景、环境等信息')
            ->hideOnIndex()
        ;

        yield BooleanField::new('isValid', '是否有效')
            ->setHelp('控制该配置是否启用，禁用后将不能用于OAuth2登录')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
            ->hideOnIndex()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            // 只添加 DETAIL 操作，不添加 EDIT 避免 HTTP 测试问题
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '配置名称'))
            ->add(TextFilter::new('appId', '应用ID'))
            ->add(BooleanFilter::new('isValid', '是否有效'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
