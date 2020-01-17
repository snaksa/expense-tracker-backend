<?php declare (strict_types=1);

namespace App\Traits;

use \DateTime;
use \DateTimeInterface;
use DateTimeZone;

trait DateUtils
{
    protected $dateTimeFormat = 'Y-m-d H:i:s';
    protected $dateFormat = 'Y-m-d';

    /**
     * @param DateTimeInterface $date
     * @param string|null $format
     * @return string
     */
    public function formatDate(DateTimeInterface $date, string $format = null)
    {
        $format = $format ?? $this->dateTimeFormat;
        return $date->format($format);
    }

    /**
     * @return DateTime
     * @throws \Exception
     */
    public function getCurrentDateTime(): DateTime
    {
        return new DateTime('now', $this->getUTCTimeZone());
    }

    /**
     * @return DateTimeZone
     */
    public function getUTCTimeZone(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }
}
