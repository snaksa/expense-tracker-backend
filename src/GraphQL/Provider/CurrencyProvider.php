<?php declare(strict_types=1);

namespace App\GraphQL\Provider;

use App\GraphQL\Types\Currency;
use Symfony\Component\Intl\Currencies;
use Overblog\GraphQLBundle\Annotation as GQL;

/**
 * @GQL\Provider
 *
 * @package App\GraphQL\Provider
 */
class CurrencyProvider
{
    /**
     * @GQL\Query(type="[Currency]")
     *
     * @return Currency[]
     */
    public function currencies(): array
    {
        $currencies = Currencies::getNames();
        $result = [];
        foreach ($currencies as $key => $name) {
            $result[] = new Currency($key, $name);
        }

        return $result;
    }
}
