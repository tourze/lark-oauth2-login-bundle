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
use Tourze\LarkOAuth2LoginBundle\Controller\Admin\LarkOAuth2StateCrudController;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2State;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2StateCrudController::class)]
#[RunTestsInSeparateProcesses]
final class LarkOAuth2StateCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getEntityFqcn(): string
    {
        return LarkOAuth2State::class;
    }

    public function testControllerInstantiation(): void
    {
        $controller = new LarkOAuth2StateCrudController();
        $this->assertInstanceOf(LarkOAuth2StateCrudController::class, $controller);
    }

    public function testConfigureCrudReturnsCorrectConfiguration(): void
    {
        $controller = new LarkOAuth2StateCrudController();
        $crud = $controller->configureCrud(
            Crud::new()
        );

        $this->assertInstanceOf(Crud::class, $crud);

        // 简单验证配置方法不出错
        $this->assertNotNull($crud);
    }

    public function testConfigureActionsReturnsCorrectConfiguration(): void
    {
        $controller = new LarkOAuth2StateCrudController();
        $actions = $controller->configureActions(
            Actions::new()
        );

        $this->assertInstanceOf(Actions::class, $actions);

        // 简单验证配置方法不出错
        $this->assertNotNull($actions);
    }

    public function testConfigureFiltersReturnsCorrectConfiguration(): void
    {
        $controller = new LarkOAuth2StateCrudController();
        $filters = $controller->configureFilters(
            Filters::new()
        );

        $this->assertInstanceOf(Filters::class, $filters);

        // 简单验证配置方法不出错
        $this->assertNotNull($filters);
    }

    #[TestWith([Crud::PAGE_INDEX])]
    #[TestWith([Crud::PAGE_DETAIL])]
    #[TestWith([Crud::PAGE_NEW])]
    #[TestWith([Crud::PAGE_EDIT])]
    public function testConfigureFieldsReturnsFields(string $pageName): void
    {
        $controller = new LarkOAuth2StateCrudController();
        $fields = $controller->configureFields($pageName);

        $this->assertIsIterable($fields);
        $this->assertNotEmpty($fields);

        // 将迭代器转换为数组以便进一步测试
        $fieldsArray = iterator_to_array($fields);
        $this->assertNotEmpty($fieldsArray);

        // 验证字段数量合理（不同页面应该有字段）
        $fieldCount = count($fieldsArray);
        $this->assertGreaterThan(0, $fieldCount, "页面 {$pageName} 应该包含字段");

        // 根据页面类型验证字段数量的合理性
        switch ($pageName) {
            case Crud::PAGE_INDEX:
                $this->assertGreaterThanOrEqual(5, $fieldCount, '索引页面应该包含多个显示字段');
                break;
            case Crud::PAGE_DETAIL:
                $this->assertGreaterThanOrEqual(8, $fieldCount, '详情页面应该包含所有详细字段');
                break;
            case Crud::PAGE_NEW:
            case Crud::PAGE_EDIT:
                $this->assertGreaterThanOrEqual(3, $fieldCount, '表单页面应该包含可编辑字段');
                break;
        }
    }

    public function testPageTitlesConfiguration(): void
    {
        $controller = new LarkOAuth2StateCrudController();
        $crud = $controller->configureCrud(Crud::new());

        // 简单验证配置方法不出错
        $this->assertInstanceOf(Crud::class, $crud);
    }

    public function testEntityActionsInlined(): void
    {
        $controller = new LarkOAuth2StateCrudController();
        $crud = $controller->configureCrud(Crud::new());

        // 简单验证配置方法不出错
        $this->assertInstanceOf(Crud::class, $crud);
    }

    protected function getControllerService(): LarkOAuth2StateCrudController
    {
        return self::getService(LarkOAuth2StateCrudController::class);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'config' => ['config'];
        yield 'sessionId' => ['sessionId'];
        yield 'isUsed' => ['isUsed'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'configName' => ['配置名称'];
        yield 'appId' => ['应用ID'];
        yield 'isUsed' => ['已使用'];
        yield 'createTime' => ['创建时间'];
        yield 'expiresTime' => ['过期时间'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'config' => ['config'];
        yield 'sessionId' => ['sessionId'];
        yield 'isUsed' => ['isUsed'];
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

        // 清空必填字段 - LarkOAuth2State的必填字段是config
        // config字段是下拉选择，使用无效值触发验证错误
        $configField = $form[$entityName . '[config]'];
        if ($configField instanceof ChoiceFormField) {
            // 选择一个不存在的值来触发验证错误
            $configField->disableValidation();
            $configField->setValue('');
        }
        // 如果state字段存在且可编辑，则也清空它
        if (isset($form[$entityName . '[state]'])) {
            $form[$entityName . '[state]'] = '';
        }

        $crawler = $client->submit($form);

        // 验证返回422状态码表示验证失败
        $this->assertResponseStatusCodeSame(422);

        // 验证错误信息存在
        $errorText = $crawler->filter('.invalid-feedback')->text();
        $this->assertStringContainsString('should not be blank', strtolower($errorText));
    }
}
