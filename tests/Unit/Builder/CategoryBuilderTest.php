<?php

namespace App\Tests\Unit\Builder;

use App\Builder\CategoryBuilder;
use App\Entity\Category;
use App\Entity\User;
use App\GraphQL\Input\Category\CategoryCreateRequest;
use App\GraphQL\Input\Category\CategoryUpdateRequest;
use App\Repository\CategoryRepository;
use App\Services\AuthorizationService;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class CategoryBuilderTest extends TestCase
{
    public function test_category_builder_create()
    {
        $user = (new User())->setId(1);
        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $entityManagerMock = $this->createMock(EntityManager::class);
        $service = new CategoryBuilder($entityManagerMock, $authServiceMock);

        /**@var Category $category*/
        $category = $service->create()->build();

        $this->assertEquals(1, $category->getUserId());
    }

    public function test_category_builder_bind_create_request()
    {
        $user = (new User())->setId(1);

        $entityManagerMock = $this->createMock(EntityManager::class);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $request = new CategoryCreateRequest();
        $request->name = 'Name';
        $request->color = '#FF00FF';
        $request->icon = 1;

        $service = new CategoryBuilder($entityManagerMock, $authServiceMock);

        /**@var Category $category*/
        $category = $service->create()->bind($request)->build();

        $this->assertEquals(1, $category->getUserId());
        $this->assertEquals('Name', $category->getName());
        $this->assertEquals('#FF00FF', $category->getColor());
        $this->assertEquals(1, $category->getIcon());
    }

    public function test_category_builder_bind_update_request()
    {
        $user = (new User())->setId(1);

        $categoryRepositoryMock = $this->createMock(CategoryRepository::class);
        $categoryRepositoryMock->method('find')
            ->willReturn((new Category())->setUserId($user->getId()));

        $entityManagerMock = $this->createMock(EntityManager::class);
        $entityManagerMock->method('getRepository')
            ->willReturn($categoryRepositoryMock);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->method('getCurrentUser')
            ->willReturn($user);

        $request = new CategoryUpdateRequest();
        $request->id = 1;
        $request->name = 'Name';
        $request->color = '#FF00FF';
        $request->icon = 1;

        $service = new CategoryBuilder($entityManagerMock, $authServiceMock);

        /**@var Category $category*/
        $category = $service->create()->bind($request)->build();

        $this->assertEquals(1, $category->getUserId());
        $this->assertEquals('Name', $category->getName());
        $this->assertEquals('#FF00FF', $category->getColor());
        $this->assertEquals(1, $category->getIcon());
    }
}
