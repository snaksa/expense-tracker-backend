<?php

namespace App\Tests\Feature\GraphQL\Provider;

use App\DataFixtures\BudgetFixtures;
use App\DataFixtures\CategoryFixtures;
use App\DataFixtures\LabelFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Budget;
use App\Entity\User;
use App\Repository\BudgetRepository;
use App\Repository\CategoryRepository;
use App\Repository\LabelRepository;
use App\Services\AuthorizationService;
use App\Tests\Feature\BaseTestCase;
use App\Tests\Types\IntegerArrayType;

class BudgetProviderTest extends BaseTestCase
{
    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->makeClient();

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            CategoryFixtures::class,
            LabelFixtures::class,
            BudgetFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
    }

    /**
     * @test
     */
    public function can_retrieve_user_budgets(): void
    {
        $budgets = $this->filterFixtures(function ($entity) {
            return $entity instanceof Budget
                && $entity->getUserId() === $this->user->getId();
        });

        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->once())->method('findUserBudgets')->with($this->user)->willReturn($budgets);
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'budgets',
            [],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('budgets', $content);

        $expected = array_map(function (Budget $budget) {
            return ['id' => $budget->getId()];
        }, $budgets);

        $this->assertEquals($expected, $content['budgets']);
    }

    /**
     * @test
     */
    public function can_not_retrieve_user_budgets_if_not_logged(): void
    {
        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'budgets',
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
    public function can_retrieve_single_budget(): void
    {
        $budget = $this->fixtures->getReference('budget_essentials');

        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->once())->method('findOneById')->with($budget->getId())->willReturn($budget);
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'budget',
            ['id' => $budget->getId()],
            ['id', 'name']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('budget', $content);

        $expected = [
            'id' => $budget->getId(),
            'name' => $budget->getName(),
        ];

        $this->assertEquals($expected, $content['budget']);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_budget_if_not_current_user(): void
    {
        $budget = $this->fixtures->getReference('budget_essentials_2');

        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->once())->method('findOneById')->with($budget->getId())->willReturn($budget);
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'budget',
            ['id' => $budget->getId()],
            ['id', 'name']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_budget_if_not_logged(): void
    {
        $budget = $this->fixtures->getReference('budget_essentials');

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'budget',
            ['id' => $budget->getId()],
            ['id', 'name']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_not_retrieve_single_budget_if_does_not_exist(): void
    {
        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->once())->method('findOneById')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'budget',
            ['id' => -1],
            ['id']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Budget not found!', $response);
    }

    /**
     * @test
     */
    public function can_create_budget(): void
    {
        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->once())->method('save');
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->exactly(3))->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'name' => 'test',
            'value' => 300,
            'startDate' => '2020-12-12 12:12:12',
            'endDate' => '2020-12-15 12:12:12',
        ];

        $this->mutation(
            'createBudget',
            [
                'input' => $inputParams
            ],
            ['name', 'value', 'startDate', 'endDate']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('createBudget', $content);

        $expected = $inputParams;

        $this->assertEquals($expected, $content['createBudget']);
    }

    /**
     * @test
     */
    public function can_not_create_budget_without_name(): void
    {

        $inputParams = [
            'name' => '',
            'value' => 300,
            'startDate' => '2020-12-12 12:12:12',
            'endDate' => '2020-12-12 12:12:12'
        ];

        $this->mutation(
            'createBudget',
            [
                'input' => $inputParams
            ],
            ['name']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Name should not be empty!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_budget_without_start_date(): void
    {

        $inputParams = [
            'name' => 'test',
            'value' => 300,
            'startDate' => '',
            'endDate' => '2020-12-12 12:12:12'
        ];

        $this->mutation(
            'createBudget',
            [
                'input' => $inputParams
            ],
            ['name']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('StartDate should not be empty!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_budget_without_end_date(): void
    {

        $inputParams = [
            'name' => 'test',
            'value' => 300,
            'startDate' => '2020-12-12 12:12:12',
            'endDate' => ''
        ];

        $this->mutation(
            'createBudget',
            [
                'input' => $inputParams
            ],
            ['name']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('EndDate should not be empty!', $response);
    }

    /**
     * @test
     */
    public function can_not_create_budget_if_not_logged(): void
    {
        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'name' => 'test',
            'value' => 300,
            'startDate' => '2020-12-12 12:12:12',
            'endDate' => '2020-12-12 12:12:12',
        ];

        $this->mutation(
            'createBudget',
            [
                'input' => $inputParams
            ],
            ['name'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_update_budget(): void
    {
        $budget = $this->fixtures->getReference('budget_essentials');
        $categoryFood = $this->fixtures->getReference('category_food');
        $categoryClothes = $this->fixtures->getReference('category_clothes');
        $labelEssentials = $this->fixtures->getReference('label_essentials');
        $labelSpoiling = $this->fixtures->getReference('label_spoiling');

        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->exactly(2))->method('find')->with($budget->getId())->willReturn($budget);
        $budgetRepository->expects($this->once())->method('save');
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $categoryRepository = $this->createMock(BudgetRepository::class);
        $categoryRepository
            ->method('find')
            ->withConsecutive([$categoryClothes->getId()], [$categoryFood->getId()])
            ->willReturnOnConsecutiveCalls($categoryClothes, $categoryFood);
        $this->client->getContainer()->set(CategoryRepository::class, $categoryRepository);

        $labelRepository = $this->createMock(BudgetRepository::class);
        $labelRepository
            ->method('find')
            ->withConsecutive([$labelSpoiling->getId()], [$labelEssentials->getId()])
            ->willReturnOnConsecutiveCalls($labelSpoiling, $labelEssentials);
        $this->client->getContainer()->set(LabelRepository::class, $labelRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $budget->getId(),
            'name' => 'test2',
            'value' => 200,
            'startDate' => '2020-11-11 11:11:11',
            'endDate' => '2020-11-15 11:11:11',
            'categoryIds' => new IntegerArrayType([$categoryClothes->getId()]),
            'labelIds' => new IntegerArrayType([$labelSpoiling->getId()]),
        ];

        $this->mutation(
            'updateBudget',
            [
                'input' => $inputParams
            ],
            [
                'id',
                'name',
                'value',
                'startDate',
                'endDate',
                'categories' => ['id'],
                'labels' => ['id'],
            ]
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('updateBudget', $content);

        $expected = $inputParams;
        unset($expected['categoryIds']);
        unset($expected['labelIds']);
        $expected['categories'] = [['id' => $categoryClothes->getId()]];
        $expected['labels'] = [['id' => $labelSpoiling->getId()]];

        $this->assertEquals($expected, $content['updateBudget']);
    }

    /**
     * @test
     */
    public function can_not_update_budget_if_does_not_exist(): void
    {
        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->once())->method('find')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $inputParams = [
            'id' => -1,
            'name' => 'test',
        ];

        $this->mutation(
            'updateBudget',
            [
                'input' => $inputParams
            ],
            ['name'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Budget not found!', $response);
    }

    /**
     * @test
     */
    public function can_not_update_budget_if_not_possession_of_current_user(): void
    {
        $budget = $this->fixtures->getReference('budget_essentials_2');

        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->exactly(2))->method('find')->with($budget->getId())->willReturn($budget);
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $budget->getId(),
            'name' => 'test'
        ];

        $this->mutation(
            'updateBudget',
            [
                'input' => $inputParams
            ],
            ['name'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized operation!', $response);
    }

    /**
     * @test
     */
    public function can_not_update_bdget_if_not_logged(): void
    {
        $budget = $this->fixtures->getReference('budget_essentials');

        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->once())->method('find')->with($budget->getId())->willReturn($budget);
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $budget->getId(),
            'name' => 'test',
        ];

        $this->mutation(
            'updateBudget',
            [
                'input' => $inputParams
            ],
            ['name'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }

    /**
     * @test
     */
    public function can_delete_budget(): void
    {
        $budget = $this->fixtures->getReference('budget_essentials');

        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->once())->method('find')->with($budget->getId())->willReturn($budget);
        $budgetRepository->expects($this->once())->method('findOneById')->with($budget->getId())->willReturn($budget);
        $budgetRepository->expects($this->once())->method('remove');
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $budget->getId()
        ];

        $this->mutation(
            'deleteBudget',
            [
                'input' => $inputParams
            ],
            ['id', 'name']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('deleteBudget', $content);

        $expected = [
            'id' => $budget->getId(),
            'name' => $budget->getName()
        ];

        $this->assertEquals($expected, $content['deleteBudget']);
    }

    /**
     * @test
     */
    public function can_not_delete_budget_if_does_not_exists(): void
    {
        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->once())->method('find')->with(-1)->willReturn(null);
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $inputParams = [
            'id' => -1
        ];

        $this->mutation(
            'deleteBudget',
            [
                'input' => $inputParams
            ],
            ['name'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertInputHasError('Budget not found!', $response);
    }

    /**
     * @test
     */
    public function can_not_delete_budget_if_not_possession_of_current_user(): void
    {
        $budget = $this->fixtures->getReference('budget_essentials_2');

        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->once())->method('find')->with($budget->getId())->willReturn($budget);
        $budgetRepository->expects($this->once())->method('findOneById')->with($budget->getId())->willReturn($budget);
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $budget->getId()
        ];

        $this->mutation(
            'deleteBudget',
            [
                'input' => $inputParams
            ],
            ['id', 'name'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized operation!', $response);
    }

    /**
     * @test
     */
    public function can_not_delete_budget_if_not_logged(): void
    {
        $budget = $this->fixtures->getReference('budget_essentials');

        $budgetRepository = $this->createMock(BudgetRepository::class);
        $budgetRepository->expects($this->once())->method('find')->with($budget->getId())->willReturn($budget);
        $this->client->getContainer()->set(BudgetRepository::class, $budgetRepository);

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $inputParams = [
            'id' => $budget->getId()
        ];

        $this->mutation(
            'deleteBudget',
            [
                'input' => $inputParams
            ],
            ['name'],
            []
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $this->assertHasError('Unauthorized access!', $response);
    }
}
