<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Entity\Transaction;
use App\Exception\GraphQLException;
use App\GraphQL\Input\Category\CategoryRecordsRequest;
use App\GraphQL\Input\Transaction\TransactionRecordsRequest;
use App\GraphQL\Types\AreaChart;
use App\Repository\CategoryRepository;
use App\Repository\TransactionRepository;
use App\Services\AuthorizationService;
use App\Traits\DateUtils;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Provider
 *
 * @package App\GraphQL\Provider
 */
class ReportsProvider
{
    use DateUtils;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * @var AuthorizationService
     */
    private $authService;

    public function __construct(
        CategoryRepository $categoryRepository,
        TransactionRepository $transactionRepository,
        AuthorizationService $authService
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->transactionRepository = $transactionRepository;
        $this->authService = $authService;
    }

    /**
     * @GQL\Query(type="AreaChart")
     *
     * @param TransactionRecordsRequest $input
     *
     * @return AreaChart
     * @throws \Exception
     */
    public function transactionSpendingFlow(TransactionRecordsRequest $input): AreaChart
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        $transactions = $this->transactionRepository->findSpendingFlow(
            $input,
            $this->authService->getCurrentUser()->getId()
        );

        $header = ['Date', 'Money'];

        if ($input->startDate) {
            $startDate = $this->createFromFormat($input->startDate, $this->dateTimeFormat, null);
        } else {
            $startDate = count($transactions)
                ? $transactions[0]['date']->setTime(0, 0)
                : null;
        }

        $endDate = $input->endDate ?
            $this->createFromFormat($input->endDate, $this->dateTimeFormat, null) :
            $this->getCurrentDateTime()->setTime(23, 59, 59);

        if ($input->timezone) {
            if ($startDate) {
                $startDate->setTimezone(new \DateTimeZone($input->timezone));
            }
            if ($endDate) {
                $endDate->setTimezone(new \DateTimeZone($input->timezone));
            }
        }

        $result = [];
        foreach ($transactions as $transaction) {
            $date = $transaction['date'];

            if ($input->timezone) {
                $date->setTimeZone(new \DateTimeZone(($input->timezone)));
            }

            if (!$startDate) {
                $startDate = $date;
            }

            $key = $date->format('Y-m-d');
            if (!isset($result[$key])) {
                $result[$key] = 0;
            }

            $result[$key] += $transaction['value'];
        }

        $data = [];
        while ($startDate && $startDate <= $endDate) {
            $dateKey = $startDate->format('Y-m-d');
            if (!isset($result[$dateKey])) {
                $result[$dateKey] = 0;
            }

            $data[] = [$dateKey, $result[$dateKey]];

            $startDate->modify('+ 1 day');
        }

        return AreaChart::fromData($header, $data);
    }

    /**
     * @GQL\Query(type="AreaChart")
     *
     * @param CategoryRecordsRequest $input
     *
     * @return AreaChart
     * @throws \Exception
     */
    public function categoriesSpendingFlow(CategoryRecordsRequest $input): AreaChart
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        /** @var Transaction[] $transactions */
        $transactions = $this->transactionRepository->findCategorySpendingFlow(
            $input,
            $this->authService->getCurrentUser()->getId()
        );

        $categories = $this->categoryRepository->findUserCategories($this->authService->getCurrentUser());

        $colors = [];
        $header = ['Date'];

        foreach ($categories as $category) {
            $header[] = $category->getName();
            $colors[] = $category->getColor();
        }

        if ($input->startDate) {
            $startDate = $this->createFromFormat($input->startDate, $this->dateTimeFormat, null);
        } else {
            $startDate = count($transactions)
                ? $transactions[0]['date']->setTime(0, 0) :
                $this->getCurrentDateTime()->setTime(23, 59, 59);
        }


        $endDate = $input->endDate ?
            $this->createFromFormat($input->endDate, $this->dateTimeFormat, null) :
            $this->getCurrentDateTime()->setTime(23, 59, 59);

        if ($input->timezone) {
            if ($startDate) {
                $startDate->setTimezone(new \DateTimeZone($input->timezone));
            }
            if ($endDate) {
                $endDate->setTimezone(new \DateTimeZone($input->timezone));
            }
        }

        $result = [];
        foreach ($transactions as $transaction) {
            $date = $transaction['date'];
            $categoryId = $transaction['category_id'];

            if ($input->timezone) {
                $date->setTimeZone(new \DateTimeZone(($input->timezone)));
            }

            $key = $date->format('Y-m-d');
            if (!isset($result[$key])) {
                $result[$key] = [];
            }

            if (!isset($result[$key][$categoryId])) {
                $result[$key][$categoryId] = 0;
            }

            $result[$key][$categoryId] += $transaction['value'];
        }

        $data = [];
        while ($startDate && $startDate <= $endDate) {
            $dateKey = $startDate->format('Y-m-d');
            if (!isset($result[$dateKey])) {
                $result[$dateKey] = [];
            }

            $recordData = [$dateKey];
            foreach ($categories as $category) {
                $recordData[] = isset($result[$dateKey][$category->getId()])
                    ? $result[$dateKey][$category->getId()]
                    : 0;
            }

            $data[] = $recordData;

            $startDate->modify('+ 1 day');
        }

        return AreaChart::fromData($header, $data, $colors);
    }

    /**
     * @GQL\Query(type="AreaChart")
     *
     * @param CategoryRecordsRequest $input
     *
     * @return AreaChart
     * @throws \Exception
     */
    public function categoriesSpendingPieChart(CategoryRecordsRequest $input): AreaChart
    {
        if (!$this->authService->isLoggedIn()) {
            throw GraphQLException::fromString('Unauthorized access!');
        }

        /** @var Transaction[] $transactions */
        $result = $this->transactionRepository->findCategorySpendingPie(
            $input,
            $this->authService->getCurrentUser()->getId()
        );

        $header = ['Category', 'Money'];
        $colors = [];
        $reportData = [];

        $categoryColors = [];
        $data = [];
        foreach ($result as $row) {
            if (!isset($data[$row['category']])) {
                $data[$row['category']] = 0;
            }
            $data[$row['category']] += $row['value'];
            $categoryColors[$row['category']] = $row['color'];
        }


        foreach ($data as $key => $value) {
            $reportData[] = [$key, $value];
            $colors[] = $categoryColors[$key];
        }

        return AreaChart::fromData($header, $reportData, $colors);
    }
}
