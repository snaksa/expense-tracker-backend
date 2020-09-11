<?php

namespace App\Tests\Feature\GraphQL\Provider;

use App\DataFixtures\CategoryFixtures;
use App\DataFixtures\TransactionFixtures;
use App\DataFixtures\UserFixtures;
use App\DataFixtures\WalletFixtures;
use App\Entity\Category;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wallet;
use App\GraphQL\Types\TransactionType;
use App\Repository\TransactionRepository;
use App\Repository\WalletRepository;
use App\Services\AuthorizationService;
use App\Tests\Feature\BaseTestCase;
use App\Tests\Types\IntegerArrayType;
use App\Traits\DateUtils;
use KunicMarko\GraphQLTest\Type\EnumType;

class ReportsProviderTest extends BaseTestCase
{
    use DateUtils;

    /**
     * @var User
     */
    private $user;

    /**
     * @var User
     */
    private $secondUser;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var Wallet
     */
    private $wallet;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->makeClient();

        $this->fixtures = $this->loadFixtures([
            UserFixtures::class,
            WalletFixtures::class,
            CategoryFixtures::class,
            TransactionFixtures::class
        ])->getReferenceRepository();

        $this->user = $this->fixtures->getReference('user_demo');
        $this->secondUser = $this->fixtures->getReference('user_demo2');
        $this->category = $this->fixtures->getReference('category_food');
        $this->wallet = $this->fixtures->getReference('user_demo_wallet_cash');
    }

    /**
     * @test
     */
    public function can_retrieve_transactions_spending_flow_by_start_date(): void
    {
        $startDate = $this->getCurrentDateTime()->modify('- 2 day')->setTime(0, 0, 0, 0);
        $transactions = $this->filterFixtures(function ($entity) use ($startDate) {
            return $entity instanceof Transaction
                && $entity->getWalletId() === $this->wallet->getId()
                && $entity->getDate() >= $startDate
                && $entity->getType() === TransactionType::EXPENSE;
        });

        $values = [];
        foreach ($transactions as $transaction) {
            $key = $transaction->getDate()->format('Y-m-d');
            if (!isset($values[$key])) {
                $values[$key] = 0;
            }

            $values[$key] += $transaction->getValue();
        }

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'transactionSpendingFlow',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()]),
                    'startDate' => $startDate->format('Y-m-d H:i:s')
                ]
            ],
            ['data']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('transactionSpendingFlow', $content);
        $this->assertArrayHasKey('data', $content['transactionSpendingFlow']);

        $expected = [];
        $endDate = $this->getCurrentDateTime()->setTime(23, 59, 59);
        while ($startDate && $startDate <= $endDate) {
            $dateKey = $startDate->format('Y-m-d');
            if (!isset($values[$dateKey])) {
                $values[$dateKey] = 0;
            }

            $expected[] = [$dateKey, $values[$dateKey]];

            $startDate->modify('+ 1 day');
        }

        $this->assertEquals($expected, $content['transactionSpendingFlow']['data']);
    }

    /**
     * @test
     */
    public function can_retrieve_transactions_spending_flow_by_start_date_and_end_date(): void
    {
        $startDate = $this->getCurrentDateTime()->modify('- 5 day')->setTime(0, 0, 0, 0);
        $endDate = $this->getCurrentDateTime()->modify('- 2 day')->setTime(23, 59, 59, 59);
        $transactions = $this->filterFixtures(function ($entity) use ($startDate, $endDate) {
            return $entity instanceof Transaction
                && $entity->getWalletId() === $this->wallet->getId()
                && $entity->getDate() >= $startDate
                && $entity->getDate() <= $endDate
                && $entity->getType() === TransactionType::EXPENSE;
        });

        $values = [];
        foreach ($transactions as $transaction) {
            $key = $transaction->getDate()->format('Y-m-d');
            if (!isset($values[$key])) {
                $values[$key] = 0;
            }

            $values[$key] += $transaction->getValue();
        }

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'transactionSpendingFlow',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()]),
                    'startDate' => $startDate->format('Y-m-d H:i:s'),
                    'endDate' => $endDate->format('Y-m-d H:i:s')
                ]
            ],
            ['data']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('transactionSpendingFlow', $content);
        $this->assertArrayHasKey('data', $content['transactionSpendingFlow']);

        $expected = [];
        while ($startDate && $startDate <= $endDate) {
            $dateKey = $startDate->format('Y-m-d');
            if (!isset($values[$dateKey])) {
                $values[$dateKey] = 0;
            }

            $expected[] = [$dateKey, $values[$dateKey]];

            $startDate->modify('+ 1 day');
        }

        $this->assertEquals($expected, $content['transactionSpendingFlow']['data']);
    }

    /**
     * @test
     */
    public function can_retrieve_transactions_spending_flow_by_start_date_and_end_date_with_timezone(): void
    {
        $timezone = 'Europe/Sofia';

        $startDate = $this->getCurrentDateTime()
            ->modify('- 5 day')
            ->setTime(0, 0, 0, 0);

        $endDate = $this->getCurrentDateTime()
            ->modify('- 2 day')
            ->setTime(23, 59, 59, 59);

        $transactions = $this->filterFixtures(function ($entity) use ($startDate, $endDate) {
            return $entity instanceof Transaction
                && $entity->getWalletId() === $this->wallet->getId()
                && $entity->getType() === TransactionType::EXPENSE
                && $entity->getDate() >= $startDate
                && $entity->getDate() <= $endDate;
        });

        $values = [];
        foreach ($transactions as $transaction) {
            $key = $transaction->getDate()->setTimezone(new \DateTimeZone($timezone))->format('Y-m-d');
            if (!isset($values[$key])) {
                $values[$key] = 0;
            }

            $values[$key] += $transaction->getValue();
        }

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'transactionSpendingFlow',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()]),
                    'startDate' => $startDate->format('Y-m-d H:i:s'),
                    'endDate' => $endDate->format('Y-m-d H:i:s')
                ]
            ],
            ['data']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('transactionSpendingFlow', $content);
        $this->assertArrayHasKey('data', $content['transactionSpendingFlow']);

        $startDate->setTimezone(new \DateTimeZone($timezone));
        $endDate->setTimezone(new \DateTimeZone($timezone));

        $expected = [];
        while ($startDate && $startDate <= $endDate) {
            $dateKey = $startDate->format('Y-m-d');
            if (!isset($values[$dateKey])) {
                $values[$dateKey] = 0;
            }

                $expected[] = [$dateKey, $values[$dateKey]];

            $startDate->modify('+ 1 day');
        }

        $this->assertEquals($expected, $content['transactionSpendingFlow']['data']);
    }

    /**
     * @test
     */
    public function can_retrieve_category_spending_flow_by_start_date(): void
    {
        $startDate = $this->getCurrentDateTime()->modify('- 2 day')->setTime(0, 0, 0, 0);
        $transactions = $this->filterFixtures(function ($entity) use ($startDate) {
            return $entity instanceof Transaction
                && $entity->getWalletId() === $this->wallet->getId()
                && $entity->getDate() >= $startDate
                && $entity->getType() === TransactionType::EXPENSE;
        });

        $values = [];
        foreach ($transactions as $transaction) {
            $key = $transaction->getDate()->format('Y-m-d');
            $categoryId = $transaction->getCategory()->getId();
            if (!isset($values[$key])) {
                $values[$key] = [];
            }

            if (!isset($values[$key][$categoryId])) {
                $values[$key][$categoryId] = 0;
            }

            $values[$key][$categoryId] += $transaction->getValue();
        }

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->exactly(2))->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'categoriesSpendingFlow',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()]),
                    'startDate' => $startDate->format('Y-m-d H:i:s')
                ]
            ],
            ['data']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('categoriesSpendingFlow', $content);
        $this->assertArrayHasKey('data', $content['categoriesSpendingFlow']);

        $categories = $this->filterFixtures(function ($entity) use ($startDate) {
            return $entity instanceof Category
                && $entity->getUserId() === $this->user->getId();
        });

        $expected = [];
        $endDate = $this->getCurrentDateTime()->setTime(23, 59, 59);
        while ($startDate && $startDate <= $endDate) {
            $dateKey = $startDate->format('Y-m-d');
            if (!isset($values[$dateKey])) {
                $values[$dateKey] = [];
            }

            $recordData = [$dateKey];
            foreach ($categories as $category) {
                $recordData[] = (string)(isset($values[$dateKey][$category->getId()]) ? $values[$dateKey][$category->getId()] : 0);
            }

            $expected[] = $recordData;

            $startDate->modify('+ 1 day');
        }

        $this->assertEquals($expected, $content['categoriesSpendingFlow']['data']);
    }

    /**
     * @test
     */
    public function can_retrieve_category_spending_flow_by_start_date_and_end_date(): void
    {
        $startDate = $this->getCurrentDateTime()->modify('- 5 day')->setTime(0, 0, 0, 0);
        $endDate = $this->getCurrentDateTime()->modify('- 2 day')->setTime(23, 59, 59, 59);
        $transactions = $this->filterFixtures(function ($entity) use ($startDate, $endDate) {
            return $entity instanceof Transaction
                && $entity->getWalletId() === $this->wallet->getId()
                && $entity->getType() === TransactionType::EXPENSE
                && $entity->getDate() >= $startDate
                && $entity->getDate() <= $endDate;
        });

        $values = [];
        foreach ($transactions as $transaction) {
            $key = $transaction->getDate()->format('Y-m-d');
            $categoryId = $transaction->getCategory()->getId();
            if (!isset($values[$key])) {
                $values[$key] = [];
            }

            if (!isset($values[$key][$categoryId])) {
                $values[$key][$categoryId] = 0;
            }

            $values[$key][$categoryId] += $transaction->getValue();
        }

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->exactly(2))->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'categoriesSpendingFlow',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()]),
                    'startDate' => $startDate->format('Y-m-d H:i:s'),
                    'endDate' => $endDate->format('Y-m-d H:i:s')
                ]
            ],
            ['data']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('categoriesSpendingFlow', $content);
        $this->assertArrayHasKey('data', $content['categoriesSpendingFlow']);

        $categories = $this->filterFixtures(function ($entity) use ($startDate) {
            return $entity instanceof Category
                && $entity->getUserId() === $this->user->getId();
        });

        $expected = [];
        while ($startDate && $startDate <= $endDate) {
            $dateKey = $startDate->format('Y-m-d');
            if (!isset($values[$dateKey])) {
                $values[$dateKey] = [];
            }

            $recordData = [$dateKey];
            foreach ($categories as $category) {
                $recordData[] = (string)(isset($values[$dateKey][$category->getId()]) ? $values[$dateKey][$category->getId()] : 0);
            }

            $expected[] = $recordData;

            $startDate->modify('+ 1 day');
        }

        $this->assertEquals($expected, $content['categoriesSpendingFlow']['data']);
    }

    /**
     * @test
     */
    public function can_retrieve_category_spending_flow_by_start_date_and_end_date_with_timezone(): void
    {
        $timezone = 'Europe/Sofia';

        $startDate = $this->getCurrentDateTime()->modify('- 5 day')->setTime(0, 0, 0, 0);
        $endDate = $this->getCurrentDateTime()->modify('- 2 day')->setTime(23, 59, 59, 59);

        $transactions = $this->filterFixtures(function ($entity) use ($startDate, $endDate, $timezone) {
            return $entity instanceof Transaction
                && $entity->getWalletId() === $this->wallet->getId()
                && $entity->getType() === TransactionType::EXPENSE
                && $entity->getDate() >= $startDate
                && $entity->getDate() <= $endDate;
        });

        $values = [];
        foreach ($transactions as $transaction) {
            $key = $transaction
                ->getDate()
                ->setTimezone(new \DateTimeZone($timezone))
                ->format('Y-m-d');

            $categoryId = $transaction->getCategory()->getId();
            if (!isset($values[$key])) {
                $values[$key] = [];
            }

            if (!isset($values[$key][$categoryId])) {
                $values[$key][$categoryId] = 0;
            }

            $values[$key][$categoryId] += $transaction->getValue();
        }

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->exactly(2))->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $startDate->setTimezone(new \DateTimeZone($timezone));
        $endDate->setTimezone(new \DateTimeZone($timezone));

        $this->query(
            'categoriesSpendingFlow',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()]),
                    'startDate' => $startDate->format('Y-m-d H:i:s'),
                    'endDate' => $endDate->format('Y-m-d H:i:s')
                ]
            ],
            ['data']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('categoriesSpendingFlow', $content);
        $this->assertArrayHasKey('data', $content['categoriesSpendingFlow']);

        $categories = $this->filterFixtures(function ($entity) {
            return $entity instanceof Category
                && $entity->getUserId() === $this->user->getId();
        });

        $startDate->setTimezone(new \DateTimeZone($timezone));
        $endDate->setTimezone(new \DateTimeZone($timezone));

        $expected = [];
        while ($startDate && $startDate <= $endDate) {
            $dateKey = $startDate->format('Y-m-d');
            if (!isset($values[$dateKey])) {
                $values[$dateKey] = [];
            }

            $recordData = [$dateKey];
            foreach ($categories as $category) {
                $recordData[] = (string)(isset($values[$dateKey][$category->getId()]) ? $values[$dateKey][$category->getId()] : 0);
            }

            $expected[] = $recordData;

            $startDate->modify('+ 1 day');
        }

        $this->assertEquals($expected, $content['categoriesSpendingFlow']['data']);
    }

    /**
     * @test
     */
    public function can_retrieve_category_spending_pie_chart_by_start_date(): void
    {
        $startDate = $this->getCurrentDateTime()->modify('- 5 day')->setTime(0, 0, 0, 0);
        $transactions = $this->filterFixtures(function ($entity) use ($startDate) {
            return $entity instanceof Transaction
                && $entity->getType() === TransactionType::EXPENSE
                && $entity->getWalletId() === $this->wallet->getId()
                && $entity->getDate() >= $startDate;
        });

        $values = [];
        foreach ($transactions as $transaction) {
            $categoryName = $transaction->getCategory()->getName();
            if (!isset($values[$categoryName])) {
                $values[$categoryName] = 0;
            }

            $values[$categoryName] += $transaction->getValue();
        }

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'categoriesSpendingPieChart',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()]),
                    'startDate' => $startDate->format('Y-m-d H:i:s'),
                    'type' => new EnumType('EXPENSE')
                ]
            ],
            ['data']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('categoriesSpendingPieChart', $content);
        $this->assertArrayHasKey('data', $content['categoriesSpendingPieChart']);

        $expected = [];
        foreach ($values as $key => $value) {
            $expected[] = [$key, (string)$value];
        }

        $this->assertEquals($expected, $content['categoriesSpendingPieChart']['data']);
    }

    /**
     * @test
     */
    public function can_retrieve_category_spending_pie_chart_by_start_date_and_end_date(): void
    {
        $startDate = $this->getCurrentDateTime()->modify('- 5 day')->setTime(0, 0, 0, 0);
        $endDate = $this->getCurrentDateTime()->modify('- 2 day')->setTime(0, 0, 0, 0);
        $transactions = $this->filterFixtures(function ($entity) use ($startDate,$endDate) {
            return $entity instanceof Transaction
                && $entity->getType() === TransactionType::EXPENSE
                && $entity->getWalletId() === $this->wallet->getId()
                && $entity->getDate() >= $startDate
                && $entity->getDate() <= $endDate;
        });

        $values = [];
        foreach ($transactions as $transaction) {
            $categoryName = $transaction->getCategory()->getName();
            if (!isset($values[$categoryName])) {
                $values[$categoryName] = 0;
            }

            $values[$categoryName] += $transaction->getValue();
        }

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'categoriesSpendingPieChart',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()]),
                    'startDate' => $startDate->format('Y-m-d H:i:s'),
                    'endDate' => $endDate->format('Y-m-d H:i:s'),
                    'type' => new EnumType('EXPENSE')
                ]
            ],
            ['data']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('categoriesSpendingPieChart', $content);
        $this->assertArrayHasKey('data', $content['categoriesSpendingPieChart']);

        $expected = [];
        foreach ($values as $key => $value) {
            $expected[] = [$key, (string)$value];
        }

        $this->assertEquals($expected, $content['categoriesSpendingPieChart']['data']);
    }

    /**
     * @test
     */
    public function can_retrieve_category_spending_pie_chart_by_start_date_and_end_date_with_timezone(): void
    {
        $startDate = $this->getCurrentDateTime()->modify('- 5 day')->setTime(0, 0, 0, 0);
        $endDate = $this->getCurrentDateTime()->modify('- 2 day')->setTime(0, 0, 0, 0);

        $transactions = $this->filterFixtures(function ($entity) use ($startDate,$endDate) {
            return $entity instanceof Transaction
                && $entity->getType() === TransactionType::EXPENSE
                && $entity->getWalletId() === $this->wallet->getId()
                && $entity->getDate() >= $startDate
                && $entity->getDate() <= $endDate;
        });

        $values = [];
        foreach ($transactions as $transaction) {
            $categoryName = $transaction->getCategory()->getName();
            if (!isset($values[$categoryName])) {
                $values[$categoryName] = 0;
            }

            $values[$categoryName] += $transaction->getValue();
        }

        $authServiceMock = $this->createMock(AuthorizationService::class);
        $authServiceMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $authServiceMock->expects($this->once())->method('getCurrentUser')->willReturn($this->user);
        $this->client->getContainer()->set(AuthorizationService::class, $authServiceMock);

        $this->query(
            'categoriesSpendingPieChart',
            [
                'input' => [
                    'walletIds' => new IntegerArrayType([$this->wallet->getId()]),
                    'startDate' => $startDate->format('Y-m-d H:i:s'),
                    'endDate' => $endDate->format('Y-m-d H:i:s'),
                    'timezone' => 'Europe/Sofia',
                    'type' => new EnumType('EXPENSE')
                ]
            ],
            ['data']
        );

        $response = $this->client->getResponse();
        $this->assertOk($response);
        $content = $this->getResponseContent($response);
        $this->assertArrayHasKey('categoriesSpendingPieChart', $content);
        $this->assertArrayHasKey('data', $content['categoriesSpendingPieChart']);

        $expected = [];
        foreach ($values as $key => $value) {
            $expected[] = [$key, (string)$value];
        }

        $this->assertEquals($expected, $content['categoriesSpendingPieChart']['data']);
    }
}
