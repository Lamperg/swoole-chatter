<?php

namespace App\Utilities;

use DateTime;
use Exception;
use DateTimeZone;

class TimeHelper
{
    public const FORMAT = "Y-m-d H:i:s";
    public const TIMEZONE = "Europe/Kiev";

    /**
     * Formatted provided time according to system rules.
     *
     * @param DateTime $dateTime
     * @return string
     */
    public static function format(DateTime $dateTime): string
    {
        $timezone = new DateTimeZone(static::TIMEZONE);
        $dateTime->setTimezone($timezone);

        return $dateTime->format(static::FORMAT);
    }

    /**
     * Creates current time object with system timezone.
     *
     * @return DateTime
     * @throws Exception
     */
    public static function now(): DateTime
    {
        return new DateTime("now", new DateTimeZone(static::TIMEZONE));
    }
}
