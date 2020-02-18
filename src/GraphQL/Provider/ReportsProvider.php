<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\Entity\Transaction;
use App\Exception\GraphQLException;
use App\GraphQL\Input\Transaction\TransactionRecordsRequest;
use App\GraphQL\Types\AreaChart;
use App\GraphQL\Types\DateTime;
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

        /** @var Transaction[] $transactions */
        $result = $this->transactionRepository->findSpendingFlow($input);

        $header = ['Date', 'Money'];
        $reportData = [];

        if ($input->date) {
            $backDate = $this->createFromFormat($input->date, $this->dateFormat, null, true);
        } else {
            $backDate = count($result)
                ? $this->createFromFormat(
                    $result[0]['date'],
                    $this->dateFormat,
                    null,
                    true
                )
                : null;
        }
        $currentDate = $this->getCurrentDateTime()->setTime(23, 59);

        while ($backDate && $backDate <= $currentDate) {
            $found = false;
            foreach ($result as $row) {
                if ($row['date'] === $backDate->format('Y-m-d')) {
                    $reportData[] = [$backDate->format('Y-m-d'), $row['total']];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $reportData[] = [$backDate->format('Y-m-d'), 0];
            }
            $backDate->modify('+ 1 day');
        }

        return AreaChart::fromData($header, $reportData);
    }
}
