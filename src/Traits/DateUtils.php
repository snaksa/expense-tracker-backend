<?php declare (strict_types=1);

namespace App\Traits;

use \DateTime;
use \DateTimeInterface;
use DateTimeZone;

trait DateUtils
{
    protected string $dateTimeFormat = 'Y-m-d H:i:s';
    protected string $dateFormat = 'Y-m-d';

    public function createFromFormat(
        string $date,
        ?string $format = null,
        DateTimeZone $timezone = null,
        bool $roundHours = false
    ): ?DateTime {
        $format = $format ?? $this->dateTimeFormat;
        $timezone = $timezone ?? $this->getUTCTimeZone();

        $dt = DateTime::createFromFormat($format, $date, $timezone);
        if ($roundHours && $dt) {
            $dt->setTime(0, 0);
        }

        return $dt ? $dt : null;
    }


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
