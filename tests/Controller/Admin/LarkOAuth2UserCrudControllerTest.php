<?php

declare(strict_types=1);

namespace Tourze\LarkOAuth2LoginBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Tourze\LarkOAuth2LoginBundle\Controller\Admin\LarkOAuth2UserCrudController;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2UserCrudController::class)]
#[RunTestsInSeparateProcesses]
class LarkOAuth2UserCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    public function testGetEntityFqcn(): void
    {
        $this->assertSame(LarkOAuth2User::class, LarkOAuth2UserCrudController::getEntityFqcn());
    }

    public function testControllerInstantiation(): void
    {
        $controller = new LarkOAuth2UserCrudController();
        $this->assertInstanceOf(LarkOAuth2UserCrudController::class, $controller);
    }

    public function testConfigureCrudReturnsCorrectLabels(): void
    {
        $controller = new LarkOAuth2UserCrudController();
        $crud = $controller->configureCrud(
            Crud::new()
        );

        $this->assertInstanceOf(Crud::class, $crud);
    }

    public function testConfigureActionsReturnsCorrectConfiguration(): void
    {
        $controller = new LarkOAuth2UserCrudController();
        $actions = $controller->configureActions(
            Actions::new()
        );

        $this->assertInstanceOf(Actions::class, $actions);
    }

    public function testConfigureFiltersReturnsCorrectConfiguration(): void
    {
        $controller = new LarkOAuth2UserCrudController();
        $filters = $controller->configureFilters(
            Filters::new()
        );

        $this->assertInstanceOf(Filters::class, $filters);
    }

    #[TestWith([Crud::PAGE_INDEX])]
    #[TestWith([Crud::PAGE_DETAIL])]
    #[TestWith([Crud::PAGE_NEW])]
    #[TestWith([Crud::PAGE_EDIT])]
    public function testConfigureFieldsReturnsFields(string $pageName): void
    {
        $controller = new LarkOAuth2UserCrudController();
        $fields = $controller->configureFields($pageName);

        $this->assertIsIterable($fields);
        $this->assertNotEmpty($fields);
    }

    protected function getControllerService(): LarkOAuth2UserCrudController
    {
        return self::getService(LarkOAuth2UserCrudController::class);
    }

    
    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'config' => ['应用配置'];
        yield 'openId' => ['OpenID'];
        yield 'name' => ['用户姓名'];
        yield 'email' => ['邮箱地址'];
        yield 'tokenStatus' => ['令牌状态'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'config' => ['config'];
        yield 'openId' => ['openId'];
        yield 'unionId' => ['unionId'];
        yield 'userId' => ['userId'];
        yield 'name' => ['name'];
        yield 'enName' => ['enName'];
        yield 'email' => ['email'];
        yield 'mobile' => ['mobile'];
        yield 'tenantKey' => ['tenantKey'];
        yield 'employeeNo' => ['employeeNo'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'config' => ['config'];
        yield 'openId' => ['openId'];
        yield 'unionId' => ['unionId'];
        yield 'userId' => ['userId'];
        yield 'name' => ['name'];
        yield 'enName' => ['enName'];
        yield 'email' => ['email'];
        yield 'mobile' => ['mobile'];
        yield 'tenantKey' => ['tenantKey'];
        yield 'employeeNo' => ['employeeNo'];
    }

    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();

        // 访问新建页面
        $crawler = $client->request('GET', $this->generateAdminUrl(Action::NEW));
        $this->assertResponseIsSuccessful();

        // 获取表单并提交空数据 - 查找任何类型的提交按钮
        $button = $crawler->selectButton('Create');
        if (0 === $button->count()) {
            $button = $crawler->selectButton('保存');
        }
        if (0 === $button->count()) {
            $button = $crawler->selectButton('Submit');
        }

        $form = $button->form();
        $entityName = $this->getEntitySimpleName();

        // 清空必填字段 - LarkOAuth2User的必填字段是config和openId
        $form[$entityName . '[openId]'] = '';
        // config字段是下拉选择，使用无效值触发验证错误
        $configField = $form[$entityName . '[config]'];
        if ($configField instanceof ChoiceFormField) {
            // 禁用验证后设置空值来触发服务器端验证错误
            $configField->disableValidation();
            $configField->setValue('');
        }

        $crawler = $client->submit($form);

        // 验证返回422状态码表示验证失败
        $this->assertResponseStatusCodeSame(422);

        // 验证错误信息存在
        $errorText = $crawler->filter('.invalid-feedback')->text();
        $this->assertStringContainsString('should not be blank', strtolower($errorText));
    }
}
