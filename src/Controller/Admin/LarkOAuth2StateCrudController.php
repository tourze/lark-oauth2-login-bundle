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
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2State;

#[AdminCrud(routePath: '/lark-oauth2/state', routeName: 'lark_oauth2_state')]
final class LarkOAuth2StateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LarkOAuth2State::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('飞书OAuth2状态')
            ->setEntityLabelInPlural('飞书OAuth2状态')
            ->setPageTitle('index', '飞书OAuth2状态管理')
            ->setPageTitle('detail', '查看飞书OAuth2状态')
            ->setPageTitle('edit', '编辑飞书OAuth2状态')
            ->setPageTitle('new', '新建飞书OAuth2状态')
            ->setSearchFields(['state', 'sessionId', 'config.appId', 'config.name'])
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setPaginatorPageSize(30)
            ->showEntityActionsInlined()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('config', '配置')
                ->setFormTypeOption('class', LarkOAuth2Config::class)
                ->setFormTypeOption('choice_label', 'name'))
            ->add(BooleanFilter::new('isUsed', '是否已使用'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('expiresTime', '过期时间'))
            ->add(DateTimeFilter::new('usedTime', '使用时间'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')
                ->onlyOnIndex(),

            TextField::new('state', '状态值')
                ->setHelp('32位MD5哈希值，用于OAuth2授权状态验证')
                ->onlyOnDetail()
                ->setFormTypeOption('disabled', true),

            AssociationField::new('config', '配置')
                ->setHelp('关联的飞书OAuth2应用配置')
                ->setRequired(true)
                ->setFormTypeOption('choice_label', function (LarkOAuth2Config $config): string {
                    return sprintf('%s (%s)', $config->getName() ?? '未命名', $config->getAppId());
                })
                ->hideOnIndex()
                ->setFormTypeOption('disabled', Crud::PAGE_EDIT === $pageName),

            TextField::new('config.name', '配置名称')
                ->onlyOnIndex()
                ->setLabel('配置名称'),

            TextField::new('config.appId', '应用ID')
                ->onlyOnIndex()
                ->setLabel('应用ID'),

            TextField::new('sessionId', '会话ID')
                ->setHelp('关联的用户会话ID')
                ->hideOnIndex(),

            BooleanField::new('isUsed', '已使用')
                ->setHelp('标记该状态值是否已被使用')
                ->renderAsSwitch(false),

            DateTimeField::new('createTime', '创建时间')
                ->setFormat('yyyy-MM-dd HH:mm:ss')
                ->setHelp('状态值创建的时间')
                ->hideOnForm(),

            DateTimeField::new('expiresTime', '过期时间')
                ->setFormat('yyyy-MM-dd HH:mm:ss')
                ->setHelp('状态值过期时间（创建后5分钟）')
                ->hideOnForm(),

            DateTimeField::new('usedTime', '使用时间')
                ->setFormat('yyyy-MM-dd HH:mm:ss')
                ->setHelp('状态值被使用的时间')
                ->hideOnIndex()
                ->hideOnForm(),

            BooleanField::new('valid', '有效状态')
                ->setHelp('状态值是否仍然有效（未使用且未过期）')
                ->onlyOnDetail()
                ->renderAsSwitch(false)
                ->setFormTypeOption('disabled', true),

            BooleanField::new('expired', '已过期')
                ->setHelp('状态值是否已过期')
                ->onlyOnDetail()
                ->renderAsSwitch(false)
                ->setFormTypeOption('disabled', true),
        ];
    }
}
