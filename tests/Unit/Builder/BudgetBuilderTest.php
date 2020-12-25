<?php

namespace App\Tests\Unit\Builder;

use App\Builder\BudgetBuilder;
use App\Entity\Budget;
use App\Entity\Category;
use App\Entity\Label;
use App\Entity\User;
use App\GraphQL\Input\Budget\BudgetCreateRequest;
use App\GraphQL\Input\Budget\BudgetUpdateRequest;
use App\Repository\BudgetRepository;
use App\Repository\CategoryRepository;
use App\Repository\LabelRepository;
use App\Services\AuthorizationService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class BudgetBuilderTest extends TestCase
{
    public function test_budget_builder_create()
    {
        $user = (new User())->setId(1);
        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $entityManagerMock = $this->createMock(EntityManager::class);
        $service = new BudgetBuilder($entityManagerMock, $authServiceMock);

        $budget = $service->create()->build();

        $this->assertEquals(1, $budget->getUserId());
    }

    public function test_budget_builder_bind_create_request()
    {
        $user = (new User())->setId(1);
        $category = (new Category())->setId(1)->setName('Test');
        $label = (new Label())->setId(1)->setName('Test');

        $categoryRepositoryMock = $this->createMock(CategoryRepository::class);
        $categoryRepositoryMock->method('find')
            ->willReturn($category);

        $labelRepositoryMock = $this->createMock(LabelRepository::class);
        $labelRepositoryMock->method('find')
            ->willReturn($label);

        $entityManagerMock = $this->createMock(EntityManager::class);
        $entityManagerMock
            ->method('getRepository')
            ->will($this->onConsecutiveCalls($categoryRepositoryMock, $labelRepositoryMock));

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $request = new BudgetCreateRequest();
        $request->name = 'Name';
        $request->value = 300;
        $request->startDate = '2020-12-12 12:12:12';
        $request->endDate = '2020-12-15 12:12:12';
        $request->categoryIds = [$category->getId()];
        $request->labelIds = [$label->getId()];

        $service = new BudgetBuilder($entityManagerMock, $authServiceMock);

        $budget = $service->create()->bind($request)->build();

        $this->assertEquals(1, $budget->getUserId());
        $this->assertEquals($request->name, $budget->getName());
        $this->assertEquals($request->value, $budget->getValue());
        $this->assertEquals($request->startDate, $budget->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals($request->endDate, $budget->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertEquals(count($request->categoryIds), $budget->getCategories()->count());
        $this->assertEquals(count($request->labelIds), $budget->getLabels()->count());
    }

    public function test_budget_builder_bind_update_request()
    {
        $user = (new User())->setId(1);
        $category = (new Category())->setId(1)->setName('Test');
        $label = (new Label())->setId(1)->setName('Test');

        $budgetRepositoryMock = $this->createMock(BudgetRepository::class);
        $budgetRepositoryMock->method('find')
            ->willReturn((new Budget())->setId(1)->setUserId($user->getId()));

        $categoryRepositoryMock = $this->createMock(CategoryRepository::class);
        $categoryRepositoryMock->method('find')
            ->willReturn($category);

        $labelRepositoryMock = $this->createMock(LabelRepository::class);
        $labelRepositoryMock->method('find')
            ->willReturn($label);

        $entityManagerMock = $this->createMock(EntityManager::class);
        $entityManagerMock
            ->method('getRepository')
            ->will($this->onConsecutiveCalls($budgetRepositoryMock, $categoryRepositoryMock, $labelRepositoryMock));

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $request = new BudgetUpdateRequest();
        $request->id = 1;
        $request->name = 'Name';
        $request->value = 300;
        $request->startDate = 1;
        $request->startDate = '2020-12-12 12:12:12';
        $request->endDate = '2020-12-15 12:12:12';
        $request->categoryIds = [$category->getId()];
        $request->labelIds = [$label->getId()];

        $service = new BudgetBuilder($entityManagerMock, $authServiceMock);

        $budget = $service->create()->bind($request)->build();

        $this->assertEquals(1, $budget->getUserId());
        $this->assertEquals($request->name, $budget->getName());
        $this->assertEquals($request->value, $budget->getValue());
        $this->assertEquals($request->startDate, $budget->getStartDate()->format('Y-m-d H:i:s'));
        $this->assertEquals($request->endDate, $budget->getEndDate()->format('Y-m-d H:i:s'));
        $this->assertEquals(count($request->categoryIds), $budget->getCategories()->count());
        $this->assertEquals(count($request->labelIds), $budget->getLabels()->count());
    }
}
