<?php

namespace App\Tests\Feature\GraphQL\Provider;

use App\DataFixtures\CategoryFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Services\AuthorizationService;
use App\Tests\Feature\BaseTestCase;

class CategoryProviderTest extends BaseTestCase
{
    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            CategoryFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');

        $this->client = $this->makeClient();
    }

    /**
     * @test
     */
    public function can_retrieve_user_categories(): void
    {
        $categories = $this->filterFixtures(function ($entity) {
            return $entity instanceof Category
                && $entity->getUserId() === $this->user->getId();
        });

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())->method('findUserCategories')->with($this->user)->willReturn($categories);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'categories',
            [],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('categories', $content);

        $expected = array_map(function (Category $category) {
            return ['id' => $category->getId()];
        }, $categories);

        $this->assertEquals($expected, $content['categories']);
    }

    /**
     * @test
     */
    public function can_not_retrieve_user_categories_if_not_logged(): void
    {
        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'categories',
            [],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_retrieve_single_category(): void
    {
        $category = $this->fixtures->getReference('category_food');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())->method('findOneById')->with($category->getId())->willReturn($category);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'category',
            ['id' => $category->getId()],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('category', $content);

        $expected = [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'color' => $category->getColor(),
        ];

        $this->assertEquals($expected, $content['category']);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_category_if_not_current_user(): void
    {
        $category = $this->fixtures->getReference('category_food_2');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())->method('findOneById')->with($category->getId())->willReturn($category);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'category',
            ['id' => $category->getId()],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_category_if_not_logged(): void
    {
        $category = $this->fixtures->getReference('category_food_2');

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'category',
            ['id' => $category->getId()],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_category_if_does_not_exist(): void
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())->method('findOneById')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'category',
            ['id' => -1],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Category not found!', $response);
    }

    /**
     * @test
     */
    public function can_create_category(): void
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())->method('save')->willReturn(null);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->exactly(3))->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'createCategory',
            [
                'input' => $inputParams
            ],
            ['name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('createCategory', $content);

        $expected = $inputParams;

        $this->assertEquals($expected, $content['createCategory']);
    }

    /**
     * @test
     */
    public function can_not_create_category_without_name(): void
    {

        $inputParams = [
            'name' => '',
            'color' => 'red'
        ];

        $this->mutation(
            'createCategory',
            [
                'input' => $inputParams
            ],
            ['name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Name should not be empty!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_category_without_color(): void
    {
        $inputParams = [
            'name' => 'test',
            'color' => ''
        ];

        $this->mutation(
            'createCategory',
            [
                'input' => $inputParams
            ],
            ['name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Color should not be empty!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_category_if_not_logged(): void
    {
        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'createCategory',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_update_category(): void
    {
        $category = $this->fixtures->getReference('category_food');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->exactly(2))->method('find')->with($category->getId())->willReturn($category);
        $categoryRepository->expects($this->once())->method('save')->willReturn($category);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $category->getId(),
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'updateCategory',
            [
                'input' => $inputParams
            ],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('updateCategory', $content);

        $expected = $inputParams;

        $this->assertEquals($expected, $content['updateCategory']);
    }

    /**
     * @test
     */
    public function can_not_update_category_if_does_not_exist(): void
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())->method('find')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $inputParams = [
            'id' => -1,
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'updateCategory',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Category not found!', $response);
    }

    /**
     * @test
     */
    public function can_not_update_category_if_not_possession_of_current_user(): void
    {
        $category = $this->fixtures->getReference('category_food_2');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->exactly(2))->method('find')->with($category->getId())->willReturn($category);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $category->getId(),
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'updateCategory',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized operation!', $response);
    }

    /**
     * @test
     */
    public function can_not_update_category_if_not_logged(): void
    {
        $category = $this->fixtures->getReference('category_food');


        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())->method('find')->with($category->getId())->willReturn($category);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $category->getId(),
            'name' => 'test',
            'color' => 'red'
        ];

        $this->mutation(
            'updateCategory',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_delete_category(): void
    {
        $category = $this->fixtures->getReference('category_food');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())->method('find')->with($category->getId())->willReturn($category);
        $categoryRepository->expects($this->once())->method('findOneById')->with($category->getId())->willReturn($category);
        $categoryRepository->expects($this->once())->method('remove')->willReturn($category);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $category->getId()
        ];

        $this->mutation(
            'deleteCategory',
            [
                'input' => $inputParams
            ],
            ['id', 'name', 'color']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('deleteCategory', $content);

        $expected = [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'color' => $category->getColor()
        ];

        $this->assertEquals($expected, $content['deleteCategory']);
    }

    /**
     * @test
     */
    public function can_not_delete_category_if_does_not_exists(): void
    {
        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())->method('find')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $inputParams = [
            'id' => -1
        ];

        $this->mutation(
            'deleteCategory',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Category not found!', $response);
    }

    /**
     * @test
     */
    public function can_not_delete_category_if_not_possession_of_current_user(): void
    {
        $category = $this->fixtures->getReference('category_food_2');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())->method('find')->with($category->getId())->willReturn($category);
        $categoryRepository->expects($this->once())->method('findOneById')->with($category->getId())->willReturn($category);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $category->getId()
        ];

        $this->mutation(
            'deleteCategory',
            [
                'input' => $inputParams
            ],
            ['id', 'name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized operation!', $response);
    }

    /**
     * @test
     */
    public function can_not_delete_category_if_not_logged(): void
    {
        $category = $this->fixtures->getReference('category_food');

        $categoryRepository = $this->createMock(CategoryRepository::class);
        $categoryRepository->expects($this->once())->method('find')->with($category->getId())->willReturn($category);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $authServiceMock = $this->getServiceMockBuilder(AuthorizationService::class)->getMock();
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $category->getId()
        ];

        $this->mutation(
            'deleteCategory',
            [
                'input' => $inputParams
            ],
            ['name', 'color'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }
}
